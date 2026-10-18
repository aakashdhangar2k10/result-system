<?php

namespace App\Http\Controllers\CourseSession;

use App\Http\Controllers\Controller;
use App\Http\Requests\CourseSession\SessionGradeRequest;
use App\Models\Batch\Batch;
use App\Models\Configuration\DivisionType;
use App\Models\Configuration\GradeType;
use App\Models\Course\Course;
use App\Models\CourseSession\SessionGrade;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class SessionGradeController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:sessiongrades.view', only: ['index']),
            new Middleware('permission:sessiongrades.create', only: ['create', 'store']),
            new Middleware('permission:sessiongrades.edit', only: ['edit', 'update']),
            new Middleware('permission:sessiongrades.delete', only: ['destroy']),
        ];
    }

    public function index(Request $request)
    {
        $perPage = in_array((int) $request->per_page, [10, 25, 50, 100]) ? (int) $request->per_page : 10;

        $query = SessionGrade::query()
            ->with(['course', 'batch.branch', 'gradeType', 'divisionType'])
            ->when($request->courseid, fn ($q, $id) => $q->where('courseid', $id))
            ->when($request->gradetypeid, fn ($q, $id) => $q->where('gradetypeid', $id))
            ->when($request->divtypeid, fn ($q, $id) => $q->where('divtypeid', $id))
            ->when($request->q, fn ($q, $search) => $q->where('description', 'like', "%{$search}%"));

        $this->applySort($query, $request);

        return view('coursesession.session_grades.index', [
            'sessionGrades' => $query->paginate($perPage)->withQueryString(),
            'perPage' => $perPage,
        ] + $this->formData());
    }

    public function create()
    {
        return view('coursesession.session_grades.create', [
            'sessionGrade' => new SessionGrade(),
        ] + $this->formData());
    }

    public function store(SessionGradeRequest $request)
    {
        SessionGrade::create($this->payload($request));

        return redirect()->route('session-grades.index')->with('success', 'Session grade created.');
    }

    public function edit(SessionGrade $sessionGrade)
    {
        return view('coursesession.session_grades.edit', [
            'sessionGrade' => $sessionGrade,
        ] + $this->formData($sessionGrade));
    }

    public function update(SessionGradeRequest $request, SessionGrade $sessionGrade)
    {
        $sessionGrade->update($this->payload($request));

        return redirect()->route('session-grades.index')->with('success', 'Session grade updated.');
    }

    public function destroy(SessionGrade $sessionGrade)
    {
        $sessionGrade->update(['is_delete' => true]);

        return redirect()->route('session-grades.index')->with('success', 'Session grade deleted.');
    }

    private function payload(SessionGradeRequest $request): array
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
                Course::select('shortname')
                    ->whereColumn('mark_course.courseid', 'mark_session_grade.courseid'),
                $direction
            );
        } elseif ($sort === 'session') {
            $query->orderBy(
                Batch::select('startyear')
                    ->whereColumn('mark_batch.batchid', 'mark_session_grade.batchid'),
                $direction
            );
        } elseif ($sort === 'gradetype') {
            $query->orderBy(
                GradeType::select('coursetypename')
                    ->whereColumn('mark_course_type.coursetypeid', 'mark_session_grade.gradetypeid'),
                $direction
            );
        } elseif ($sort === 'divisiontype') {
            $query->orderBy(
                DivisionType::select('divtypename')
                    ->whereColumn('mark_division_type.divtypeid', 'mark_session_grade.divtypeid'),
                $direction
            );
        } elseif ($sort === 'active') {
            $query->orderBy('active', $direction);
        } else {
            // default order when no header was clicked
            $query->orderBy('courseid')->orderByDesc('batchid');
        }

        // tie-breaker so pagination stays stable
        $query->orderBy('sessiongradeid');
    }

    // Dropdown data. When editing, the record's current values stay selectable
    // even if they were deactivated later.
    private function formData(?SessionGrade $current = null): array
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
            'batches' => $active(Batch::query()->with('branch'), 'batchid', $current?->batchid)
                ->orderByDesc('startyear')->get(),
            'gradeTypes' => $active(GradeType::query(), 'coursetypeid', $current?->gradetypeid)
                ->orderBy('coursetypename')->get(),
            'divisionTypes' => $active(DivisionType::query(), 'divtypeid', $current?->divtypeid)
                ->orderBy('divtypename')->get(),
        ];
    }
}