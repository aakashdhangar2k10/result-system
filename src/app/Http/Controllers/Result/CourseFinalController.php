<?php

namespace App\Http\Controllers\Result;

use App\Http\Controllers\Controller;
use App\Models\Course\Course;
use App\Models\Course\CourseFinal;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CourseFinalController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:coursefinals.view', only: ['index']),
            new Middleware('permission:coursefinals.edit', only: ['edit', 'update']),
        ];
    }

    public function index()
    {
        return view('result_final.index', [
            'courses' => Course::where('active', true)->orderBy('shortname')->get(),
            'finals' => CourseFinal::orderBy('to_term')->get()->groupBy('courseid'),
        ]);
    }

    public function edit(Course $course)
    {
        return view('result_final.edit', [
            'course' => $course,
            'rows' => CourseFinal::where('courseid', $course->courseid)->orderBy('to_term')->get()
                ->map(fn ($f) => ['from_term' => $f->from_term, 'to_term' => $f->to_term, 'degree_label' => $f->degree_label])
                ->all(),
        ]);
    }

    public function update(Request $request, Course $course)
    {
        $total = (int) $course->total_terms;
        $word = $course->structure_type === 'year' ? 'year' : 'semester';

        $data = $request->validate([
            'finals' => ['nullable', 'array', 'max:6'],
            'finals.*.to_term' => ['required', 'integer', "between:1,{$total}", 'distinct'],
            'finals.*.from_term' => ['required', 'integer', 'min:1'],
            'finals.*.degree_label' => ['nullable', 'string', 'max:100'],
        ], [
            'finals.*.to_term.between' => "The last {$word} must be between 1 and {$total}.",
            'finals.*.to_term.distinct' => 'Two rows end at the same ' . $word . '.',
        ]);

        foreach ($data['finals'] ?? [] as $i => $row) {
            if ((int) $row['from_term'] > (int) $row['to_term']) {
                throw ValidationException::withMessages([
                    "finals.{$i}.from_term" => "A row cannot start after the {$word} where it ends.",
                ]);
            }
        }

        DB::transaction(function () use ($course, $data) {
            CourseFinal::where('courseid', $course->courseid)->delete();

            foreach ($data['finals'] ?? [] as $row) {
                CourseFinal::create([
                    'courseid' => $course->courseid,
                    'from_term' => (int) $row['from_term'],
                    'to_term' => (int) $row['to_term'],
                    'degree_label' => trim((string) ($row['degree_label'] ?? '')) ?: null,
                ]);
            }
        });

        return redirect()->route('result-finals.index')->with('success', 'Final result points saved for ' . $course->shortname . '.');
    }
}