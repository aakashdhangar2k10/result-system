<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use App\Support\DepartmentAccess;
use App\Support\ScopedByDepartment;
class Department extends Model
{
    use ScopedByDepartment;
    protected $table = 'mark_department';
    protected $primaryKey = 'deptid';

    // Only an "updatedate" column, no created_at
    const CREATED_AT = null;
    const UPDATED_AT = 'updatedate';

    protected $fillable = [
        'fullname', 'shortname', 'logo', 'address',
        'affilationshortname', 'affilationname', 'affilationyear',
        'joindate', 'active', 'is_delete',
    ];

    
    
    protected function casts(): array
    {
        return [
            'joindate' => 'date',
            'updatedate' => 'datetime',
            'active' => 'boolean',
            'is_delete' => 'boolean',
        ];
    }

    // Hide soft-deleted rows from every query automatically
    protected static function booted(): void
    {
        static::addGlobalScope('notDeleted', function (Builder $query) {
            $query->where('mark_department.is_delete', false);
        });
    }

    // Route model binding uses deptid in URLs
    public function getRouteKeyName(): string
    {
        return 'deptid';
    }

    public function limitToDepartments(Builder $query): void
    {
    $query->whereIn($this->qualifyColumn('deptid'), DepartmentAccess::departmentIds());
    }

    protected function logoUrl(): Attribute
    {
        return Attribute::get(fn () => $this->logo ? asset('storage/' . $this->logo) : null);
    }
}