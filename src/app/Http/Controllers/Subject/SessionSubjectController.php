<?php

namespace App\Http\Controllers\Subject;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Semester\TermOptions;
use App\Http\Requests\Subject\SessionSubjectRequest;
use App\Models\Batch\Batch;
use App\Models\Course\Course;
use App\Models\Subject\SessionSubject;
use App\Models\Subject\Subject;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;

class SessionSubjectController extends Controller implements HasMiddleware
{
    use TermOptions;

    public static function middleware(): array
    {
        return [
            new Middleware('permission:sessionsubjects.view', only: ['index']),
            new Middleware('permission:sessionsubjects.create', only: ['create', 'store']),
            new Middleware('permission:sessionsubjects.edit', only: ['edit', 'update']),
            new Middleware('permission:sessionsubjects.create|sessionsubjects.edit', only: ['options']),
            new Middleware('permission:sessionsubjects.delete', only: ['destroy']),
        ];
    }

    public function index(Request $request)
    {
        $perPage = in_array((int) $request->per_page, [10, 25, 50, 100]) ? (int) $request->per_page : 10;

        $query = SessionSubject::query()
            ->with(['course', 'batch.branch', 'term', 'subjects'])
            ->when($request->courseid, fn ($q, $id) => $q->where('courseid', $id))
            ->when($request->batchid, fn ($q, $id) => $q->where('batchid', $id))
            ->when($request->semid, fn ($q, $id) => $q->where('semid', $id))
            ->when($request->q, function ($q, $search) {
                // search by subject code or name
                $q->whereHas('subjects', function ($s) use ($search) {
                    $s->where('subjectcode', 'like', "%{$search}%")
                      ->orWhere('subjectname', 'like', "%{$search}%");
                });
            });

        $this->applySort($query, $request);

        return view('session_subject.index', [
            'rows' => $query->paginate($perPage)->withQueryString(),
            'perPage' => $perPage,
        ] + $this->termOptions());
    }

    public function create()
    {
        return view('session_subject.create', $this->termOptions());
    }

    // HTML fragment with the subject ticks for a course + semester, loaded by the add page
    public function options(Request $request)
    {
        if (! $request->ajax()) {
            return redirect()->route('session-subjects.index');
        }

        $request->validate([
            'courseid' => ['required', 'integer'],
            'semid' => ['required', 'integer'],
        ]);

        $selected = array_map('intval', (array) $request->input('selected', []));

        return view('session_subject._subjects', $this->subjectGroups(
            (int) $request->courseid,
            (int) $request->semid,
            $selected,
            $request->boolean('check_all')
        ));
    }

    public function store(SessionSubjectRequest $request)
    {
        DB::transaction(function () use ($request) {
            $record = SessionSubject::create($request->safe()->only(['courseid', 'batchid', 'semid']));
            $record->subjects()->sync($request->validated('subject_ids'));
        });

        return redirect()->route('session-subjects.index')->with('success', 'Session subjects saved.');
    }

    public function edit(SessionSubject $sessionSubject)
    {
        $sessionSubject->load(['course', 'batch.branch', 'term', 'subjects']);

        return view('session_subject.edit', [
            'sessionSubject' => $sessionSubject,
        ] + $this->subjectGroups(
            $sessionSubject->courseid,
            $sessionSubject->semid,
            $sessionSubject->subjects->pluck('subjectid')->map(fn ($id) => (int) $id)->all()
        ));
    }

    public function update(SessionSubjectRequest $request, SessionSubject $sessionSubject)
    {
        DB::transaction(function () use ($request, $sessionSubject) {
            $sessionSubject->subjects()->sync($request->validated('subject_ids'));
            $sessionSubject->touch();
        });

        return redirect()->route('session-subjects.index')->with('success', 'Session subjects updated.');
    }

    public function destroy(SessionSubject $sessionSubject)
    {
        $sessionSubject->update(['is_delete' => true]);

        return redirect()->route('session-subjects.index')->with('success', 'Session subjects deleted.');
    }

    // The subjects of a course + semester, grouped by subject type.
    // Subjects already chosen stay in the list even if they were deactivated later.
    private function subjectGroups(int $courseId, int $semId, array $selected = [], bool $checkAll = false): array
    {
        $subjects = Subject::with('subjectType')
            ->where('courseid', $courseId)
            ->where('semid', $semId)
            ->where(function ($q) use ($selected) {
                $q->where('active', true);

                if ($selected) {
                    $q->orWhereIn('subjectid', $selected);
                }
            })
            ->orderBy('subjecttypeid')
            ->orderBy('subjectorder')
            ->orderBy('subjectid')
            ->get();

        return [
            'groups' => $subjects->groupBy('subjecttypeid'),
            'selected' => $checkAll ? $subjects->pluck('subjectid')->all() : $selected,
        ];
    }

    private function applySort($query, Request $request): void
    {
        $direction = $request->dir === 'desc' ? 'desc' : 'asc';
        $sort = $request->sort;

        if ($sort === 'course') {
            $query->orderBy(
                Course::select('shortname')->whereColumn('mark_course.courseid', 'mark_session_subject.courseid'),
                $direction
            );
        } elseif ($sort === 'session') {
            $query->orderBy(
                Batch::select('startyear')->whereColumn('mark_batch.batchid', 'mark_session_subject.batchid'),
                $direction
            );
        } elseif ($sort === 'term') {
            $query->orderBy('semid', $direction);
        } else {
            // default order when no header was clicked
            $query->orderBy('courseid')->orderByDesc('batchid')->orderBy('semid');
        }

        // tie-breaker so pagination stays stable
        $query->orderBy('sessionsubjectid');
    }
}