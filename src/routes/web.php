<?php

use App\Http\Controllers\Administration\GroupController;
use App\Http\Controllers\Administration\RoleController;
use App\Http\Controllers\Administration\UserController;

use App\Http\Controllers\Configuration\CourseTypeController;
use App\Http\Controllers\Configuration\SemesterListController;
use App\Http\Controllers\Configuration\GradeTypeController;
use App\Http\Controllers\Configuration\DivisionTypeController;
use App\Http\Controllers\Configuration\SubjectTypeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DepartmentContextController;
use App\Http\Controllers\StructureController;
use App\Http\Controllers\Grade\GradeController;
use App\Http\Controllers\Division\DivisionController;
use App\Http\Controllers\Course\CourseController;
use App\Http\Controllers\Branch\BranchController;
use App\Http\Controllers\Batch\BatchController;
use App\Http\Controllers\CourseSession\SessionGradeController;
use App\Http\Controllers\Semester\CourseSemesterController;
use App\Http\Controllers\Semester\SemesterExamController;
use App\Http\Controllers\Subject\SubjectController;
use App\Http\Controllers\Subject\SubjectOrderController;
use App\Http\Controllers\Subject\SessionSubjectController;
use App\Http\Controllers\Student\StudentController;
use App\Http\Controllers\Student\StudentImportController;
use App\Http\Controllers\Student\StudentListController;
use App\Http\Controllers\Subject\OptionalSubjectStudentController;
use App\Http\Controllers\Result\ResultController;
use App\Http\Controllers\Result\ResultTemplateController;
use App\Http\Controllers\Result\CourseFinalController;
use App\Http\Controllers\WebAuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;


    Route::redirect('/', '/dashboard');

    Route::middleware('guest')->group(function () {
    Route::get('/login', [WebAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [WebAuthController::class, 'login']);
    });

    Route::middleware(['auth', 'active'])->group(function () {
    Route::post('/logout', [WebAuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', DashboardController::class)->name('dashboard.index');
    Route::post('department-context', [DepartmentContextController::class, 'update'])->name('department-context.update');
    Route::get('structure', StructureController::class)->name('structure.index');
    
    Route::prefix('administration')->group(function () {
    Route::resource('users', UserController::class)->names('admin-users');
    Route::resource('roles', RoleController::class)->except('show')->names('admin-roles');
    Route::resource('groups', GroupController::class)->except('show')->names('admin-groups');
    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('profile/password', [ProfileController::class, 'password'])->name('profile.password');
    Route::put('profile/photo', [ProfileController::class, 'photo'])->name('profile.photo');
    Route::delete('profile/photo', [ProfileController::class, 'removePhoto'])->name('profile.photo.remove');
    
    });

    Route::resource('departments', DepartmentController::class)->except('show');
    Route::resource('grades', GradeController::class)->except('show');
    Route::resource('divisions', DivisionController::class)->except('show');
    Route::resource('courses', CourseController::class)->except('show');
    Route::resource('branches', BranchController::class)->except('show');
    Route::resource('batches', BatchController::class)->except('show');
    
    // configuration
    Route::prefix('configuration')->group(function () {
        Route::resource('course-types', CourseTypeController::class)->except('show');
        Route::resource('semesters', SemesterListController::class)->except('show');
        Route::resource('grade-types', GradeTypeController::class)->except('show');
        Route::resource('division-types', DivisionTypeController::class)->except('show');
        Route::resource('subject-types', SubjectTypeController::class)->except('show');
    });

    Route::resource('course-semesters', CourseSemesterController::class)->except('show');
    Route::resource('semester-exams', SemesterExamController::class)->except('show');
    Route::resource('subjects', SubjectController::class)->except('show');
    Route::get('subject-orders', [SubjectOrderController::class, 'index'])->name('subject-orders.index');
    Route::put('subject-orders', [SubjectOrderController::class, 'update'])->name('subject-orders.update');
    Route::get('session-subjects/options', [SessionSubjectController::class, 'options'])->name('session-subjects.options');
    Route::resource('session-subjects', SessionSubjectController::class)->except('show');
    Route::patch('students/{student}/status', [StudentController::class, 'status'])->name('students.status');
    Route::resource('students', StudentController::class);

    // coursesession
    Route::prefix('coursesession')->group(function () {
        Route::resource('session-grades', SessionGradeController::class);
    });

    // studentImport
    Route::prefix('student-imports')->name('student-imports.')->controller(StudentImportController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::get('template', 'template')->name('template');
        Route::post('/', 'store')->name('store');
        Route::get('{studentImport}', 'show')->name('show');
        Route::post('{studentImport}/confirm', 'confirm')->name('confirm');
        Route::post('{studentImport}/clear', 'clear')->name('clear');
        Route::delete('{studentImport}', 'destroy')->name('destroy');
    });

    Route::get('student-lists/options', [StudentListController::class, 'options'])->name('student-lists.options');
    Route::resource('student-lists', StudentListController::class);
    Route::post('student-lists/{student_list}/lock', [StudentListController::class, 'lock'])->name('student-lists.lock');
    Route::post('student-lists/{student_list}/unlock', [StudentListController::class, 'unlock'])->name('student-lists.unlock');


    Route::get('optional-subject-students/options', [OptionalSubjectStudentController::class, 'students'])->name('optional-subject-students.options');
    Route::resource('optional-subject-students', OptionalSubjectStudentController::class)->except('show');

    // Result
    Route::prefix('results')->name('results.')->controller(ResultController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::get('entry', 'entry')->name('entry');
        Route::post('calculate', 'calculate')->name('calculate');
        Route::post('semester/{semesterExam}/sgpa', 'calculateSgpa')->name('sgpa');
        Route::get('semester/{semesterExam}/student/{student}', 'studentDetail')->name('student');
        Route::post('/', 'store')->name('store');
        Route::get('{subjectResult}', 'show')->name('show');
        Route::get('{subjectResult}/edit', 'edit')->name('edit');
        Route::put('{subjectResult}', 'update')->name('update');
        Route::delete('{subjectResult}', 'destroy')->name('destroy');
        Route::get('semester/{semesterExam}/download', 'download')->name('download');
        Route::get('semester/{semesterExam}/gradesheet', 'gradesheet')->name('gradesheet');
    });

    Route::get('result-templates/{resultTemplate}/preview', [ResultTemplateController::class, 'preview'])->name('result-templates.preview');
    Route::resource('result-templates', ResultTemplateController::class)->except('show');

    Route::post('result-templates/{resultTemplate}/duplicate', [ResultTemplateController::class, 'duplicate'])->name('result-templates.duplicate');
    Route::get('result-finals', [CourseFinalController::class, 'index'])->name('result-finals.index');
    Route::get('result-finals/{course}/edit', [CourseFinalController::class, 'edit'])->name('result-finals.edit');
    Route::put('result-finals/{course}', [CourseFinalController::class, 'update'])->name('result-finals.update');
});
