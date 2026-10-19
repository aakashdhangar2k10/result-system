<?php

namespace App\Services;

use App\Models\Course\Course;
use App\Models\Student\Student;
use App\Models\Student\StudentImport;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use RuntimeException;
use Throwable;

class StudentImportService
{
    public const MAX_ROWS = 2000;

    private const HEADER_SCAN_ROWS = 15;

    // keep in step with the mobile rule in StudentRequest
    private const MOBILE_REGEX = '/^\+?[0-9]{10,13}$/';

    // header text (lower case, letters and digits only) => student field
    private const HEADERS = [
        'rollno' => 'rollno',
        'enrollmentno' => 'enrollmentno',
        'apaarid' => 'apaarid',
        'firstname' => 'firstname',
        'lastname' => 'lastname',
        'fatherlabel' => 'father_label',
        'fathertitle' => 'father_title',
        'fathername' => 'fathername',
        'mothertitle' => 'mother_title',
        'mothername' => 'mothername',
        'address' => 'address',
        'mobileno' => 'mobile',
        'mobile' => 'mobile',
    ];

    private const FIELDS = [
        'rollno', 'enrollmentno', 'apaarid', 'firstname', 'lastname',
        'father_label', 'father_title', 'fathername', 'mother_title', 'mothername',
        'address', 'mobile',
    ];

    private const REQUIRED = ['rollno', 'enrollmentno', 'firstname', 'fathername', 'mothername'];

    private const UPPERCASE = [
        'rollno', 'enrollmentno', 'apaarid', 'firstname', 'lastname', 'fathername', 'mothername',
    ];

    private const LABELS = [
        'rollno' => 'Roll No.',
        'enrollmentno' => 'Enrollment No.',
        'apaarid' => 'APAAR ID',
        'firstname' => 'First Name',
        'lastname' => 'Last Name',
        'father_label' => 'Father Label',
        'father_title' => 'Father Title',
        'fathername' => "Father's Name",
        'mother_title' => 'Mother Title',
        'mothername' => "Mother's Name",
        'address' => 'Address',
        'mobile' => 'Mobile No.',
    ];

    private const TEMPLATE_HEADERS = [
        'S.No.', 'Roll No.', 'Enrollment No.', 'First Name', 'Last Name',
        'Father Label', 'Father Title', 'Father Name', 'Mother Title', 'Mother Name',
        'Address', 'Mobile No.', 'Image', 'APAAR ID',
    ];

    // ------------------------------------------------------------------ reading

    /** Reads the sheet and returns ['title', 'course', 'rows']. Throws RuntimeException when the file is unusable. */
    public function parse(string $path): array
    {
        try {
            $reader = IOFactory::createReaderForFile($path);
            $reader->setReadDataOnly(true);
            $grid = $reader->load($path)->getActiveSheet()->toArray(null, true, false, false);
        } catch (Throwable $e) {
            throw new RuntimeException('The file could not be read. Upload the Excel template as an .xlsx or .xls file.');
        }

        [$headerIndex, $map] = $this->findHeader($grid);

        $missing = array_diff(self::REQUIRED, array_keys($map));

        if ($missing) {
            throw new RuntimeException('These columns are missing in the sheet: '
                . implode(', ', array_map(fn ($field) => self::LABELS[$field], $missing)) . '.');
        }

        $rows = [];

        foreach ($grid as $index => $cells) {
            if ($index <= $headerIndex) {
                continue;
            }

            $data = [];
            foreach (self::FIELDS as $field) {
                $data[$field] = isset($map[$field]) ? $this->cell($cells[$map[$field]] ?? null) : null;
            }

            // skip rows with nothing in the student columns
            if (! array_filter($data, fn ($value) => $value !== null)) {
                continue;
            }

            if (count($rows) >= self::MAX_ROWS) {
                throw new RuntimeException('The sheet has more than ' . self::MAX_ROWS . ' student rows. Split it into smaller files.');
            }

            $rows[] = $this->buildRow($index + 1, $data);
        }

        if (! $rows) {
            throw new RuntimeException('No student rows were found below the header row.');
        }

        // the lines above the header: school / department, then course
        $lines = collect(array_slice($grid, 0, $headerIndex))
            ->map(fn ($cells) => $this->firstFilled($cells))
            ->filter()
            ->values();

        return [
            'title' => $lines->count() > 1 ? $lines[0] : null,
            'course' => $lines->count() > 1 ? $lines[1] : ($lines[0] ?? null),
            'rows' => $rows,
        ];
    }

    // ------------------------------------------------------------------ checking

