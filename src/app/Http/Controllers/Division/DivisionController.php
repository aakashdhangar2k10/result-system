<?php

namespace App\Http\Controllers\Division;

use App\Http\Controllers\Controller;
use App\Http\Requests\Division\DivisionRequest;
use App\Models\Configuration\CourseType;
use App\Models\Configuration\DivisionType;
use App\Models\Division\Division;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class DivisionController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:divisions.view', only: ['index']),
            new Middleware('permission:divisions.create', only: ['create', 'store']),
            new Middleware('permission:divisions.edit', only: ['edit', 'update']),
            new Middleware('permission:divisions.delete', only: ['destroy']),
        ];
    }

    public function index(Request $request)
    {
        $perPage = in_array((int) $request->per_page, [10, 25, 50, 100]) ? (int) $request->per_page : 10;

        $query = Division::query()
            ->with(['courseType', 'divisionType'])
            ->when($request->coursetypeid, fn ($q, $id) => $q->where('coursetypeid', $id))
            ->when($request->divtypeid, fn ($q, $id) => $q->where('divtypeid', $id))
            ->when($request->q, fn ($q, $search) => $q->where('divisionname', 'like', "%{$search}%"));

        $this->applySort($query, $request);

        return view('division.index', [
            'divisions' => $query->paginate($perPage)->withQueryString(),
            'courseTypes' => $this->courseTypes(),
            'divisionTypes' => $this->divisionTypes(),
            'perPage' => $perPage,
        ]);
    }

    public function create()
    {
        return view('division.create', [
            'division' => new Division(),
            'courseTypes' => $this->courseTypes(),
            'divisionTypes' => $this->divisionTypes(),
        ]);
    }

    public function store(DivisionRequest $request)
    {
        $data = $request->validated();
        $data['active'] = $request->boolean('active');

        Division::create($data);

        return redirect()->route('divisions.index')->with('success', 'Division created.');
    }

    public function edit(Division $division)
    {
        return view('division.edit', [
            'division' => $division,
            'courseTypes' => $this->courseTypes(),
            'divisionTypes' => $this->divisionTypes(),
        ]);
    }

    public function update(DivisionRequest $request, Division $division)
    {
        $data = $request->validated();
        $data['active'] = $request->boolean('active');

        $division->update($data);

        return redirect()->route('divisions.index')->with('success', 'Division updated.');
    }

    public function destroy(Division $division)
    {
        $division->update(['is_delete' => true]);

        return redirect()->route('divisions.index')->with('success', 'Division deleted.');
    }

    private function applySort($query, Request $request): void
    {
        $direction = $request->dir === 'desc' ? 'desc' : 'asc';
        $sort = $request->sort;

        // Only these plain columns may be sorted
        $columns = ['divisionname', 'startpoint', 'endpoint', 'active'];

        if ($sort === 'course') {
            $query->orderBy(
                CourseType::select('fullname')
                    ->whereColumn('mark_coursetype.coursetypeid', 'mark_division.coursetypeid'),
                $direction
            );
        } elseif ($sort === 'divisiontype') {
            $query->orderBy(
                DivisionType::select('divtypename')
                    ->whereColumn('mark_division_type.divtypeid', 'mark_division.divtypeid'),
                $direction
            );
        } elseif (in_array($sort, $columns, true)) {
            $query->orderBy($sort, $direction);
        } else {
            // default order when no header was clicked
            $query->orderBy('coursetypeid')->orderBy('divtypeid')->orderByDesc('startpoint');
        }

        // tie-breaker so pagination stays stable
        $query->orderBy('divisionid');
    }

    private function courseTypes()
    {
        return CourseType::where('active', true)->orderBy('fullname')->get();
    }

    private function divisionTypes()
    {
        return DivisionType::where('active', true)->orderBy('divtypename')->get();
    }
}