<?php

namespace App\Models\Division;

use App\Models\Configuration\CourseType;
use App\Models\Configuration\DivisionType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Division extends Model
{
    protected $table = 'mark_division';
    protected $primaryKey = 'divisionid';

    protected $fillable = [
        'coursetypeid', 'divtypeid', 'divisionname',
        'startpoint', 'endpoint', 'active', 'is_delete',
    ];

    protected function casts(): array
    {
        return [
            'startpoint' => 'decimal:2',
            'endpoint' => 'decimal:2',
            'active' => 'boolean',
            'is_delete' => 'boolean',
        ];
    }

    // Hide soft-deleted rows from every query automatically
    protected static function booted(): void
    {
        static::addGlobalScope('notDeleted', function (Builder $query) {
            $query->where('mark_division.is_delete', false);
        });
    }

    public function getRouteKeyName(): string
    {
        return 'divisionid';
    }

    public function courseType(): BelongsTo
    {
        return $this->belongsTo(CourseType::class, 'coursetypeid', 'coursetypeid');
    }

    public function divisionType(): BelongsTo
    {
        return $this->belongsTo(DivisionType::class, 'divtypeid', 'divtypeid');
    }
}