<?php

namespace App\Models\Course;

use App\Models\Configuration\GradeType;
use App\Models\Department;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Course extends Model
{
    public const STRUCTURES = [
        'semester' => 'Semester-wise',
        'year' => 'Year-wise',
    ];

    protected $table = 'mark_course';
    protected $primaryKey = 'courseid';

    protected $fillable = [
        'deptid', 'coursetypeid', 'fullname', 'shortname',
        'structure_type', 'total_terms', 'has_branch',
        'description', 'active', 'is_delete',
    ];

    protected function casts(): array
    {
        return [
            'total_terms' => 'integer',
            'has_branch' => 'boolean',
            'active' => 'boolean',
            'is_delete' => 'boolean',
        ];
    }

    // Hide soft-deleted rows from every query automatically
    protected static function booted(): void
    {
        static::addGlobalScope('notDeleted', function (Builder $query) {
            $query->where('mark_course.is_delete', false);
        });
    }

    public function getRouteKeyName(): string
    {
        return 'courseid';
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'deptid', 'deptid');
    }

    public function courseType(): BelongsTo
    {
        return $this->belongsTo(GradeType::class, 'coursetypeid', 'coursetypeid');
    }

    // "8 Semesters (4 years)" or "3 Years"
    protected function durationLabel(): Attribute
    {
        return Attribute::get(function () {
            $terms = (int) $this->total_terms;

            if ($this->structure_type === 'year') {
                return $terms . ' ' . ($terms === 1 ? 'Year' : 'Years');
            }

            $years = $terms / 2;
            $years = $years == (int) $years ? (int) $years : $years;

            return $terms . ' ' . ($terms === 1 ? 'Semester' : 'Semesters')
                . ' (' . $years . ' ' . ($years == 1 ? 'year' : 'years') . ')';
        });
    }
}