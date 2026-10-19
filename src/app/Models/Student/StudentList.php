<?php

namespace App\Models\Student;

use App\Models\Semester\CourseSemester;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\HasMany;
class StudentList extends Model
{
    // entries added from other sessions of the same course
    public const EXTRA_TYPES = [
        'back' => 'Back paper',
        'repeat' => 'Repeat semester',
    ];

    public const TYPES = ['regular' => 'Regular'] + self::EXTRA_TYPES;

    protected $table = 'mark_student_list';
    protected $primaryKey = 'studentlistid';

    protected $fillable = ['semesterid', 'is_locked', 'locked_at', 'locked_by', 'is_delete'];

    protected function casts(): array
    {
        return [
            'is_locked' => 'boolean',
            'locked_at' => 'datetime',
            'is_delete' => 'boolean',
        ];
    }

    // Hide soft-deleted rows from every query automatically
    protected static function booted(): void
    {
        static::addGlobalScope('notDeleted', function (Builder $query) {
            $query->where('mark_student_list.is_delete', false);
        });
    }

    public function getRouteKeyName(): string
    {
        return 'studentlistid';
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(CourseSemester::class, 'semesterid', 'semesterid');
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(
            Student::class,
            'mark_student_list_item',
            'studentlistid',
            'studentid',
            'studentlistid',
            'studentid'
        )->withPivot(['entry_type', 'remark'])->withTimestamps()->orderBy('mark_student.rollno');
    }

    public function locker(): BelongsTo
    {
    return $this->belongsTo(User::class, 'locked_by');
    }

    // newest first
    public function lockLog(): HasMany
    {
    return $this->hasMany(StudentListLock::class, 'studentlistid', 'studentlistid')->latest('lockid');
    }
}