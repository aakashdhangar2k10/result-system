<?php

namespace App\Models\Semester;

use App\Models\Batch\Batch;
use App\Models\Configuration\SemesterList;
use App\Models\Course\Course;
use App\Support\Term;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseSemester extends Model
{
    protected $table = 'mark_semester';
    protected $primaryKey = 'semesterid';

    protected $fillable = [
        'courseid', 'batchid', 'semid', 'semestermonth', 'validcredit',
        'description', 'active', 'is_delete',
    ];

    protected function casts(): array
    {
        return [
            'semestermonth' => 'date',
            'validcredit' => 'integer',
            'active' => 'boolean',
            'is_delete' => 'boolean',
        ];
    }

    // Hide soft-deleted rows from every query automatically
    protected static function booted(): void
    {
        static::addGlobalScope('notDeleted', function (Builder $query) {
            $query->where('mark_semester.is_delete', false);
        });
    }

    public function getRouteKeyName(): string
    {
        return 'semesterid';
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

    // "First Semester" or "First Year", depending on the course
    protected function termLabel(): Attribute
    {
        return Attribute::get(fn () => $this->term
            ? Term::label($this->term->name, $this->course?->structure_type)
            : '—');
    }
}