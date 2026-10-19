<?php

namespace App\Http\Requests\Student;

use App\Models\Student\Student;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // access is controlled by route permissions
    }

    // Names and numbers are stored in capitals, like the existing records
    protected function prepareForValidation(): void
    {
        $upper = fn ($value) => is_string($value) && trim($value) !== '' ? mb_strtoupper(trim($value)) : $value;

        $fields = ['rollno', 'enrollmentno', 'apaarid', 'firstname', 'lastname', 'fathername', 'mothername'];

        $this->merge(collect($fields)
            ->mapWithKeys(fn ($field) => [$field => $upper($this->input($field))])
            ->all());
    }

    public function rules(): array
    {
        $id = $this->route('student')?->studentid;

        return [
            'courseid' => [
                'required',
                Rule::exists('mark_course', 'courseid')->where('is_delete', false),
            ],
            'batchid' => [
                'required',
                // the session must belong to the selected course
                Rule::exists('mark_batch', 'batchid')
                    ->where('courseid', $this->courseid)
                    ->where('is_delete', false),
            ],
            'rollno' => ['required', 'string', 'max:50',Rule::unique('mark_student', 'rollno')->where('is_delete', false)->ignore($id, 'studentid'),],
            'enrollmentno' => ['required', 'string', 'max:50',Rule::unique('mark_student', 'enrollmentno')->where('is_delete', false)->ignore($id, 'studentid'),],
            'apaarid' => ['nullable', 'string', 'max:30',Rule::unique('mark_student', 'apaarid')->where('is_delete', false)->ignore($id, 'studentid'),],
            'firstname' => ['required', 'string', 'max:100'],
            'lastname' => ['nullable', 'string', 'max:100'],
            'father_label' => ['nullable', Rule::in(Student::FATHER_LABELS)],
            'father_title' => ['nullable', Rule::in(Student::FATHER_TITLES)],
            'fathername' => ['required', 'string', 'max:150'],
            'mother_title' => ['nullable', Rule::in(Student::MOTHER_TITLES)],
            'mothername' => ['required', 'string', 'max:150'],
            'address' => ['nullable', 'string', 'max:500'],
            'mobile' => ['nullable', 'regex:/^\+?[0-9]{10,13}$/'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,gif,png', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'batchid.exists' => 'The selected session does not belong to the selected course.',
            'rollno.unique' => 'This roll number is already used by another student.',
            'enrollmentno.unique' => 'This enrollment number is already used by another student.',
            'apaarid.unique' => 'This APAAR ID is already used by another student.',
            'mobile.regex' => 'Enter a valid mobile number (10 to 13 digits).',
            'image.max' => 'The image must not be larger than 2 MB.',
            'image.mimes' => 'The image must be a .jpg, .jpeg, .gif or .png file.',
        ];
    }
}