<?php

namespace App\Http\Controllers;

use App\Http\Requests\DepartmentRequest;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Storage;

class DepartmentController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:departments.view', only: ['index']),
            new Middleware('permission:departments.create', only: ['create', 'store']),
            new Middleware('permission:departments.edit', only: ['edit', 'update']),
            new Middleware('permission:departments.delete', only: ['destroy']),
        ];
    }

    public function index(Request $request)
    {
        $departments = Department::query()
            ->when($request->q, function ($query, $q) {
                $query->where(function ($w) use ($q) {
                    $w->where('fullname', 'like', "%{$q}%")
                      ->orWhere('shortname', 'like', "%{$q}%");
                });
            })
            ->orderByDesc('deptid')
            ->paginate(10)
            ->withQueryString();

        return view('departments.index', compact('departments'));
    }

    public function create()
    {
        return view('departments.create', ['department' => new Department()]);
    }

    public function store(DepartmentRequest $request)
    {
        $data = $request->safe()->except('logo');
        $data['active'] = $request->boolean('active');

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('department-logos', 'public');
        }

        Department::create($data);

        return redirect()->route('departments.index')->with('success', 'Department created.');
    }

    public function edit(Department $department)
    {
        return view('departments.edit', compact('department'));
    }

    public function update(DepartmentRequest $request, Department $department)
    {
        $data = $request->safe()->except('logo');
        $data['active'] = $request->boolean('active');

        if ($request->hasFile('logo')) {
            if ($department->logo) {
                Storage::disk('public')->delete($department->logo);
            }
            $data['logo'] = $request->file('logo')->store('department-logos', 'public');
        }

        $department->update($data);

        return redirect()->route('departments.index')->with('success', 'Department updated.');
    }

    public function destroy(Department $department)
    {
        // Soft delete: keep the row, flag it as deleted
        $department->update(['is_delete' => true]);

        return redirect()->route('departments.index')->with('success', 'Department deleted.');
    }
}