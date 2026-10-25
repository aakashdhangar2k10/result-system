<?php

namespace App\Http\Requests\Administration;

use App\Support\DepartmentAccess;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Validator;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // access is controlled by route permissions
    }

    // a user who sees only some departments cannot give "all departments" to anybody
    protected function prepareForValidation(): void
    {
        if (! DepartmentAccess::unrestricted()) {
            $this->merge(['department_access' => 'selected']);
        }
    }

    public function rules(): array
    {
        $id = $this->route('user')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email:rfc', 'max:255', Rule::unique('users', 'email')->ignore($id)],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            // a new user needs a password; on edit an empty field keeps the old one
            'password' => [$id ? 'nullable' : 'required', 'confirmed', Password::min(8)->letters()->numbers()],
            'active' => ['nullable', 'boolean'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['integer', Rule::exists('roles', 'id')->where('guard_name', 'api')],
            'groups' => ['nullable', 'array'],
            'groups.*' => ['integer', Rule::exists('mark_group', 'groupid')->where('is_delete', false)],
            'department_access' => ['required', Rule::in(['all', 'selected'])],
            'departments' => ['nullable', 'array'],
            'departments.*' => ['integer', Rule::exists('mark_department', 'deptid')->where('is_delete', false)],
            'remove_photo' => ['nullable', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($this->input('department_access') === 'selected' && empty($this->input('departments'))) {
                $validator->errors()->add('departments', 'Select at least one department, or choose "All departments".');
            }
        });
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'This email is already used by another user.',
            'password.required' => 'Enter a password for the new user.',
            'photo.max' => 'The photo must not be larger than 2 MB.',
            'photo.mimes' => 'The photo must be a .jpg, .jpeg, .png or .webp file.',
        ];
    }
}