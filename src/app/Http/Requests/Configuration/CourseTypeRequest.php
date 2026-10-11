<?php

namespace App\Http\Requests\Configuration;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CourseTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // access is controlled by route permissions
    }

    public function rules(): array
    {
        $id = $this->route('course_type')?->coursetypeid;

        return [
            'fullname' => ['required', 'string', 'max:255'],
            'shortname' => [
                'required', 'string', 'max:50',
                Rule::unique('mark_coursetype', 'shortname')
                    ->where('is_delete', false)
                    ->ignore($id, 'coursetypeid'),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'active' => ['nullable', 'boolean'],
        ];
    }
}