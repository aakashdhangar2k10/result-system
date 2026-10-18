<?php

namespace App\Http\Requests\Batch;

use App\Models\Course\Course;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // access is controlled by route permissions
    }

    // A course without branches never gets a branch, whatever the form sent
    protected function prepareForValidation(): void
    {
        $course = Course::find($this->courseid);

        if (! $course || ! $course->has_branch) {
            $this->merge(['branchid' => null]);
        }
    }

    public function rules(): array
    {
        $id = $this->route('batch')?->batchid;
        $course = Course::find($this->courseid);
        $needsBranch = (bool) $course?->has_branch;

        return [
            'courseid' => [
                'required',
                Rule::exists('mark_course', 'courseid')->where('is_delete', false),
            ],
            'branchid' => $needsBranch
                ? [
                    'required',
                    // the branch must belong to the selected course
                    Rule::exists('mark_branch', 'branchid')
                        ->where('courseid', $this->courseid)
                        ->where('is_delete', false),
                ]
                : ['nullable'],
            'startyear' => [
                'required', 'integer', 'min:1990', 'max:' . (date('Y') + 10),
                // one batch per course + branch + start year
                Rule::unique('mark_batch', 'startyear')
                    ->where(function ($q) {
                        $q->where('courseid', $this->courseid)->where('is_delete', false);

                        $this->branchid
                            ? $q->where('branchid', $this->branchid)
                            : $q->whereNull('branchid');
                    })
                    ->ignore($id, 'batchid'),
            ],
            'endyear' => ['required', 'integer', 'gt:startyear', 'max:' . (date('Y') + 15)],
            'description' => ['nullable', 'string', 'max:1000'],
            'active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'branchid.required' => 'This course has branches, so select a branch.',
            'branchid.exists' => 'The selected branch does not belong to this course.',
            'startyear.unique' => 'A batch with this start year already exists for the selected course and branch.',
            'endyear.gt' => 'End year must be after the start year.',
        ];
    }
}