<?php

namespace App\Models\Subject;

use App\Models\Configuration\SemesterList;
use App\Models\Configuration\SubjectType;
use App\Models\Course\Course;
use App\Support\Term;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subject extends Model
{
    protected $table = 'mark_subject';
    protected $primaryKey = 'subjectid';

    protected $fillable = [
    'courseid', 'semid', 'subjecttypeid', 'subjectname', 'subjectcredit', 'subjectcode',
    'subjectorder', 'maxinternalmarks', 'maxexternalmarks', 'add_grade', 'active', 'is_delete',
    ];

    protected function casts(): array
    {
        return [
            'subjectcredit' => 'decimal:1',
            'maxinternalmarks' => 'integer',
            'maxexternalmarks' => 'integer',
            'add_grade' => 'boolean',
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

    // a deleted type still has to show on old subjects, so the soft-delete scope is lifted here
    public function subjectType(): BelongsTo
    {
        return $this->belongsTo(SubjectType::class, 'subjecttypeid', 'subjecttypeid')
            ->withoutGlobalScopes();
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
        return Attribute::get(fn () => $this->subjectType?->subjecttypename ?? '—');
    }

    protected function totalMarks(): Attribute
    {
        return Attribute::get(fn () => (int) $this->maxinternalmarks + (int) $this->maxexternalmarks);
    }
}