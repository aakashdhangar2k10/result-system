<?php

namespace App\Http\Controllers\Configuration;

use App\Http\Controllers\Controller;
use App\Http\Requests\Configuration\SemesterListRequest;
use App\Models\Configuration\SemesterList;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class SemesterListController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:semesters.view', only: ['index']),
            new Middleware('permission:semesters.create', only: ['create', 'store']),
            new Middleware('permission:semesters.edit', only: ['edit', 'update']),
            new Middleware('permission:semesters.delete', only: ['destroy']),
        ];
    }

    public function index(Request $request)
    {
        $semesters = SemesterList::query()
            ->when($request->q, function ($query, $q) {
                $query->where(function ($w) use ($q) {
                    $w->where('name', 'like', "%{$q}%")
                      ->orWhere('roman', 'like', "%{$q}%");
                });
            })
            ->orderBy('semid')
            ->paginate(10)
            ->withQueryString();

        return view('configuration.semesters.index', compact('semesters'));
    }

    public function create()
    {
        return view('configuration.semesters.create', ['semester' => new SemesterList()]);
    }

    public function store(SemesterListRequest $request)
    {
        $data = $request->validated();
        $data['active'] = $request->boolean('active');

        SemesterList::create($data);

        return redirect()->route('semesters.index')->with('success', 'Semester created.');
    }

    public function edit(SemesterList $semester)
    {
        return view('configuration.semesters.edit', compact('semester'));
    }

    public function update(SemesterListRequest $request, SemesterList $semester)
    {
        $data = $request->validated();
        $data['active'] = $request->boolean('active');

        $semester->update($data);

        return redirect()->route('semesters.index')->with('success', 'Semester updated.');
    }

    public function destroy(SemesterList $semester)
    {
        $semester->update(['is_delete' => true]);

        return redirect()->route('semesters.index')->with('success', 'Semester deleted.');
    }
}