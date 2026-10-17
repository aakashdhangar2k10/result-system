<?php

namespace App\Http\Requests\Course;

use App\Models\Course\Course;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // access is controlled by route permissions
    }

    public function rules(): array
    {
        $id = $this->route('course')?->courseid;

        // the same name / short name can't repeat inside one department + course type
        $unique = fn (string $column) => Rule::unique('mark_course', $column)
            ->where(fn ($q) => $q
                ->where('deptid', $this->deptid)
                ->where('coursetypeid', $this->coursetypeid)
                ->where('is_delete', false))
            ->ignore($id, 'courseid');

        // semesters up to 12, years up to 6
        $maxTerms = $this->structure_type === 'year' ? 6 : 12;

        return [
            'deptid' => [
                'required',
                Rule::exists('mark_department', 'deptid')->where('is_delete', false),
            ],
            'coursetypeid' => [
                'required',
                Rule::exists('mark_course_type', 'coursetypeid')->where('is_delete', false),
            ],
            'fullname' => ['required', 'string', 'max:255', $unique('fullname')],
            'shortname' => ['required', 'string', 'max:50', $unique('shortname')],
            'structure_type' => ['required', Rule::in(array_keys(Course::STRUCTURES))],
            'total_terms' => ['required', 'integer', 'min:1', 'max:' . $maxTerms],
            'has_branch' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string', 'max:1000'],
            'active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'fullname.unique' => 'This course already exists for the selected department and course type.',
            'shortname.unique' => 'This course name is already used for the selected department and course type.',
            'total_terms.max' => 'Too many terms: use up to 12 semesters or 6 years.',
        ];
    }
}