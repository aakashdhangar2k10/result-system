<?php

namespace App\Http\Requests\Configuration;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SemesterListRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // access is controlled by route permissions
    }

    protected function prepareForValidation(): void
    {
        if ($this->roman) {
            $this->merge(['roman' => strtoupper(trim($this->roman))]);
        }
    }

    public function rules(): array
    {
        $id = $this->route('semester')?->semid;

        return [
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('mark_semester_list', 'name')
                    ->where('is_delete', false)
                    ->ignore($id, 'semid'),
            ],
            'roman' => ['required', 'string', 'max:10', 'regex:/^[IVXLCDM]+$/'],
            'joindate' => ['nullable', 'date'],
            'active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return ['roman.regex' => 'Roman numeral may only contain I, V, X, L, C, D, M.'];
    }
}