<?php

namespace App\Http\Controllers\Batch;

use App\Http\Controllers\Controller;
use App\Http\Requests\Batch\BatchRequest;
use App\Models\Batch\Batch;
use App\Models\Branch\Branch;
use App\Models\Course\Course;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class BatchController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:batches.view', only: ['index']),
            new Middleware('permission:batches.create', only: ['create', 'store']),
            new Middleware('permission:batches.edit', only: ['edit', 'update']),
            new Middleware('permission:batches.delete', only: ['destroy']),
        ];
    }

    public function index(Request $request)
    {
        $perPage = in_array((int) $request->per_page, [10, 25, 50, 100]) ? (int) $request->per_page : 10;

        $query = Batch::query()
            ->with(['course', 'branch'])
            ->when($request->courseid, fn ($q, $id) => $q->where('courseid', $id))
            ->when($request->q, function ($q, $search) {
                $q->where(function ($w) use ($search) {
                    $w->where('startyear', 'like', "%{$search}%")
                      ->orWhere('endyear', 'like', "%{$search}%");
                });
            });

        $this->applySort($query, $request);

        return view('batch.index', [
            'batches' => $query->paginate($perPage)->withQueryString(),
            'courses' => $this->courses(),
            'perPage' => $perPage,
        ]);
    }

    public function create()
    {
        return view('batch.create', [
            'batch' => new Batch(),
            'courses' => $this->courses(),
            'branches' => $this->branches(),
        ]);
    }

    public function store(BatchRequest $request)
    {
        Batch::create($this->payload($request));

        return redirect()->route('batches.index')->with('success', 'Batch created.');
    }

    public function edit(Batch $batch)
    {
        return view('batch.edit', [
            'batch' => $batch,
            'courses' => $this->courses($batch->courseid),
            'branches' => $this->branches($batch->branchid),
        ]);
    }

    public function update(BatchRequest $request, Batch $batch)
    {
        $batch->update($this->payload($request));

        return redirect()->route('batches.index')->with('success', 'Batch updated.');
    }

    public function destroy(Batch $batch)
    {
        $batch->update(['is_delete' => true]);

        return redirect()->route('batches.index')->with('success', 'Batch deleted.');
    }

    private function payload(BatchRequest $request): array
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
        $columns = ['startyear', 'endyear', 'active'];

        if ($sort === 'course') {
            $query->orderBy(
                Course::select('shortname')
                    ->whereColumn('mark_course.courseid', 'mark_batch.courseid'),
                $direction
            );
        } elseif ($sort === 'branch') {
            $query->orderBy(
                Branch::select('shortname')
                    ->whereColumn('mark_branch.branchid', 'mark_batch.branchid'),
                $direction
            );
        } elseif (in_array($sort, $columns, true)) {
            $query->orderBy($sort, $direction);
        } else {
            // default order when no header was clicked: newest batches first
            $query->orderByDesc('startyear')->orderBy('courseid');
        }

        // tie-breaker so pagination stays stable
        $query->orderBy('batchid');
    }

    // Active courses; when editing, the batch's current course stays selectable
    private function courses(?int $includeId = null)
    {
        return Course::query()
            ->where(function ($q) use ($includeId) {
                $q->where('active', true);

                if ($includeId) {
                    $q->orWhere('courseid', $includeId);
                }
            })
            ->orderBy('shortname')
            ->get();
    }

    // Active branches; the form narrows them to the selected course
    private function branches(?int $includeId = null)
    {
        return Branch::query()
            ->where(function ($q) use ($includeId) {
                $q->where('active', true);

                if ($includeId) {
                    $q->orWhere('branchid', $includeId);
                }
            })
            ->orderBy('shortname')
            ->get();
    }
}