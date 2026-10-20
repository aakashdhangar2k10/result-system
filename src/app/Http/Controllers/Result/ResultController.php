<?php

namespace App\Http\Controllers\Result;

use App\Http\Controllers\Controller;
use App\Models\Batch\Batch;
use App\Models\Configuration\SemesterList;
use App\Models\Course\Course;
use App\Models\Result\SubjectResult;
use App\Models\Result\SubjectResultItem;
use App\Models\Semester\SemesterExam;
use App\Models\Subject\Subject;
use App\Services\ResultService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use RuntimeException;
use App\Models\Result\SemesterResult;
use App\Models\Student\Student;
use App\Services\SemesterResultService;

class ResultController extends Controller implements HasMiddleware
{
    public function __construct(private ResultService $service, private SemesterResultService $sgpa) {}

    public static function middleware(): array
    {
        return [
            new Middleware('permission:subjectresults.view', only: ['index', 'show']),
            new Middleware('permission:subjectresults.create', only: ['create', 'store']),
            new Middleware('permission:subjectresults.edit', only: ['edit', 'update']),
            new Middleware('permission:subjectresults.create|subjectresults.edit', only: ['entry', 'calculate']),
            new Middleware('permission:subjectresults.delete', only: ['destroy']),
            new Middleware('permission:subjectresults.view', only: ['index', 'show', 'studentDetail']),
            new Middleware('permission:semesterresults.calculate', only: ['calculateSgpa']),
        ];
    }

    // ---- result sheet: students x subjects of one exam
    // public function index(Request $request)
    // {
    //     $view = $request->view === 'marks' ? 'marks' : 'grades';
    //     $exam = $request->filled('semesterexamid')
    //         ? SemesterExam::with(['course', 'batch.branch', 'term'])->find($request->semesterexamid)
    //         : null;

    //     $results = collect();
    //     $students = collect();
    //     $cells = [];
    //     $stats = [];
    //     $missing = collect();

    //     if ($exam) {
    //         $results = SubjectResult::with('subject')
    //             ->where('semesterexamid', $exam->semesterexamid)
    //             ->get()
    //             ->sortBy(fn($result) => [$result->subject?->subjectorder ?? 0, $result->resultid])
    //             ->values();

    //         $items = SubjectResultItem::with('student')
    //             ->whereIn('resultid', $results->pluck('resultid'))
    //             ->get();

    //         $students = $items->pluck('student')->filter()->unique('studentid')->sortBy('rollno')->values();

    //         foreach ($items as $item) {
    //             $cells[$item->studentid][$item->resultid] = $item;

    //             $stat = $stats[$item->resultid] ?? ['total' => 0, 'pass' => 0, 'fail' => 0, 'absent' => 0];
    //             $stat['total']++;
    //             $item->is_absent ? $stat['absent']++ : ($item->is_pass ? $stat['pass']++ : $stat['fail']++);
    //             $stats[$item->resultid] = $stat;
    //         }

    //         // subjects of this course and semester that have no result yet
    //         $missing = Subject::where('courseid', $exam->courseid)
    //             ->where('semid', $exam->semid)
    //             ->where('active', true)
    //             ->whereNotIn('subjectid', $results->pluck('subjectid'))
    //             ->orderBy('subjectorder')
    //             ->get();
    //     }

    //     return view('result.index', [
    //         'view' => $view,
    //         'exam' => $exam,
    //         'results' => $results,
    //         'students' => $students,
    //         'cells' => $cells,
    //         'stats' => $stats,
    //         'missing' => $missing,
    //     ] + $this->formData());
    // }
    public function index(Request $request)
    {
        $view = $request->view === 'marks' ? 'marks' : 'grades';
        $exam = $request->filled('semesterexamid')
            ? SemesterExam::with(['course', 'batch.branch', 'term'])->find($request->semesterexamid)
            : null;

        $results = collect();
        $students = collect();
        $cells = [];
        $stats = [];
        $missing = collect();

        $semResult = null;
        $sgpaItems = collect();
        $rule = null;
        $outdated = false;

        if ($exam) {
            $results = SubjectResult::with('subject')
                ->where('semesterexamid', $exam->semesterexamid)
                ->get()
                ->sortBy(fn($result) => [$result->subject?->subjectorder ?? 0, $result->resultid])
                ->values();

            $items = SubjectResultItem::with('student')
                ->whereIn('resultid', $results->pluck('resultid'))
                ->get();

            $students = $items->pluck('student')->filter()->unique('studentid')->sortBy('rollno')->values();

            foreach ($items as $item) {
                $cells[$item->studentid][$item->resultid] = $item;

                $stat = $stats[$item->resultid] ?? ['total' => 0, 'pass' => 0, 'fail' => 0, 'absent' => 0];
                $stat['total']++;
                $item->is_absent ? $stat['absent']++ : ($item->is_pass ? $stat['pass']++ : $stat['fail']++);
                $stats[$item->resultid] = $stat;
            }

            // subjects of this course and semester that have no result yet
            $missing = Subject::where('courseid', $exam->courseid)
                ->where('semid', $exam->semid)
                ->where('active', true)
                ->whereNotIn('subjectid', $results->pluck('subjectid'))
                ->orderBy('subjectorder')
                ->get();

            // SGPA and the result rule
            $rule = $this->sgpa->rule($exam);
            $semResult = SemesterResult::with(['items', 'calculator'])->where('semesterexamid', $exam->semesterexamid)->first();

            if ($semResult) {
                $sgpaItems = $semResult->items->keyBy('studentid');
                $outdated = $semResult->is_stale || ($rule && $semResult->ruleDiffers($rule));
            }
        }

        return view('result.index', [
            'view' => $view,
            'exam' => $exam,
            'results' => $results,
            'students' => $students,
            'cells' => $cells,
            'stats' => $stats,
            'missing' => $missing,
            'semResult' => $semResult,
            'sgpaItems' => $sgpaItems,
            'rule' => $rule,
            'outdated' => $outdated,
        ] + $this->formData());
    }

