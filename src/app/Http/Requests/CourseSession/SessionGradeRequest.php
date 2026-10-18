<?php

namespace App\Http\Requests\CourseSession;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SessionGradeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // access is controlled by route permissions
    }

    public function rules(): array
    {
        $id = $this->route('session_grade')?->sessiongradeid;

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
                // one grade setup per session
                Rule::unique('mark_session_grade', 'batchid')
                    ->where('is_delete', false)
                    ->ignore($id, 'sessiongradeid'),
            ],
            'gradetypeid' => [
                'required',
                Rule::exists('mark_course_type', 'coursetypeid')->where('is_delete', false),
            ],
            'divtypeid' => [
                'required',
                Rule::exists('mark_division_type', 'divtypeid')->where('is_delete', false),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'batchid.exists' => 'The selected session does not belong to the selected course.',
            'batchid.unique' => 'This session already has a grade setup. Edit the existing one instead.',
        ];
    }
}