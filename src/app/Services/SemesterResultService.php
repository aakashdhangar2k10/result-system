<?php

namespace App\Services;

use App\Models\CourseSession\SessionGrade;
use App\Models\Result\SemesterResult;
use App\Models\Result\SemesterResultItem;
use App\Models\Result\SubjectResult;
use App\Models\Semester\SemesterExam;
use App\Models\Student\Student;
use App\Models\Subject\Subject;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SemesterResultService
{
    public const COLORS = ['pass' => 'success', 'promoted' => 'primary', 'fail' => 'danger'];

    // the result rule of the course + session (Course Session → Session Grade)
    public function rule(SemesterExam $exam): ?SessionGrade
    {
        return SessionGrade::where('courseid', $exam->courseid)
            ->where('batchid', $exam->batchid)
            ->first();
    }

    // ------------------------------------------------------------------ calculating

    /** Calculates and saves SGPA + result of every student of the exam. Returns ['errors' => [...]] or the counts. */
    public function calculate(SemesterExam $exam, int $userId): array
    {
        $plan = $this->prepare($exam);

        if ($plan['errors']) {
            return ['errors' => $plan['errors']];
        }

        $rule = $plan['rule'];
        $counts = ['pass' => 0, 'promoted' => 0, 'fail' => 0];
        $items = [];

        foreach ($plan['need'] as $studentId => $subjectIds) {
            $score = $this->score($subjectIds, $plan['latest'][$studentId], $plan['subjects'], $rule);
            $counts[$score['status']]++;

            $items[] = [
                'studentid' => $studentId,
                'subjects_count' => count($subjectIds),
                'credits' => $score['credits'],
                'earned_credits' => $score['earned'],
                'credit_points' => $score['points'],
                'sgpa' => $score['sgpa'],
                'backlogs' => $score['backlogs'],
                'backlog_subjects' => $score['backlog_subjects'] !== '' ? mb_substr($score['backlog_subjects'], 0, 250) : null,
                'status' => $score['status'],
                'result_label' => $score['label'],
            ];
        }

        DB::transaction(function () use ($exam, $rule, $items, $counts, $userId) {
            // people calculating the same exam wait for each other
            SemesterExam::lockForUpdate()->find($exam->semesterexamid);

            $header = SemesterResult::firstOrNew(['semesterexamid' => $exam->semesterexamid]);

            $header->fill([
                'courseid' => $exam->courseid,
                'batchid' => $exam->batchid,
                'semid' => $exam->semid,
                'student_count' => count($items),
                'pass_count' => $counts['pass'],
                'promoted_count' => $counts['promoted'],
                'fail_count' => $counts['fail'],
                'max_backlogs' => $rule->max_backlogs,
                'min_sgpa' => $rule->min_sgpa,
                'sgpa_with_failed' => $rule->sgpa_with_failed,
                'pass_label' => $rule->pass_label,
                'promoted_label' => $rule->promoted_label,
                'fail_label' => $rule->fail_label,
                'is_stale' => false,
                'calculated_by' => $userId,
                'calculated_at' => now(),
            ])->save();

            SemesterResultItem::where('semresultid', $header->semresultid)->delete();

            $now = now();
            $rows = array_map(fn($item) => $item + [
                'semresultid' => $header->semresultid,
                'created_at' => $now,
                'updated_at' => $now,
            ], $items);

            foreach (array_chunk($rows, 200) as $chunk) {
                SemesterResultItem::insert($chunk);
            }
        });

        return ['errors' => [], 'count' => count($items), 'counts' => $counts, 'rule' => $rule];
    }

    /** One student: the SGPA and the result under the rule. */
    public function score(array $subjectIds, array $latestOfStudent, Collection $subjects, object $rule): array
    {
        $credits = 0.0;
        $earned = 0.0;
        $points = 0.0;
        $backlogs = [];

        foreach ($subjectIds as $id) {
            $row = $latestOfStudent[$id];
            $credit = (float) $row->subject_credit;

            if ($row->is_pass) {
                $credits += $credit;
                $earned += $credit;
                $points += $credit * (int) $row->grade_point;
            } else {
                $backlogs[] = $subjects[$id]->subjectcode;

                // a failed subject adds its credits to the divisor, with 0 points
                if ($rule->sgpa_with_failed) {
                    $credits += $credit;
                }
            }
        }

        $sgpa = $credits > 0 ? round($points / $credits, 2) : 0.0;
        $count = count($backlogs);
        $min = $rule->min_sgpa === null ? null : (float) $rule->min_sgpa;

        // the rule: too many backlogs or a low SGPA = fail, no backlog = pass, otherwise promoted
        if ($count > (int) $rule->max_backlogs || ($min !== null && $sgpa < $min)) {
            $status = 'fail';
        } elseif ($count === 0) {
            $status = 'pass';
        } else {
            $status = 'promoted';
        }

        return [
            'credits' => round($credits, 1),
            'earned' => round($earned, 1),
            'points' => round($points, 2),
            'sgpa' => $sgpa,
            'backlogs' => $count,
            'backlog_subjects' => implode(', ', $backlogs),
            'status' => $status,
            'label' => $rule->{$status . '_label'},
        ];
    }

    // "Why is this student PROMOTED?"
    public function explain(object $item, object $rule): string
    {
        $max = (int) $rule->max_backlogs;
        $min = $rule->min_sgpa === null ? null : (float) $rule->min_sgpa;
        $minText = $min === null ? '' : rtrim(rtrim(number_format($min, 2, '.', ''), '0'), '.');

        if ($item->status === 'pass') {
            return 'No backlog' . ($min !== null ? " and the SGPA is not below {$minText}" : '') . '.';
        }

        if ($item->status === 'promoted') {
            return "{$item->backlogs} backlog(s), within the {$max} allowed.";
        }

        $reasons = [];

        if ($item->backlogs > $max) {
            $reasons[] = "{$item->backlogs} backlog(s), more than the {$max} allowed";
        }

        if ($min !== null && (float) $item->sgpa < $min) {
            $reasons[] = 'SGPA ' . number_format((float) $item->sgpa, 2) . " is below the minimum {$minText}";
        }

        return implode('; ', $reasons) . '.';
    }

    /** The subject lines of one student, for the popup: [['subject' => Subject, 'row' => latest result or null], ...]. */
    public function breakdown(SemesterExam $exam, int $studentId): array
    {
        [$subjects, $need, $latest] = $this->gather($exam, [$studentId]);

        $lines = [];

        foreach ($need[$studentId] ?? [] as $subjectId) {
            $lines[] = ['subject' => $subjects[$subjectId], 'row' => $latest[$studentId][$subjectId] ?? null];
        }

        return $lines;
    }

    // ------------------------------------------------------------------ checks

    /** Checks that SGPA can be calculated. Returns ['errors' => [...]] or the data the calculation needs. */
    public function prepare(SemesterExam $exam): array
    {
        $rule = $this->rule($exam);

        if (! $rule) {
            return $this->stop('No result rule is set for this session. Open Course Session → Session Grade, add this session and set its result rule.');
        }

        $entered = SubjectResult::where('semesterexamid', $exam->semesterexamid)->pluck('subjectid')->map(fn($id) => (int) $id)->all();

        if (! $entered) {
            return $this->stop('No subject result has been added for this exam yet.');
        }

        // the students who have marks in this exam
        $scope = DB::table('mark_result_item as i')
            ->join('mark_result as r', 'r.resultid', '=', 'i.resultid')
            ->where('r.is_delete', false)
            ->where('r.semesterexamid', $exam->semesterexamid)
            ->distinct()
            ->pluck('i.studentid')->map(fn($id) => (int) $id)->all();

        [$subjects, $need, $latest, $chosen] = $this->gather($exam, $scope);

        // first attempt: every subject needs its result before SGPA can be calculated
        if ((int) $exam->attempt === 1) {
            $notEntered = $subjects->filter(function ($subject) use ($entered, $chosen) {
                $needed = ! $subject->subjectType?->is_optional || ! empty($chosen->get($subject->subjectid, []));

                return $needed && ! in_array((int) $subject->subjectid, $entered, true);
            });

            if ($notEntered->isNotEmpty()) {
                return $this->stop(
                    'Result is not added yet for: '
                        . $notEntered->map(fn($s) => "{$s->subjectcode} ({$s->subjectname})")->implode(', ')
                        . '. Add the result of every subject first.'
                );
            }
        }

        // every student needs a result for every subject they take (an earlier attempt counts)
        $gaps = [];

        foreach ($need as $studentId => $subjectIds) {
            foreach ($subjectIds as $subjectId) {
                if (! isset($latest[$studentId][$subjectId])) {
                    $gaps[$studentId][] = $subjects[$subjectId]->subjectcode;
                }
            }
        }

        if ($gaps) {
            $rolls = Student::whereIn('studentid', array_keys($gaps))->pluck('rollno', 'studentid');
            $messages = [];

            foreach (array_slice($gaps, 0, 8, true) as $studentId => $codes) {
                $messages[] = 'Marks are missing for ' . ($rolls[$studentId] ?? "student {$studentId}") . ': ' . implode(', ', $codes) . '.';
            }

            if (count($gaps) > 8) {
                $messages[] = 'And ' . (count($gaps) - 8) . ' more students.';
            }

            return ['errors' => $messages];
        }

        return compact('rule', 'subjects', 'need', 'latest') + ['errors' => []];
    }

    // ------------------------------------------------------------------ data

    /**
     * Subjects, the subjects each student takes, and each student's latest result of each subject.
     * "Latest" = the highest attempt up to this exam, so a back-paper pass replaces the earlier failure.
     */
    private function gather(SemesterExam $exam, array $studentIds): array
    {
        $subjects = Subject::with('subjectType')
            ->where('courseid', $exam->courseid)
            ->where('semid', $exam->semid)
            ->where('active', true)
            ->orderBy('subjectorder')->orderBy('subjectid')
            ->get()
            ->keyBy('subjectid');

        // optional subjects are taken only by the students who chose them
        $chosen = DB::table('mark_optional_subject_student_item as i')
            ->join('mark_optional_subject_student as o', 'o.optionalid', '=', 'i.optionalid')
            ->where('o.is_delete', false)
            ->where('o.batchid', $exam->batchid)
            ->whereIn('o.subjectid', $subjects->keys()->all())
            ->get(['o.subjectid', 'i.studentid'])
            ->groupBy('subjectid')
            ->map(fn($rows) => $rows->pluck('studentid')->map(fn($id) => (int) $id)->all());

        $need = [];

        foreach ($studentIds as $studentId) {
            $need[$studentId] = $subjects
                ->filter(fn($subject) => ! $subject->subjectType?->is_optional
                    || in_array($studentId, $chosen->get($subject->subjectid, []), true))
                ->keys()->map(fn($id) => (int) $id)->all();
        }

        $latest = [];

        if ($studentIds) {
            $rows = DB::table('mark_result_item as i')
                ->join('mark_result as r', 'r.resultid', '=', 'i.resultid')
                ->where('r.is_delete', false)
                ->where('r.courseid', $exam->courseid)
                ->where('r.batchid', $exam->batchid)
                ->where('r.semid', $exam->semid)
                ->where('r.attempt', '<=', $exam->attempt)
                ->whereIn('i.studentid', $studentIds)
                ->orderBy('r.attempt')->orderBy('r.resultid')
                ->get([
                    'i.studentid',
                    'r.subjectid',
                    'r.attempt',
                    'r.subject_credit',
                    'r.entry_mode',
                    'i.grade',
                    'i.grade_point',
                    'i.is_pass',
                    'i.is_absent',
                    'i.internal',
                    'i.external',
                    'i.external_absent',
                    'i.total'
                ]);

            // ordered by attempt: the last row of a student + subject is the latest one
            foreach ($rows as $row) {
                $latest[(int) $row->studentid][(int) $row->subjectid] = $row;
            }
        }

        return [$subjects, $need, $latest, $chosen];
    }

    private function stop(string ...$messages): array
    {
        return ['errors' => $messages];
    }
}
