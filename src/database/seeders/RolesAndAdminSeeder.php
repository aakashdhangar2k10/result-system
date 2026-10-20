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
            'divisions.view', 'divisions.create', 'divisions.edit', 'divisions.delete',
            'courses.view', 'courses.create', 'courses.edit', 'courses.delete',
            'branches.view', 'branches.create', 'branches.edit', 'branches.delete',
            'batches.view', 'batches.create', 'batches.edit', 'batches.delete',
            'sessiongrades.view', 'sessiongrades.create', 'sessiongrades.edit', 'sessiongrades.delete',
            'coursesemesters.view', 'coursesemesters.create', 'coursesemesters.edit', 'coursesemesters.delete',
            'semesterexams.view', 'semesterexams.create', 'semesterexams.edit', 'semesterexams.delete',
            'subjects.view', 'subjects.create', 'subjects.edit', 'subjects.delete',
            'subjecttypes.view', 'subjecttypes.create', 'subjecttypes.edit', 'subjecttypes.delete',
            'subjectorders.view', 'subjectorders.edit',
            'sessionsubjects.view', 'sessionsubjects.create', 'sessionsubjects.edit', 'sessionsubjects.delete',
            'students.view', 'students.create', 'students.edit','students.delete',
            'studentimports.view', 'studentimports.create', 'studentimports.clear',
            'studentlists.view', 'studentlists.create', 'studentlists.edit', 'studentlists.delete',
            'studentlists.lock', 'studentlists.unlock',
            'optionalsubjectstudents.view', 'optionalsubjectstudents.create', 'optionalsubjectstudents.edit', 'optionalsubjectstudents.delete',
            'subjectresults.view', 'subjectresults.create', 'subjectresults.edit', 'subjectresults.delete',
            'semesterresults.calculate','semesterresults.download',
            'adminusers.view', 'adminusers.create', 'adminusers.edit', 'adminusers.delete',
            'adminroles.view', 'adminroles.create', 'adminroles.edit', 'adminroles.delete',
            'admingroups.view', 'admingroups.create', 'admingroups.edit', 'admingroups.delete',
        ];

        foreach ($permissions as $p) {
            Permission::findOrCreate($p, 'api');
        }

        // the administrator always has every permission
        Role::findOrCreate('admin', 'api')->syncPermissions($permissions);

       // first-time defaults only: after that these roles are managed under Administration → Roles
        $teacher = Role::findOrCreate('teacher', 'api');
        if ($teacher->wasRecentlyCreated) {
        $teacher->syncPermissions([
        'marks.enter', 'marks.view', 'results.view',
        'departments.view', 'coursetypes.view', 'semesters.view',
        'gradetypes.view', 'divisiontypes.view', 'grades.view', 'divisions.view',
        'courses.view', 'branches.view', 'batches.view', 'sessiongrades.view',
        'coursesemesters.view', 'semesterexams.view', 'subjects.view', 'subjecttypes.view',
        'subjectorders.view', 'sessionsubjects.view', 'students.view', 'studentlists.view',
        'optionalsubjectstudents.view',
        'subjectresults.view', 'subjectresults.create', 'subjectresults.edit',
        'semesterresults.download',
        ]);
        }

       $student = Role::findOrCreate('student', 'api');
        if ($student->wasRecentlyCreated) {
            $student->syncPermissions(['results.view']);
        }

        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'Admin', 'password' => 'password123']
        );
        $admin->assignRole('admin');
    }
}