<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\StudentListRequest;
use App\Models\Batch\Batch;
use App\Models\Course\Course;
use App\Models\Semester\CourseSemester;
use App\Models\Student\Student;
use App\Models\Student\StudentList;
use App\Support\Term;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use App\Models\Student\StudentListLock;

class StudentListController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:studentlists.view', only: ['index', 'show']),
            new Middleware('permission:studentlists.create', only: ['create', 'store']),
            new Middleware('permission:studentlists.edit', only: ['edit', 'update']),
            new Middleware('permission:studentlists.create|studentlists.edit', only: ['options']),
            new Middleware('permission:studentlists.delete', only: ['destroy']),
            new Middleware('permission:studentlists.lock', only: ['lock']),
            new Middleware('permission:studentlists.unlock', only: ['unlock']),
        ];
    }

    public function index(Request $request)
    {
        $perPage = in_array((int) $request->per_page, [10, 25, 50, 100]) ? (int) $request->per_page : 10;

        $query = StudentList::query()
            ->join('mark_semester', 'mark_semester.semesterid', '=', 'mark_student_list.semesterid')
            ->join('mark_course', 'mark_course.courseid', '=', 'mark_semester.courseid')
            ->join('mark_batch', 'mark_batch.batchid', '=', 'mark_semester.batchid')
            ->where('mark_semester.is_delete', false)
            ->select('mark_student_list.*')
            ->addSelect([
                // students of the session that could be on this list
                'total_students' => Student::query()->selectRaw('count(*)')
                    ->whereColumn('mark_student.batchid', 'mark_semester.batchid')
                    ->where('mark_student.active', true),
                // students actually on this list
                'added_students' => DB::table('mark_student_list_item as i')
                    ->join('mark_student as s', 's.studentid', '=', 'i.studentid')
                    ->where('s.is_delete', false)
                    ->whereColumn('i.studentlistid', 'mark_student_list.studentlistid')
                    ->selectRaw('count(*)'),
                // of those, back paper and repeat entries
                'extra_students' => DB::table('mark_student_list_item as i')
                    ->join('mark_student as s', 's.studentid', '=', 'i.studentid')
                    ->where('s.is_delete', false)
                    ->where('i.entry_type', '<>', 'regular')
                    ->whereColumn('i.studentlistid', 'mark_student_list.studentlistid')
                    ->selectRaw('count(*)'),
            ])
            ->with(['semester.course', 'semester.batch.branch', 'semester.term'])
            ->when($request->courseid, fn ($q, $id) => $q->where('mark_semester.courseid', $id))
            ->when($request->batchid, fn ($q, $id) => $q->where('mark_semester.batchid', $id))
            ->when($request->lock === 'locked', fn ($q) => $q->where('mark_student_list.is_locked', true))
            ->when($request->lock === 'unlocked', fn ($q) => $q->where('mark_student_list.is_locked', false))
            ->when($request->q, function ($q, $search) {
                $q->where(function ($w) use ($search) {
                    $w->where('mark_course.fullname', 'like', "%{$search}%")
                      ->orWhere('mark_course.shortname', 'like', "%{$search}%");
                });
            });

        $this->applySort($query, $request);

        return view('student_list.index', [
            'lists' => $query->paginate($perPage)->withQueryString(),
            'perPage' => $perPage,
            'courses' => Course::orderBy('shortname')->get(),
            'batches' => Batch::with(['course', 'branch'])->orderByDesc('startyear')->get(),
        ]);
    }

    // popup with the students of one list
    public function show(Request $request, StudentList $studentList)
    {
        if (! $request->ajax()) {
            return redirect()->route('student-lists.index');
        }

       $studentList->load(['semester.course', 'semester.batch.branch', 'semester.term','students.batch', 'locker', 'lockLog.user',]);

        return view('student_list._detail', ['list' => $studentList]);
    }

    public function create()
    {
        return view('student_list.create', [
            'courses' => Course::where('active', true)->orderBy('shortname')->get(),
            'batches' => Batch::where('active', true)->with(['course', 'branch'])->orderByDesc('startyear')->get(),
            // only semesters with a session and without a list yet
            'semesters' => CourseSemester::with(['course', 'term'])
                ->whereNotNull('batchid')
                ->where('active', true)
                ->whereNotIn('semesterid', StudentList::select('semesterid'))
                ->orderBy('courseid')->orderByDesc('batchid')->orderBy('semid')
                ->get(),
        ]);
    }

    // HTML fragment with the students of a semester, loaded by the add page
    public function options(Request $request)
    {
        if (! $request->ajax()) {
            return redirect()->route('student-lists.index');
        }

        $request->validate(['semesterid' => ['required', 'integer']]);

        $semester = CourseSemester::with(['course', 'batch.branch', 'term'])
            ->whereNotNull('batchid')
            ->findOrFail($request->semesterid);

        // after a validation error the earlier ticks are sent back; otherwise every session student is ticked
        $selectedHome = $request->boolean('restore')
            ? array_map('intval', (array) $request->input('selected', []))
            : null;

        $selectedExtra = [];
        foreach ((array) $request->input('extra', []) as $id => $type) {
            $selectedExtra[(int) $id] = ['type' => $type, 'remark' => $request->input("remark.{$id}")];
        }

        return view('student_list._students', $this->studentData($semester, $selectedHome, $selectedExtra));
    }

    public function store(StudentListRequest $request)
    {
        $data = $request->validated();

        DB::transaction(function () use ($data) {
            $list = StudentList::create(['semesterid' => $data['semesterid']]);
            $list->students()->sync($this->pivotData($data));
        });

        return redirect()->route('student-lists.index')->with('success', 'Student list saved.');
    }

    public function edit(StudentList $studentList)
    {
        if ($studentList->is_locked) {
            return redirect()->route('student-lists.index')->with('error', $this->lockedMessage());
        }

        $studentList->load(['semester.course', 'semester.batch.branch', 'semester.term', 'students']);

        $selectedHome = $studentList->students
            ->filter(fn ($student) => $student->pivot->entry_type === 'regular')
            ->pluck('studentid')->map(fn ($id) => (int) $id)->all();

        $selectedExtra = $studentList->students
            ->filter(fn ($student) => $student->pivot->entry_type !== 'regular')
            ->mapWithKeys(fn ($student) => [(int) $student->studentid => [
                'type' => $student->pivot->entry_type,
                'remark' => $student->pivot->remark,
            ]])
            ->all();

        return view('student_list.edit', [
            'list' => $studentList,
        ] + $this->studentData($studentList->semester, $selectedHome, $selectedExtra));
    }

    public function update(StudentListRequest $request, StudentList $studentList)
    {
        if ($studentList->is_locked) {
            return redirect()->route('student-lists.index')->with('error', $this->lockedMessage());
        }

        $data = $request->validated();

        DB::transaction(function () use ($studentList, $data) {
            $studentList->students()->sync($this->pivotData($data));
            $studentList->touch();
        });

        return redirect()->route('student-lists.index')->with('success', 'Student list updated.');
    }

    public function destroy(StudentList $studentList)
    {
        if ($studentList->is_locked) {
            return redirect()->route('student-lists.index')->with('error', $this->lockedMessage());
        }

        // soft delete, the same as the other modules
        $studentList->update(['is_delete' => true]);

        return redirect()->route('student-lists.index')->with('success', 'Student list deleted.');
    }

    public function lock(Request $request, StudentList $studentList)
    {
    $data = $request->validate(['note' => ['nullable', 'string', 'max:255']]);

    return $this->changeLock($studentList, true, $data['note'] ?? null, $request->user()->id)
        ? back()->with('success', 'Student list locked. It cannot be edited or deleted until it is unlocked.')
        : back()->with('error', 'This student list is already locked.');
    }

    public function unlock(Request $request, StudentList $studentList)
    {
    // opening a list that may already have a result needs a reason
    $data = $request->validate(
        ['note' => ['required', 'string', 'min:5', 'max:255']],
        [
            'note.required' => 'Write the reason for unlocking this list.',
            'note.min' => 'The reason must be at least 5 characters.',
        ]
    );

    return $this->changeLock($studentList, false, $data['note'], $request->user()->id)
        ? back()->with('success', 'Student list unlocked. Lock it again after your changes.')
        : back()->with('error', 'This student list is not locked.');
    }
    // ------------------------------------------------------------------ helpers

    private function lockedMessage(): string
    {
        return 'The result of this semester is already generated, so its student list is locked.';
    }

    // the rows saved in mark_student_list_item
    private function pivotData(array $data): array
    {
        $sync = [];

        foreach ($data['students'] ?? [] as $id) {
            $sync[(int) $id] = ['entry_type' => 'regular', 'remark' => null];
        }

        foreach ($data['extra_ids'] ?? [] as $id) {
            $sync[(int) $id] = [
                'entry_type' => $data['extra_type'][$id] ?? 'back',
                'remark' => ($data['extra_remark'][$id] ?? null) ?: null,
            ];
        }

        return $sync;
    }

    // everything the student ticks need
    private function studentData(CourseSemester $semester, ?array $selectedHome = null, array $selectedExtra = []): array
    {
        // students of the semester's own session (active ones, and any already on the list)
        $home = Student::query()
            ->where('batchid', $semester->batchid)
            ->where(function ($q) use ($selectedHome) {
                $q->where('active', true);

                if ($selectedHome) {
                    $q->orWhereIn('studentid', $selectedHome);
                }
            })
            ->orderBy('rollno')
            ->get();

        // students of the same course from other sessions: candidates for back paper / repeat
        $extraIds = array_keys($selectedExtra);

        $extra = Student::query()
            ->with('batch')
            ->where('courseid', $semester->courseid)
            ->where('batchid', '<>', $semester->batchid)
            ->where(function ($q) use ($extraIds) {
                $q->where('active', true);

                if ($extraIds) {
                    $q->orWhereIn('studentid', $extraIds);
                }
            })
            ->get()
            ->sortBy(fn ($student) => [isset($selectedExtra[$student->studentid]) ? 0 : 1, $student->batchid, $student->rollno])
            ->values();

        return [
            'semester' => $semester,
            'home' => $home,
            'extra' => $extra,
            'selectedHome' => $selectedHome ?? $home->pluck('studentid')->map(fn ($id) => (int) $id)->all(),
            'selectedExtra' => $selectedExtra,
            'previousIds' => $this->previousMembers($semester),
        ];
    }

    // Students on the list of the previous semester of the same session (null when there is nothing to compare with)
    private function previousMembers(CourseSemester $semester): ?array
    {
        $number = Term::number($semester->term?->roman);

        if ($number <= 1) {
            return null;
        }

        $previous = CourseSemester::with('term')
            ->where('courseid', $semester->courseid)
            ->where('batchid', $semester->batchid)
            ->get()
            ->first(fn ($item) => Term::number($item->term?->roman) === $number - 1);

        $list = $previous ? StudentList::where('semesterid', $previous->semesterid)->first() : null;

        return $list
            ? DB::table('mark_student_list_item')
                ->where('studentlistid', $list->studentlistid)
                ->pluck('studentid')->map(fn ($id) => (int) $id)->all()
            : null;
    }

    private function applySort($query, Request $request): void
    {
        $direction = $request->dir === 'desc' ? 'desc' : 'asc';

        $sortable = [
            'course' => 'mark_course.fullname',
            'session' => 'mark_batch.startyear',
            'term' => 'mark_semester.semid',
            'month' => 'mark_semester.semestermonth',
            'total' => 'total_students',
            'added' => 'added_students',
        ];

        if (isset($sortable[$request->sort])) {
            $query->orderBy($sortable[$request->sort], $direction);
        } else {
            // default order when no header was clicked
            $query->orderBy('mark_semester.courseid')
                ->orderByDesc('mark_batch.startyear')
                ->orderBy('mark_semester.semid');
        }

        // tie-breaker so pagination stays stable
        $query->orderBy('mark_student_list.studentlistid');
    }

    private function changeLock(StudentList $list, bool $lock, ?string $note, int $userId): bool
    {
    return DB::transaction(function () use ($list, $lock, $note, $userId) {
        // lock the row, so two people clicking at the same moment cannot both change it
        $current = StudentList::lockForUpdate()->find($list->studentlistid);

        if (! $current || $current->is_locked === $lock) {
            return false;
        }

        $current->update([
            'is_locked' => $lock,
            'locked_at' => $lock ? now() : null,
            'locked_by' => $lock ? $userId : null,
        ]);

        StudentListLock::create([
            'studentlistid' => $current->studentlistid,
            'action' => $lock ? 'lock' : 'unlock',
            'note' => $note,
            'user_id' => $userId,
        ]);

        return true;
    });
    }
}