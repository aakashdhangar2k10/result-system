<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;

trait ScopedByDepartment
{
    protected static function bootScopedByDepartment(): void
    {
        static::addGlobalScope('department', function (Builder $query) {
            if (DepartmentAccess::unrestricted()) {
                return;
            }

            $query->getModel()->limitToDepartments($query);
        });
    }

    // By default a table is limited through its courseid column. A model can override this.
    public function limitToDepartments(Builder $query): void
    {
        $query->whereIn($this->qualifyColumn('courseid'), DepartmentAccess::courseIds());
    }
}