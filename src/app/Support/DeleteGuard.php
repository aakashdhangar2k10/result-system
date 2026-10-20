<?php

namespace App\Support;

use App\Models\Batch\Batch;
use App\Models\Branch\Branch;
use App\Models\Configuration\CourseType;
use App\Models\Configuration\DivisionType;
use App\Models\Configuration\GradeType;
use App\Models\Configuration\SemesterList;
use App\Models\Configuration\SubjectType;
use App\Models\Course\Course;
use App\Models\Department;
use App\Models\Semester\CourseSemester;
use App\Models\Student\Student;
use App\Models\Student\StudentList;
use App\Models\Subject\Subject;
use Illuminate\Database\Eloquent\Model;
use App\Models\Semester\SemesterExam;
use Illuminate\Support\Facades\DB;

use App\Models\Administration\Group;
use App\Models\User;
use Spatie\Permission\Models\Role;

class DeleteGuard
{
    /** Where is this record used? Returns ['Place' => count], empty when it is free. */
    public static function usages(Model $model): array
    {
        $rule = self::rules()[$model::class] ?? null;

        if (! $rule) {
            return [];
        }

        $found = [];

        foreach ($rule['uses'] as $place => $query) {
            $count = $query($model->getKey())->count();

            if ($count > 0) {
                $found[$place] = $count;
            }
        }

        return $found;
    }

    /** "Courses (3), Student → Student (12)" */
    public static function describe(array $usages): string
    {
        return collect($usages)->map(fn($count, $place) => "{$place} ({$count})")->implode(', ');
    }

    /** The message to show when the record cannot be deleted, or null when it is free. */
    public static function blocker(Model $model): ?string
    {
        $usages = self::usages($model);

        if (! $usages) {
            return null;
        }

        $rule = self::rules()[$model::class];
        $name = ($rule['name'])($model);

        return "{$rule['label']} '{$name}' cannot be deleted because it is used in: "
            . self::describe($usages) . '. Remove it from there first.';
    }

