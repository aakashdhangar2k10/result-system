<?php

namespace App\Http\Requests\Subject;

use App\Models\Configuration\SemesterList;
use App\Models\Configuration\SubjectType;
use App\Models\Course\Course;
use App\Support\Term;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // access is controlled by route permissions
    }

    // Whatever the chosen subject type does not ask for is cleared here,
    // so hidden fields can never be forced through the form.
    protected function prepareForValidation(): void
    {
        $type = SubjectType::find($this->subjecttypeid);

        $this->merge([
            'maxinternalmarks' => $type?->has_internal ? $this->maxinternalmarks : 0,
            'maxexternalmarks' => $type?->has_external ? $this->maxexternalmarks : 0,
            'add_grade' => $type?->has_grade ? $this->boolean('add_grade') : false,
        ]);
    }

    public function rules(): array
    {
        $id = $this->route('subject')?->subjectid;
        $course = Course::find($this->courseid);
        $type = SubjectType::find($this->subjecttypeid);

        return [
            'courseid' => [
                'required',
                Rule::exists('mark_course', 'courseid')->where('is_delete', false),
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
            ],
            'subjecttypeid' => [
                'required',
                Rule::exists('mark_subject_type', 'subjecttypeid')->where('is_delete', false),
            ],
            'subjectname' => [
                'required', 'string', 'max:255',
                // the same name can't repeat in one course + semester
                Rule::unique('mark_subject', 'subjectname')
                    ->where(fn ($q) => $q
                        ->where('courseid', $this->courseid)
                        ->where('semid', $this->semid)
                        ->where('is_delete', false))
                    ->ignore($id, 'subjectid'),
            ],
            'subjectcredit' => ['required', 'numeric', 'min:0', 'max:30'],
            'subjectcode' => [
                'required', 'string', 'max:50',
                // the same code can't repeat inside one course
                Rule::unique('mark_subject', 'subjectcode')
                    ->where(fn ($q) => $q->where('courseid', $this->courseid)->where('is_delete', false))
                    ->ignore($id, 'subjectid'),
            ],
            // only required when the subject type asks for them
            'maxinternalmarks' => [$type?->has_internal ? 'required' : 'nullable', 'integer', 'min:0', 'max:1000'],
            'maxexternalmarks' => [$type?->has_external ? 'required' : 'nullable', 'integer', 'min:0', 'max:1000'],
            'add_grade' => ['nullable', 'boolean'],
            'active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'subjectname.unique' => 'This subject already exists in the selected course and semester/year.',
            'subjectcode.unique' => 'This subject code is already used in the selected course.',
        ];
    }
}