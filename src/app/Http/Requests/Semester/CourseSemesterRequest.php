<?php

namespace App\Http\Requests\Semester;

use App\Models\Configuration\SemesterList;
use App\Models\Course\Course;
use App\Support\Term;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CourseSemesterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // access is controlled by route permissions
    }

    public function rules(): array
    {
        $id = $this->route('course_semester')?->semesterid;
        $course = Course::find($this->courseid);

        return [
            'courseid' => [
                'required',
                Rule::exists('mark_course', 'courseid')->where('is_delete', false),
            ],
            // optional, but when given it must belong to the selected course
            'batchid' => [
                'nullable',
                Rule::exists('mark_batch', 'batchid')
                    ->where('courseid', $this->courseid)
                    ->where('is_delete', false),
            ],
            'semid' => [
                'required',
                Rule::exists('mark_semester_list', 'semid')->where('is_delete', false),
                // the term number can't be larger than the course length
                function ($attribute, $value, $fail) use ($course) {
                    $term = SemesterList::find($value);

                    if ($course && $term && Term::number($term->roman) > $course->total_terms) {
                        $fail('This course has only ' . $course->total_terms . ' '
                            . ($course->structure_type === 'year' ? 'year(s)' : 'semester(s)') . '.');
                    }
                },
                // one record per course + session + term
                Rule::unique('mark_semester', 'semid')
                    ->where(function ($q) {
                        $q->where('courseid', $this->courseid)->where('is_delete', false);

                        $this->batchid
                            ? $q->where('batchid', $this->batchid)
                            : $q->whereNull('batchid');
                    })
                    ->ignore($id, 'semesterid'),
            ],
            'semestermonth' => ['required', 'date'],
            'validcredit' => ['required', 'integer', 'min:0', 'max:200'],
            'description' => ['nullable', 'string', 'max:1000'],
            'active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'batchid.exists' => 'The selected session does not belong to the selected course.',
            'semid.unique' => 'This semester/year is already set up for the selected course and session.',
        ];
    }
}