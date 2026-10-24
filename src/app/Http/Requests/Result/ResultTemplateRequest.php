<?php

namespace App\Http\Requests\Result;

use App\Models\Batch\Batch;
use App\Models\Result\ResultTemplate;
use App\Models\Result\ResultTemplateAssignment;
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
        $space = ['nullable', 'numeric', 'between:0,60'];

        return [
            'name' => [
                'required', 'string', 'max:100',
                Rule::unique('mark_result_template', 'name')->where('is_delete', false)->ignore($id, 'templateid'),
            ],

            // where the template is used: course + session (or every session)
            'assignments' => ['nullable', 'array', 'max:40'],
            'assignments.*.courseid' => ['required', Rule::exists('mark_course', 'courseid')->where('is_delete', false)],
            'assignments.*.batchid' => ['nullable', Rule::exists('mark_batch', 'batchid')->where('is_delete', false)],

            // both can be empty: the sheet then starts with the course name
            'title' => ['nullable', 'string', 'max:60'],
            'programme_label' => ['nullable', 'string', 'max:80'],
            'exam_label' => ['required', 'string', 'max:30'],
            'date_format' => ['required', Rule::in(array_keys(ResultTemplate::DATE_FORMATS))],

            'university_name' => ['nullable', 'string', 'max:150'],
            'header_lines_top' => ['nullable', 'string', 'max:500'],
            'header_lines_bottom' => ['nullable', 'string', 'max:500'],
            'institute_label' => ['required', 'string', 'max:80'],
            'address_label' => ['required', 'string', 'max:80'],
            'institute_address' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:1024'],
            'remove_logo' => ['nullable', 'boolean'],
            'footer_items' => ['nullable', 'array', 'max:' . ResultTemplate::MAX_SIGNATURES],
            'footer_items.*' => ['nullable', 'string', 'max:40'],

            'header_align' => ['required', Rule::in(array_keys(ResultTemplate::ALIGN))],
            'title_align' => ['required', Rule::in(array_keys(ResultTemplate::ALIGN))],
            'institute_align' => ['required', Rule::in(array_keys(ResultTemplate::ALIGN))],
            'programme_align' => ['required', Rule::in(array_keys(ResultTemplate::ALIGN))],
            'logo_position' => ['required', Rule::in(array_keys(ResultTemplate::SIDES))],
            'photo_position' => ['required', Rule::in(array_keys(ResultTemplate::SIDES))],

            'margin_mode' => ['required', Rule::in(['auto', 'custom'])],
            'margin_top' => $margin,
            'margin_right' => $margin,
            'margin_bottom' => $margin,
            'margin_left' => $margin,

            'spacing_mode' => ['required', Rule::in(['auto', 'custom'])],
            'space_header_top' => $space,
            'space_header_side' => $space,
            'space_header_lines' => ['nullable', 'numeric', 'between:0,20'],
            'space_after_header' => $space,
            'space_sections' => $space,
            'space_footer' => $space,
        ] + array_fill_keys(ResultTemplate::FLAGS, ['nullable', 'boolean']);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $id = $this->route('result_template')?->templateid;

            foreach (['header_lines_top' => 'above', 'header_lines_bottom' => 'below'] as $field => $where) {
                if (count(ResultTemplate::lines($this->input($field))) > 6) {
                    $validator->errors()->add($field, "Use at most 6 lines {$where} the department.");
                }
            }

            // each course + session belongs to one template only
            $seen = [];

            foreach ((array) $this->input('assignments', []) as $i => $row) {
                $course = (int) ($row['courseid'] ?? 0);
                $batch = ($row['batchid'] ?? '') !== '' ? (int) $row['batchid'] : null;

                if (! $course) {
                    continue;
                }

                if ($batch && ! Batch::where('batchid', $batch)->where('courseid', $course)->exists()) {
                    $validator->errors()->add("assignments.{$i}.batchid", 'A selected session does not belong to its course.');
                    continue;
                }

                $key = $course . '-' . ($batch ?? 0);

                if (isset($seen[$key])) {
                    $validator->errors()->add("assignments.{$i}.courseid", 'The same course and session is listed twice.');
                    continue;
                }

                $seen[$key] = true;

                $owner = ResultTemplateAssignment::query()
                    ->join('mark_result_template as t', 't.templateid', '=', 'mark_result_template_assignment.templateid')
                    ->where('t.is_delete', false)
                    ->where('mark_result_template_assignment.courseid', $course)
                    ->when(
                        $batch,
                        fn ($q) => $q->where('mark_result_template_assignment.batchid', $batch),
                        fn ($q) => $q->whereNull('mark_result_template_assignment.batchid')
                    )
                    ->when($id, fn ($q) => $q->where('t.templateid', '<>', $id))
                    ->value('t.name');

                if ($owner) {
                    $validator->errors()->add("assignments.{$i}.courseid", "This course and session already belongs to the template '{$owner}'.");
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'A template with this name already exists.',
            'logo.max' => 'The logo must not be larger than 1 MB.',
            'margin_top.required_if' => 'Enter the margin in mm.',
            'margin_right.required_if' => 'Enter the margin in mm.',
            'margin_bottom.required_if' => 'Enter the margin in mm.',
            'margin_left.required_if' => 'Enter the margin in mm.',
            'space_header_lines.between' => 'The gap between header lines can be 0 to 20 mm.',
            'assignments.*.courseid.required' => 'Select a course in every row, or remove the row.',
        ];
    }
}