    private static function rules(): array
    {
        // rows of $table that point at the record through $column (deleted rows are ignored)
        $live = fn(string $table, string $column) => fn($id) => DB::table($table)
            ->where($column, $id)
            ->where('is_delete', false);

        // rows of an item table (list of ids) whose parent record is not deleted
        $through = fn(string $item, string $column, string $parent, string $parentKey) => fn($id) => DB::table("{$item} as i")
            ->join("{$parent} as p", "p.{$parentKey}", '=', "i.{$parentKey}")
            ->where('p.is_delete', false)
            ->where("i.{$column}", $id);

        return [
            Department::class => [
                'label' => 'Department',
                'name' => fn($m) => $m->fullname,
                'uses' => [
                    'Courses' => $live('mark_course', 'deptid'),
                ],
                'Administration → Users' => fn($id) => DB::table('mark_user_department as ud')
                    ->join('users as u', 'u.id', '=', 'ud.user_id')
                    ->where('u.is_delete', false)
                    ->where('ud.deptid', $id),
            ],

            Role::class => [
                'label' => 'Role',
                'name' => fn($m) => $m->name,
                'uses' => [
                    'Administration → Users' => fn($id) => DB::table('model_has_roles as mr')
                        ->join('users as u', 'u.id', '=', 'mr.model_id')
                        ->where('mr.model_type', User::class)
                        ->where('u.is_delete', false)
                        ->where('mr.role_id', $id),
                ],
            ],

            Group::class => [
                'label' => 'Group',
                'name' => fn($m) => $m->name,
                'uses' => [
                    'Administration → Users' => fn($id) => DB::table('mark_group_user as gu')
                        ->join('users as u', 'u.id', '=', 'gu.user_id')
                        ->where('u.is_delete', false)
                        ->where('gu.groupid', $id),
                ],
            ],

            CourseType::class => [
                'label' => 'Course type',
                'name' => fn($m) => $m->fullname,
                'uses' => [
                    'Grade' => $live('mark_grade', 'coursetypeid'),
                    'Division' => $live('mark_division', 'coursetypeid'),
                ],
            ],

            GradeType::class => [
                'label' => 'Grade type',
                'name' => fn($m) => $m->coursetypename,
                'uses' => [
                    'Courses' => $live('mark_course', 'coursetypeid'),
                    'Grade' => $live('mark_grade', 'gradetypeid'),
                    'Course Session → Session Grade' => $live('mark_session_grade', 'gradetypeid'),
                ],
            ],

            DivisionType::class => [
                'label' => 'Division type',
                'name' => fn($m) => $m->divtypename,
                'uses' => [
                    'Division' => $live('mark_division', 'divtypeid'),
                    'Course Session → Session Grade' => $live('mark_session_grade', 'divtypeid'),
                ],
            ],

            SemesterList::class => [
                'label' => 'Semester',
                'name' => fn($m) => $m->name,
                'uses' => [
                    'Semester → Semester' => $live('mark_semester', 'semid'),
                    'Semester → Semester Exam' => $live('mark_semester_exam', 'semid'),
                    'Subject → Subject' => $live('mark_subject', 'semid'),
                    'Subject → Session Subject' => $live('mark_session_subject', 'semid'),
                    'Result → Result' => $live('mark_result', 'semid'),
                ],
            ],

            SubjectType::class => [
                'label' => 'Subject type',
                'name' => fn($m) => $m->subjecttypename,
                'uses' => [
                    'Subject → Subject' => $live('mark_subject', 'subjecttypeid'),
                ],
            ],

            Course::class => [
                'label' => 'Course',
                'name' => fn($m) => $m->fullname,
                'uses' => [
                    'Branches' => $live('mark_branch', 'courseid'),
                    'Batch / Session' => $live('mark_batch', 'courseid'),
                    'Semester → Semester' => $live('mark_semester', 'courseid'),
                    'Semester → Semester Exam' => $live('mark_semester_exam', 'courseid'),
                    'Subject → Subject' => $live('mark_subject', 'courseid'),
                    'Subject → Session Subject' => $live('mark_session_subject', 'courseid'),
                    'Course Session → Session Grade' => $live('mark_session_grade', 'courseid'),
                    'Student → Student' => $live('mark_student', 'courseid'),
                    'Result → Result' => $live('mark_result', 'courseid'),
                ],
            ],

            Branch::class => [
                'label' => 'Branch',
                'name' => fn($m) => $m->fullname,
                'uses' => [
                    'Batch / Session' => $live('mark_batch', 'branchid'),
                ],
            ],

            Batch::class => [
                'label' => 'Session',
                'name' => fn($m) => $m->label,
                'uses' => [
                    'Semester → Semester' => $live('mark_semester', 'batchid'),
                    'Semester → Semester Exam' => $live('mark_semester_exam', 'batchid'),
                    'Course Session → Session Grade' => $live('mark_session_grade', 'batchid'),
                    'Subject → Session Subject' => $live('mark_session_subject', 'batchid'),
                    'Student → Student' => $live('mark_student', 'batchid'),
                    'Student → Optional Subject Student' => $live('mark_optional_subject_student', 'batchid'),
                    'Result → Result' => $live('mark_result', 'batchid'),
                ],
            ],

            CourseSemester::class => [
                'label' => 'Semester',
                'name' => fn($m) => trim(($m->course?->shortname ?? '') . ' ' . ($m->batch?->label ?? '') . ' ' . $m->term_label),
                'uses' => [
                    'Student → Student List' => $live('mark_student_list', 'semesterid'),
                ],
            ],

            Subject::class => [
                'label' => 'Subject',
                'name' => fn($m) => $m->subjectname,
                'uses' => [
                    'Subject → Session Subject' => $through('mark_session_subject_item', 'subjectid', 'mark_session_subject', 'sessionsubjectid'),
                    'Student → Optional Subject Student' => $live('mark_optional_subject_student', 'subjectid'),
                    'Result → Result' => $live('mark_result', 'subjectid'),
                ],
            ],

            Student::class => [
                'label' => 'Student',
                'name' => fn($m) => $m->full_name . ' (' . $m->rollno . ')',
                'uses' => [
                    'Student → Student List' => $through('mark_student_list_item', 'studentid', 'mark_student_list', 'studentlistid'),
                    'Student → Optional Subject Student' => $through('mark_optional_subject_student_item', 'studentid', 'mark_optional_subject_student', 'optionalid'),
                    'Result → Result' => $through('mark_result_item', 'studentid', 'mark_result', 'resultid'),
                ],
            ],

            StudentList::class => [
                'label' => 'Student list',
                'name' => fn($m) => trim(($m->semester?->course?->shortname ?? '') . ' ' . ($m->semester?->batch?->label ?? '') . ' ' . ($m->semester?->term_label ?? '')),
                'uses' => [
                    // optional-subject records of the same course + session + semester
                    'Student → Optional Subject Student' => fn($id) => DB::table('mark_optional_subject_student as o')
                        ->join('mark_subject as s', 's.subjectid', '=', 'o.subjectid')
                        ->join('mark_semester as m', function ($join) {
                            $join->on('m.courseid', '=', 's.courseid')
                                ->on('m.semid', '=', 's.semid')
                                ->on('m.batchid', '=', 'o.batchid');
                        })
                        ->join('mark_student_list as l', 'l.semesterid', '=', 'm.semesterid')
                        ->where('o.is_delete', false)
                        ->where('l.studentlistid', $id),
                    'Result → Result' => $live('mark_result', 'studentlistid'),
                ],
            ],

            SemesterExam::class => [
                'label' => 'Semester exam',
                'name' => fn($m) => trim(($m->course?->shortname ?? '') . ' ' . $m->term_label . ' ' . $m->exam_label),
                'uses' => [
                    'Result → Result' => $live('mark_result', 'semesterexamid'),
                ],
            ],
        ];
    }
}
