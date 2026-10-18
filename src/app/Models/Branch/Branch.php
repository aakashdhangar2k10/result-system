<?php

namespace App\Models\Branch;

use App\Models\Course\Course;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Branch extends Model
{
    protected $table = 'mark_branch';
    protected $primaryKey = 'branchid';

    protected $fillable = ['courseid', 'fullname', 'shortname', 'description', 'active', 'is_delete'];

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
            $query->where('mark_branch.is_delete', false);
        });
    }

    public function getRouteKeyName(): string
    {
        return 'branchid';
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'courseid', 'courseid');
    }
}