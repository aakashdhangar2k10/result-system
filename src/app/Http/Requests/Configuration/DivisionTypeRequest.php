<?php

namespace App\Http\Requests\Configuration;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DivisionTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // access is controlled by route permissions
    }

    public function rules(): array
    {
        $id = $this->route('division_type')?->divtypeid;

        return [
            'divtypename' => [
                'required', 'string', 'max:255',
                Rule::unique('mark_division_type', 'divtypename')
                    ->where('is_delete', false)
                    ->ignore($id, 'divtypeid'),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'active' => ['nullable', 'boolean'],
        ];
    }
}