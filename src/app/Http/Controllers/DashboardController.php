<?php

namespace App\Http\Controllers;

use App\Models\Batch\Batch;
use App\Models\Course\Course;
use App\Models\Department;
use App\Models\Result\SemesterResult;
use App\Models\Result\SubjectResult;
use App\Models\Semester\SemesterExam;
use App\Models\Student\Student;
use App\Models\Student\StudentImport;
use App\Models\Subject\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();
        $user->loadMissing(['groups', 'departments']);

        // everybody lands here after login: without dashboard access the page is just a welcome note
        if (! $user->can('dashboard.view')) {
            return view('dashboard.index', ['user' => $user, 'full' => false]);
        }

        // count tiles: only the modules the user may open (department access is applied by the models)
        $tiles = collect([
            ['students.view', 'Students', fn () => Student::where('active', true)->count(), 'bi-people-fill', 'primary', 'students.index'],
            ['courses.view', 'Courses', fn () => Course::where('active', true)->count(), 'bi-mortarboard-fill', 'success', 'courses.index'],
            ['departments.view', 'Departments', fn () => Department::where('active', true)->count(), 'bi-building', 'info', 'departments.index'],
            ['batches.view', 'Sessions', fn () => Batch::where('active', true)->count(), 'bi-calendar-range', 'warning', 'batches.index'],
            ['subjects.view', 'Subjects', fn () => Subject::where('active', true)->count(), 'bi-journal-bookmark-fill', 'secondary', 'subjects.index'],
        ])
            ->filter(fn ($tile) => $user->can($tile[0]))
            ->map(fn ($tile) => [
                'label' => $tile[1],
                'value' => ($tile[2])(),
                'icon' => $tile[3],
                'color' => $tile[4],
                'url' => route($tile[5]),
            ])
            ->values();

        $attention = ['needsSgpa' => collect(), 'stale' => collect(), 'previews' => 0];
        $latest = collect();

        if ($user->can('subjectresults.view')) {
            // results are entered, but the SGPA was never calculated
            $attention['needsSgpa'] = SemesterExam::with(['course', 'batch', 'term'])
                ->whereIn('semesterexamid', SubjectResult::select('semesterexamid'))
                ->whereNotIn('semesterexamid', SemesterResult::select('semesterexamid'))
                ->orderByDesc('semesterexamid')
                ->limit(5)
                ->get();

            // marks changed after the SGPA was calculated
            $attention['stale'] = SemesterResult::with(['exam.course', 'exam.batch', 'exam.term'])
                ->where('is_stale', true)
                ->whereIn('semesterexamid', SemesterExam::select('semesterexamid'))
                ->limit(5)
                ->get();

            $latest = SemesterResult::with(['exam.course', 'exam.batch', 'exam.term'])
                ->whereIn('semesterexamid', SemesterExam::select('semesterexamid'))
                ->latest('calculated_at')
                ->limit(6)
                ->get();
        }

        if ($user->can('studentimports.view')) {
            $attention['previews'] = StudentImport::where('status', 'preview')->count();
        }

        $byCourse = collect();

        if ($user->can('students.view')) {
            $byCourse = Student::query()
                ->where('active', true)
                ->select('courseid', DB::raw('count(*) as total'))
                ->groupBy('courseid')
                ->with('course')
                ->orderByDesc('total')
                ->limit(8)
                ->get();
        }

        // quick actions: label, permission, route, icon
        $actions = collect([
            ['Add Student', 'students.create', 'students.create', 'bi-person-plus-fill'],
            ['Add With Excel', 'studentimports.create', 'student-imports.create', 'bi-file-earmark-excel'],
            ['Add Result', 'subjectresults.create', 'results.create', 'bi-pencil-square'],
            ['Result Sheets', 'subjectresults.view', 'results.index', 'bi-card-list'],
            ['Student List', 'studentlists.view', 'student-lists.index', 'bi-list-check'],
            ['Users', 'adminusers.view', 'admin-users.index', 'bi-shield-lock'],
        ])
            ->filter(fn ($action) => $user->can($action[1]))
            ->map(fn ($action) => ['label' => $action[0], 'url' => route($action[2]), 'icon' => $action[3]])
            ->values();

        return view('dashboard.index', [
            'user' => $user,
            'full' => true,
            'tiles' => $tiles,
            'attention' => $attention,
            'latest' => $latest,
            'byCourse' => $byCourse,
            'actions' => $actions,
        ]);
    }
}