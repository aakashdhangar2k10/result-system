<?php

namespace App\Http\Requests\Division;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DivisionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // access is controlled by route permissions
    }

    public function rules(): array
    {
        $id = $this->route('division')?->divisionid;

        return [
            'coursetypeid' => [
                'required',
                Rule::exists('mark_coursetype', 'coursetypeid')->where('is_delete', false),
            ],
            'divtypeid' => [
                'required',
                Rule::exists('mark_division_type', 'divtypeid')->where('is_delete', false),
            ],
            'divisionname' => [
                'required', 'string', 'max:255',
                // the same name can't repeat for the same course type + division type
                Rule::unique('mark_division', 'divisionname')
                    ->where(fn ($q) => $q
                        ->where('coursetypeid', $this->coursetypeid)
                        ->where('divtypeid', $this->divtypeid)
                        ->where('is_delete', false))
                    ->ignore($id, 'divisionid'),
            ],
            'startpoint' => ['required', 'numeric', 'min:0', 'max:100'],
            'endpoint' => ['required', 'numeric', 'min:0', 'max:100', 'gte:startpoint'],
            'active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'divisionname.unique' => 'This division already exists for the selected course type and division type.',
            'endpoint.gte' => 'End point must be greater than or equal to the start point.',
        ];
    }
}