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

    /** True when nothing is restricted: no user, an administrator, or a user with all departments. */
    public static function unrestricted(): bool
    {
        $user = self::actor();

        if (! $user) {
            return true;
        }

        return self::$cache[$user->id]['all']
            ??= ($user->hasRole('admin', 'api') || (bool) $user->all_departments);
    }

    public static function departmentIds(): array
    {
        $user = self::actor();

        if (! $user) {
            return [];
        }

        return self::$cache[$user->id]['departments']
            ??= DB::table('mark_user_department as ud')
                ->join('mark_department as d', 'd.deptid', '=', 'ud.deptid')
                ->where('d.is_delete', false)
                ->where('ud.user_id', $user->id)
                ->pluck('ud.deptid')->map(fn ($id) => (int) $id)->all();
    }

    public static function courseIds(): array
    {
        $user = self::actor();

        if (! $user) {
            return [];
        }

        return self::$cache[$user->id]['courses']
            ??= DB::table('mark_course')
                ->whereIn('deptid', self::departmentIds())
                ->where('is_delete', false)
                ->pluck('courseid')->map(fn ($id) => (int) $id)->all();
    }

    // call after the departments of a user changed
    public static function flush(): void
    {
        self::$cache = [];
    }
}