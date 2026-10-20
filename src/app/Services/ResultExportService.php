<?php

namespace App\Services;

use App\Models\Result\SemesterResult;
use App\Models\Result\SubjectResult;
use App\Models\Result\SubjectResultItem;
use App\Models\Semester\CourseSemester;
use App\Models\Semester\SemesterExam;
use App\Support\Term;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\Response;

class ResultExportService
{
    public const SORTS = [
        'roll' => 'Roll No.',
        'enrollment' => 'Enrollment No.',
        'name' => 'Student Name',
        'sgpa' => 'SGPA',
    ];

    // ------------------------------------------------------------------ the sheet

    /** Everything a downloaded sheet shows, in one array used by both the PDF and the Excel file. */
    public function build(SemesterExam $exam, array $options): array
    {
        $exam->loadMissing(['course.department', 'batch.branch', 'term']);

        $view = ($options['view'] ?? 'grades') === 'marks' ? 'marks' : 'grades';
        $sort = array_key_exists($options['sort'] ?? '', self::SORTS) ? $options['sort'] : 'roll';
        $dir = ($options['dir'] ?? 'asc') === 'desc' ? 'desc' : 'asc';

        $header = SemesterResult::with('items')->where('semesterexamid', $exam->semesterexamid)->firstOrFail();
        $sgpa = $header->items->keyBy('studentid');

        $results = SubjectResult::with('subject')
            ->where('semesterexamid', $exam->semesterexamid)
            ->get()
            ->sortBy(fn ($result) => [$result->subject?->subjectorder ?? 0, $result->resultid])
            ->values();

        $items = SubjectResultItem::with('student')->whereIn('resultid', $results->pluck('resultid'))->get();

        $cells = [];
        foreach ($items as $item) {
            $cells[$item->studentid][$item->resultid] = $item;
        }

        $rows = $items->pluck('student')->filter()->unique('studentid')
            ->map(function ($student) use ($results, $cells, $sgpa, $view) {
                $summary = $sgpa->get($student->studentid);

                return [
                    'roll' => (string) $student->rollno,
                    'enrollment' => (string) $student->enrollmentno,
                    'name' => (string) $student->full_name,
                    'cells' => $results
                        ->map(fn ($result) => $this->cell($cells[$student->studentid][$result->resultid] ?? null, $view))
                        ->all(),
                    'sgpa' => $summary ? number_format((float) $summary->sgpa, 2) : '-',
                    'sgpa_value' => $summary ? (float) $summary->sgpa : -1.0,
                    'result' => $summary?->result_label ?? '-',
                ];
            })
            ->values()
            ->all();

        usort($rows, function ($a, $b) use ($sort, $dir) {
            $primary = match ($sort) {
                'sgpa' => $a['sgpa_value'] <=> $b['sgpa_value'],
                'name' => strnatcasecmp($a['name'], $b['name']),
                'enrollment' => strnatcasecmp($a['enrollment'], $b['enrollment']),
                default => strnatcasecmp($a['roll'], $b['roll']),
            };

            if ($dir === 'desc') {
                $primary = -$primary;
            }

            // equal values: roll number, always ascending
            return $primary !== 0 ? $primary : strnatcasecmp($a['roll'], $b['roll']);
        });

        foreach ($rows as $i => $row) {
            $rows[$i]['sr'] = $i + 1;
        }

        $subjects = $results->map(fn ($result) => [
            'code' => (string) $result->subject?->subjectcode,
            'name' => (string) $result->subject?->subjectname,
            'credit' => ResultService::fmt($result->subject_credit),
            'max' => (int) $result->max_internal + (int) $result->max_external,
        ])->all();

        $course = $exam->course;
        $batch = $exam->batch;

        $isYear = $course?->structure_type === 'year';
        $years = $isYear ? (int) $course->total_terms : (int) ceil(((int) $course?->total_terms) / 2);

        $semester = CourseSemester::where('courseid', $exam->courseid)
            ->where('batchid', $exam->batchid)
            ->where('semid', $exam->semid)
            ->first();

        // "Result: M.B.A. (Foreign Trade)- 2 Years I Semester, July - November-2023"
        $title = 'Result: ' . $course?->fullname . '- ' . $years . ' ' . ($years === 1 ? 'Year' : 'Years')
            . ' ' . $exam->term?->roman . ' ' . ($isYear ? 'Year' : 'Semester')
            . $this->period($semester?->semestermonth, $exam->exammonth);

        if ($exam->attempt > 1) {
            $title .= ' - ' . (SemesterExam::ORDINALS[$exam->attempt] ?? $exam->attempt) . ' Attempt';
        }

        return [
            'department' => (string) $course?->department?->fullname,
            'university' => (string) config('result.university'),
            'title' => $title,
            'batch' => 'BATCH:- ' . $batch?->startyear . '-' . substr((string) $batch?->endyear, -2)
                . ($batch?->branch ? ' (' . $batch->branch->shortname . ')' : ''),
            'view' => $view,
            'sort' => $sort,
            'dir' => $dir,
            'subjects' => $subjects,
            'pairs' => array_chunk($subjects, 2),   // the subject list is printed two to a row
            'rows' => $rows,
            'declared' => $exam->resultdate?->format('j F, Y'),
            'signatures' => (array) config('result.signatures'),
            'file' => Str::slug(implode(' ', [
                'result', $course?->shortname, $batch?->label, Term::base($exam->term?->name), 'attempt', $exam->attempt,
            ])) ?: 'result',
        ];
    }

