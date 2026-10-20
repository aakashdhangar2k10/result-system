<?php

namespace App\Services;

use App\Models\Administration\Group;
use App\Models\User;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AccessService
{
    private const ACTIONS = ['view', 'create', 'edit', 'delete'];

    /** The access matrix: one row per module, one column per action. */
    public function matrix(): array
    {
        $modules = [];
        $seen = [];

        foreach (Permission::where('guard_name', 'api')->orderBy('name')->get() as $permission) {
            [$module, $action] = array_pad(explode('.', $permission->name, 2), 2, 'access');

            $modules[$module][$action] = $permission;
            $seen[$action] = true;
        }

        $others = array_values(array_diff(array_keys($seen), self::ACTIONS));
        sort($others);

        $columns = array_values(array_filter(
            array_merge(self::ACTIONS, $others),
            fn ($action) => isset($seen[$action])
        ));

        $rows = [];
        foreach ($modules as $key => $cells) {
            $rows[] = [
                'key' => $key,
                'label' => config("access.modules.{$key}", Str::headline($key)),
                'cells' => $cells,
            ];
        }

        usort($rows, fn ($a, $b) => strcasecmp($a['label'], $b['label']));

        return [
            'columns' => array_map(fn ($action) => [
                'key' => $action,
                'label' => config("access.actions.{$action}", Str::headline($action)),
            ], $columns),
            'rows' => $rows,
        ];
    }

    /** ["students.view", "students.edit", "marks.enter"] => [['label' => 'Student', 'actions' => ['View', 'Edit']], ...] */
    public function summary(iterable $names): array
    {
        $byModule = [];

        foreach ($names as $name) {
            [$module, $action] = array_pad(explode('.', $name, 2), 2, 'access');
            $byModule[$module][] = config("access.actions.{$action}", Str::headline($action));
        }

        $rows = [];
        foreach ($byModule as $module => $actions) {
            $rows[] = ['label' => config("access.modules.{$module}", Str::headline($module)), 'actions' => $actions];
        }

        usort($rows, fn ($a, $b) => strcasecmp($a['label'], $b['label']));

        return $rows;
    }

    // ------------------------------------------------------------------ who can give what

    /** Permission names the actor may give to others: everything for an administrator, otherwise only his own. */
    public function grantableNames(User $actor): array
    {
        return $actor->isAdmin()
            ? Permission::where('guard_name', 'api')->pluck('name')->all()
            : $actor->allPermissionNames()->all();
    }

    public function canManageRole(User $actor, Role $role): bool
    {
        return $actor->isAdmin()
            || ($role->name !== 'admin'
                && $role->permissions->pluck('name')->diff($actor->allPermissionNames())->isEmpty());
    }

    public function canManageGroup(User $actor, Group $group): bool
    {
        return $actor->isAdmin()
            || $group->permissions->pluck('name')->diff($actor->allPermissionNames())->isEmpty();
    }

    public function grantableRoleIds(User $actor): array
    {
        return Role::with('permissions')->where('guard_name', 'api')->get()
            ->filter(fn ($role) => $this->canManageRole($actor, $role))
            ->pluck('id')->map(fn ($id) => (int) $id)->values()->all();
    }

    public function grantableGroupIds(User $actor): array
    {
        return Group::with('permissions')->get()
            ->filter(fn ($group) => $this->canManageGroup($actor, $group))
            ->pluck('groupid')->map(fn ($id) => (int) $id)->values()->all();
    }

    /**
     * The new list after an edit by someone who can only change part of it:
     * what he submitted (limited to what he may give) plus what was there and he may not touch.
     */
    public function keep(array $existing, array $submitted, array $grantable): array
    {
        $grantable = array_flip($grantable);

        $kept = array_filter($existing, fn ($value) => ! isset($grantable[$value]));
        $added = array_filter($submitted, fn ($value) => isset($grantable[$value]));

        return array_values(array_unique(array_merge($kept, $added)));
    }
}