<?php

namespace App\Http\Requests\Result;

use App\Models\Result\ResultTemplate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ResultTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // access is controlled by route permissions
    }

    public function rules(): array
    {
        $id = $this->route('result_template')?->templateid;

        $margin = ['nullable', 'required_if:margin_mode,custom', 'integer', 'between:0,60'];

        return [
            'name' => [
                'required', 'string', 'max:100',
                Rule::unique('mark_result_template', 'name')->where('is_delete', false)->ignore($id, 'templateid'),
            ],
            'courseid' => [
                'nullable',
                Rule::exists('mark_course', 'courseid')->where('is_delete', false),
                // one template per course
                Rule::unique('mark_result_template', 'courseid')->where('is_delete', false)->ignore($id, 'templateid'),
            ],
            'title' => ['required', 'string', 'max:60'],
            'programme_label' => ['required', 'string', 'max:80'],
            'university_name' => ['nullable', 'string', 'max:150'],
            'header_lines_top' => ['nullable', 'string', 'max:500'],
            'header_lines_bottom' => ['nullable', 'string', 'max:500'],
            'institute_address' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:1024'],
            'remove_logo' => ['nullable', 'boolean'],
            'footer_items' => ['nullable', 'array', 'max:' . ResultTemplate::MAX_SIGNATURES],
            'footer_items.*' => ['nullable', 'string', 'max:40'],
            'margin_mode' => ['required', Rule::in(['auto', 'custom'])],
            'margin_top' => $margin,
            'margin_right' => $margin,
            'margin_bottom' => $margin,
            'margin_left' => $margin,
        ] + array_fill_keys(ResultTemplate::FLAGS, ['nullable', 'boolean']);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            foreach (['header_lines_top' => 'above', 'header_lines_bottom' => 'below'] as $field => $where) {
                if (count(ResultTemplate::lines($this->input($field))) > 6) {
                    $validator->errors()->add($field, "Use at most 6 lines {$where} the department.");
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'A template with this name already exists.',
            'courseid.unique' => 'This course already has its own template. Edit that one, or choose another course.',
            'logo.max' => 'The logo must not be larger than 1 MB.',
            'margin_top.required_if' => 'Enter the margin in mm.',
            'margin_right.required_if' => 'Enter the margin in mm.',
            'margin_bottom.required_if' => 'Enter the margin in mm.',
            'margin_left.required_if' => 'Enter the margin in mm.',
        ];
    }
}