<?php

namespace App\Models;

use App\Models\Administration\Group;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable, HasRoles;

    protected string $guard_name = 'api';

    protected $fillable = ['name', 'email', 'photo', 'password', 'active', 'is_delete', 'all_departments'];

    protected $hidden = ['password', 'remember_token'];

    private ?Collection $groupPermissionCache = null;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'active' => 'boolean',
            'is_delete' => 'boolean',
            'all_departments' => 'boolean',
        ];
    }

    // A deleted user is hidden everywhere, so it can never log in
    protected static function booted(): void
    {
        static::addGlobalScope('notDeleted', function (Builder $query) {
            $query->where('users.is_delete', false);
        });
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return ['roles' => $this->getRoleNames()];
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'mark_group_user', 'user_id', 'groupid', 'id', 'groupid');
    }

    public function departments(): BelongsToMany
    {
        return $this->belongsToMany(Department::class, 'mark_user_department', 'user_id', 'deptid', 'id', 'deptid');
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin', 'api');
    }

    // Permissions this user gets from the groups he belongs to (active groups only)
    public function groupPermissionNames(): Collection
    {
        return $this->groupPermissionCache ??= DB::table('mark_group_user as gu')
            ->join('mark_group as g', 'g.groupid', '=', 'gu.groupid')
            ->join('mark_group_permission as gp', 'gp.groupid', '=', 'g.groupid')
            ->join('permissions as p', 'p.id', '=', 'gp.permission_id')
            ->where('gu.user_id', $this->getKey())
            ->where('g.active', true)
            ->where('g.is_delete', false)
            ->pluck('p.name')
            ->unique()
            ->values();
    }

    // roles + groups together
    public function allPermissionNames(): Collection
    {
        return $this->getAllPermissions()->pluck('name')->merge($this->groupPermissionNames())->unique()->values();
    }

    // initials on a coloured circle: no photo file is needed
    // the uploaded photo, or the initials on a coloured circle
    protected function avatarUrl(): Attribute
    {
        return Attribute::get(function () {
            if ($this->photo && Storage::disk('public')->exists($this->photo)) {
                return asset('storage/' . $this->photo);
            }

            $initials = collect(preg_split('/\s+/', trim((string) $this->name)))
                ->filter()
                ->take(2)
                ->map(fn($part) => mb_strtoupper(mb_substr($part, 0, 1)))
                ->implode('') ?: '?';

            $colors = ['#0d6efd', '#198754', '#dc3545', '#fd7e14', '#6f42c1', '#20c997', '#0aa2c0'];
            $color = $colors[crc32((string) $this->email) % count($colors)];

            $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="128" height="128" viewBox="0 0 128 128">'
                . '<rect width="128" height="128" fill="' . $color . '"/>'
                . '<text x="50%" y="50%" dy=".35em" text-anchor="middle" font-family="Arial,Helvetica,sans-serif" font-size="56" fill="#ffffff">'
                . e($initials) . '</text></svg>';

            return 'data:image/svg+xml;base64,' . base64_encode($svg);
        });
    }
}
