<?php
use App\Http\Controllers\Configuration\CourseTypeController;
use App\Http\Controllers\Configuration\SemesterListController;
use App\Http\Controllers\Configuration\GradeTypeController;
use App\Http\Controllers\Configuration\DivisionTypeController;
use App\Http\Controllers\Configuration\SubjectTypeController;
use App\Http\Controllers\DepartmentController;
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

use App\Http\Controllers\WebAuthController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware('guest')->group(function () {
    Route::get('/login', [WebAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [WebAuthController::class, 'login']);
});

Route::middleware(['auth', 'role:admin|teacher|student'])->group(function () {
    Route::post('/logout', [WebAuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', fn () => view('dashboard'))->name('dashboard');
    
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
    
});