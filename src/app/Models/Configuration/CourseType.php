<?php

namespace App\Models\Configuration;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class CourseType extends Model
{
    protected $table = 'mark_coursetype';
    protected $primaryKey = 'coursetypeid';

    const CREATED_AT = null;
    const UPDATED_AT = 'updatedate';

    protected $fillable = ['fullname', 'shortname', 'description', 'active', 'is_delete'];

    protected function casts(): array
    {
        return [
            'updatedate' => 'datetime',
            'active' => 'boolean',
            'is_delete' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope('notDeleted', function (Builder $query) {
            $query->where('mark_coursetype.is_delete', false);
        });
    }

    public function getRouteKeyName(): string
    {
        return 'coursetypeid';
    }
}