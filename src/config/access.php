<?php

return [
    // the three roles of the system: they cannot be deleted or renamed
    'system_roles' => ['admin', 'teacher', 'student'],

    // permission prefix => name in the access matrix
    'modules' => [
        'marks' => 'Marks (legacy)',
        'results' => 'Results (student view)',
        'users' => 'Users (API)',
        'departments' => 'Departments',
        'courses' => 'Courses',
        'branches' => 'Branches',
        'batches' => 'Batch / Session',
        'coursetypes' => 'Course Types',
        'semesters' => 'Semester List',
        'gradetypes' => 'Grade Types',
        'divisiontypes' => 'Division Types',
        'subjecttypes' => 'Subject Types',
        'grades' => 'Grade',
        'divisions' => 'Division',
        'sessiongrades' => 'Session Grade (result rule)',
        'coursesemesters' => 'Semester',
        'semesterexams' => 'Semester Exam',
        'subjects' => 'Subject',
        'subjectorders' => 'Subject Order',
        'sessionsubjects' => 'Session Subject',
        'students' => 'Student',
        'studentimports' => 'Student Import (Excel)',
        'studentlists' => 'Student List',
        'optionalsubjectstudents' => 'Optional Subject Student',
        'subjectresults' => 'Result entry (subject marks)',
        'semesterresults' => 'Semester Result (SGPA)',
        'adminusers' => 'Administration: Users',
        'adminroles' => 'Administration: Roles',
        'admingroups' => 'Administration: Groups',
        'dashboard' => 'Dashboard',
        'coursefinals' => 'Final Result Points',
        'structure' => 'Structure & Flow',
    ],

    // permission suffix => column name
    'actions' => [
        'view' => 'View', 'create' => 'Add', 'edit' => 'Edit', 'delete' => 'Delete',
        'enter' => 'Enter', 'generate' => 'Generate', 'manage' => 'Manage', 'lock' => 'Lock',
        'unlock' => 'Unlock', 'clear' => 'Clear', 'calculate' => 'Calculate', 'download' => 'Download',
    ],
];