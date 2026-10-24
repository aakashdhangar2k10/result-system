<?php

namespace App\Http\Controllers\Result;

use App\Http\Controllers\Controller;
use App\Http\Requests\Result\ResultTemplateRequest;
use App\Models\Course\Course;
use App\Models\Result\ResultTemplate;
use App\Models\Result\SemesterResult;
use App\Models\Semester\SemesterExam;
use App\Services\GradeSheetService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ResultTemplateController extends Controller implements HasMiddleware
{
    public function __construct(private GradeSheetService $sheets)
    {
    }

    public static function middleware(): array
    {
        return [
            new Middleware('permission:resulttemplates.view', only: ['index', 'preview']),
            new Middleware('permission:resulttemplates.create', only: ['create', 'store']),
            new Middleware('permission:resulttemplates.edit', only: ['edit', 'update']),
            new Middleware('permission:resulttemplates.delete', only: ['destroy']),
        ];
    }

    public function index(Request $request)
    {
        $templates = ResultTemplate::with('course')
            ->when($request->q, fn ($q, $search) => $q->where('name', 'like', "%{$search}%"))
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();

        return view('result_template.index', compact('templates'));
    }

    public function create()
    {
        return view('result_template.create', [
            'template' => ResultTemplate::defaults(),
            'courses' => $this->courses(),
        ]);
    }

    public function store(ResultTemplateRequest $request)
    {
        DB::transaction(function () use ($request) {
            $template = ResultTemplate::create($this->payload($request));
            $this->settleDefault($template);
        });

        return redirect()->route('result-templates.index')->with('success', 'Template saved.');
    }

    public function edit(ResultTemplate $resultTemplate)
    {
        return view('result_template.edit', [
            'template' => $resultTemplate,
            'courses' => $this->courses(),
        ]);
    }

    public function update(ResultTemplateRequest $request, ResultTemplate $resultTemplate)
    {
        DB::transaction(function () use ($request, $resultTemplate) {
            $resultTemplate->update($this->payload($request, $resultTemplate));
            $this->settleDefault($resultTemplate->refresh());
        });

        return redirect()->route('result-templates.index')->with('success', 'Template updated.');
    }

    public function destroy(ResultTemplate $resultTemplate)
    {
        if ($resultTemplate->is_default) {
            return back()->with('error', 'The default template cannot be deleted. Make another template the default first.');
        }

        // soft delete, the same as the other modules
        $resultTemplate->update(['is_delete' => true, 'active' => false]);

        return redirect()->route('result-templates.index')->with('success', 'Template deleted.');
    }

    // the template printed for the first student of a calculated exam
    public function preview(ResultTemplate $resultTemplate)
    {
        foreach (SemesterResult::latest('calculated_at')->limit(10)->get() as $header) {
            $exam = SemesterExam::find($header->semesterexamid);

            if (! $exam) {
                continue;
            }

            $studentId = DB::table('mark_semester_result_item')
                ->where('semresultid', $header->semresultid)
                ->orderBy('studentid')
                ->value('studentid');

            $pack = $this->sheets->pack($exam, $resultTemplate, ['student' => $studentId]);

            if ($pack['sheets']) {
                return $this->sheets->pdf($pack);
            }
        }

        return back()->with('error', 'Calculate the SGPA of an exam first, so the preview has real data to show.');
    }

    // ------------------------------------------------------------------ helpers

    private function payload(ResultTemplateRequest $request, ?ResultTemplate $current = null): array
    {
        $data = $request->safe()->except(['logo', 'remove_logo']);

        foreach (ResultTemplate::FLAGS as $flag) {
            $data[$flag] = $request->boolean($flag);
        }

        $data['courseid'] = $request->filled('courseid') ? (int) $request->courseid : null;

        $data['footer_items'] = array_values(array_filter(
            array_map(fn ($label) => trim((string) $label), (array) $request->input('footer_items', [])),
            fn ($label) => $label !== ''
        ));

        // the default template stays the default until another one takes over
        if ($current?->is_default) {
            $data['is_default'] = true;
        }

        if (($data['margin_mode'] ?? 'auto') !== 'custom') {
            foreach (['top', 'right', 'bottom', 'left'] as $side) {
                $data["margin_{$side}"] = null;
            }
        }

        if ($request->boolean('remove_logo') && $current?->logo) {
            Storage::disk('public')->delete($current->logo);
            $data['logo'] = null;
        }

        if ($request->hasFile('logo')) {
            if ($current?->logo) {
                Storage::disk('public')->delete($current->logo);
            }

            $data['logo'] = $request->file('logo')->store('result-logos', 'public');
        }

        return $data;
    }

    // exactly one default template
    private function settleDefault(ResultTemplate $template): void
    {
        if ($template->is_default) {
            ResultTemplate::where('templateid', '<>', $template->templateid)->update(['is_default' => false]);
        } elseif (! ResultTemplate::where('is_default', true)->exists()) {
            $template->update(['is_default' => true]);
        }
    }

    private function courses()
    {
        return Course::where('active', true)->orderBy('shortname')->get();
    }
}