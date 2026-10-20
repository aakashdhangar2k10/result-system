<?php

namespace App\Http\Controllers\Configuration;

use App\Http\Controllers\Controller;
use App\Http\Requests\Configuration\SubjectTypeRequest;
use App\Models\Configuration\SubjectType;
use App\Models\Subject\Subject;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use App\Support\DeleteGuard;
class SubjectTypeController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:subjecttypes.view', only: ['index']),
            new Middleware('permission:subjecttypes.create', only: ['create', 'store']),
            new Middleware('permission:subjecttypes.edit', only: ['edit', 'update']),
            new Middleware('permission:subjecttypes.delete', only: ['destroy']),
        ];
    }

    public function index(Request $request)
    {
        $perPage = in_array((int) $request->per_page, [10, 25, 50, 100]) ? (int) $request->per_page : 10;

        $query = SubjectType::query()
            ->when($request->q, fn ($q, $search) => $q->where('subjecttypename', 'like', "%{$search}%"));

        $direction = $request->dir === 'desc' ? 'desc' : 'asc';
        $columns = ['subjecttypename', 'has_internal', 'has_external', 'has_grade', 'is_optional', 'active'];

        in_array($request->sort, $columns, true)
            ? $query->orderBy($request->sort, $direction)
            : $query->orderBy('subjecttypeid');

        $query->orderBy('subjecttypeid');

        return view('configuration.subject_types.index', [
            'subjectTypes' => $query->paginate($perPage)->withQueryString(),
            'perPage' => $perPage,
        ]);
    }

    public function create()
    {
        return view('configuration.subject_types.create', [
            'subjectType' => new SubjectType(['has_internal' => true, 'has_external' => true, 'has_grade' => false,'is_optional' => false, 'active' => true]),
        ]);
    }

    public function store(SubjectTypeRequest $request)
    {
        SubjectType::create($this->payload($request));

        return redirect()->route('subject-types.index')->with('success', 'Subject type created.');
    }

    public function edit(SubjectType $subjectType)
    {
        return view('configuration.subject_types.edit', compact('subjectType'));
    }

    public function update(SubjectTypeRequest $request, SubjectType $subjectType)
    {
        $subjectType->update($this->payload($request));

        return redirect()->route('subject-types.index')->with('success', 'Subject type updated.');
    }

    public function destroy(SubjectType $subjectType)
    {
        if ($blocked = DeleteGuard::blocker($subjectType)) {
        return back()->with('error', $blocked);
        }
        // subjects keep pointing at their type, so a type in use can't be deleted
        if (Subject::where('subjecttypeid', $subjectType->subjecttypeid)->exists()) {
            return redirect()->route('subject-types.index')
                ->with('error', 'This subject type is used by subjects and cannot be deleted. Mark it inactive instead.');
        }

        $subjectType->update(['is_delete' => true]);

        return redirect()->route('subject-types.index')->with('success', 'Subject type deleted.');
    }

    private function payload(SubjectTypeRequest $request): array
    {
        $data = $request->validated();

        foreach (['has_internal', 'has_external', 'has_grade', 'is_optional', 'active'] as $flag) {
            $data[$flag] = $request->boolean($flag);
        }

        return $data;
    }
}