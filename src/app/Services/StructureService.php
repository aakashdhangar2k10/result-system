<?php

namespace App\Services;

use App\Models\Semester\SemesterExam;
use App\Support\DepartmentAccess;
use App\Support\Term;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class StructureService
{
    // the steps of the flow, in order: key => [label, permission needed to open it]
    public const STEPS = [
        'rule' => ['Result rule', 'sessiongrades.view'],
        'semesters' => ['Semesters', 'coursesemesters.view'],
        'subjects' => ['Subjects', 'subjects.view'],
        'session_subjects' => ['Session subjects', 'sessionsubjects.view'],
        'students' => ['Students', 'students.view'],
        'lists' => ['Student lists', 'studentlists.view'],
        'exams' => ['Exams', 'semesterexams.view'],
        'results' => ['Results', 'subjectresults.view'],
        'sgpa' => ['SGPA', 'subjectresults.view'],
    ];

    public function build(): array
    {
        $scoped = ! DepartmentAccess::unrestricted();

        $departments = DB::table('mark_department')
            ->where('is_delete', false)
            ->when($scoped, fn ($q) => $q->whereIn('deptid', DepartmentAccess::departmentIds()))
            ->orderBy('fullname')
            ->get();

        $courses = DB::table('mark_course')
            ->where('is_delete', false)
            ->whereIn('deptid', $departments->pluck('deptid'))
            ->orderBy('shortname')
            ->get();

        $courseIds = $courses->pluck('courseid');

        $batches = DB::table('mark_batch as b')
            ->leftJoin('mark_branch as br', 'br.branchid', '=', 'b.branchid')
            ->where('b.is_delete', false)
            ->whereIn('b.courseid', $courseIds)
            ->orderByDesc('b.startyear')
            ->get(['b.batchid', 'b.courseid', 'b.startyear', 'b.endyear', 'br.shortname as branch']);

        // the semesters of each course + session, in term order
        $semesters = DB::table('mark_semester as s')
            ->join('mark_semester_list as l', 'l.semid', '=', 's.semid')
            ->where('s.is_delete', false)
            ->whereIn('s.courseid', $courseIds)
            ->whereNotNull('s.batchid')
            ->get(['s.semesterid', 's.courseid', 's.batchid', 's.semid', 's.semestermonth', 'l.roman'])
            ->groupBy(fn ($s) => $s->courseid . '-' . $s->batchid)
            ->map(fn ($group) => $group->sortBy(fn ($s) => Term::number($s->roman))->values());

        $subjectCount = DB::table('mark_subject')
            ->where('is_delete', false)->where('active', true)
            ->whereIn('courseid', $courseIds)
            ->select('courseid', 'semid', DB::raw('count(*) as total'))
            ->groupBy('courseid', 'semid')
            ->get()
            ->mapWithKeys(fn ($r) => [$r->courseid . '-' . $r->semid => (int) $r->total]);

        $sessionSubjects = DB::table('mark_session_subject')
            ->where('is_delete', false)
            ->whereIn('courseid', $courseIds)
            ->get(['courseid', 'batchid', 'semid'])
            ->mapWithKeys(fn ($r) => [$r->courseid . '-' . $r->batchid . '-' . $r->semid => true]);

        $students = DB::table('mark_student')
            ->where('is_delete', false)->where('active', true)
            ->whereIn('courseid', $courseIds)
            ->select('batchid', DB::raw('count(*) as total'))
            ->groupBy('batchid')
            ->pluck('total', 'batchid');

        $lists = DB::table('mark_student_list as l')
            ->join('mark_semester as s', 's.semesterid', '=', 'l.semesterid')
            ->leftJoin('mark_student_list_item as i', 'i.studentlistid', '=', 'l.studentlistid')
            ->where('l.is_delete', false)
            ->whereIn('s.courseid', $courseIds)
            ->groupBy('l.studentlistid', 'l.semesterid', 'l.is_locked')
            ->get(['l.semesterid', 'l.is_locked', DB::raw('count(i.studentid) as members')])
            ->keyBy('semesterid');

        $rules = DB::table('mark_session_grade')
            ->where('is_delete', false)
            ->whereIn('courseid', $courseIds)
            ->get(['courseid', 'batchid'])
            ->mapWithKeys(fn ($r) => [$r->courseid . '-' . $r->batchid => true]);

        $examRows = DB::table('mark_semester_exam')
            ->where('is_delete', false)
            ->whereIn('courseid', $courseIds)
            ->whereNotNull('batchid')
            ->orderBy('attempt')->orderBy('semesterexamid')
            ->get(['semesterexamid', 'courseid', 'batchid', 'semid', 'attempt', 'exammonth']);

        $examGroups = $examRows->groupBy(fn ($e) => $e->courseid . '-' . $e->batchid . '-' . $e->semid);
        $examIds = $examRows->pluck('semesterexamid');

        $entered = DB::table('mark_result')
            ->where('is_delete', false)
            ->whereIn('semesterexamid', $examIds)
            ->select('semesterexamid', DB::raw('count(*) as total'))
            ->groupBy('semesterexamid')
            ->pluck('total', 'semesterexamid');

        $calculated = DB::table('mark_semester_result')
            ->whereIn('semesterexamid', $examIds)
            ->get(['semesterexamid', 'is_stale'])
            ->keyBy('semesterexamid');

        $result = [];

        foreach ($departments as $department) {
            $totals = ['courses' => 0, 'sessions' => 0, 'subjects' => 0, 'students' => 0, 'calculated' => 0, 'ok' => 0, 'all' => 0];
            $courseList = [];

            foreach ($courses->where('deptid', $department->deptid) as $course) {
                $terms = (int) $course->total_terms;
                $word = $course->structure_type === 'year' ? 'Year' : 'Semester';
                $sessionList = [];

                $totals['courses']++;
                $totals['subjects'] += $subjectCount
                    ->filter(fn ($value, $key) => str_starts_with($key, $course->courseid . '-'))
                    ->sum();

                foreach ($batches->where('courseid', $course->courseid) as $batch) {
                    $key = $course->courseid . '-' . $batch->batchid;
                    $semRows = [];

                    foreach ($semesters->get($key, collect()) as $sem) {
                        $subjectsN = $subjectCount[$course->courseid . '-' . $sem->semid] ?? 0;
                        $list = $lists->get($sem->semesterid);

                        $exams = [];
                        foreach ($examGroups->get($key . '-' . $sem->semid, collect()) as $e) {
                            $c = $calculated->get($e->semesterexamid);

                            $exams[] = [
                                'id' => (int) $e->semesterexamid,
                                'label' => (SemesterExam::ORDINALS[$e->attempt] ?? $e->attempt) . ' (' . Carbon::parse($e->exammonth)->format('F-Y') . ')',
                                'entered' => (int) ($entered[$e->semesterexamid] ?? 0),
                                'calculated' => (bool) $c,
                                'stale' => (bool) ($c->is_stale ?? false),
                            ];

                            if ($c) {
                                $totals['calculated']++;
                            }
                        }

                        // results are judged on the first exam: later attempts cover only the students who failed
                        $resultStatus = 'na';
                        $sgpaStatus = 'na';

                        if ($exams) {
                            $first = $exams[0];

                            $resultStatus = $first['entered'] === 0
                                ? 'miss'
                                : (($subjectsN > 0 && $first['entered'] >= $subjectsN) ? 'ok' : 'part');

                            $withResults = array_values(array_filter($exams, fn ($x) => $x['entered'] > 0));

                            if ($withResults) {
                                $sgpaStatus = collect($withResults)->contains('stale', true)
                                    ? 'stale'
                                    : (collect($withResults)->every(fn ($x) => $x['calculated']) ? 'ok' : 'miss');
                            }
                        }

                        $semRows[] = [
                            'semesterid' => (int) $sem->semesterid,
                            'semid' => (int) $sem->semid,
                            'label' => $sem->roman . ' ' . $word,
                            'month' => $sem->semestermonth ? Carbon::parse($sem->semestermonth)->format('F-Y') : '—',
                            'subjects' => $subjectsN,
                            'session_subject' => isset($sessionSubjects[$key . '-' . $sem->semid]),
                            'list' => $list ? ['members' => (int) $list->members, 'locked' => (bool) $list->is_locked] : null,
                            'exams' => $exams,
                            'results' => $resultStatus,
                            'sgpa' => $sgpaStatus,
                        ];
                    }

                    $n = count($semRows);
                    $count = fn (callable $test) => count(array_filter($semRows, $test));

                    $studentCount = (int) ($students[$batch->batchid] ?? 0);
                    $withExams = array_filter($semRows, fn ($r) => $r['exams']);
                    $sgpaPool = array_filter($semRows, fn ($r) => $r['sgpa'] !== 'na');

                    $steps = [
                        'rule' => $this->step('rule', isset($rules[$key]) ? 'ok' : 'miss', isset($rules[$key]) ? 'set' : 'not set', $course->courseid, $batch->batchid),
                        'semesters' => $this->ratio('semesters', $n, $terms, $course->courseid, $batch->batchid),
                        'subjects' => $this->ratio('subjects', $count(fn ($r) => $r['subjects'] > 0), $n, $course->courseid, $batch->batchid),
                        'session_subjects' => $this->ratio('session_subjects', $count(fn ($r) => $r['session_subject']), $n, $course->courseid, $batch->batchid),
                        'students' => $this->step('students', $studentCount > 0 ? 'ok' : 'miss', (string) $studentCount, $course->courseid, $batch->batchid),
                        'lists' => $this->ratio('lists', $count(fn ($r) => $r['list'] !== null), $n, $course->courseid, $batch->batchid),
                        'exams' => $this->ratio('exams', $count(fn ($r) => $r['exams']), $n, $course->courseid, $batch->batchid),
                        'results' => $withExams
                            ? $this->ratio('results', count(array_filter($withExams, fn ($r) => $r['results'] === 'ok')), count($withExams), $course->courseid, $batch->batchid)
                            : $this->step('results', 'na', '—', $course->courseid, $batch->batchid),
                        'sgpa' => $sgpaPool
                            ? (count(array_filter($sgpaPool, fn ($r) => $r['sgpa'] === 'stale')) > 0
                                ? $this->step('sgpa', 'stale', 'out of date', $course->courseid, $batch->batchid)
                                : $this->ratio('sgpa', count(array_filter($sgpaPool, fn ($r) => $r['sgpa'] === 'ok')), count($sgpaPool), $course->courseid, $batch->batchid))
                            : $this->step('sgpa', 'na', '—', $course->courseid, $batch->batchid),
                    ];

                    foreach ($steps as $step) {
                        if ($step['status'] !== 'na') {
                            $totals['all']++;
                            $totals['ok'] += $step['status'] === 'ok' ? 1 : 0;
                        }
                    }

                    $totals['sessions']++;
                    $totals['students'] += $studentCount;

                    $sessionList[] = [
                        'id' => (int) $batch->batchid,
                        'label' => $batch->startyear . '–' . $batch->endyear,
                        'branch' => $batch->branch,
                        'students' => $studentCount,
                        'steps' => $steps,
                        'next' => collect($steps)->first(fn ($s) => in_array($s['status'], ['miss', 'part', 'stale'], true)),
                        'semesters' => $semRows,
                    ];
                }

                $courseList[] = [
                    'id' => (int) $course->courseid,
                    'shortname' => $course->shortname,
                    'fullname' => $course->fullname,
                    'structure' => $terms . ' ' . strtolower($word) . ($terms === 1 ? '' : 's'),
                    'has_branch' => (bool) $course->has_branch,
                    'sessions' => $sessionList,
                ];
            }

            $totals['percent'] = $totals['all'] > 0 ? (int) round($totals['ok'] / $totals['all'] * 100) : 0;

            $result[] = [
                'id' => (int) $department->deptid,
                'fullname' => $department->fullname,
                'shortname' => $department->shortname,
                'active' => (bool) $department->active,
                'courses' => $courseList,
                'totals' => $totals,
            ];
        }

        return [
            'departments' => $result,
            'steps' => self::STEPS,
            'canSwitch' => DepartmentAccess::switcher()['show'],
            'selectedDepartment' => DepartmentAccess::selected(),
        ];
    }

    // ------------------------------------------------------------------ helpers

    // done of total: complete, partial or missing
    private function ratio(string $key, int $done, int $total, int $course, int $batch): array
    {
        if ($total === 0) {
            return $this->step($key, 'miss', '0', $course, $batch);
        }

        return $this->step($key, $done === $total ? 'ok' : ($done > 0 ? 'part' : 'miss'), "{$done}/{$total}", $course, $batch);
    }

    private function step(string $key, string $status, string $text, int $course, int $batch): array
    {
        $filter = ['courseid' => $course, 'batchid' => $batch];

        $url = match ($key) {
            'rule' => route('session-grades.index', ['courseid' => $course]),
            'semesters' => route('course-semesters.index', $filter),
            'subjects' => route('subjects.index', ['courseid' => $course]),
            'session_subjects' => route('session-subjects.index', $filter),
            'students' => route('students.index', $filter),
            'lists' => route('student-lists.index', $filter),
            'exams' => route('semester-exams.index', $filter),
            default => route('results.index', $filter),
        };

        return [
            'key' => $key,
            'label' => self::STEPS[$key][0],
            'perm' => self::STEPS[$key][1],
            'status' => $status,
            'text' => $text,
            'url' => $url,
        ];
    }
}