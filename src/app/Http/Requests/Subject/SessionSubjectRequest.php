<?php

namespace App\Http\Requests\Subject;

use App\Models\Configuration\SemesterList;
use App\Models\Course\Course;
use App\Support\Term;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SessionSubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // access is controlled by route permissions
    }

    public function rules(): array
    {
        $record = $this->route('session_subject'); // only set on the edit page
        $courseId = $record?->courseid ?? $this->courseid;
        $semId = $record?->semid ?? $this->semid;

        $rules = [
            'subject_ids' => ['required', 'array', 'min:1'],
            // every ticked subject must belong to this course + semester
            'subject_ids.*' => [
                'integer', 'distinct',
                Rule::exists('mark_subject', 'subjectid')
                    ->where('courseid', $courseId)
                    ->where('semid', $semId)
                    ->where('is_delete', false),
            ],
        ];

        if ($record) {
            return $rules;
        }

        $course = Course::find($this->courseid);

        return $rules + [
            'courseid' => [
                'required',
                Rule::exists('mark_course', 'courseid')->where('is_delete', false),
            ],
            'batchid' => [
                'required',
                // the session must belong to the selected course
                Rule::exists('mark_batch', 'batchid')
                    ->where('courseid', $this->courseid)
                    ->where('is_delete', false),
            ],
            'semid' => [
                'required',
                Rule::exists('mark_semester_list', 'semid')->where('is_delete', false),
                // the term number can't be larger than the course length
                function ($attribute, $value, $fail) use ($course) {
                    $term = SemesterList::find($value);

                    if ($course && $term && Term::number($term->roman) > $course->total_terms) {
                        $fail('This course has only ' . $course->total_terms . ' '
                            . ($course->structure_type === 'year' ? 'year(s)' : 'semester(s)') . '.');
                    }
                },
                // one setup per course + session + semester
                Rule::unique('mark_session_subject', 'semid')
                    ->where(fn ($q) => $q
                        ->where('courseid', $this->courseid)
                        ->where('batchid', $this->batchid)
                        ->where('is_delete', false)),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'subject_ids.required' => 'Select at least one subject.',
            'subject_ids.min' => 'Select at least one subject.',
            'subject_ids.*.exists' => 'One of the selected subjects does not belong to this course and semester.',
            'batchid.exists' => 'The selected session does not belong to the selected course.',
            'semid.unique' => 'Subjects are already set up for this course, session and semester. Edit the existing one instead.',
        ];
    }
}