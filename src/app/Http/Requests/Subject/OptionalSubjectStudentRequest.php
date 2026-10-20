<?php

namespace App\Http\Requests\Subject;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Subject\OptionalSubjectStudent;
use App\Models\Subject\Subject;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class OptionalSubjectStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // access is controlled by route permissions
    }

    public function rules(): array
    {
        $record = $this->route('optional_subject_student'); // only set on the edit page

        $rules = [
            'students' => ['required', 'array', 'min:1'],
            'students.*' => ['integer', 'distinct'],
        ];

        // course, session, semester and subject are chosen on the add page and fixed afterwards
        if (! $record) {
            $rules += [
                'courseid' => ['required', Rule::exists('mark_course', 'courseid')->where('is_delete', false)],
                'batchid' => [
                    'required',
                    Rule::exists('mark_batch', 'batchid')
                        ->where('courseid', $this->courseid)
                        ->where('is_delete', false),
                ],
                'semid' => ['required', Rule::exists('mark_semester_list', 'semid')->where('is_delete', false)],
                'subjectid' => [
                    'required',
                    Rule::exists('mark_subject', 'subjectid')
                        ->where('courseid', $this->courseid)
                        ->where('semid', $this->semid)
                        ->where('is_delete', false),
                    // one record per session + subject
                    Rule::unique('mark_optional_subject_student', 'subjectid')
                        ->where('batchid', $this->batchid)
                        ->where('is_delete', false),
                ],
            ];
        }

        return $rules;
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $record = $this->route('optional_subject_student');
            $subject = Subject::with('subjectType')->find($record?->subjectid ?? $this->subjectid);
            $batchId = $record?->batchid ?? $this->batchid;

            if (! $subject || ! $batchId) {
                return;
            }

            if (! $subject->subjectType?->is_optional) {
                $validator->errors()->add('subjectid', 'This is not an optional subject. Tick "Optional" on its subject type first.');
                return;
            }

            $list = OptionalSubjectStudent::studentListFor((int) $batchId, $subject);

            if (! $list) {
                $validator->errors()->add('subjectid', 'There is no student list for this course, session and semester yet. Create it under Student List first.');
                return;
            }

            if ($list->is_locked) {
                $validator->errors()->add('subjectid', 'The result of this semester is already generated, so its student list is locked.');
                return;
            }

            // everyone ticked must be on that student list
            $ticked = array_map('intval', (array) $this->input('students', []));

            $onList = DB::table('mark_student_list_item')
                ->where('studentlistid', $list->studentlistid)
                ->whereIn('studentid', $ticked)
                ->pluck('studentid')->map(fn ($id) => (int) $id)->all();

            if (array_diff($ticked, $onList)) {
                $validator->errors()->add('students', 'Some selected students are not on the student list of this semester.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'students.required' => 'Select at least one student (or delete this record if nobody chose the subject).',
            'students.min' => 'Select at least one student (or delete this record if nobody chose the subject).',
            'batchid.exists' => 'The selected session does not belong to the selected course.',
            'subjectid.exists' => 'The selected subject does not belong to this course and semester.',
            'subjectid.unique' => 'Students are already set up for this subject in this session. Edit the existing record instead.',
        ];
    }
}