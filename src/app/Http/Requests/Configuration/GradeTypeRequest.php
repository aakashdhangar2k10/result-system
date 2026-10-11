<?php

namespace App\Http\Requests\Configuration;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GradeTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // access is controlled by route permissions
    }

    public function rules(): array
    {
        $id = $this->route('grade_type')?->coursetypeid;

        return [
            'coursetypename' => [
                'required', 'string', 'max:255',
                Rule::unique('mark_course_type', 'coursetypename')
                    ->where('is_delete', false)
                    ->ignore($id, 'coursetypeid'),
            ],
            'active' => ['nullable', 'boolean'],
        ];
    }
}