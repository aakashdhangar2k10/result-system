<?php

namespace App\Services;

use App\Models\CourseSession\SessionGrade;
use App\Models\Grade\Grade;
use App\Models\Result\SubjectResult;
use App\Models\Result\SubjectResultItem;
use App\Models\Semester\CourseSemester;
use App\Models\Semester\SemesterExam;
use App\Models\Student\StudentList;
use App\Models\Student\StudentListLock;
use App\Models\Subject\Subject;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ResultService
{
    // internal marks: three tests, the best two count, so each test is out of (max internal / BEST_OF)
    public const TESTS = ['t1', 't2', 't3'];
    public const BEST_OF = 2;

    // the grade that means "absent" in your Grade table
    public const ABSENT_GRADE = 'Ab';

    /** 12.50 => "12.5", 40.00 => "40", 0 => "0" */
    public static function fmt(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        return rtrim(rtrim(number_format((float) $value, 2, '.', ''), '0'), '.');
    }

    // attempt 1 uses the first-attempt columns of the Grade table, later attempts the second-attempt ones
    public static function letter(Grade $row, int $attempt): string
    {
        return (string) ($attempt >= 2 ? $row->secondattemptgrade : $row->firstattemptgrade);
    }

    public static function point(Grade $row, int $attempt): int
    {
        return (int) ($attempt >= 2 ? $row->secondattemptcredit : $row->firstattemptcredit);
    }

    public function letters(Collection $scale, int $attempt): array
    {
        return $scale->map(fn($row) => self::letter($row, $attempt))->unique()->values()->all();
    }

    // ------------------------------------------------------------------ what a result needs

    /** Student list, grade scale and grade type for an exam + subject. Throws RuntimeException with a readable message. */
    public function context(SemesterExam $exam, Subject $subject): array
    {
        if (! $exam->batchid) {
            throw new RuntimeException('This exam has no session. Edit the semester exam and select the session.');
        }

        if ((int) $exam->courseid !== (int) $subject->courseid || (int) $exam->semid !== (int) $subject->semid) {
            throw new RuntimeException('This subject does not belong to the course and semester of the exam.');
        }

        $semester = CourseSemester::where('courseid', $exam->courseid)
            ->where('batchid', $exam->batchid)
            ->where('semid', $exam->semid)
            ->first();

        $list = $semester ? StudentList::where('semesterid', $semester->semesterid)->first() : null;

        if (! $list) {
            throw new RuntimeException('There is no student list for this course, session and semester. Create it under Student → Student List first.');
        }

        $sessionGrade = SessionGrade::where('courseid', $exam->courseid)->where('batchid', $exam->batchid)->first();

        if (! $sessionGrade) {
            throw new RuntimeException('No grade type is set for this session. Set it under Course Session → Session Grade first.');
        }

        $scale = Grade::where('gradetypeid', $sessionGrade->gradetypeid)
            ->where('active', true)
            ->orderByDesc('startscore')->orderByDesc('endscore')
            ->get();

        if ($scale->isEmpty()) {
            throw new RuntimeException('The grade type of this session has no grades. Add them under Grade first.');
        }

        return [
            'list' => $list,
            'scale' => $scale,
            'gradetypeid' => $sessionGrade->gradetypeid,
        ];
    }

    // the maximum marks: as saved on an existing result, otherwise from the subject
    public function maxes(Subject $subject, ?SubjectResult $editing = null): array
    {
        return $editing
            ? [
                'internal' => (int) $editing->max_internal,
                'external' => (int) $editing->max_external,
                'credit' => (float) $editing->subject_credit,
            ]
            : [
                'internal' => (int) $subject->maxinternalmarks,
                'external' => (int) $subject->maxexternalmarks,
                'credit' => (float) $subject->subjectcredit,
            ];
    }

    // marks can be entered only when the subject has marks and is not graded directly
    public function canEnterMarks(Subject $subject, array $maxes): bool
    {
        return ($maxes['internal'] + $maxes['external']) > 0 && ! $subject->add_grade;
    }

    /** The students who need a result for this subject in this exam, keyed by student id. */
    public function entryStudents(StudentList $list, Subject $subject, SemesterExam $exam, ?SubjectResult $editing = null): Collection
    {
        $students = $list->students()->where('mark_student.active', true)->get();

        // optional subject: only the students who chose it
        if ($subject->subjectType?->is_optional) {
            $chosen = DB::table('mark_optional_subject_student_item as i')
                ->join('mark_optional_subject_student as o', 'o.optionalid', '=', 'i.optionalid')
                ->where('o.is_delete', false)
                ->where('o.batchid', $exam->batchid)
                ->where('o.subjectid', $subject->subjectid)
                ->pluck('i.studentid')->map(fn($id) => (int) $id)->all();

            $students = $students->filter(fn($student) => in_array((int) $student->studentid, $chosen, true));
        }

        // a student who has already passed the subject does not sit it again
        $examIdsOfList = SemesterExam::where('courseid', $exam->courseid)
            ->where('batchid', $exam->batchid)
            ->where('semid', $exam->semid)
            ->pluck('semesterexamid')->map(fn($id) => (int) $id);

        $passed = SubjectResultItem::query()
            ->join('mark_result as r', 'r.resultid', '=', 'mark_result_item.resultid')
            ->where('r.is_delete', false)
            ->where('r.subjectid', $subject->subjectid)
            ->where('mark_result_item.is_pass', true)
            ->whereIn('mark_result_item.studentid', $students->pluck('studentid'))
            ->when($editing, fn($q) => $q->where('r.resultid', '<>', $editing->resultid))
            ->get(['mark_result_item.studentid', 'r.semesterexamid']);

        $students = $students->reject(function ($student) use ($passed, $examIdsOfList) {
            $exams = $passed->where('studentid', $student->studentid)->pluck('semesterexamid')->map(fn($id) => (int) $id);

            if ($exams->isEmpty()) {
                return false;
            }

            // a repeat student redoes the semester: only a pass inside this list counts for them
            if ($student->pivot?->entry_type === 'repeat') {
                return $exams->intersect($examIdsOfList)->isNotEmpty();
            }

            return true;
        })->keyBy('studentid');

        // when editing, the students already saved stay even if they left the list
        if ($editing) {
            foreach ($editing->items as $item) {
                if ($item->student && ! $students->has($item->studentid)) {
                    $students->put($item->studentid, $item->student);
                }
            }

            $students = $students->sortBy('rollno');
        }

        return $students;
    }

    // ------------------------------------------------------------------ calculating

    /**
     * Checks the submitted rows and calculates every student.
     * Returns ['errors' => [...]] (key "form", or "studentId.field") and, when there are none, the computed rows.
     */
    public function process(array $input, ?SubjectResult $editing = null): array
    {
        $fail = fn(string $message) => ['errors' => ['form' => $message]];

        $exam = SemesterExam::find($input['semesterexamid'] ?? null);
        $subject = Subject::with('subjectType')->find($input['subjectid'] ?? null);

        if (! $exam || ! $subject) {
            return $fail('Select the attempt and the subject.');
        }

        try {
            $context = $this->context($exam, $subject);
        } catch (RuntimeException $e) {
            return $fail($e->getMessage());
        }

        $taken = SubjectResult::where('semesterexamid', $exam->semesterexamid)
            ->where('subjectid', $subject->subjectid)
            ->when($editing, fn($q) => $q->where('resultid', '<>', $editing->resultid))
            ->exists();

        if ($taken) {
            return $fail('You have already added result for this subject.');
        }

        $maxes = $this->maxes($subject, $editing);
        $mode = $input['entry_mode'] ?? 'marks';

        if (! in_array($mode, ['marks', 'grade'], true)) {
            return $fail('Choose marks or grade entry.');
        }

        if ($mode === 'marks' && ! $this->canEnterMarks($subject, $maxes)) {
            return $fail('This subject is graded directly. Use grade entry.');
        }

        $students = $this->entryStudents($context['list'], $subject, $exam, $editing);

        if ($students->isEmpty()) {
            return $fail('No student needs a result for this subject in this exam.');
        }

        // the submitted students must be exactly the students of the subject
        $rows = (array) ($input['rows'] ?? []);
        $expected = $students->keys()->map(fn($id) => (int) $id)->sort()->values()->all();
        $given = collect(array_keys($rows))->map(fn($id) => (int) $id)->sort()->values()->all();

        if ($expected !== $given) {
            return $fail('The students of this subject changed. Reload the page and try again.');
        }

        $errors = [];
        $computed = [];

        foreach ($students as $id => $student) {
            [$data, $rowErrors] = $this->evaluate((array) $rows[$id], $mode, $maxes, $context['scale'], (int) $exam->attempt);

            foreach ($rowErrors as $field => $message) {
                $errors["{$id}.{$field}"] = $message;
            }

            if ($data) {
                $computed[$id] = $data;
            }
        }

        return compact('errors', 'computed', 'exam', 'subject', 'context', 'mode', 'maxes', 'students');
    }

    /** One student: [calculated data or null, errors by field]. */
    public function evaluate(array $row, string $mode, array $maxes, Collection $scale, int $attempt): array
    {
        $data = $this->blank();

        // ---- grade entry: the grade is picked, nothing is calculated
        if ($mode === 'grade') {
            $letter = trim((string) ($row['grade'] ?? ''));
            $match = $scale->first(fn($g) => $letter !== '' && strcasecmp(self::letter($g, $attempt), $letter) === 0);

            if (! $match) {
                return [null, ['grade' => 'Select a grade.']];
            }

            $data['grade'] = self::letter($match, $attempt);
            $data['grade_point'] = self::point($match, $attempt);
            $data['is_absent'] = strcasecmp($data['grade'], self::ABSENT_GRADE) === 0;
            $data['is_pass'] = ! $data['is_absent'] && $data['grade_point'] > 0;

            return [$data, []];
        }

        // ---- marks entry
        $errors = [];
        $hasInternal = $maxes['internal'] > 0;
        $hasExternal = $maxes['external'] > 0;
        $internalAbsent = false;

        if ($hasInternal) {
            $testMax = $maxes['internal'] / self::BEST_OF;   // 40 => each test is out of 20
            $scores = [];
            $absentTests = 0;

            foreach (self::TESTS as $test) {
                $absent = ! empty($row["{$test}_absent"]);
                $data["{$test}_absent"] = $absent;

                if ($absent) {
                    $absentTests++;
                    $scores[] = 0.0;   // an absent test counts as 0
                    continue;
                }

                $value = $this->number($row[$test] ?? null, $testMax);

                if ($value === null) {
                    $errors[$test] = 'Enter 0 to ' . self::fmt($testMax) . ', or tick Absent.';
                    continue;
                }

                $data[$test] = $value;
                $scores[] = $value;
            }

            if (! $errors) {
                rsort($scores);   // best first
                $data['internal'] = round(array_sum(array_slice($scores, 0, self::BEST_OF)), 2);
                $internalAbsent = $absentTests === count(self::TESTS);
            }
        }

        if ($hasExternal) {
            $absent = ! empty($row['external_absent']);
            $data['external_absent'] = $absent;

            if (! $absent) {
                $value = $this->number($row['external'] ?? null, $maxes['external']);

                if ($value === null) {
                    $errors['external'] = 'Enter 0 to ' . self::fmt($maxes['external']) . ', or tick Absent.';
                } else {
                    $data['external'] = $value;
                }
            }
        }

        if ($errors) {
            return [null, $errors];
        }

        $total = round(($data['internal'] ?? 0) + ($data['external'] ?? 0), 2);
        $data['total'] = $total;
        $data['percentage'] = round($total / ($maxes['internal'] + $maxes['external']) * 100, 2);

        // absent in the external exam (or in all internal tests when there is no external) = absent in the subject
        $data['is_absent'] = $hasExternal ? $data['external_absent'] : $internalAbsent;

        if ($data['is_absent']) {
            $absentRow = $scale->first(fn($g) => strcasecmp(self::letter($g, $attempt), self::ABSENT_GRADE) === 0);

            $data['grade'] = $absentRow ? self::letter($absentRow, $attempt) : self::ABSENT_GRADE;
            $data['grade_point'] = $absentRow ? self::point($absentRow, $attempt) : 0;
            $data['is_pass'] = false;

            return [$data, []];
        }

        // round to a whole number, then find the grade band (change round() to floor() or ceil() for another policy)
        $score = (int) round($data['percentage']);

        $match = $scale->first(fn($g) => strcasecmp(self::letter($g, $attempt), self::ABSENT_GRADE) !== 0
            && $score >= $g->startscore && $score <= $g->endscore);

        if (! $match) {
            return [null, ['grade' => "No grade is defined for {$score} in the Grade table."]];
        }

        $data['grade'] = self::letter($match, $attempt);
        $data['grade_point'] = self::point($match, $attempt);
        $data['is_pass'] = $data['grade_point'] > 0;

        return [$data, []];
    }

    // ------------------------------------------------------------------ saving

    public function save(array $processed, ?SubjectResult $editing, int $userId): SubjectResult
    {
        return DB::transaction(function () use ($processed, $editing, $userId) {
            $exam = $processed['exam'];
            $subject = $processed['subject'];

            // people entering results of the same exam wait for each other, so a subject cannot get two results
            SemesterExam::lockForUpdate()->find($exam->semesterexamid);

            if (! $editing && SubjectResult::where('semesterexamid', $exam->semesterexamid)->where('subjectid', $subject->subjectid)->exists()) {
                throw new RuntimeException('You have already added result for this subject.');
            }

            if ($editing) {
                $editing->update(['entry_mode' => $processed['mode'], 'updated_by' => $userId]);
                $editing->items()->delete();
                $result = $editing;
            } else {
                $result = SubjectResult::create([
                    'courseid' => $exam->courseid,
                    'batchid' => $exam->batchid,
                    'semid' => $exam->semid,
                    'semesterexamid' => $exam->semesterexamid,
                    'subjectid' => $subject->subjectid,
                    'studentlistid' => $processed['context']['list']->studentlistid,
                    'entry_mode' => $processed['mode'],
                    'attempt' => (int) $exam->attempt,
                    'max_internal' => $processed['maxes']['internal'],
                    'max_external' => $processed['maxes']['external'],
                    'subject_credit' => $processed['maxes']['credit'],
                    'gradetypeid' => $processed['context']['gradetypeid'],
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ]);
            }

            $now = now();
            $insert = [];

            foreach ($processed['computed'] as $studentId => $data) {
                $insert[] = $data + [
                    'resultid' => $result->resultid,
                    'studentid' => $studentId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            foreach (array_chunk($insert, 200) as $chunk) {
                SubjectResultItem::insert($chunk);
            }

            // the first result of a semester locks its student list
            $this->lockList($processed['context']['list'], $userId);
            $this->markStale($exam->semesterexamid);

            return $result;
        });
    }

    public function delete(SubjectResult $result, int $userId): void
    {
        DB::transaction(function () use ($result, $userId) {
            $result->update(['is_delete' => true, 'updated_by' => $userId]);
            $this->unlockIfEmpty($result, $userId);
            $this->markStale($result->semesterexamid);
        });
    }

    // marks changed: the SGPA of this exam (and of later attempts of the same semester) is out of date
    public function markStale(int $semesterExamId): void
    {
        $exam = SemesterExam::find($semesterExamId);

        if (! $exam) {
            return;
        }

        $ids = SemesterExam::where('courseid', $exam->courseid)
            ->where('batchid', $exam->batchid)
            ->where('semid', $exam->semid)
            ->where('attempt', '>=', $exam->attempt)
            ->pluck('semesterexamid');

        DB::table('mark_semester_result')->whereIn('semesterexamid', $ids)->update(['is_stale' => true]);
    }

    private function lockList(StudentList $list, int $userId): void
    {
        $current = StudentList::lockForUpdate()->find($list->studentlistid);

        if (! $current || $current->is_locked) {
            return;
        }

        $current->update(['is_locked' => true, 'locked_at' => now(), 'locked_by' => $userId]);

        StudentListLock::create([
            'studentlistid' => $current->studentlistid,
            'action' => 'lock',
            'note' => 'Locked automatically: result entry started',
            'user_id' => $userId,
        ]);
    }

    private function unlockIfEmpty(SubjectResult $result, int $userId): void
    {
        if (! $result->studentlistid || SubjectResult::where('studentlistid', $result->studentlistid)->exists()) {
            return;
        }

        $list = StudentList::lockForUpdate()->find($result->studentlistid);

        if (! $list || ! $list->is_locked) {
            return;
        }

        // only a lock made by result entry is removed automatically; a lock set by hand stays
        $last = StudentListLock::where('studentlistid', $list->studentlistid)->latest('lockid')->first();

        if (! $last || $last->action !== 'lock' || ! str_starts_with((string) $last->note, 'Locked automatically')) {
            return;
        }

        $list->update(['is_locked' => false, 'locked_at' => null, 'locked_by' => null]);

        StudentListLock::create([
            'studentlistid' => $list->studentlistid,
            'action' => 'unlock',
            'note' => 'Unlocked automatically: all results of this semester were deleted',
            'user_id' => $userId,
        ]);
    }

    // ------------------------------------------------------------------ helpers

    private function blank(): array
    {
        return [
            't1' => null,
            't2' => null,
            't3' => null,
            't1_absent' => false,
            't2_absent' => false,
            't3_absent' => false,
            'internal' => null,
            'external' => null,
            'external_absent' => false,
            'total' => null,
            'percentage' => null,
            'grade' => null,
            'grade_point' => 0,
            'is_absent' => false,
            'is_pass' => false,
        ];
    }

    // a number from 0 to $max with at most two decimals, or null
    private function number(mixed $raw, float $max): ?float
    {
        if ($raw === null || trim((string) $raw) === '' || ! is_numeric($raw)) {
            return null;
        }

        $value = (float) $raw;

        if ($value < 0 || $value > $max || round($value, 2) !== $value) {
            return null;
        }

        return $value;
    }
}
