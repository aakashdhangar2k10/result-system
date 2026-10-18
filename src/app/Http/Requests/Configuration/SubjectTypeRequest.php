<?php

namespace App\Http\Requests\Configuration;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class SubjectTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // access is controlled by route permissions
    }

    public function rules(): array
    {
        $id = $this->route('subject_type')?->subjecttypeid;

        return [
            'subjecttypename' => [
                'required', 'string', 'max:100',
                Rule::unique('mark_subject_type', 'subjecttypename')
                    ->where('is_delete', false)
                    ->ignore($id, 'subjecttypeid'),
            ],
            'has_internal' => ['nullable', 'boolean'],
            'has_external' => ['nullable', 'boolean'],
            'has_grade' => ['nullable', 'boolean'],
            'active' => ['nullable', 'boolean'],
        ];
    }

    // a type has to ask for at least one thing
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if (! $this->boolean('has_internal') && ! $this->boolean('has_external') && ! $this->boolean('has_grade')) {
                $validator->errors()->add('has_internal', 'Select at least one thing the subject form should ask for.');
            }
        });
    }
}