<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // access is controlled by route permissions
    }

    public function rules(): array
    {
        $id = $this->route('department')?->deptid;

        return [
            'fullname' => ['required', 'string', 'max:255'],
            'shortname' => [
                'required', 'string', 'max:50',
                Rule::unique('mark_department', 'shortname')
                    ->where('is_delete', false)
                    ->ignore($id, 'deptid'),
            ],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'address' => ['nullable', 'string', 'max:1000'],
            'affilationshortname' => ['nullable', 'string', 'max:50'],
            'affilationname' => ['nullable', 'string', 'max:255'],
            'affilationyear' => ['nullable', 'integer', 'min:1900', 'max:' . date('Y')],
            'joindate' => ['nullable', 'date'],
            'active' => ['nullable', 'boolean'],
        ];
    }
}