<?php

namespace App\Services;

use App\Models\Configuration\SemesterList;
use App\Models\Result\ResultTemplate;
use App\Models\Result\SemesterResult;
use App\Models\Result\SemesterResultItem;
use App\Models\Semester\SemesterExam;
use App\Models\Student\Student;
use App\Support\Term;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class GradeSheetService
{
    public function __construct(private SemesterResultService $sgpa) {}

    /** The template to print with: the one chosen, else the course's own, else the default, else the built-in layout. */
    public function template(SemesterExam $exam, ?int $templateId = null): ResultTemplate
    {
        if ($templateId && $chosen = ResultTemplate::where('active', true)->find($templateId)) {
            return $chosen;
        }

        return ResultTemplate::where('active', true)->where('courseid', $exam->courseid)->first()
            ?? ResultTemplate::where('active', true)->where('is_default', true)->first()
            ?? ResultTemplate::where('active', true)->first()
            ?? ResultTemplate::defaults();
    }

    /**
     * Everything a printout needs: the template, the one-click overrides and one sheet per student.
     * Options: header (0/1), photo (0/1), student (id), sort, dir.
     */
    public function pack(SemesterExam $exam, ResultTemplate $template, array $options): array
    {
        $exam->loadMissing(['course.department', 'batch.branch', 'term']);

        $course = $exam->course;
        $batch = $exam->batch;

        // one click: the print popup can switch the header and the photo for this printout only
        $showHeader = isset($options['header']) ? (bool) (int) $options['header'] : (bool) $template->show_header;
        $showPhoto = isset($options['photo']) ? (bool) (int) $options['photo'] : (bool) $template->show_photo;

        $header = SemesterResult::with('items')->where('semesterexamid', $exam->semesterexamid)->firstOrFail();
        $summaries = $header->items->keyBy('studentid');

        $students = Student::whereIn('studentid', $summaries->keys())->get();

        if (! empty($options['student'])) {
            $students = $students->where('studentid', (int) $options['student'])->values();
        }

        $students = $this->order($students->all(), $summaries, $options['sort'] ?? 'roll', $options['dir'] ?? 'asc');

        // the SGPA of every semester of these students (for the semester-wise table)
        $history = SemesterResultItem::query()
            ->join('mark_semester_result as h', 'h.semresultid', '=', 'mark_semester_result_item.semresultid')
            ->join('mark_semester_exam as e', 'e.semesterexamid', '=', 'h.semesterexamid')
            ->where('h.courseid', $exam->courseid)
            ->where('h.batchid', $exam->batchid)
            ->where('e.is_delete', false)
            ->whereIn('mark_semester_result_item.studentid', collect($students)->pluck('studentid'))
            ->get(['mark_semester_result_item.*', 'h.semid as term_semid', 'h.semesterexamid as term_exam', 'e.attempt as term_attempt'])
            ->groupBy('studentid');

        $currentNumber = Term::number($exam->term?->roman);

        $terms = SemesterList::get()
            ->filter(fn($t) => ($n = Term::number($t->roman)) >= 1 && $n <= (int) $course?->total_terms)
            ->sortBy(fn($t) => Term::number($t->roman))
            ->values();

        $isYear = $course?->structure_type === 'year';

        $sheets = [];

        foreach ($students as $student) {
            $summary = $summaries->get($student->studentid);

            $rows = [];
            foreach ($this->sgpa->breakdown($exam, $student->studentid) as $line) {
                $subject = $line['subject'];
                $row = $line['row'];

                $credit = $row ? (float) $row->subject_credit : (float) $subject->subjectcredit;
                $point = $row ? (int) $row->grade_point : null;

                $marks = null;
                if ($row && ($row->entry_mode ?? 'marks') === 'marks') {
                    $marks = [
                        'internal' => $row->internal !== null ? ResultService::fmt($row->internal) : '-',
                        'external' => $row->external_absent ? 'Ab' : ($row->external !== null ? ResultService::fmt($row->external) : '-'),
                        'total' => $row->is_absent ? 'Ab' : ($row->total !== null ? ResultService::fmt($row->total) : '-'),
                    ];
                }

                $rows[] = [
                    'code' => (string) $subject->subjectcode,
                    'title' => (string) $subject->subjectname,
                    'credit' => ResultService::fmt($credit),
                    'grade' => $row ? (string) $row->grade : '-',
                    'point' => $point === null ? '-' : $point,
                    'points' => ResultService::fmt(($row && $row->is_pass) ? $credit * $point : 0) ?: '0',
                    'marks' => $marks,
                ];
            }

            // semester-wise details: this exam for the current semester, the latest attempt for the earlier ones
            $mine = $history->get($student->studentid, collect());
            $cells = [];

            foreach ($terms as $term) {
                $number = Term::number($term->roman);
                $found = null;

                if ($number === $currentNumber) {
                    $found = $mine->first(fn($r) => (int) $r->term_exam === (int) $exam->semesterexamid);
                } elseif ($number < $currentNumber) {
                    $found = $mine->where('term_semid', $term->semid)->sortByDesc('term_attempt')->first();
                }

                $cells[] = [
                    'label' => (string) $term->roman,
                    'credit' => $found ? ResultService::fmt($found->credits) : '',
                    'sgpa' => $found ? number_format((float) $found->sgpa, 2) : '',
                    'attempt' => $found ? (SemesterExam::ORDINALS[$found->term_attempt] ?? (string) $found->term_attempt) : '',
                    'result' => $found?->result_label ?? '',
                ];
            }

            $sheets[] = [
                'department' => (string) $course?->department?->fullname,
                'programme' => $this->programme($template, $course),
                'batch' => $batch?->startyear . '-' . substr((string) $batch?->endyear, -2)
                    . ($batch?->branch ? ' (' . $batch->branch->shortname . ')' : ''),
                'examination' => (string) $exam->exammonth?->format('F-Y'),
                'term_word' => $isYear ? 'Year' : 'Semester',
                'term' => (string) $exam->term?->roman,
                'name' => (string) $student->full_name,
                'enrollment' => (string) $student->enrollmentno,
                'roll' => (string) $student->rollno,
                'father_label' => ($student->father_label ?: 'Father') . "'s Name",
                'father' => trim($student->father_title . ' ' . $student->fathername),
                'mother' => trim($student->mother_title . ' ' . $student->mothername),
                'photo' => $showPhoto ? $this->dataUri($student->image) : null,
                'rows' => $rows,
                'total_credits' => ResultService::fmt($summary?->credits) ?: '0',
                'total_points' => ResultService::fmt($summary?->credit_points) ?: '0',
                'sgpa' => number_format((float) ($summary?->sgpa ?? 0), 2),
                'terms' => $cells,
                'declared' => $exam->resultdate?->format('j F, Y'),
            ];
        }

        return [
            'template' => $template,
            'show_header' => $showHeader,
            'show_photo' => $showPhoto,
            'logo' => $template->show_logo ? $this->dataUri($template->logo) : null,
            'margins' => $template->marginsMm(),
            'spacing' => $template->spacingMm(),
            'sheets' => $sheets,
            'file' => Str::slug(implode(' ', array_filter([
                'gradesheet',
                $course?->shortname,
                $batch?->label,
                Term::base($exam->term?->name),
                'attempt',
                $exam->attempt,
                count($sheets) === 1 ? $sheets[0]['roll'] : null,
            ]))) ?: 'gradesheet',
        ];
    }

    public function pdf(array $pack): Response
    {
        // many students means many pages and photos
        @ini_set('memory_limit', '512M');
        @set_time_limit(180);

        $dir = storage_path('app/dompdf');
        File::ensureDirectoryExists($dir);

        $options = new Options();
        $options->set('defaultFont', 'Helvetica');
        $options->set('isRemoteEnabled', false);
        $options->set('tempDir', $dir);
        $options->set('fontCache', $dir);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml(View::make('result.gradesheet.pdf', ['pack' => $pack])->render());
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $pack['file'] . '.pdf"',
        ]);
    }

    // ------------------------------------------------------------------ helpers
    // "M.B.A. (Foreign Trade)- 2 Years": the length is added only when the course name does not already carry it
    private function programme(ResultTemplate $template, $course): string
    {
        $name = trim((string) $course?->fullname);

        if (! $template->show_duration || preg_match('/\b\d+\s*(?:years?|yrs?|semesters?|sem)\b/i', $name)) {
            return $name;
        }

        $years = $course?->structure_type === 'year'
            ? (int) $course->total_terms
            : (int) ceil(((int) $course?->total_terms) / 2);

        return $years < 1 ? $name : $name . '- ' . $years . ' ' . ($years === 1 ? 'Year' : 'Years');
    }

    private function order(array $students, $summaries, string $sort, string $dir): array
    {
        usort($students, function ($a, $b) use ($summaries, $sort, $dir) {
            $primary = match ($sort) {
                'sgpa' => (float) ($summaries[$a->studentid]->sgpa ?? 0) <=> (float) ($summaries[$b->studentid]->sgpa ?? 0),
                'name' => strnatcasecmp($a->full_name, $b->full_name),
                'enrollment' => strnatcasecmp($a->enrollmentno, $b->enrollmentno),
                default => strnatcasecmp($a->rollno, $b->rollno),
            };

            if ($dir === 'desc') {
                $primary = -$primary;
            }

            // equal values: roll number, always ascending
            return $primary !== 0 ? $primary : strnatcasecmp($a->rollno, $b->rollno);
        });

        return $students;
    }

    // images go into the PDF as data, so dompdf never has to read files from disk
    private function dataUri(?string $path): ?string
    {
        $disk = Storage::disk('public');

        if (! $path || ! $disk->exists($path)) {
            return null;
        }

        return 'data:' . ($disk->mimeType($path) ?: 'image/png') . ';base64,' . base64_encode($disk->get($path));
    }
}
