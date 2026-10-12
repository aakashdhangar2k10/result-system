<?php

namespace App\Http\Requests\Grade;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GradeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // access is controlled by route permissions
    }

    public function rules(): array
    {
        $id = $this->route('grade')?->gradeid;

        return [
            'coursetypeid' => [
                'required',
                Rule::exists('mark_coursetype', 'coursetypeid')->where('is_delete', false),
            ],
            'gradetypeid' => [
                'required',
                Rule::exists('mark_course_type', 'coursetypeid')->where('is_delete', false),
            ],
            'firstattemptgrade' => [
                'required', 'string', 'max:10',
                // the same grade letter can't repeat for the same course type + grade type
                Rule::unique('mark_grade', 'firstattemptgrade')
                    ->where(fn ($q) => $q
                        ->where('coursetypeid', $this->coursetypeid)
                        ->where('gradetypeid', $this->gradetypeid)
                        ->where('is_delete', false))
                    ->ignore($id, 'gradeid'),
            ],
            'firstattemptcredit' => ['required', 'integer', 'min:0', 'max:100'],
            'secondattemptgrade' => ['required', 'string', 'max:10'],
            'secondattemptcredit' => ['required', 'integer', 'min:0', 'max:100'],
            'startscore' => ['required', 'integer', 'min:0', 'max:100'],
            'endscore' => ['required', 'integer', 'min:0', 'max:100', 'gte:startscore'],
            'description' => ['nullable', 'string', 'max:255'],
            'active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'firstattemptgrade.unique' => 'This grade already exists for the selected course type and grade type.',
            'endscore.gte' => 'End score must be greater than or equal to the start score.',
        ];
    }
}