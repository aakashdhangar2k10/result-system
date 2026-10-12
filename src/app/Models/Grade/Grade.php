<?php

namespace App\Models\Grade;

use App\Models\Configuration\CourseType;
use App\Models\Configuration\GradeType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Grade extends Model
{
    protected $table = 'mark_grade';
    protected $primaryKey = 'gradeid';

    protected $fillable = [
        'coursetypeid', 'gradetypeid',
        'firstattemptgrade', 'firstattemptcredit',
        'secondattemptgrade', 'secondattemptcredit',
        'startscore', 'endscore', 'description',
        'active', 'is_delete',
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
            $query->where('mark_grade.is_delete', false);
        });
    }

    public function getRouteKeyName(): string
    {
        return 'gradeid';
    }

    public function courseType(): BelongsTo
    {
        return $this->belongsTo(CourseType::class, 'coursetypeid', 'coursetypeid');
    }

    public function gradeType(): BelongsTo
    {
        return $this->belongsTo(GradeType::class, 'gradetypeid', 'coursetypeid');
    }
}