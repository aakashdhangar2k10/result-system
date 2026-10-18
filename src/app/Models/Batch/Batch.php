<?php

namespace App\Models\Batch;

use App\Models\Branch\Branch;
use App\Models\Course\Course;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Batch extends Model
{
    protected $table = 'mark_batch';
    protected $primaryKey = 'batchid';

    protected $fillable = [
        'courseid', 'branchid', 'startyear', 'endyear',
        'description', 'active', 'is_delete',
    ];

    protected function casts(): array
    {
        return [
            'startyear' => 'integer',
            'endyear' => 'integer',
            'active' => 'boolean',
            'is_delete' => 'boolean',
        ];
    }

    // Hide soft-deleted rows from every query automatically
    protected static function booted(): void
    {
        static::addGlobalScope('notDeleted', function (Builder $query) {
            $query->where('mark_batch.is_delete', false);
        });
    }

    public function getRouteKeyName(): string
    {
        return 'batchid';
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'courseid', 'courseid');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branchid', 'branchid');
    }

    // "2024–2028"
    protected function label(): Attribute
    {
        return Attribute::get(fn () => $this->startyear . '–' . $this->endyear);
    }
}