<?php

namespace App\Http\Controllers\Subject;

use App\Http\Controllers\Controller;
use App\Http\Requests\Subject\SubjectRequest;
use App\Models\Configuration\SemesterList;
use App\Models\Configuration\SubjectType;
use App\Models\Course\Course;
use App\Models\Subject\Subject;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class SubjectController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:subjects.view', only: ['index']),
            new Middleware('permission:subjects.create', only: ['create', 'store']),
            new Middleware('permission:subjects.edit', only: ['edit', 'update']),
            new Middleware('permission:subjects.delete', only: ['destroy']),
        ];
    }

    public function index(Request $request)
    {
        $perPage = in_array((int) $request->per_page, [10, 25, 50, 100]) ? (int) $request->per_page : 10;

        $query = Subject::query()
            ->with(['course', 'term', 'subjectType'])
            ->when($request->courseid, fn ($q, $id) => $q->where('courseid', $id))
            ->when($request->semid, fn ($q, $id) => $q->where('semid', $id))
            ->when($request->subjecttypeid, fn ($q, $id) => $q->where('subjecttypeid', $id))
            ->when($request->q, function ($q, $search) {
                $q->where(function ($w) use ($search) {
                    $w->where('subjectname', 'like', "%{$search}%")
                      ->orWhere('subjectcode', 'like', "%{$search}%");
                });
            });

        $this->applySort($query, $request);

        return view('subject.index', [
            'subjects' => $query->paginate($perPage)->withQueryString(),
            'perPage' => $perPage,
        ] + $this->options());
    }

    public function create()
    {
        return view('subject.create', ['subject' => new Subject()] + $this->options());
    }

    public function store(SubjectRequest $request)
    {
    $data = $this->payload($request);
    $data['subjectorder'] = $this->nextOrder($data['courseid'], $data['semid']);

    Subject::create($data);

    return redirect()->route('subjects.index')->with('success', 'Subject created.');
    }

    public function edit(Subject $subject)
    {
        return view('subject.edit', ['subject' => $subject] + $this->options($subject));
    }

    public function update(SubjectRequest $request, Subject $subject)
    {
    $data = $this->payload($request);

    // moved to another course or semester: put it at the end of the new list
    if ((int) $data['courseid'] !== (int) $subject->courseid || (int) $data['semid'] !== (int) $subject->semid) {
        $data['subjectorder'] = $this->nextOrder($data['courseid'], $data['semid']);
    }

    $subject->update($data);

    return redirect()->route('subjects.index')->with('success', 'Subject updated.');
    }

    private function nextOrder($courseId, $semId): int
    {
    return (int) Subject::where('courseid', $courseId)->where('semid', $semId)->max('subjectorder') + 1;
    }

    public function destroy(Subject $subject)
    {
        $subject->update(['is_delete' => true]);

        return redirect()->route('subjects.index')->with('success', 'Subject deleted.');
    }

    private function payload(SubjectRequest $request): array
    {
        $data = $request->validated();
        $data['add_grade'] = $request->boolean('add_grade');
        $data['active'] = $request->boolean('active');

        return $data;
    }

    private function applySort($query, Request $request): void
    {
        $direction = $request->dir === 'desc' ? 'desc' : 'asc';
        $sort = $request->sort;

        // Only these plain columns may be sorted
        $columns = [
            'subjectname', 'subjectcode', 'subjectcredit',
            'maxinternalmarks', 'maxexternalmarks', 'add_grade', 'active',
        ];

        if ($sort === 'course') {
            $query->orderBy(
                Course::select('shortname')->whereColumn('mark_course.courseid', 'mark_subject.courseid'),
                $direction
            );
        } elseif ($sort === 'subjecttype') {
            $query->orderBy(
                SubjectType::select('subjecttypename')
                    ->whereColumn('mark_subject_type.subjecttypeid', 'mark_subject.subjecttypeid'),
                $direction
            );
        } elseif ($sort === 'term') {
            $query->orderBy('semid', $direction);
        } elseif (in_array($sort, $columns, true)) {
            $query->orderBy($sort, $direction);
        } else {
            // default order when no header was clicked
            $query->orderBy('courseid')->orderBy('semid')->orderBy('subjectorder')->orderBy('subjectname');
        }

        // tie-breaker so pagination stays stable
        $query->orderBy('subjectid');
    }

    // Active courses, terms and subject types. When editing, the record's current
    // values stay selectable even if they were deactivated later.
    private function options(?Subject $current = null): array
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
            'terms' => $active(SemesterList::query(), 'semid', $current?->semid)
                ->orderBy('semid')->get(),
            'subjectTypes' => $active(SubjectType::query(), 'subjecttypeid', $current?->subjecttypeid)
                ->orderBy('subjecttypeid')->get(),
        ];
    }
}