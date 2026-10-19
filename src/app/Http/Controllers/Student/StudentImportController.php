<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Batch\Batch;
use App\Models\Course\Course;
use App\Models\Student\Student;
use App\Models\Student\StudentImport;
use App\Services\StudentImportService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use RuntimeException;

class StudentImportController extends Controller implements HasMiddleware
{
    public function __construct(private StudentImportService $service)
    {
    }

    public static function middleware(): array
    {
        return [
            new Middleware('permission:studentimports.view', only: ['index', 'show']),
            new Middleware('permission:studentimports.create', only: ['create', 'store', 'template', 'confirm', 'destroy']),
            new Middleware('permission:studentimports.clear', only: ['clear']),
        ];
    }

    // ---- import log
    public function index(Request $request)
    {
        $perPage = in_array((int) $request->per_page, [10, 25, 50, 100]) ? (int) $request->per_page : 10;

        $imports = StudentImport::listing()
            ->with(['course', 'batch.branch', 'uploader'])
            ->when($request->courseid, fn ($q, $id) => $q->where('courseid', $id))
            ->when($request->batchid, fn ($q, $id) => $q->where('batchid', $id))
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->latest('importid')
            ->paginate($perPage)
            ->withQueryString();

        return view('student_import.index', [
            'imports' => $imports,
            'perPage' => $perPage,
            'statuses' => StudentImport::STATUSES,
        ] + $this->options());
    }

    // ---- step 1: choose course + session + file
    public function create()
    {
        return view('student_import.create', $this->options());
    }

    public function template(Request $request)
    {
        $course = $request->filled('courseid')
            ? Course::with('department')->find($request->courseid)
            : null;

        $spreadsheet = $this->service->template($course);

        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
        }, 'student-import-template.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    // ---- step 2: read + check the file, nothing is saved to students yet
    public function store(Request $request)
    {
        $data = $request->validate([
            'courseid' => ['required', Rule::exists('mark_course', 'courseid')->where('is_delete', false)],
            'batchid' => [
                'required',
                Rule::exists('mark_batch', 'batchid')
                    ->where('courseid', $request->courseid)
                    ->where('is_delete', false),
            ],
            'file' => ['required', 'file', 'mimes:xlsx,xls', 'max:2048'],
        ], [
            'batchid.exists' => 'The selected session does not belong to the selected course.',
            'file.mimes' => 'Upload an Excel file (.xlsx or .xls).',
            'file.max' => 'The file must not be larger than 2 MB.',
        ]);

        $file = $request->file('file');

        try {
            $sheet = $this->service->parse($file->getRealPath());
        } catch (RuntimeException $e) {
            return back()->withInput()->withErrors(['file' => $e->getMessage()]);
        }

        $rows = $this->service->check($sheet['rows']);

        $import = StudentImport::create([
            'courseid' => $data['courseid'],
            'batchid' => $data['batchid'],
            'file_name' => $file->getClientOriginalName(),
            'sheet_title' => $sheet['title'],
            'sheet_course' => $sheet['course'],
            'rows' => $rows,
            'status' => 'preview',
            'uploaded_by' => $request->user()->id,
        ] + $this->service->counts($rows));

        return redirect()->route('student-imports.show', $import);
    }

    // ---- preview / result
    public function show(StudentImport $studentImport)
    {
        $studentImport->load(['course', 'batch.branch', 'uploader', 'clearer']);

        return view('student_import.show', [
            'import' => $studentImport,
            'rows' => $studentImport->rows ?? [],
        ]);
    }

    // ---- step 3: verified, save the valid rows
    public function confirm(Request $request, StudentImport $studentImport)
    {
        $request->validate(
            ['verified' => ['accepted']],
            ['verified.accepted' => 'Tick the box to confirm that you have checked the preview.']
        );

        $outcome = DB::transaction(function () use ($studentImport) {
            // lock the log row so a double click cannot import the sheet twice
            $import = StudentImport::lockForUpdate()->findOrFail($studentImport->importid);

            if ($import->status !== 'preview') {
                return ['error' => 'This sheet was already imported or discarded.'];
            }

            $stillThere = Batch::where('batchid', $import->batchid)->where('courseid', $import->courseid)->exists();

            if (! $stillThere) {
                return ['error' => 'The course or session of this sheet no longer exists.'];
            }

            $result = $this->service->commit($import);

            if ($result['imported'] === 0) {
                return ['error' => 'No student could be imported: every row has an error or was added by someone else.'];
            }

            $import->update([
                'rows' => $result['rows'],
                'imported_rows' => $result['imported'],
                'skipped_rows' => $result['skipped'],
                'status' => 'imported',
                'imported_at' => now(),
            ]);

            return $result;
        });

        if (isset($outcome['error'])) {
            return redirect()->route('student-imports.show', $studentImport)->with('error', $outcome['error']);
        }

        return redirect()->route('student-imports.show', $studentImport)
            ->with('success', "{$outcome['imported']} students imported, {$outcome['skipped']} rows skipped.");
    }

    // ---- throw away a preview
    public function destroy(StudentImport $studentImport)
    {
        if ($studentImport->status !== 'preview') {
            return back()->with('error', 'Only a sheet that is still in preview can be discarded.');
        }

        $studentImport->update(['status' => 'discarded', 'rows' => null]);

        return redirect()->route('student-imports.index')
            ->with('success', 'Sheet discarded. Nothing was saved to the student list.');
    }

    // ---- remove the students that came from one imported sheet
    public function clear(Request $request, StudentImport $studentImport)
    {
        if ($studentImport->status !== 'imported') {
            return back()->with('error', 'Only an imported sheet can be cleared.');
        }

        $cleared = DB::transaction(function () use ($studentImport, $request) {
            // soft delete, the same as deleting a student by hand
            $count = Student::where('importid', $studentImport->importid)->update(['is_delete' => true]);

            $studentImport->update([
                'status' => 'cleared',
                'cleared_rows' => $count,
                'cleared_at' => now(),
                'cleared_by' => $request->user()->id,
            ]);

            return $count;
        });

        return redirect()->route('student-imports.show', $studentImport)
            ->with('success', "{$cleared} students from this sheet were cleared.");
    }

    private function options(): array
    {
        return [
            'courses' => Course::where('active', true)->orderBy('shortname')->get(),
            'batches' => Batch::where('active', true)->with(['course', 'branch'])->orderByDesc('startyear')->get(),
        ];
    }
}