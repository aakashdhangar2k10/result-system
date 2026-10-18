<?php
use App\Http\Controllers\Configuration\CourseTypeController;
use App\Http\Controllers\Configuration\SemesterListController;
use App\Http\Controllers\Configuration\GradeTypeController;
use App\Http\Controllers\Configuration\DivisionTypeController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\Grade\GradeController;
use App\Http\Controllers\Division\DivisionController;
use App\Http\Controllers\Course\CourseController;
use App\Http\Controllers\Branch\BranchController;

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

    // configuration
    Route::prefix('configuration')->group(function () {
        Route::resource('course-types', CourseTypeController::class)->except('show');
        Route::resource('semesters', SemesterListController::class)->except('show');
        Route::resource('grade-types', GradeTypeController::class)->except('show');
        Route::resource('division-types', DivisionTypeController::class)->except('show');
    });
});