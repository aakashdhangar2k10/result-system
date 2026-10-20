<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\StudentRequest;
use App\Models\Batch\Batch;
use App\Models\Course\Course;
use App\Models\Student\Student;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Storage;
use App\Support\DeleteGuard;
class StudentController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:students.view', only: ['index', 'show']),
            new Middleware('permission:students.create', only: ['create', 'store']),
            new Middleware('permission:students.edit', only: ['edit', 'update', 'status']),
            new Middleware('permission:students.delete', only: ['destroy']),
        ];
    }

    public function index(Request $request)
    {
        $perPage = in_array((int) $request->per_page, [10, 25, 50, 100]) ? (int) $request->per_page : 10;

        $query = Student::query()
            ->with(['course', 'batch.branch'])
            ->when($request->courseid, fn ($q, $id) => $q->where('courseid', $id))
            ->when($request->batchid, fn ($q, $id) => $q->where('batchid', $id))
            ->when($request->q, function ($q, $search) {
                $q->where(function ($w) use ($search) {
                    $w->where('firstname', 'like', "%{$search}%")
                      ->orWhere('lastname', 'like', "%{$search}%")
                      ->orWhere('rollno', 'like', "%{$search}%")
                      ->orWhere('enrollmentno', 'like', "%{$search}%")
                      ->orWhere('apaarid', 'like', "%{$search}%")
                      ->orWhere('fathername', 'like', "%{$search}%")
                      ->orWhere('mothername', 'like', "%{$search}%")
                      ->orWhere('mobile', 'like', "%{$search}%");
                });
            });

        $this->applySort($query, $request);

        return view('student.index', [
            'students' => $query->paginate($perPage)->withQueryString(),
            'perPage' => $perPage,
        ] + $this->options());
    }

    // HTML fragment for the popup opened by the magnifier button
    public function show(Request $request, Student $student)
    {
        if (! $request->ajax()) {
            return redirect()->route('students.index');
        }

        $student->load(['course', 'batch.branch']);

        return view('student._detail', compact('student'));
    }

    public function create()
    {
        return view('student.create', ['student' => new Student()] + $this->options());
    }

    public function store(StudentRequest $request)
    {
        $data = $request->safe()->except('image');

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('student-photos', 'public');
        }

        Student::create($data);

        return redirect()->route('students.index')->with('success', 'Student added.');
    }

    public function edit(Student $student)
    {
        return view('student.edit', ['student' => $student] + $this->options($student));
    }

    public function update(StudentRequest $request, Student $student)
    {
        $data = $request->safe()->except('image');

        if ($request->hasFile('image')) {
            if ($student->image) {
                Storage::disk('public')->delete($student->image);
            }
            $data['image'] = $request->file('image')->store('student-photos', 'public');
        }

        $student->update($data);

        return redirect()->route('students.index')->with('success', 'Student updated.');
    }

    // Active / Deactive buttons in the list
    public function status(Request $request, Student $student)
    {
        $data = $request->validate(['active' => ['required', 'boolean']]);

        $student->update(['active' => (bool) $data['active']]);

        return back()->with('success', 'Student ' . ($student->active ? 'activated' : 'deactivated') . '.');
    }

    private function applySort($query, Request $request): void
    {
        $direction = $request->dir === 'desc' ? 'desc' : 'asc';
        $sort = $request->sort;

        // Only these plain columns may be sorted
        $columns = ['rollno', 'enrollmentno', 'apaarid', 'firstname', 'fathername', 'mothername', 'active'];

        if ($sort === 'course') {
            $query->orderBy(
                Course::select('fullname')->whereColumn('mark_course.courseid', 'mark_student.courseid'),
                $direction
            );
        } elseif ($sort === 'session') {
            $query->orderBy(
                Batch::select('startyear')->whereColumn('mark_batch.batchid', 'mark_student.batchid'),
                $direction
            );
        } elseif (in_array($sort, $columns, true)) {
            $query->orderBy($sort, $direction);
        } else {
            // default order when no header was clicked
            $query->orderBy('courseid')->orderByDesc('batchid')->orderBy('rollno');
        }

        // tie-breaker so pagination stays stable
        $query->orderBy('studentid');
    }

    // Active courses and sessions. When editing, the student's current values
    // stay selectable even if they were deactivated later.
    private function options(?Student $current = null): array
    {
        $active = fn ($query, string $key, $id) => $query->where(function ($q) use ($key, $id) {
            $q->where('active', true);

            if ($id) {
                $q->orWhere($key, $id);
            }
        });

        return [
            'courses' => $active(Course::query(), 'courseid', $current?->courseid)
                ->orderBy('shortname')->get(),
            'batches' => $active(Batch::query()->with(['course', 'branch']), 'batchid', $current?->batchid)
                ->orderByDesc('startyear')->get(),
        ];
    }

    public function destroy(Student $student)
    {
        if ($blocked = DeleteGuard::blocker($student)) {
        return back()->with('error', $blocked);
        }
    // soft delete: the row and the photo are kept, so the student can be restored later
    $student->update(['is_delete' => true]);
    return redirect()->route('students.index')->with('success', 'Student deleted.');
    }
}