    /** Adds errors and warnings to every row. */
    public function check(array $rows): array
    {
        $values = fn (string $field) => array_column(array_column($rows, 'data'), $field);

        $existing = [
            'rollno' => $this->existing('rollno', $values('rollno')),
            'enrollmentno' => $this->existing('enrollmentno', $values('enrollmentno')),
            'apaarid' => $this->existing('apaarid', $values('apaarid')),
        ];

        $seen = ['rollno' => [], 'enrollmentno' => [], 'apaarid' => [], 'person' => []];

        foreach ($rows as $i => $row) {
            $data = $row['data'];
            $errors = $row['errors'];
            $warnings = [];

            $validator = Validator::make($data, [
                'rollno' => ['required', 'string', 'max:50'],
                'enrollmentno' => ['required', 'string', 'max:50'],
                'apaarid' => ['nullable', 'string', 'max:30'],
                'firstname' => ['required', 'string', 'max:100'],
                'lastname' => ['nullable', 'string', 'max:100'],
                'fathername' => ['required', 'string', 'max:150'],
                'mothername' => ['required', 'string', 'max:150'],
                'address' => ['nullable', 'string', 'max:500'],
                'mobile' => ['nullable', 'regex:' . self::MOBILE_REGEX],
            ], [
                'mobile.regex' => 'Mobile No. must be 10 to 13 digits.',
            ], self::LABELS);

            $errors = array_merge($errors, $validator->errors()->all());

            foreach (['rollno', 'enrollmentno', 'apaarid'] as $field) {
                $value = $data[$field];

                if ($value === null) {
                    continue;
                }

                if (isset($existing[$field][$value])) {
                    $errors[] = self::LABELS[$field] . " '{$value}' already exists in the student list.";
                }

                if (isset($seen[$field][$value])) {
                    $errors[] = self::LABELS[$field] . " '{$value}' is repeated (first used in row {$seen[$field][$value]}).";
                } else {
                    $seen[$field][$value] = $row['row'];
                }
            }

            // same person twice in one sheet is only a warning: it may be a typo in the roll number
            if ($data['firstname'] !== null) {
                $person = implode('|', [$data['firstname'], $data['lastname'], $data['fathername'], $data['mothername']]);

                if (isset($seen['person'][$person])) {
                    $warnings[] = "Same name and parents as row {$seen['person'][$person]}. Check that this is not a duplicate student.";
                } else {
                    $seen['person'][$person] = $row['row'];
                }
            }

            $rows[$i]['errors'] = array_values(array_unique($errors));
            $rows[$i]['warnings'] = $warnings;
        }

        return $rows;
    }

    public function counts(array $rows): array
    {
        $total = count($rows);
        $withErrors = count(array_filter($rows, fn ($row) => ! empty($row['errors'])));
        $withWarnings = count(array_filter($rows, fn ($row) => empty($row['errors']) && ! empty($row['warnings'])));

        return [
            'total_rows' => $total,
            'valid_rows' => $total - $withErrors,
            'error_rows' => $withErrors,
            'warning_rows' => $withWarnings,
        ];
    }

    // ------------------------------------------------------------------ saving