    public function create()
    {
        return view('result.create', $this->formData());
    }

    // HTML fragment: the student table for an exam + subject (or the "already added" message)
    public function entry(Request $request)
    {
        if (! $request->ajax()) {
            return redirect()->route('results.index');
        }

        $request->validate([
            'semesterexamid' => ['required', 'integer'],
            'subjectid' => ['required', 'integer'],
            'mode' => ['nullable', 'in:marks,grade'],
            'result' => ['nullable', 'integer'],
        ]);

        $editing = $request->filled('result')
            ? SubjectResult::with('items.student')->find($request->result)
            : null;

        $exam = SemesterExam::findOrFail($request->semesterexamid);
        $subject = Subject::with('subjectType')->findOrFail($request->subjectid);

        return view('result._entry', $this->entryData($exam, $subject, (string) $request->input('mode', 'marks'), $editing));
    }

    // "Calculate Grade": validates the marks and returns total, grade and grade point for every student
    public function calculate(Request $request)
    {
        $editing = $request->filled('result_id')
            ? SubjectResult::with('items.student')->find($request->result_id)
            : null;

        $input = $request->all();

        if ($editing) {
            $input['semesterexamid'] = $editing->semesterexamid;
            $input['subjectid'] = $editing->subjectid;
        }

        $processed = $this->service->process($input, $editing);

        if ($processed['errors']) {
            return response()->json([
                'ok' => false,
                'message' => $processed['errors']['form'] ?? 'Some marks need your attention. Fix the highlighted cells and calculate again.',
                'errors' => collect($processed['errors'])->except('form')->all(),
            ], 422);
        }

        return response()->json([
            'ok' => true,
            'rows' => collect($processed['computed'])->map(fn($data) => [
                'internal' => ResultService::fmt($data['internal']),
                'external' => $data['external_absent'] ? 'Ab' : ResultService::fmt($data['external']),
                'total' => ResultService::fmt($data['total']),
                'grade' => $data['grade'],
                'point' => $data['grade_point'],
                'absent' => $data['is_absent'],
                'pass' => $data['is_pass'],
            ])->all(),
        ]);
    }

    public function store(Request $request)
    {
        $processed = $this->service->process($request->all());

        if ($processed['errors']) {
            return $this->backWithErrors($request, $processed['errors']);
        }

        try {
            $result = $this->service->save($processed, null, $request->user()->id);
        } catch (RuntimeException $e) {
            return back()->withInput($request->except('rows'))->with('error', $e->getMessage());
        }

        return redirect()->route('results.index', $this->filters($result))
            ->with('success', 'Result of ' . $processed['subject']->subjectname . ' saved for ' . count($processed['computed']) . ' students.');
    }

    // popup with every mark of one subject result
    public function show(Request $request, SubjectResult $subjectResult)
    {
        if (! $request->ajax()) {
            return redirect()->route('results.index');
        }

        $subjectResult->load(['course', 'batch.branch', 'term', 'exam', 'subject.subjectType', 'items.student']);

        return view('result._detail', [
            'result' => $subjectResult,
            'items' => $subjectResult->items->sortBy(fn($item) => $item->student?->rollno)->values(),
        ]);
    }

    // "Calculate SGPA": answers the progress popup
    public function calculateSgpa(Request $request, SemesterExam $semesterExam)
    {
        $outcome = $this->sgpa->calculate($semesterExam, $request->user()->id);

        if ($outcome['errors']) {
            return response()->json([
                'ok' => false,
                'message' => 'SGPA cannot be calculated yet.',
                'errors' => $outcome['errors'],
            ], 422);
        }

        $rule = $outcome['rule'];
        $counts = $outcome['counts'];

        return response()->json([
            'ok' => true,
            'message' => 'SGPA calculated successfully!',
            'summary' => "{$outcome['count']} students · {$rule->pass_label} {$counts['pass']}"
                . " · {$rule->promoted_label} {$counts['promoted']} · {$rule->fail_label} {$counts['fail']}",
        ]);
    }

