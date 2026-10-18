<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Http\Requests\Branch\BranchRequest;
use App\Models\Branch\Branch;
use App\Models\Course\Course;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class BranchController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:branches.view', only: ['index']),
            new Middleware('permission:branches.create', only: ['create', 'store']),
            new Middleware('permission:branches.edit', only: ['edit', 'update']),
            new Middleware('permission:branches.delete', only: ['destroy']),
        ];
    }

    public function index(Request $request)
    {
        $perPage = in_array((int) $request->per_page, [10, 25, 50, 100]) ? (int) $request->per_page : 10;

        $query = Branch::query()
            ->with('course')
            ->when($request->courseid, fn ($q, $id) => $q->where('courseid', $id))
            ->when($request->q, function ($q, $search) {
                $q->where(function ($w) use ($search) {
                    $w->where('fullname', 'like', "%{$search}%")
                      ->orWhere('shortname', 'like', "%{$search}%");
                });
            });

        $this->applySort($query, $request);

        return view('branch.index', [
            'branches' => $query->paginate($perPage)->withQueryString(),
            'courses' => $this->courses(),
            'perPage' => $perPage,
        ]);
    }

    public function create()
    {
        return view('branch.create', [
            'branch' => new Branch(),
            'courses' => $this->courses(),
        ]);
    }

    public function store(BranchRequest $request)
    {
        Branch::create($this->payload($request));

        return redirect()->route('branches.index')->with('success', 'Branch created.');
    }

    public function edit(Branch $branch)
    {
        return view('branch.edit', [
            'branch' => $branch,
            'courses' => $this->courses($branch->courseid),
        ]);
    }

    public function update(BranchRequest $request, Branch $branch)
    {
        $branch->update($this->payload($request));

        return redirect()->route('branches.index')->with('success', 'Branch updated.');
    }

    public function destroy(Branch $branch)
    {
        $branch->update(['is_delete' => true]);

        return redirect()->route('branches.index')->with('success', 'Branch deleted.');
    }

    private function payload(BranchRequest $request): array
    {
        $data = $request->validated();
        $data['active'] = $request->boolean('active');

        return $data;
    }

    private function applySort($query, Request $request): void
    {
        $direction = $request->dir === 'desc' ? 'desc' : 'asc';
        $sort = $request->sort;

        // Only these plain columns may be sorted
        $columns = ['fullname', 'shortname', 'active'];

        if ($sort === 'course') {
            $query->orderBy(
                Course::select('shortname')
                    ->whereColumn('mark_course.courseid', 'mark_branch.courseid'),
                $direction
            );
        } elseif (in_array($sort, $columns, true)) {
            $query->orderBy($sort, $direction);
        } else {
            // default order when no header was clicked
            $query->orderBy('courseid')->orderBy('fullname');
        }

        // tie-breaker so pagination stays stable
        $query->orderBy('branchid');
    }

    // Only courses that have branches enabled. When editing, the branch's current
    // course stays in the list even if branches were later switched off for it.
    private function courses(?int $includeId = null)
    {
        return Course::query()
            ->where(function ($q) use ($includeId) {
                $q->where(fn ($w) => $w->where('active', true)->where('has_branch', true));

                if ($includeId) {
                    $q->orWhere('courseid', $includeId);
                }
            })
            ->orderBy('shortname')
            ->get();
    }
}