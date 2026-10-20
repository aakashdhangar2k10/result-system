<?php

namespace App\Http\Requests\Administration;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // access is controlled by route permissions
    }

    // "Exam Cell" becomes "exam-cell"
    protected function prepareForValidation(): void
    {
        if (is_string($this->name)) {
            $this->merge(['name' => preg_replace('/\s+/', '-', mb_strtolower(trim($this->name)))]);
        }
    }

    public function rules(): array
    {
        $id = $this->route('role')?->id;

        return [
            'name' => [
                'required', 'string', 'max:50', 'regex:/^[a-z0-9][a-z0-9_\-]*$/',
                Rule::unique('roles', 'name')->where('guard_name', 'api')->ignore($id),
            ],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', Rule::exists('permissions', 'id')->where('guard_name', 'api')],
        ];
    }

    public function messages(): array
    {
        return [
            'name.regex' => 'Use small letters, numbers, - and _ only.',
            'name.unique' => 'A role with this name already exists.',
        ];
    }
}