    // popup of one student: every subject, the SGPA working and the reason for the result
    public function studentDetail(Request $request, SemesterExam $semesterExam, Student $student)
    {
        if (! $request->ajax()) {
            return redirect()->route('results.index');
        }

        $semesterExam->load(['course', 'batch.branch', 'term']);

        $header = SemesterResult::where('semesterexamid', $semesterExam->semesterexamid)->first();

        return view('result._student', [
            'exam' => $semesterExam,
            'student' => $student,
            'header' => $header,
            'item' => $header?->items()->where('studentid', $student->studentid)->first(),
            'lines' => $this->sgpa->breakdown($semesterExam, $student->studentid),
            'explainer' => $this->sgpa,
        ]);
    }

    public function edit(SubjectResult $subjectResult)
    {
        $subjectResult->load(['course', 'batch.branch', 'term', 'exam', 'subject.subjectType', 'items.student']);

        $maxes = $this->service->maxes($subjectResult->subject, $subjectResult);

        return view('result.edit', [
            'result' => $subjectResult,
            'maxes' => $maxes,
            'canMarks' => $this->service->canEnterMarks($subjectResult->subject, $maxes),
            'entry' => $this->entryData($subjectResult->exam, $subjectResult->subject, $subjectResult->entry_mode, $subjectResult),
        ]);
    }

    public function update(Request $request, SubjectResult $subjectResult)
    {
        // the exam and the subject of a result never change
        $input = $request->all();
        $input['semesterexamid'] = $subjectResult->semesterexamid;
        $input['subjectid'] = $subjectResult->subjectid;

        $processed = $this->service->process($input, $subjectResult);

        if ($processed['errors']) {
            return $this->backWithErrors($request, $processed['errors']);
        }

        $this->service->save($processed, $subjectResult, $request->user()->id);

        return redirect()->route('results.index', $this->filters($subjectResult))
            ->with('success', 'Result of ' . $processed['subject']->subjectname . ' updated.');
    }

    public function destroy(Request $request, SubjectResult $subjectResult)
    {
        $subjectResult->load('subject');

        $this->service->delete($subjectResult, $request->user()->id);

        return redirect()->route('results.index', $this->filters($subjectResult))
            ->with('success', 'Result of ' . $subjectResult->subject?->subjectname . ' deleted.');
    }

    // ------------------------------------------------------------------ helpers

    private function entryData(SemesterExam $exam, Subject $subject, string $mode, ?SubjectResult $editing = null): array
    {
        // a result already added (add page only)
        if (! $editing) {
            $existing = SubjectResult::where('semesterexamid', $exam->semesterexamid)
                ->where('subjectid', $subject->subjectid)
                ->first();

            if ($existing) {
                return ['message' => 'You have already added result for this subject.', 'existing' => $existing];
            }
        }

        try {
            $context = $this->service->context($exam, $subject);
        } catch (RuntimeException $e) {
            return ['message' => $e->getMessage()];
        }

        $maxes = $this->service->maxes($subject, $editing);

        // a subject graded directly, or without marks, can only be entered as grades
        $mode = ($mode === 'grade' || ! $this->service->canEnterMarks($subject, $maxes)) ? 'grade' : 'marks';

        $students = $this->service->entryStudents($context['list'], $subject, $exam, $editing);

        if ($students->isEmpty()) {
            return ['message' => 'No student needs a result for this subject in this exam.'];
        }

        // saved values are shown only when the entry type is the one they were saved with
        $prefill = ($editing && $editing->entry_mode === $mode) ? $editing->items->keyBy('studentid') : collect();

        return [
            'message' => null,
            'mode' => $mode,
            'students' => $students,
            'prefill' => $prefill,
            'maxes' => $maxes,
            'testMax' => $maxes['internal'] / ResultService::BEST_OF,
            'letters' => $this->service->letters($context['scale'], (int) $exam->attempt),
        ];
    }

    private function backWithErrors(Request $request, array $errors)
    {
        $messages = collect($errors)->values();
        $text = $messages->take(3)->implode(' ');

        if ($messages->count() > 3) {
            $text .= ' (and ' . ($messages->count() - 3) . ' more)';
        }

        return back()->withInput($request->except('rows'))->with('error', $text);
    }

    private function filters(SubjectResult $result): array
    {
        return [
            'courseid' => $result->courseid,
            'batchid' => $result->batchid,
            'semid' => $result->semid,
            'semesterexamid' => $result->semesterexamid,
        ];
    }

    private function formData(): array
    {
        return [
            'courses' => Course::where('active', true)->orderBy('shortname')->get(),
            'batches' => Batch::where('active', true)->with(['course', 'branch'])->orderByDesc('startyear')->get(),
            'terms' => SemesterList::where('active', true)->orderBy('semid')->get(),
            'exams' => SemesterExam::where('active', true)->whereNotNull('batchid')
                ->orderBy('courseid')->orderByDesc('batchid')->orderBy('semid')->orderBy('attempt')->get(),
            'subjects' => Subject::with('subjectType')->where('active', true)
                ->orderBy('courseid')->orderBy('semid')->orderBy('subjectorder')->get(),
        ];
    }
}
