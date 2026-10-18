<?php

namespace App\Http\Controllers\Subject;

use App\Http\Controllers\Controller;
use App\Models\Configuration\SemesterList;
use App\Models\Course\Course;
use App\Models\Subject\Subject;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SubjectOrderController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:subjectorders.view', only: ['index']),
            new Middleware('permission:subjectorders.edit', only: ['update']),
        ];
    }

    public function index(Request $request)
    {
        // the list appears once both a course and a semester are chosen
        $searched = $request->filled('courseid') && $request->filled('semid');

        $subjects = collect();

        if ($searched) {
            $subjects = Subject::query()
                ->with(['course', 'term', 'subjectType'])
                ->where('courseid', $request->courseid)
                ->where('semid', $request->semid)
                ->orderBy('subjectorder')
                ->orderBy('subjectid')
                ->get();
        }

        return view('subject_order.index', [
            'subjects' => $subjects,
            'searched' => $searched,
            'courses' => Course::query()
                ->where(fn ($q) => $q->where('active', true)->orWhere('courseid', $request->courseid))
                ->orderBy('shortname')->get(),
            'terms' => SemesterList::query()
                ->where(fn ($q) => $q->where('active', true)->orWhere('semid', $request->semid))
                ->orderBy('semid')->get(),
        ]);
    }

    // Receives the subject ids in their new on-screen order
    public function update(Request $request)
    {
        $data = $request->validate([
            'courseid' => ['required', Rule::exists('mark_course', 'courseid')->where('is_delete', false)],
            'semid' => ['required', Rule::exists('mark_semester_list', 'semid')->where('is_delete', false)],
            'subject_ids' => ['required', 'array', 'min:1'],
            'subject_ids.*' => ['integer', 'distinct'],
        ]);

        $back = ['courseid' => $data['courseid'], 'semid' => $data['semid']];

        // the submitted list must be exactly the subjects of this course + semester
        $existing = Subject::where('courseid', $data['courseid'])
            ->where('semid', $data['semid'])
            ->pluck('subjectid')->map(fn ($id) => (int) $id)->sort()->values()->all();

        $submitted = collect($data['subject_ids'])->map(fn ($id) => (int) $id)->sort()->values()->all();

        if ($existing !== $submitted) {
            return redirect()->route('subject-orders.index', $back)
                ->with('error', 'The subject list changed while you were editing. Please try again.');
        }

        DB::transaction(function () use ($data) {
            foreach (array_values($data['subject_ids']) as $position => $id) {
                Subject::whereKey($id)->update(['subjectorder' => $position + 1]);
            }
        });

        return redirect()->route('subject-orders.index', $back)->with('success', 'Subject order saved.');
    }
}