<?php

namespace App\Http\Requests\Administration;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // access is controlled by route permissions
    }

    public function rules(): array
    {
        $id = $this->route('group')?->groupid;

        return [
            'name' => [
                'required', 'string', 'max:100',
                Rule::unique('mark_group', 'name')->where('is_delete', false)->ignore($id, 'groupid'),
            ],
            'description' => ['nullable', 'string', 'max:255'],
            'active' => ['nullable', 'boolean'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', Rule::exists('permissions', 'id')->where('guard_name', 'api')],
            'members' => ['nullable', 'array'],
            'members.*' => ['integer', Rule::exists('users', 'id')->where('is_delete', false)],
        ];
    }

    public function messages(): array
    {
        return ['name.unique' => 'A group with this name already exists.'];
    }
}