<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndAdminSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'marks.enter', 'marks.view', 'results.generate', 'results.view', 'users.manage',
            'departments.view', 'departments.create', 'departments.edit', 'departments.delete',
            'coursetypes.view', 'coursetypes.create', 'coursetypes.edit', 'coursetypes.delete',
            'semesters.view', 'semesters.create', 'semesters.edit', 'semesters.delete',
            'gradetypes.view', 'gradetypes.create', 'gradetypes.edit', 'gradetypes.delete',
            'divisiontypes.view', 'divisiontypes.create', 'divisiontypes.edit', 'divisiontypes.delete',
            'grades.view', 'grades.create', 'grades.edit', 'grades.delete',
        ];

        foreach ($permissions as $p) {
            Permission::findOrCreate($p, 'api');
        }

        Role::findOrCreate('admin', 'api')->syncPermissions($permissions);

        Role::findOrCreate('teacher', 'api')->syncPermissions([
            'marks.enter', 'marks.view', 'results.view',
            'departments.view', 'coursetypes.view','semesters.view','gradetypes.view','divisiontypes.view','grades.view',
        ]);

        Role::findOrCreate('student', 'api')->syncPermissions(['results.view']);

        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'Admin', 'password' => 'password123']
        );
        $admin->assignRole('admin');
    }
}