<?php

namespace App\Http\Controllers\Configuration;

use App\Http\Controllers\Controller;
use App\Http\Requests\Configuration\GradeTypeRequest;
use App\Models\Configuration\GradeType;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class GradeTypeController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:gradetypes.view', only: ['index']),
            new Middleware('permission:gradetypes.create', only: ['create', 'store']),
            new Middleware('permission:gradetypes.edit', only: ['edit', 'update']),
            new Middleware('permission:gradetypes.delete', only: ['destroy']),
        ];
    }

    public function index(Request $request)
    {
        $gradeTypes = GradeType::query()
            ->when($request->q, fn ($query, $q) => $query->where('coursetypename', 'like', "%{$q}%"))
            ->orderByDesc('coursetypeid')
            ->paginate(10)
            ->withQueryString();

        return view('configuration.grade_types.index', compact('gradeTypes'));
    }

    public function create()
    {
        return view('configuration.grade_types.create', ['gradeType' => new GradeType()]);
    }

    public function store(GradeTypeRequest $request)
    {
        $data = $request->validated();
        $data['active'] = $request->boolean('active');

        GradeType::create($data);

        return redirect()->route('grade-types.index')->with('success', 'Grade type created.');
    }

    public function edit(GradeType $gradeType)
    {
        return view('configuration.grade_types.edit', compact('gradeType'));
    }

    public function update(GradeTypeRequest $request, GradeType $gradeType)
    {
        $data = $request->validated();
        $data['active'] = $request->boolean('active');

        $gradeType->update($data);

        return redirect()->route('grade-types.index')->with('success', 'Grade type updated.');
    }

    public function destroy(GradeType $gradeType)
    {
        $gradeType->update(['is_delete' => true]);

        return redirect()->route('grade-types.index')->with('success', 'Grade type deleted.');
    }
}