<?php

namespace App\Http\Controllers\Semester;

use App\Http\Controllers\Controller;
use App\Http\Requests\Semester\SemesterExamRequest;
use App\Models\Batch\Batch;
use App\Models\Course\Course;
use App\Models\Semester\SemesterExam;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use App\Support\DeleteGuard;
class SemesterExamController extends Controller implements HasMiddleware
{
    use TermOptions;

    public static function middleware(): array
    {
        return [
            new Middleware('permission:semesterexams.view', only: ['index']),
            new Middleware('permission:semesterexams.create', only: ['create', 'store']),
            new Middleware('permission:semesterexams.edit', only: ['edit', 'update']),
            new Middleware('permission:semesterexams.delete', only: ['destroy']),
        ];
    }

    public function index(Request $request)
    {
        $perPage = in_array((int) $request->per_page, [10, 25, 50, 100]) ? (int) $request->per_page : 10;

        $query = SemesterExam::query()
            ->with(['course', 'batch.branch', 'term'])
            ->when($request->courseid, fn ($q, $id) => $q->where('courseid', $id))
            ->when($request->batchid, fn ($q, $id) => $q->where('batchid', $id))
            ->when($request->semid, fn ($q, $id) => $q->where('semid', $id))
            ->when($request->q, fn ($q, $search) => $q->where('description', 'like', "%{$search}%"));

        $this->applySort($query, $request);

        return view('semester_exam.index', [
            'rows' => $query->paginate($perPage)->withQueryString(),
            'perPage' => $perPage,
        ] + $this->termOptions());
    }

    public function create()
    {
        return view('semester_exam.create', [
            'semesterExam' => new SemesterExam(['attempt' => 1]),
            'attempts' => SemesterExam::ATTEMPTS,
        ] + $this->termOptions());
    }

    public function store(SemesterExamRequest $request)
    {
        SemesterExam::create($this->payload($request));

        return redirect()->route('semester-exams.index')->with('success', 'Semester exam created.');
    }

    public function edit(SemesterExam $semesterExam)
    {
        return view('semester_exam.edit', [
            'semesterExam' => $semesterExam,
            'attempts' => SemesterExam::ATTEMPTS,
        ] + $this->termOptions($semesterExam));
    }

    public function update(SemesterExamRequest $request, SemesterExam $semesterExam)
    {
        $semesterExam->update($this->payload($request));

        return redirect()->route('semester-exams.index')->with('success', 'Semester exam updated.');
    }

    public function destroy(SemesterExam $semesterExam)
    {
        if ($blocked = DeleteGuard::blocker($semesterExam)) {
        return back()->with('error', $blocked);
        }
        $semesterExam->update(['is_delete' => true]);

        return redirect()->route('semester-exams.index')->with('success', 'Semester exam deleted.');
    }

    private function payload(SemesterExamRequest $request): array
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
                Course::select('shortname')->whereColumn('mark_course.courseid', 'mark_semester_exam.courseid'),
                $direction
            );
        } elseif ($sort === 'session') {
            $query->orderBy(
                Batch::select('startyear')->whereColumn('mark_batch.batchid', 'mark_semester_exam.batchid'),
                $direction
            );
        } elseif ($sort === 'term') {
            $query->orderBy('semid', $direction);
        } elseif (in_array($sort, ['attempt', 'exammonth', 'resultdate', 'active'], true)) {
            $query->orderBy($sort, $direction);
        } else {
            // default order when no header was clicked
            $query->orderBy('courseid')->orderByDesc('batchid')->orderBy('semid')->orderBy('attempt');
        }

        // tie-breaker so pagination stays stable
        $query->orderBy('semesterexamid');
    }
}