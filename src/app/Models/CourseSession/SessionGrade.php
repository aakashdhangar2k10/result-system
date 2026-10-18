<?php

namespace App\Models\CourseSession;

use App\Models\Batch\Batch;
use App\Models\Configuration\DivisionType;
use App\Models\Configuration\GradeType;
use App\Models\Course\Course;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SessionGrade extends Model
{
    protected $table = 'mark_session_grade';
    protected $primaryKey = 'sessiongradeid';

    protected $fillable = [
        'courseid', 'batchid', 'gradetypeid', 'divtypeid',
        'description', 'active', 'is_delete',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'is_delete' => 'boolean',
        ];
    }

    // Hide soft-deleted rows from every query automatically
    protected static function booted(): void
    {
        static::addGlobalScope('notDeleted', function (Builder $query) {
            $query->where('mark_session_grade.is_delete', false);
        });
    }

    public function getRouteKeyName(): string
    {
        return 'sessiongradeid';
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'courseid', 'courseid');
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class, 'batchid', 'batchid');
    }

    public function gradeType(): BelongsTo
    {
        return $this->belongsTo(GradeType::class, 'gradetypeid', 'coursetypeid');
    }

    public function divisionType(): BelongsTo
    {
        return $this->belongsTo(DivisionType::class, 'divtypeid', 'divtypeid');
    }
}