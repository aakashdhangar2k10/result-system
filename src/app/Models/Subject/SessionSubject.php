<?php

namespace App\Models\Subject;

use App\Models\Batch\Batch;
use App\Models\Configuration\SemesterList;
use App\Models\Course\Course;
use App\Support\Term;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SessionSubject extends Model
{
    protected $table = 'mark_session_subject';
    protected $primaryKey = 'sessionsubjectid';

    protected $fillable = ['courseid', 'batchid', 'semid', 'is_delete'];

    protected function casts(): array
    {
        return ['is_delete' => 'boolean'];
    }

    // Hide soft-deleted rows from every query automatically
    protected static function booted(): void
    {
        static::addGlobalScope('notDeleted', function (Builder $query) {
            $query->where('mark_session_subject.is_delete', false);
        });
    }

    public function getRouteKeyName(): string
    {
        return 'sessionsubjectid';
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

    // the chosen subjects, in the order set on the Subject Order screen
    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(
            Subject::class,
            'mark_session_subject_item',
            'sessionsubjectid',
            'subjectid',
            'sessionsubjectid',
            'subjectid'
        )->orderBy('mark_subject.subjectorder')->orderBy('mark_subject.subjectid');
    }

    // "First Semester" or "First Year", depending on the course
    protected function termLabel(): Attribute
    {
        return Attribute::get(fn () => $this->term
            ? Term::label($this->term->name, $this->course?->structure_type)
            : '—');
    }

    // "MBM-1011, MBM-1021, ..."
    protected function subjectCodes(): Attribute
    {
        return Attribute::get(fn () => $this->subjects->pluck('subjectcode')->implode(', '));
    }
}