    /** Saves the valid rows. Returns the rows with their result, plus the imported and skipped counts. */
    public function commit(StudentImport $import): array
    {
        $rows = $import->rows ?? [];
        $candidates = array_filter($rows, fn ($row) => empty($row['errors']));

        // check again against the live student list: someone may have added the same numbers after the preview
        $taken = [];
        foreach (['rollno', 'enrollmentno', 'apaarid'] as $field) {
            $taken[$field] = $this->existing($field, array_column(array_column($candidates, 'data'), $field));
        }

        $now = now();
        $insert = [];

        foreach ($rows as $i => $row) {
            if (! empty($row['errors'])) {
                $rows[$i]['result'] = 'skipped';
                $rows[$i]['note'] = 'Not imported because the row has errors.';
                continue;
            }

            $clash = null;
            foreach (['rollno', 'enrollmentno', 'apaarid'] as $field) {
                $value = $row['data'][$field];

                if ($value !== null && isset($taken[$field][$value])) {
                    $clash = self::LABELS[$field] . " '{$value}' was added by someone else after the preview.";
                    break;
                }
            }

            if ($clash) {
                $rows[$i]['result'] = 'skipped';
                $rows[$i]['note'] = $clash;
                continue;
            }

            $insert[] = $row['data'] + [
                'courseid' => $import->courseid,
                'batchid' => $import->batchid,
                'importid' => $import->importid,
                'image' => null,
                'active' => true,
                'is_delete' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $rows[$i]['result'] = 'imported';
            $rows[$i]['note'] = null;
        }

        foreach (array_chunk($insert, 200) as $chunk) {
            Student::insert($chunk);
        }

        return [
            'rows' => $rows,
            'imported' => count($insert),
            'skipped' => count($rows) - count($insert),
        ];
    }

    // ------------------------------------------------------------------ template

    public function template(?Course $course = null): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Students');

        $end = 4 + self::MAX_ROWS;

        // rows 1 and 2: school / department and course, like the sheet you already use
        $sheet->mergeCells('A1:N1');
        $sheet->mergeCells('A2:N2');
        $sheet->setCellValue('A1', $course?->department?->fullname ?? 'SCHOOL / DEPARTMENT NAME');
        $sheet->setCellValue('A2', $course?->fullname ?? 'COURSE NAME');
        $sheet->getStyle('A1:A2')->getFont()->setBold(true)->setSize(13);
        $sheet->getStyle('A1:A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // row 4: headers
        $sheet->fromArray([self::TEMPLATE_HEADERS], null, 'A4');
        $sheet->getStyle('A4:N4')->getFont()->setBold(true);
        $sheet->getStyle('A4:N4')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('DDEBF7');

        $widths = ['A' => 7, 'B' => 18, 'C' => 18, 'D' => 18, 'E' => 20, 'F' => 14, 'G' => 14,
            'H' => 28, 'I' => 14, 'J' => 26, 'K' => 30, 'L' => 16, 'M' => 10, 'N' => 18];
        foreach ($widths as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }

        // numbers such as roll no. and mobile are kept as text, so Excel does not change them
        foreach (['B', 'C', 'L', 'N'] as $column) {
            $sheet->getStyle("{$column}5:{$column}{$end}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
        }

        // drop-down lists for the label and the titles
        $dropdowns = [
            'F' => ["Father's", "Husband's", "Guardian's"],
            'G' => Student::FATHER_TITLES,
            'I' => Student::MOTHER_TITLES,
        ];

        foreach ($dropdowns as $column => $options) {
            $validation = new DataValidation();
            $validation->setType(DataValidation::TYPE_LIST);
            $validation->setAllowBlank(true);
            $validation->setShowDropDown(true);
            $validation->setShowErrorMessage(true);
            $validation->setErrorTitle('Not in the list');
            $validation->setError('Pick a value from the list.');
            $validation->setFormula1('"' . implode(',', $options) . '"');
            $sheet->setDataValidation("{$column}5:{$column}{$end}", $validation);
        }

        $sheet->freezePane('A5');

        return $spreadsheet;
    }

    // ------------------------------------------------------------------ helpers

    private function findHeader(array $grid): array
    {
        foreach (array_slice($grid, 0, self::HEADER_SCAN_ROWS, true) as $index => $cells) {
            $found = [];

            foreach ($cells as $column => $value) {
                $key = $this->normalize($value);

                if (isset(self::HEADERS[$key]) && ! isset($found[self::HEADERS[$key]])) {
                    $found[self::HEADERS[$key]] = $column;
                }
            }

            if (isset($found['rollno'], $found['enrollmentno'])) {
                return [$index, $found];
            }
        }

        throw new RuntimeException('The header row was not found. Use the Excel template: it needs the "Roll No." and "Enrollment No." columns.');
    }

    private function buildRow(int $rowNumber, array $data): array
    {
        $errors = [];

        foreach (self::UPPERCASE as $field) {
            if ($data[$field] !== null) {
                $data[$field] = mb_strtoupper($data[$field]);
            }
        }

        if ($data['mobile'] !== null) {
            $data['mobile'] = preg_replace('/[\s\-()]/', '', $data['mobile']);
        }

        // "Father's" / "Mr" in the sheet are matched to the lists used by the Add Student form
        $lists = [
            'father_label' => [Student::FATHER_LABELS, 'Father Label'],
            'father_title' => [Student::FATHER_TITLES, 'Father Title'],
            'mother_title' => [Student::MOTHER_TITLES, 'Mother Title'],
        ];

        foreach ($lists as $field => [$options, $name]) {
            if ($data[$field] === null) {
                continue;
            }

            $matched = $this->matchOption($data[$field], $options);

            if ($matched === null) {
                $errors[] = "{$name} '{$data[$field]}' is not valid (use: " . implode(', ', $options) . ').';
            }

            $data[$field] = $matched;
        }

        return [
            'row' => $rowNumber,
            'data' => $data,
            'errors' => $errors,
            'warnings' => [],
            'result' => null,
            'note' => null,
        ];
    }

    private function matchOption(string $value, array $options): ?string
    {
        $clean = fn (string $text) => preg_replace(
            '/[^a-z]/',
            '',
            mb_strtolower(preg_replace("/['’]s$/iu", '', trim($text)))
        );

        $wanted = $clean($value);

        foreach ($options as $option) {
            if ($clean($option) === $wanted) {
                return $option;
            }
        }

        return null;
    }

    private function existing(string $column, array $values): array
    {
        $values = array_values(array_unique(array_filter($values, fn ($value) => $value !== null && $value !== '')));
        $found = [];

        foreach (array_chunk($values, 500) as $chunk) {
            foreach (Student::whereIn($column, $chunk)->pluck($column) as $value) {
                $found[mb_strtoupper((string) $value)] = true;
            }
        }

        return $found;
    }

    private function normalize(mixed $value): string
    {
        return preg_replace('/[^a-z0-9]/', '', mb_strtolower(trim((string) $value)));
    }

    private function firstFilled(array $cells): ?string
    {
        foreach ($cells as $value) {
            $text = $this->cell($value);

            if ($text !== null) {
                return $text;
            }
        }

        return null;
    }

    private function cell(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        // numbers typed into Excel cells (roll no., mobile) come back as int/float
        if (is_float($value) && floor($value) == $value) {
            $value = sprintf('%.0f', $value);
        } elseif (is_bool($value)) {
            $value = $value ? '1' : '0';
        }

        $text = trim(preg_replace('/\s+/u', ' ', (string) $value));

        return $text === '' ? null : $text;
    }
}