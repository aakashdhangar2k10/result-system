<?php

namespace App\Http\Requests\Semester;

use App\Models\Configuration\SemesterList;
use App\Models\Course\Course;
use App\Models\Semester\SemesterExam;
use App\Support\Term;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SemesterExamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // access is controlled by route permissions
    }

    public function rules(): array
    {
        $id = $this->route('semester_exam')?->semesterexamid;
        $course = Course::find($this->courseid);

        return [
            'courseid' => [
                'required',
                Rule::exists('mark_course', 'courseid')->where('is_delete', false),
            ],
            'batchid' => [
                'nullable',
                Rule::exists('mark_batch', 'batchid')
                    ->where('courseid', $this->courseid)
                    ->where('is_delete', false),
            ],
            'semid' => [
                'required',
                Rule::exists('mark_semester_list', 'semid')->where('is_delete', false),
                function ($attribute, $value, $fail) use ($course) {
                    $term = SemesterList::find($value);

                    if ($course && $term && Term::number($term->roman) > $course->total_terms) {
                        $fail('This course has only ' . $course->total_terms . ' '
                            . ($course->structure_type === 'year' ? 'year(s)' : 'semester(s)') . '.');
                    }
                },
                // one exam per course + session + term + attempt
                Rule::unique('mark_semester_exam', 'semid')
                    ->where(function ($q) {
                        $q->where('courseid', $this->courseid)
                          ->where('attempt', $this->attempt)
                          ->where('is_delete', false);

                        $this->batchid
                            ? $q->where('batchid', $this->batchid)
                            : $q->whereNull('batchid');
                    })
                    ->ignore($id, 'semesterexamid'),
            ],
            'attempt' => ['required', Rule::in(array_keys(SemesterExam::ATTEMPTS))],
            'exammonth' => ['required', 'date'],
            'resultdate' => ['nullable', 'date', 'after_or_equal:exammonth'],
            'description' => ['nullable', 'string', 'max:1000'],
            'active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'batchid.exists' => 'The selected session does not belong to the selected course.',
            'semid.unique' => 'This exam attempt already exists for the selected course, session and semester/year.',
            'resultdate.after_or_equal' => 'Result date cannot be before the exam month.',
        ];
    }
}