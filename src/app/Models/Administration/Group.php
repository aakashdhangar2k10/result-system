<?php

namespace App\Models\Administration;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Models\Permission;

class Group extends Model
{
    protected $table = 'mark_group';
    protected $primaryKey = 'groupid';

    protected $fillable = ['name', 'description', 'active', 'is_delete'];

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
            $query->where('mark_group.is_delete', false);
        });
    }

    public function getRouteKeyName(): string
    {
        return 'groupid';
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'mark_group_user', 'groupid', 'user_id', 'groupid', 'id');
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'mark_group_permission', 'groupid', 'permission_id', 'groupid', 'id');
    }
}