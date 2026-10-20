<?php

namespace App\Http\Controllers\Semester;

use App\Http\Controllers\Controller;
use App\Http\Requests\Semester\CourseSemesterRequest;
use App\Models\Batch\Batch;
use App\Models\Course\Course;
use App\Models\Semester\CourseSemester;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use App\Support\DeleteGuard;
class CourseSemesterController extends Controller implements HasMiddleware
{
    use TermOptions;

    public static function middleware(): array
    {
        return [
            new Middleware('permission:coursesemesters.view', only: ['index']),
            new Middleware('permission:coursesemesters.create', only: ['create', 'store']),
            new Middleware('permission:coursesemesters.edit', only: ['edit', 'update']),
            new Middleware('permission:coursesemesters.delete', only: ['destroy']),
        ];
    }

    public function index(Request $request)
    {
        $perPage = in_array((int) $request->per_page, [10, 25, 50, 100]) ? (int) $request->per_page : 10;

        $query = CourseSemester::query()
            ->with(['course', 'batch.branch', 'term'])
            ->when($request->courseid, fn ($q, $id) => $q->where('courseid', $id))
            ->when($request->batchid, fn ($q, $id) => $q->where('batchid', $id))
            ->when($request->q, fn ($q, $search) => $q->where('description', 'like', "%{$search}%"));

        $this->applySort($query, $request);

        return view('semester.index', [
            'rows' => $query->paginate($perPage)->withQueryString(),
            'perPage' => $perPage,
        ] + $this->termOptions());
    }

    public function create()
    {
        return view('semester.create', ['courseSemester' => new CourseSemester()] + $this->termOptions());
    }

    public function store(CourseSemesterRequest $request)
    {
        CourseSemester::create($this->payload($request));

        return redirect()->route('course-semesters.index')->with('success', 'Semester created.');
    }

    public function edit(CourseSemester $courseSemester)
    {
        return view('semester.edit', ['courseSemester' => $courseSemester] + $this->termOptions($courseSemester));
    }

    public function update(CourseSemesterRequest $request, CourseSemester $courseSemester)
    {
        $courseSemester->update($this->payload($request));

        return redirect()->route('course-semesters.index')->with('success', 'Semester updated.');
    }

    public function destroy(CourseSemester $courseSemester)
    {
        if ($blocked = DeleteGuard::blocker($courseSemester)) {
        return back()->with('error', $blocked);
        }
        $courseSemester->update(['is_delete' => true]);

        return redirect()->route('course-semesters.index')->with('success', 'Semester deleted.');
    }

    private function payload(CourseSemesterRequest $request): array
    {
        $data = $request->validated();
        $data['active'] = $request->boolean('active');

        return $data;
    }

    private function applySort($query, Request $request): void
    {
        $direction = $request->dir === 'desc' ? 'desc' : 'asc';
        $sort = $request->sort;

        if ($sort === 'course') {
            $query->orderBy(
                Course::select('shortname')->whereColumn('mark_course.courseid', 'mark_semester.courseid'),
                $direction
            );
        } elseif ($sort === 'session') {
            $query->orderBy(
                Batch::select('startyear')->whereColumn('mark_batch.batchid', 'mark_semester.batchid'),
                $direction
            );
        } elseif ($sort === 'term') {
            $query->orderBy('semid', $direction);
        } elseif (in_array($sort, ['semestermonth', 'validcredit', 'active'], true)) {
            $query->orderBy($sort, $direction);
        } else {
            // default order when no header was clicked
            $query->orderBy('courseid')->orderByDesc('batchid')->orderBy('semid');
        }

        // tie-breaker so pagination stays stable
        $query->orderBy('semesterid');
    }
}