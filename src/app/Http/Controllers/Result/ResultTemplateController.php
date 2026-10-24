<?php

namespace App\Http\Controllers\Result;

use App\Http\Controllers\Controller;
use App\Http\Requests\Result\ResultTemplateRequest;
use App\Models\Batch\Batch;
use App\Models\Course\Course;
use App\Models\Result\ResultTemplate;
use App\Models\Result\SemesterResult;
use App\Models\Semester\SemesterExam;
use App\Models\User;
use App\Services\GradeSheetService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ResultTemplateController extends Controller implements HasMiddleware
{
    public function __construct(private GradeSheetService $sheets)
    {
    }

    public static function middleware(): array
    {
        return [
            new Middleware('permission:resulttemplates.view', only: ['index', 'preview']),
            new Middleware('permission:resulttemplates.create', only: ['create', 'store', 'duplicate']),
            new Middleware('permission:resulttemplates.edit', only: ['edit', 'update']),
            new Middleware('permission:resulttemplates.delete', only: ['destroy']),
        ];
    }

    public function index(Request $request)
    {
        $templates = ResultTemplate::query()
            ->withCount('usages')
            ->with(['assignments.course', 'assignments.batch'])
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
            'assignmentRows' => [],
            'used' => 0,
            'lastUsed' => null,
        ] + $this->formData());
    }

    public function store(ResultTemplateRequest $request)
    {
        DB::transaction(function () use ($request) {
            $template = ResultTemplate::create($this->payload($request));
            $this->saveAssignments($template, $request);
            $this->settleDefault($template);
        });

        return redirect()->route('result-templates.index')->with('success', 'Template saved.');
    }

    public function edit(Request $request, ResultTemplate $resultTemplate)
    {
        if ($message = $this->lockMessage($request->user(), $resultTemplate)) {
            return redirect()->route('result-templates.index')->with('error', $message);
        }

        $resultTemplate->load('assignments');

        return view('result_template.edit', [
            'template' => $resultTemplate,
            'assignmentRows' => $resultTemplate->assignments
                ->map(fn ($a) => ['courseid' => $a->courseid, 'batchid' => $a->batchid])->all(),
            'used' => $resultTemplate->usages()->count(),
            'lastUsed' => $resultTemplate->usages()->latest('usageid')->value('created_at'),
        ] + $this->formData());
    }

    public function update(ResultTemplateRequest $request, ResultTemplate $resultTemplate)
    {
        if ($message = $this->lockMessage($request->user(), $resultTemplate)) {
            return redirect()->route('result-templates.index')->with('error', $message);
        }

        DB::transaction(function () use ($request, $resultTemplate) {
            $resultTemplate->update($this->payload($request, $resultTemplate));
            $this->saveAssignments($resultTemplate, $request);
            $this->settleDefault($resultTemplate->refresh());
        });

        return redirect()->route('result-templates.index')->with('success', 'Template updated.');
    }

    public function destroy(ResultTemplate $resultTemplate)
    {
        if ($resultTemplate->is_default) {
            return back()->with('error', 'The default template cannot be deleted. Make another template the default first.');
        }

        // a template that printed grade sheets is part of their history
        $used = $resultTemplate->usages()->count();

        if ($used > 0) {
            return back()->with('error', "This template was used to print {$used} grade sheet(s) and cannot be deleted. An administrator can mark it inactive instead.");
        }

        // soft delete, the same as the other modules
        $resultTemplate->update(['is_delete' => true, 'active' => false]);

        return redirect()->route('result-templates.index')->with('success', 'Template deleted.');
    }

    // a copy that can be edited freely: the way to change a template that is locked
    public function duplicate(ResultTemplate $resultTemplate)
    {
        $copy = DB::transaction(function () use ($resultTemplate) {
            $data = $resultTemplate->only((new ResultTemplate())->getFillable());

            $name = $resultTemplate->name . ' (copy)';
            $number = 2;

            while (ResultTemplate::where('name', $name)->exists()) {
                $name = $resultTemplate->name . " (copy {$number})";
                $number++;
            }

            $data['name'] = $name;
            $data['is_default'] = false;
            $data['active'] = true;
            $data['is_delete'] = false;

            if ($resultTemplate->logo && Storage::disk('public')->exists($resultTemplate->logo)) {
                $path = 'result-logos/' . Str::uuid() . '.' . pathinfo($resultTemplate->logo, PATHINFO_EXTENSION);
                Storage::disk('public')->copy($resultTemplate->logo, $path);
                $data['logo'] = $path;
            }

            return ResultTemplate::create($data);
        });

        return redirect()->route('result-templates.edit', $copy)
            ->with('success', 'A copy was created. Assign it to courses and change what you need.');
    }

    // the template printed for the first student of a calculated exam (a preview is not a real printout)
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

    // a used template can be changed by an administrator only
    private function lockMessage(User $user, ResultTemplate $template): ?string
    {
        $used = $template->usages()->count();

        if ($used === 0 || $user->isAdmin()) {
            return null;
        }

        return "This template was used to print {$used} grade sheet(s), so only an administrator can change it. Use Duplicate to make a copy of your own.";
    }

    private function payload(ResultTemplateRequest $request, ?ResultTemplate $current = null): array
    {
        $data = $request->safe()->except(['logo', 'remove_logo', 'assignments']);

        foreach (ResultTemplate::FLAGS as $flag) {
            $data[$flag] = $request->boolean($flag);
        }

        $data['footer_items'] = array_values(array_filter(
            array_map(fn ($label) => trim((string) $label), (array) $request->input('footer_items', [])),
            fn ($label) => $label !== ''
        ));

        // the default template stays the default until another one takes over
        if ($current?->is_default) {
            $data['is_default'] = true;
        }

        // automatic mode forgets the typed numbers
        if (($data['margin_mode'] ?? 'auto') !== 'custom') {
            foreach (['top', 'right', 'bottom', 'left'] as $side) {
                $data["margin_{$side}"] = null;
            }
        }

        if (($data['spacing_mode'] ?? 'auto') !== 'custom') {
            foreach (ResultTemplate::SPACING_FIELDS as $name) {
                $data["space_{$name}"] = null;
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

    // replaces the assignments of the template with the rows of the form
    private function saveAssignments(ResultTemplate $template, ResultTemplateRequest $request): void
    {
        $template->assignments()->delete();

        $rows = collect((array) $request->input('assignments', []))
            ->map(fn ($row) => [
                'courseid' => (int) ($row['courseid'] ?? 0),
                'batchid' => ($row['batchid'] ?? '') !== '' ? (int) $row['batchid'] : null,
            ])
            ->filter(fn ($row) => $row['courseid'] > 0)
            ->unique(fn ($row) => $row['courseid'] . '-' . ($row['batchid'] ?? 0))
            ->values();

        foreach ($rows as $row) {
            $template->assignments()->create($row);
        }
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

    private function formData(): array
    {
        return [
            'courses' => Course::where('active', true)->orderBy('shortname')->get(),
            'batches' => Batch::where('active', true)->with('branch')->orderByDesc('startyear')->get(),
        ];
    }
}