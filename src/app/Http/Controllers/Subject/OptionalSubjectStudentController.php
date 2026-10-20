<?php

namespace App\Http\Controllers\Subject;

use App\Http\Controllers\Controller;
use App\Http\Requests\Subject\OptionalSubjectStudentRequest;
use App\Models\Batch\Batch;
use App\Models\Configuration\SemesterList;
use App\Models\Configuration\SubjectType;
use App\Models\Course\Course;
use App\Models\Subject\OptionalSubjectStudent;
use App\Models\Subject\Subject;
use App\Support\DeleteGuard;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;

class OptionalSubjectStudentController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:optionalsubjectstudents.view', only: ['index']),
            new Middleware('permission:optionalsubjectstudents.create', only: ['create', 'store', 'students']),
            new Middleware('permission:optionalsubjectstudents.edit', only: ['edit', 'update']),
            new Middleware('permission:optionalsubjectstudents.delete', only: ['destroy']),
        ];
    }

    public function index(Request $request)
    {
        $perPage = in_array((int) $request->per_page, [10, 25, 50, 100]) ? (int) $request->per_page : 10;

        $query = OptionalSubjectStudent::query()
            ->join('mark_subject', 'mark_subject.subjectid', '=', 'mark_optional_subject_student.subjectid')
            ->join('mark_course', 'mark_course.courseid', '=', 'mark_subject.courseid')
            ->join('mark_batch', 'mark_batch.batchid', '=', 'mark_optional_subject_student.batchid')
            ->select('mark_optional_subject_student.*')
            ->addSelect([
                // is the student list of this semester locked (result generated)?
                'list_locked' => DB::table('mark_student_list as l')
                    ->join('mark_semester as s', 's.semesterid', '=', 'l.semesterid')
                    ->where('l.is_delete', false)
                    ->where('s.is_delete', false)
                    ->whereColumn('s.courseid', 'mark_subject.courseid')
                    ->whereColumn('s.batchid', 'mark_optional_subject_student.batchid')
                    ->whereColumn('s.semid', 'mark_subject.semid')
                    ->selectRaw('coalesce(max(l.is_locked), 0)'),
            ])
            ->with(['subject.course', 'subject.term', 'batch.branch', 'students'])
            ->when($request->courseid, fn ($q, $id) => $q->where('mark_subject.courseid', $id))
            ->when($request->batchid, fn ($q, $id) => $q->where('mark_optional_subject_student.batchid', $id))
            ->when($request->semid, fn ($q, $id) => $q->where('mark_subject.semid', $id))
            ->when($request->q, function ($q, $search) {
                $q->where(function ($w) use ($search) {
                    $w->where('mark_subject.subjectname', 'like', "%{$search}%")
                      ->orWhere('mark_subject.subjectcode', 'like', "%{$search}%")
                      ->orWhereExists(function ($s) use ($search) {
                          $s->select(DB::raw(1))
                              ->from('mark_optional_subject_student_item as i')
                              ->join('mark_student as st', 'st.studentid', '=', 'i.studentid')
                              ->whereColumn('i.optionalid', 'mark_optional_subject_student.optionalid')
                              ->where('st.rollno', 'like', "%{$search}%");
                      });
                });
            });

        $this->applySort($query, $request);

        return view('optional_subject_student.index', [
            'rows' => $query->paginate($perPage)->withQueryString(),
            'perPage' => $perPage,
        ] + $this->formData());
    }

    public function create()
    {
        return view('optional_subject_student.create', $this->formData());
    }

    // HTML fragment with the students of the semester's list, loaded by the add page
    public function students(Request $request)
    {
        if (! $request->ajax()) {
            return redirect()->route('optional-subject-students.index');
        }

        $request->validate([
            'batchid' => ['required', 'integer'],
            'subjectid' => ['required', 'integer'],
        ]);

        $subject = Subject::with('subjectType')->findOrFail($request->subjectid);

        // after a validation error the earlier ticks are sent back
        $selected = $request->boolean('restore')
            ? array_map('intval', (array) $request->input('selected', []))
            : [];

        return view('optional_subject_student._students', $this->studentData($subject, (int) $request->batchid, $selected));
    }

    public function store(OptionalSubjectStudentRequest $request)
    {
        $data = $request->validated();

        DB::transaction(function () use ($data) {
            $record = OptionalSubjectStudent::create([
                'batchid' => $data['batchid'],
                'subjectid' => $data['subjectid'],
            ]);

            $record->students()->sync(array_map('intval', $data['students']));
        });

        return redirect()->route('optional-subject-students.index')->with('success', 'Optional subject students saved.');
    }

    public function edit(OptionalSubjectStudent $optionalSubjectStudent)
    {
        $record = $optionalSubjectStudent->load(['subject.course', 'subject.term', 'subject.subjectType', 'batch.branch', 'students']);

        if ($message = $this->lockedMessage($record)) {
            return redirect()->route('optional-subject-students.index')->with('error', $message);
        }

        return view('optional_subject_student.edit', [
            'record' => $record,
        ] + $this->studentData(
            $record->subject,
            $record->batchid,
            $record->students->pluck('studentid')->map(fn ($id) => (int) $id)->all(),
            $record
        ));
    }

    public function update(OptionalSubjectStudentRequest $request, OptionalSubjectStudent $optionalSubjectStudent)
    {
        DB::transaction(function () use ($request, $optionalSubjectStudent) {
            $optionalSubjectStudent->students()->sync(array_map('intval', $request->validated('students')));
            $optionalSubjectStudent->touch();
        });

        return redirect()->route('optional-subject-students.index')->with('success', 'Optional subject students updated.');
    }

    public function destroy(OptionalSubjectStudent $optionalSubjectStudent)
    {
        $optionalSubjectStudent->load('subject');

        if ($message = $this->lockedMessage($optionalSubjectStudent)) {
            return back()->with('error', $message);
        }

        if ($blocked = DeleteGuard::blocker($optionalSubjectStudent)) {
            return back()->with('error', $blocked);
        }

        // soft delete, the same as the other modules
        $optionalSubjectStudent->update(['is_delete' => true]);

        return redirect()->route('optional-subject-students.index')->with('success', 'Optional subject students deleted.');
    }

    // ------------------------------------------------------------------ helpers

    private function lockedMessage(OptionalSubjectStudent $record): ?string
    {
        $list = OptionalSubjectStudent::studentListFor($record->batchid, $record->subject);

        return $list?->is_locked
            ? 'The result of this semester is already generated, so its optional subject students are locked.'
            : null;
    }

    // everything the student ticks need
    private function studentData(Subject $subject, int $batchId, array $selected = [], ?OptionalSubjectStudent $record = null): array
    {
        $empty = ['students' => collect(), 'stale' => collect(), 'selected' => []];

        if (! $subject->subjectType?->is_optional) {
            return $empty + ['message' => 'This is not an optional subject.'];
        }

        $list = OptionalSubjectStudent::studentListFor($batchId, $subject);

        if (! $list) {
            return $empty + ['message' => 'There is no student list for this course, session and semester yet. Create it under Student → Student List first.'];
        }

        if ($list->is_locked) {
            return $empty + ['message' => 'The result of this semester is already generated, so its student list is locked.'];
        }

        $students = $list->students()->get();

        // chosen earlier but no longer on the student list: shown, so nobody disappears silently
        $stale = $record
            ? $record->students->reject(fn ($student) => $students->contains('studentid', $student->studentid))->values()
            : collect();

        return [
            'message' => null,
            'students' => $students,
            'stale' => $stale,
            'selected' => $selected,
        ];
    }

    private function applySort($query, Request $request): void
    {
        $direction = $request->dir === 'desc' ? 'desc' : 'asc';

        $sortable = [
            'course' => 'mark_course.fullname',
            'session' => 'mark_batch.startyear',
            'term' => 'mark_subject.semid',
            'subject' => 'mark_subject.subjectname',
        ];

        if (isset($sortable[$request->sort])) {
            $query->orderBy($sortable[$request->sort], $direction);
        } else {
            // default order when no header was clicked
            $query->orderBy('mark_course.fullname')
                ->orderByDesc('mark_batch.startyear')
                ->orderBy('mark_subject.semid')
                ->orderBy('mark_subject.subjectname');
        }

        // tie-breaker so pagination stays stable
        $query->orderBy('mark_optional_subject_student.optionalid');
    }

    private function formData(): array
    {
        $optionalTypes = SubjectType::where('is_optional', true)->where('active', true)->select('subjecttypeid');

        return [
            'courses' => Course::where('active', true)->orderBy('shortname')->get(),
            'batches' => Batch::where('active', true)->with(['course', 'branch'])->orderByDesc('startyear')->get(),
            'terms' => SemesterList::where('active', true)->orderBy('semid')->get(),
            // only subjects of an "optional" subject type
            'subjects' => Subject::where('active', true)
                ->whereIn('subjecttypeid', $optionalTypes)
                ->orderBy('courseid')->orderBy('semid')->orderBy('subjectorder')
                ->get(),
        ];
    }
}