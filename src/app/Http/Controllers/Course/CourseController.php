<?php

namespace App\Http\Controllers\Course;

use App\Http\Controllers\Controller;
use App\Http\Requests\Course\CourseRequest;
use App\Models\Configuration\GradeType;
use App\Models\Course\Course;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class CourseController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:courses.view', only: ['index']),
            new Middleware('permission:courses.create', only: ['create', 'store']),
            new Middleware('permission:courses.edit', only: ['edit', 'update']),
            new Middleware('permission:courses.delete', only: ['destroy']),
        ];
    }

    public function index(Request $request)
    {
        $perPage = in_array((int) $request->per_page, [10, 25, 50, 100]) ? (int) $request->per_page : 10;

        $query = Course::query()
            ->with(['department', 'courseType'])
            ->when($request->deptid, fn ($q, $id) => $q->where('deptid', $id))
            ->when($request->coursetypeid, fn ($q, $id) => $q->where('coursetypeid', $id))
            ->when($request->structure_type, fn ($q, $type) => $q->where('structure_type', $type))
            ->when($request->q, function ($q, $search) {
                $q->where(function ($w) use ($search) {
                    $w->where('fullname', 'like', "%{$search}%")
                      ->orWhere('shortname', 'like', "%{$search}%");
                });
            });

        $this->applySort($query, $request);

        return view('course.index', [
            'courses' => $query->paginate($perPage)->withQueryString(),
            'departments' => $this->departments(),
            'courseTypes' => $this->courseTypes(),
            'structures' => Course::STRUCTURES,
            'perPage' => $perPage,
        ]);
    }

    public function create()
    {
        return view('course.create', [
            'course' => new Course(['structure_type' => 'semester']),
            'departments' => $this->departments(),
            'courseTypes' => $this->courseTypes(),
            'structures' => Course::STRUCTURES,
        ]);
    }

    public function store(CourseRequest $request)
    {
        Course::create($this->payload($request));

        return redirect()->route('courses.index')->with('success', 'Course created.');
    }

    public function edit(Course $course)
    {
        return view('course.edit', [
            'course' => $course,
            'departments' => $this->departments(),
            'courseTypes' => $this->courseTypes(),
            'structures' => Course::STRUCTURES,
        ]);
    }

    public function update(CourseRequest $request, Course $course)
    {
        $course->update($this->payload($request));

        return redirect()->route('courses.index')->with('success', 'Course updated.');
    }

    public function destroy(Course $course)
    {
        $course->update(['is_delete' => true]);

        return redirect()->route('courses.index')->with('success', 'Course deleted.');
    }

    private function payload(CourseRequest $request): array
    {
        $data = $request->validated();
        $data['has_branch'] = $request->boolean('has_branch');
        $data['active'] = $request->boolean('active');

        return $data;
    }

    private function applySort($query, Request $request): void
    {
        $direction = $request->dir === 'desc' ? 'desc' : 'asc';
        $sort = $request->sort;

        // Only these plain columns may be sorted
        $columns = ['fullname', 'shortname', 'structure_type', 'total_terms', 'has_branch', 'active'];

        if ($sort === 'department') {
            $query->orderBy(
                Department::select('fullname')
                    ->whereColumn('mark_department.deptid', 'mark_course.deptid'),
                $direction
            );
        } elseif ($sort === 'coursetype') {
            $query->orderBy(
                GradeType::select('coursetypename')
                    ->whereColumn('mark_course_type.coursetypeid', 'mark_course.coursetypeid'),
                $direction
            );
        } elseif (in_array($sort, $columns, true)) {
            $query->orderBy($sort, $direction);
        } else {
            // default order when no header was clicked
            $query->orderBy('deptid')->orderBy('coursetypeid')->orderBy('fullname');
        }

        // tie-breaker so pagination stays stable
        $query->orderBy('courseid');
    }

    private function departments()
    {
        return Department::where('active', true)->orderBy('fullname')->get();
    }

    private function courseTypes()
    {
        return GradeType::where('active', true)->orderBy('coursetypename')->get();
    }
}