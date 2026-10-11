<?php

namespace App\Models\Configuration;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class GradeType extends Model
{
    protected $table = 'mark_course_type';
    protected $primaryKey = 'coursetypeid';

    protected $fillable = ['coursetypename', 'active', 'is_delete'];

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
            $query->where('mark_course_type.is_delete', false);
        });
    }

    public function getRouteKeyName(): string
    {
        return 'coursetypeid';
    }
}