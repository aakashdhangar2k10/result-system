<?php

namespace App\Http\Requests\Branch;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BranchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // access is controlled by route permissions
    }

    public function rules(): array
    {
        $id = $this->route('branch')?->branchid;

        // the same name / short name can't repeat inside one course
        $unique = fn (string $column) => Rule::unique('mark_branch', $column)
            ->where(fn ($q) => $q->where('courseid', $this->courseid)->where('is_delete', false))
            ->ignore($id, 'branchid');

        return [
            // only courses that are marked as having branches
            'courseid' => [
                'required',
                Rule::exists('mark_course', 'courseid')
                    ->where('is_delete', false)
                    ->where('has_branch', true),
            ],
            'fullname' => ['required', 'string', 'max:255', $unique('fullname')],
            'shortname' => ['required', 'string', 'max:50', $unique('shortname')],
            'description' => ['nullable', 'string', 'max:1000'],
            'active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'courseid.exists' => 'Select a course that has branches enabled.',
            'fullname.unique' => 'This branch already exists in the selected course.',
            'shortname.unique' => 'This short name is already used in the selected course.',
        ];
    }
}