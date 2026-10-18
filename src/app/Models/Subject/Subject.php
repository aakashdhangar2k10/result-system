<?php

namespace App\Models\Subject;

use App\Models\Configuration\SemesterList;
use App\Models\Course\Course;
use App\Support\Term;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subject extends Model
{
    public const TYPES = [
        'main' => 'Main',
        'optional' => 'Optional',
        'comprehensive_viva' => 'Comprehensive Viva',
        'seminar' => 'Seminar',
    ];

    protected $table = 'mark_subject';
    protected $primaryKey = 'subjectid';

    protected $fillable = [
        'courseid', 'semid', 'subjecttype', 'subjectname', 'subjectcredit', 'subjectcode',
        'maxinternalmarks', 'maxexternalmarks', 'active', 'is_delete',
    ];

    protected function casts(): array
    {
        return [
            'subjectcredit' => 'float',
            'maxinternalmarks' => 'integer',
            'maxexternalmarks' => 'integer',
            'active' => 'boolean',
            'is_delete' => 'boolean',
        ];
    }

    // Hide soft-deleted rows from every query automatically
    protected static function booted(): void
    {
        static::addGlobalScope('notDeleted', function (Builder $query) {
            $query->where('mark_subject.is_delete', false);
        });
    }

    public function getRouteKeyName(): string
    {
        return 'subjectid';
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'courseid', 'courseid');
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

    protected function typeLabel(): Attribute
    {
        return Attribute::get(fn () => self::TYPES[$this->subjecttype] ?? $this->subjecttype);
    }
}