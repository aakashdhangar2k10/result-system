<?php

namespace App\Http\Controllers\Grade;

use App\Http\Controllers\Controller;
use App\Http\Requests\Grade\GradeRequest;
use App\Models\Configuration\CourseType;
use App\Models\Configuration\GradeType;
use App\Models\Grade\Grade;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use App\Support\DeleteGuard;
class GradeController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:grades.view', only: ['index']),
            new Middleware('permission:grades.create', only: ['create', 'store']),
            new Middleware('permission:grades.edit', only: ['edit', 'update']),
            new Middleware('permission:grades.delete', only: ['destroy']),
        ];
    }

    public function index(Request $request)
    {
        $perPage = in_array((int) $request->per_page, [10, 25, 50, 100]) ? (int) $request->per_page : 10;

        $grades = Grade::query()
            ->with(['courseType', 'gradeType'])
            ->when($request->gradetypeid, fn ($query, $id) => $query->where('gradetypeid', $id))
            ->when($request->q, function ($query, $q) {
                $query->where(function ($w) use ($q) {
                    $w->where('firstattemptgrade', 'like', "%{$q}%")
                      ->orWhere('secondattemptgrade', 'like', "%{$q}%")
                      ->orWhere('description', 'like', "%{$q}%");
                });
            })
            ->orderBy('coursetypeid')
            ->orderBy('gradetypeid')
            ->orderByDesc('startscore')
            ->orderByDesc('endscore')
            ->paginate($perPage)
            ->withQueryString();

        return view('grade.index', [
            'grades' => $grades,
            'gradeTypes' => $this->gradeTypes(),
            'perPage' => $perPage,
        ]);
    }

    public function create()
    {
        return view('grade.create', [
            'grade' => new Grade(),
            'courseTypes' => $this->courseTypes(),
            'gradeTypes' => $this->gradeTypes(),
        ]);
    }

    public function store(GradeRequest $request)
    {
        $data = $request->validated();
        $data['active'] = $request->boolean('active');

        Grade::create($data);

        return redirect()->route('grades.index')->with('success', 'Grade created.');
    }

    public function edit(Grade $grade)
    {
        return view('grade.edit', [
            'grade' => $grade,
            'courseTypes' => $this->courseTypes(),
            'gradeTypes' => $this->gradeTypes(),
        ]);
    }

    public function update(GradeRequest $request, Grade $grade)
    {
        $data = $request->validated();
        $data['active'] = $request->boolean('active');

        $grade->update($data);

        return redirect()->route('grades.index')->with('success', 'Grade updated.');
    }

    public function destroy(Grade $grade)
    {
        if ($blocked = DeleteGuard::blocker($grade)) {
        return back()->with('error', $blocked);
        }
        $grade->update(['is_delete' => true]);

        return redirect()->route('grades.index')->with('success', 'Grade deleted.');
    }

    private function courseTypes()
    {
        return CourseType::where('active', true)->orderBy('fullname')->get();
    }

    private function gradeTypes()
    {
        return GradeType::where('active', true)->orderBy('coursetypename')->get();
    }
}