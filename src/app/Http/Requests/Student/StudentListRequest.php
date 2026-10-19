<?php

namespace App\Http\Requests\Student;

use App\Models\Semester\CourseSemester;
use App\Models\Student\StudentList;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StudentListRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // access is controlled by route permissions
    }

    public function rules(): array
    {
        $list = $this->route('student_list'); // only set on the edit page
        $semester = CourseSemester::find($list?->semesterid ?? $this->input('semesterid'));

        $rules = [
            // students of the semester's own session: "regular" entries
            'students' => ['nullable', 'array'],
            'students.*' => [
                'integer', 'distinct',
                Rule::exists('mark_student', 'studentid')
                    ->where('batchid', $semester?->batchid)
                    ->where('is_delete', false),
            ],

            // same course, different session: "back paper" / "repeat semester" entries
            'extra_ids' => ['nullable', 'array'],
            'extra_ids.*' => [
                'integer', 'distinct',
                Rule::exists('mark_student', 'studentid')
                    ->where('courseid', $semester?->courseid)
                    ->whereNot('batchid', $semester?->batchid)
                    ->where('is_delete', false),
            ],
            'extra_type' => ['nullable', 'array'],
            'extra_type.*' => [Rule::in(array_keys(StudentList::EXTRA_TYPES))],
            'extra_remark' => ['nullable', 'array'],
            'extra_remark.*' => ['nullable', 'string', 'max:255'],
        ];

        // the semester is chosen when the list is created and cannot change afterwards
        if (! $list) {
            $rules['semesterid'] = [
                'required',
                Rule::exists('mark_semester', 'semesterid')->where('is_delete', false)->whereNotNull('batchid'),
                Rule::unique('mark_student_list', 'semesterid')->where('is_delete', false),
            ];
        }

        return $rules;
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if (empty($this->input('students')) && empty($this->input('extra_ids'))) {
                $validator->errors()->add('students', 'Select at least one student.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'semesterid.exists' => 'Select a semester that has a session.',
            'semesterid.unique' => 'A student list already exists for this semester. Edit that list instead.',
            'students.*.exists' => 'A selected student does not belong to this session.',
            'extra_ids.*.exists' => 'Back / repeat students must be from the same course, in a different session.',
        ];
    }
}