<?php

namespace App\Support;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DepartmentAccess
{
    private static array $cache = [];

    // the logged-in web user (null in the console, in queues and on the login page)
    private static function actor()
    {
        return Auth::guard('web')->user();
    }

    /** Does the user's own access cover every department? (administrator, or "all departments") */
    public static function hasAll(): bool
    {
        $user = self::actor();

        if (! $user) {
            return true;
        }

        return self::$cache[$user->id]['has_all']
            ??= ($user->hasRole('admin', 'api') || (bool) $user->all_departments);
    }

    /** The departments the user may work in. */
    public static function permittedIds(): array
    {
        $user = self::actor();

        if (! $user) {
            return [];
        }

        return self::$cache[$user->id]['permitted'] ??= self::hasAll()
            ? DB::table('mark_department')->where('is_delete', false)->pluck('deptid')->map(fn ($id) => (int) $id)->all()
            : DB::table('mark_user_department as ud')
                ->join('mark_department as d', 'd.deptid', '=', 'ud.deptid')
                ->where('d.is_delete', false)
                ->where('ud.user_id', $user->id)
                ->pluck('ud.deptid')->map(fn ($id) => (int) $id)->all();
    }

    /** The department chosen in the page header, or null for "all". */
    public static function selected(): ?int
    {
        if (! self::actor()) {
            return null;
        }

        $id = (int) session('dept_context', 0);

        return $id && in_array($id, self::permittedIds(), true) ? $id : null;
    }

    /** True when nothing is limited: no user, or a user who sees everything and has not picked a department. */
    public static function unrestricted(): bool
    {
        if (! self::actor()) {
            return true;
        }

        return self::hasAll() && self::selected() === null;
    }

    public static function departmentIds(): array
    {
        if (! self::actor()) {
            return [];
        }

        $selected = self::selected();

        return $selected ? [$selected] : self::permittedIds();
    }

    public static function courseIds(): array
    {
        $user = self::actor();

        if (! $user) {
            return [];
        }

        $ids = self::departmentIds();

        return self::$cache[$user->id]['courses'][implode(',', $ids)]
            ??= DB::table('mark_course')
                ->whereIn('deptid', $ids)
                ->where('is_delete', false)
                ->pluck('courseid')->map(fn ($id) => (int) $id)->all();
    }

    /** Data for the department menu in the page header. */
    public static function switcher(): array
    {
        if (! self::actor()) {
            return ['show' => false];
        }

        $ids = self::permittedIds();

        // with one department there is nothing to choose
        if (count($ids) < 2) {
            return ['show' => false];
        }

        $items = DB::table('mark_department')
            ->whereIn('deptid', $ids)
            ->where('is_delete', false)
            ->orderBy('fullname')
            ->get(['deptid', 'fullname', 'shortname']);

        $selected = self::selected();
        $current = $items->firstWhere('deptid', $selected);

        return [
            'show' => true,
            'selected' => $selected,
            'items' => $items,
            'label' => $current ? ($current->shortname ?: $current->fullname) : (self::hasAll() ? 'All departments' : 'All my departments'),
        ];
    }

    // call after the departments of a user or the chosen department changed
    public static function flush(): void
    {
        self::$cache = [];
    }
}