    // ------------------------------------------------------------------ PDF

    public function pdf(array $sheet): Response
    {
        // dompdf keeps its font cache and temp files here
        $dir = storage_path('app/dompdf');
        File::ensureDirectoryExists($dir);

        $options = new Options();
        $options->set('defaultFont', 'Times');
        $options->set('isRemoteEnabled', false);
        $options->set('tempDir', $dir);
        $options->set('fontCache', $dir);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml(View::make('result.export.pdf', ['sheet' => $sheet])->render());
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $sheet['file'] . '.pdf"',
        ]);
    }

    // ------------------------------------------------------------------ Excel

    public function xlsx(array $sheet): Response
    {
        $spreadsheet = new Spreadsheet();
        $ws = $spreadsheet->getActiveSheet();
        $ws->setTitle('Result');

        $col = fn (int $index) => Coordinate::stringFromColumnIndex($index);

        // columns A-D: Sr. No., Roll Number, Enrollment Number, Student Name; then one column per subject
        $firstSubject = 5;
        $sgpaCol = $firstSubject + count($sheet['subjects']);
        $resultCol = $sgpaCol + 1;
        $last = $col($resultCol);
        $head = 6;

        // a text value is always stored as text, so a name or a number can never turn into a formula
        $text = fn (string $cell, mixed $value) => $ws->setCellValueExplicit($cell, (string) $value, DataType::TYPE_STRING);

        // heading lines
        foreach ([$sheet['department'], $sheet['university'], $sheet['title'], $sheet['batch']] as $i => $line) {
            $row = $i + 1;
            $ws->mergeCells("A{$row}:{$last}{$row}");
            $text("A{$row}", $line);
        }

        $ws->getStyle('A1:A4')->getFont()->setBold(true);
        $ws->getStyle('A1:A2')->getFont()->setSize(13);
        $ws->getStyle('A1:A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // table header
        foreach (['S. No.', 'Roll Number', 'Enrollment Number', 'Student Name'] as $i => $label) {
            $text($col($i + 1) . $head, $label);
        }

        foreach ($sheet['subjects'] as $i => $subject) {
            $text(
                $col($firstSubject + $i) . $head,
                $subject['code'] . ($sheet['view'] === 'marks' ? "\n(" . $subject['max'] . ')' : '')
            );
        }

        $text($col($sgpaCol) . $head, 'SGPA');
        $text($col($resultCol) . $head, 'Result');

        // students
        $r = $head;

        foreach ($sheet['rows'] as $row) {
            $r++;

            $ws->setCellValue("A{$r}", $row['sr']);
            $text("B{$r}", $row['roll']);
            $text("C{$r}", $row['enrollment']);
            $text("D{$r}", $row['name']);

            foreach ($row['cells'] as $i => $value) {
                $cell = $col($firstSubject + $i) . $r;

                // marks are real numbers, so they can be added up in Excel
                is_numeric($value) ? $ws->setCellValue($cell, (float) $value) : $text($cell, $value);
            }

            $sgpaCell = $col($sgpaCol) . $r;
            $row['sgpa_value'] >= 0 ? $ws->setCellValue($sgpaCell, $row['sgpa_value']) : $text($sgpaCell, '-');
            $text($col($resultCol) . $r, $row['result']);
        }

        $headRange = "A{$head}:{$last}{$head}";
        $tableRange = "A{$head}:{$last}{$r}";

        $ws->getStyle($tableRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $ws->getStyle($headRange)->getFont()->setBold(true);
        $ws->getStyle($headRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E9ECEF');
        $ws->getStyle($headRange)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setWrapText(true);
        $ws->getStyle('A' . ($head + 1) . ":C{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $ws->getStyle($col($firstSubject) . ($head + 1) . ":{$last}{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $ws->getStyle($col($sgpaCol) . ($head + 1) . ':' . $col($sgpaCol) . $r)->getNumberFormat()->setFormatCode('0.00');
        $ws->getRowDimension($head)->setRowHeight(32);

        // subject list: code, name, credit
        $r += 2;
        $listStart = $r;

        $text("B{$r}", 'Subject Code');
        $ws->mergeCells("C{$r}:D{$r}");
        $text("C{$r}", 'Subject Name');
        $text("E{$r}", 'Subject Credit');
        $ws->getStyle("B{$r}:E{$r}")->getFont()->setBold(true);
        $ws->getStyle("B{$r}:E{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setWrapText(true);

        foreach ($sheet['subjects'] as $subject) {
            $r++;
            $text("B{$r}", $subject['code']);
            $ws->mergeCells("C{$r}:D{$r}");
            $text("C{$r}", $subject['name']);
            is_numeric($subject['credit']) ? $ws->setCellValue("E{$r}", (float) $subject['credit']) : $text("E{$r}", $subject['credit']);
        }

        $ws->getStyle("B{$listStart}:E{$r}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $ws->getStyle("B" . ($listStart + 1) . ":B{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $ws->getStyle("E" . ($listStart + 1) . ":E{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // declaration date and signatures
        if ($sheet['declared']) {
            $r += 2;
            $text("A{$r}", 'Date of Declaration of Result: ' . $sheet['declared']);
            $ws->getStyle("A{$r}")->getFont()->setBold(true);
        }

        $r += 3;
        foreach (array_slice($sheet['signatures'], 0, 3) as $i => $label) {
            $cell = $col([2, 4, $resultCol][$i]) . $r;
            $text($cell, $label);
            $ws->getStyle($cell)->getFont()->setBold(true);
            $ws->getStyle($cell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        // column widths
        foreach (['A' => 7, 'B' => 18, 'C' => 19, 'D' => 32] as $letter => $width) {
            $ws->getColumnDimension($letter)->setWidth($width);
        }
        for ($i = $firstSubject; $i < $sgpaCol; $i++) {
            $ws->getColumnDimension($col($i))->setWidth(11);
        }
        $ws->getColumnDimension($col($sgpaCol))->setWidth(9);
        $ws->getColumnDimension($col($resultCol))->setWidth(14);

        // printing: A4 landscape, header row on every page
        $ws->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
        $ws->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
        $ws->getPageSetup()->setScale(70);
        $ws->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd($head, $head);
        $ws->freezePane('E' . ($head + 1));

        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
        }, $sheet['file'] . '.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    // ------------------------------------------------------------------ helpers

    // grades as letters, or total marks; "Ab" for absent
    private function cell(?SubjectResultItem $item, string $view): string
    {
        if (! $item) {
            return '-';
        }

        if ($view === 'marks' && $item->total !== null && ! $item->is_absent) {
            return ResultService::fmt($item->total);
        }

        return (string) $item->grade;
    }

    // ", July - November-2023" (or ", July-2023 - January-2024" when it crosses a year)
    private function period($from, $to): string
    {
        if (! $from && ! $to) {
            return '';
        }

        if (! $from) {
            return ', ' . $to->format('F-Y');
        }

        if (! $to || $from->format('Y-m') === $to->format('Y-m')) {
            return ', ' . ($to ?? $from)->format('F-Y');
        }

        return $from->year === $to->year
            ? ', ' . $from->format('F') . ' - ' . $to->format('F-Y')
            : ', ' . $from->format('F-Y') . ' - ' . $to->format('F-Y');
    }
}