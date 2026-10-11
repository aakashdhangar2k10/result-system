<?php

namespace App\Models\Configuration;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SemesterList extends Model
{
    protected $table = 'mark_semester_list';
    protected $primaryKey = 'semid';

    const CREATED_AT = null;
    const UPDATED_AT = 'updatedate';

    protected $fillable = ['name', 'roman', 'joindate', 'active', 'is_delete'];

    protected function casts(): array
    {
        return [
            'joindate' => 'date',
            'updatedate' => 'datetime',
            'active' => 'boolean',
            'is_delete' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope('notDeleted', function (Builder $query) {
            $query->where('mark_semester_list.is_delete', false);
        });
    }

    public function getRouteKeyName(): string
    {
        return 'semid';
    }
}