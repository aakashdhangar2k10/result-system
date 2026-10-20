<?php

namespace App\Models\Result;

use App\Models\Batch\Batch;
use App\Models\Configuration\SemesterList;
use App\Models\Course\Course;
use App\Models\Semester\SemesterExam;
use App\Models\Student\StudentList;
use App\Models\Subject\Subject;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubjectResult extends Model
{
    public const MODES = ['marks' => 'Marks', 'grade' => 'Grade'];

    protected $table = 'mark_result';
    protected $primaryKey = 'resultid';

    protected $fillable = [
        'courseid', 'batchid', 'semid', 'semesterexamid', 'subjectid', 'studentlistid',
        'entry_mode', 'attempt', 'max_internal', 'max_external', 'subject_credit', 'gradetypeid',
        'is_delete', 'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'max_internal' => 'integer',
            'max_external' => 'integer',
            'subject_credit' => 'decimal:1',
            'is_delete' => 'boolean',
        ];
    }

    // Hide soft-deleted rows from every query automatically
    protected static function booted(): void
    {
        static::addGlobalScope('notDeleted', function (Builder $query) {
            $query->where('mark_result.is_delete', false);
        });
    }

    public function getRouteKeyName(): string
    {
        return 'resultid';
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'courseid', 'courseid');
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class, 'batchid', 'batchid');
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(SemesterList::class, 'semid', 'semid');
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(SemesterExam::class, 'semesterexamid', 'semesterexamid');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subjectid', 'subjectid');
    }

    public function studentList(): BelongsTo
    {
        return $this->belongsTo(StudentList::class, 'studentlistid', 'studentlistid');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SubjectResultItem::class, 'resultid', 'resultid');
    }
}