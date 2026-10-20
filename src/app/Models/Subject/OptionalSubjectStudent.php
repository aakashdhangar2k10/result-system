<?php

namespace App\Models\Subject;

use App\Models\Batch\Batch;
use App\Models\Semester\CourseSemester;
use App\Models\Student\Student;
use App\Models\Student\StudentList;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class OptionalSubjectStudent extends Model
{
    protected $table = 'mark_optional_subject_student';
    protected $primaryKey = 'optionalid';

    protected $fillable = ['batchid', 'subjectid', 'is_delete'];

    protected function casts(): array
    {
        return ['is_delete' => 'boolean'];
    }

    // Hide soft-deleted rows from every query automatically
    protected static function booted(): void
    {
        static::addGlobalScope('notDeleted', function (Builder $query) {
            $query->where('mark_optional_subject_student.is_delete', false);
        });
    }

    public function getRouteKeyName(): string
    {
        return 'optionalid';
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subjectid', 'subjectid');
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class, 'batchid', 'batchid');
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(
            Student::class,
            'mark_optional_subject_student_item',
            'optionalid',
            'studentid',
            'optionalid',
            'studentid'
        )->withTimestamps()->orderBy('mark_student.rollno');
    }

    // The Student List that decides who can be chosen: same course + session + semester
    public static function studentListFor(int $batchId, Subject $subject): ?StudentList
    {
        $semester = CourseSemester::where('courseid', $subject->courseid)
            ->where('batchid', $batchId)
            ->where('semid', $subject->semid)
            ->first();

        return $semester ? StudentList::where('semesterid', $semester->semesterid)->first() : null;
    }

    public function limitToDepartments(Builder $query): void
    {
        $query->whereIn($this->qualifyColumn('subjectid'), Subject::select('subjectid'));
    }
}
