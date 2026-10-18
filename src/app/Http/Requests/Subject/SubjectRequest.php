<?php

namespace App\Http\Requests\Subject;

use App\Models\Configuration\SemesterList;
use App\Models\Course\Course;
use App\Models\Subject\Subject;
use App\Support\Term;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // access is controlled by route permissions
    }

    public function rules(): array
    {
        $id = $this->route('subject')?->subjectid;
        $course = Course::find($this->courseid);

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
            'subjecttype' => ['required', Rule::in(array_keys(Subject::TYPES))],
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
                // the code is unique inside a course
                Rule::unique('mark_subject', 'subjectcode')
                    ->where(fn ($q) => $q->where('courseid', $this->courseid)->where('is_delete', false))
                    ->ignore($id, 'subjectid'),
            ],
            'maxinternalmarks' => ['required', 'integer', 'min:0', 'max:500'],
            'maxexternalmarks' => ['required', 'integer', 'min:0', 'max:500'],
            'active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'subjectname.unique' => 'This subject already exists for the selected course and semester.',
            'subjectcode.unique' => 'This subject code is already used in the selected course.',
        ];
    }
}