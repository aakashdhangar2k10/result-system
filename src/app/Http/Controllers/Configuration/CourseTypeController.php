<?php

namespace App\Http\Controllers\Configuration;

use App\Http\Controllers\Controller;
use App\Http\Requests\Configuration\CourseTypeRequest;
use App\Models\Configuration\CourseType;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use App\Support\DeleteGuard;
class CourseTypeController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:coursetypes.view', only: ['index']),
            new Middleware('permission:coursetypes.create', only: ['create', 'store']),
            new Middleware('permission:coursetypes.edit', only: ['edit', 'update']),
            new Middleware('permission:coursetypes.delete', only: ['destroy']),
        ];
    }

    public function index(Request $request)
    {
        $courseTypes = CourseType::query()
            ->when($request->q, function ($query, $q) {
                $query->where(function ($w) use ($q) {
                    $w->where('fullname', 'like', "%{$q}%")
                      ->orWhere('shortname', 'like', "%{$q}%");
                });
            })
            ->orderByDesc('coursetypeid')
            ->paginate(10)
            ->withQueryString();

        return view('configuration.course_types.index', compact('courseTypes'));
    }

    public function create()
    {
        return view('configuration.course_types.create', ['courseType' => new CourseType()]);
    }

    public function store(CourseTypeRequest $request)
    {
        $data = $request->validated();
        $data['active'] = $request->boolean('active');

        CourseType::create($data);

        return redirect()->route('course-types.index')->with('success', 'Course type created.');
    }

    public function edit(CourseType $courseType)
    {
        return view('configuration.course_types.edit', compact('courseType'));
    }

    public function update(CourseTypeRequest $request, CourseType $courseType)
    {
        $data = $request->validated();
        $data['active'] = $request->boolean('active');

        $courseType->update($data);

        return redirect()->route('course-types.index')->with('success', 'Course type updated.');
    }

    public function destroy(CourseType $courseType)
    {
        if ($blocked = DeleteGuard::blocker($courseType)) {
        return back()->with('error', $blocked);
        }
        $courseType->update(['is_delete' => true]);

        return redirect()->route('course-types.index')->with('success', 'Course type deleted.');
    }
}