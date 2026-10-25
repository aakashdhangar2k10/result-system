<?php

namespace App\Models\Result;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ResultTemplate extends Model
{
    public const LAYOUTS = [
        'semester' => 'Semester grade sheet',
        'phd' => 'Ph.D. course work sheet',
    ];

    // the academic session as printed on the Ph.D. sheet
    public const SESSION_FORMATS = [
        'start' => '2022',
        'short' => '2022-23',
        'full' => '2022-2023',
    ];

    // what a row of the Ph.D. information table can show
    public const INFO_FIELDS = [
        'institute' => 'Name & Address of the Institute / School of Studies',
        'course' => 'Programme',
        'subject' => 'Subject',
        'name' => 'Name of the Student',
        'father' => "Father's/ Husband's Name",
        'mother' => "Mother's Name",
        'enrollment' => 'Enrollment No.',
        'roll' => 'Roll No.',
        'session' => 'Academic Session',
        'batch' => 'Batch',
        'term' => 'Semester',
        'exam' => 'Examination Month & Year',
        'apaar' => 'APAAR ID',
    ];

    // the rows of your Ph.D. sample
    public const DEFAULT_INFO_ROWS = [
        ['field' => 'institute', 'label' => 'Name & Address of the Institue/ School of Studies College Where Ph.D. Course Work is Conducted'],
        ['field' => 'subject', 'label' => 'Subject'],
        ['field' => 'name', 'label' => 'Name of the Student:'],
        ['field' => 'father', 'label' => "Father's/ Husband's Name"],
        ['field' => 'mother', 'label' => "Mother's Name"],
        ['field' => 'enrollment', 'label' => 'Enrollment No.'],
        ['field' => 'roll', 'label' => 'Roll No.'],
        ['field' => 'session', 'label' => 'Academic Session'],
        ['field' => 'term', 'label' => 'Semester'],
        ['field' => 'exam', 'label' => 'Examination Month & Year'],
    ];

    public const DEFAULT_CERTIFICATE = 'This is certified that Mr./Ms./Mrs. {name} has successfully completed the Ph.D. Course Work as per UGC Regulations 2016/2022 and the Ph.D. Ordinance of the University.';

    // quick-add buttons in the footer editor
    public const SIGNATURES = ['Prepared by', 'Co-ordinator', 'Head', 'Head/Director', 'Asst. Registrar(Exam.)', 'Controller of Examinations', 'Dean'];
    public const MAX_SIGNATURES = 6;

    public const ALIGN = ['left' => 'Left', 'center' => 'Centre', 'right' => 'Right'];
    public const SIDES = ['left' => 'Left', 'right' => 'Right'];

    // the date printed in "Date of Result"
    public const DATE_FORMATS = [
        'j F, Y' => '18 June, 2025',
        'd/m/Y' => '18/06/2025',
        'd-m-Y' => '18-06-2025',
        'd M Y' => '18 Jun 2025',
    ];

    // page margins in millimetres: top, right, bottom, left
    public const AUTO_MARGINS = [14, 12, 14, 12];

    // spacing in millimetres, used when the mode is "auto" (and for any empty box in "custom")
    public const AUTO_SPACING = [
        'header_top' => 0,       // above the header
        'header_side' => 0,      // left and right inset of the header
        'header_lines' => 1,     // between two header lines
        'after_header' => 4,     // between the header and the grade sheet
        'sections' => 4,         // between the tables
        'footer' => 12,          // above the signatures
    ];

    public const SPACING_FIELDS = ['header_top', 'header_side', 'header_lines', 'after_header', 'sections', 'footer'];

    public const FLAGS = [
        'is_default', 'show_header', 'show_logo', 'show_department', 'show_header_line', 'show_photo',
        'show_duration', 'show_institute_block', 'show_marks', 'show_semester_summary', 'show_legend',
        'show_declared_date', 'show_repeat_note', 'show_final_result',
        'show_attempt_row', 'show_result_row', 'show_equivalent', 'show_division',
        'show_equivalent_note', 'show_certificate', 'certificate_pass_only',
        'show_footer', 'active',
    ];

    protected $table = 'mark_result_template';
    protected $primaryKey = 'templateid';

    protected $fillable = [
        'name', 'layout', 'is_default', 'title', 'programme_label', 'exam_label', 'date_format', 'show_duration',
        'show_header', 'show_logo', 'logo', 'university_name', 'header_lines_top', 'show_department',
        'header_lines_bottom', 'show_header_line', 'show_photo',
        'show_institute_block', 'institute_label', 'address_label', 'institute_address',
        'show_marks', 'show_semester_summary', 'show_legend', 'show_declared_date',
        'show_repeat_note', 'show_final_result',
        'info_rows', 'default_subject', 'session_format', 'attempt_label', 'result_row_label',
        'equivalent_label', 'division_label', 'certificate_text',
        'show_attempt_row', 'show_result_row', 'show_equivalent', 'show_division',
        'show_equivalent_note', 'show_certificate', 'certificate_pass_only',
        'show_footer', 'footer_items',
        'header_align', 'title_align', 'institute_align', 'programme_align', 'logo_position', 'photo_position',
        'margin_mode', 'margin_top', 'margin_right', 'margin_bottom', 'margin_left',
        'spacing_mode', 'space_header_top', 'space_header_side', 'space_header_lines',
        'space_after_header', 'space_sections', 'space_footer',
        'active', 'is_delete',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'show_header' => 'boolean',
            'show_logo' => 'boolean',
            'show_department' => 'boolean',
            'show_header_line' => 'boolean',
            'show_photo' => 'boolean',
            'show_duration' => 'boolean',
            'show_institute_block' => 'boolean',
            'show_marks' => 'boolean',
            'show_semester_summary' => 'boolean',
            'show_legend' => 'boolean',
            'show_declared_date' => 'boolean',
            'show_repeat_note' => 'boolean',
            'show_final_result' => 'boolean',
            'show_attempt_row' => 'boolean',
            'show_result_row' => 'boolean',
            'show_equivalent' => 'boolean',
            'show_division' => 'boolean',
            'show_equivalent_note' => 'boolean',
            'show_certificate' => 'boolean',
            'certificate_pass_only' => 'boolean',
            'show_footer' => 'boolean',
            'footer_items' => 'array',
            'info_rows' => 'array',
            'space_header_top' => 'decimal:1',
            'space_header_side' => 'decimal:1',
            'space_header_lines' => 'decimal:1',
            'space_after_header' => 'decimal:1',
            'space_sections' => 'decimal:1',
            'space_footer' => 'decimal:1',
            'active' => 'boolean',
            'is_delete' => 'boolean',
        ];
    }

    // Hide soft-deleted rows from every query automatically
    protected static function booted(): void
    {
        static::addGlobalScope('notDeleted', function (Builder $query) {
            $query->where('mark_result_template.is_delete', false);
        });
    }

    public function getRouteKeyName(): string
    {
        return 'templateid';
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(ResultTemplateAssignment::class, 'templateid', 'templateid');
    }

    public function usages(): HasMany
    {
        return $this->hasMany(ResultTemplateUsage::class, 'templateid', 'templateid');
    }

    // Used when no template has been saved yet, and as the starting point of "Add template"
    public static function defaults(): static
    {
        return new static([
            'name' => '',
            'layout' => 'semester',
            'title' => 'GRADE-SHEET',
            'programme_label' => 'Name of the Programme:',
            'exam_label' => 'Examination',
            'date_format' => 'j F, Y',
            'show_duration' => true,
            'show_header' => true,
            'show_logo' => true,
            'university_name' => (string) config('result.university'),
            'header_lines_top' => '',
            'show_department' => true,
            'header_lines_bottom' => '',
            'show_header_line' => true,
            'show_photo' => false,
            'show_institute_block' => false,
            'institute_label' => 'Name of the Institute / School of Studies :',
            'address_label' => 'Address of Institute / School of Studies :',
            'show_marks' => false,
            'show_semester_summary' => true,
            'show_legend' => true,
            'show_declared_date' => true,
            'show_repeat_note' => true,
            'show_final_result' => true,
            'info_rows' => self::DEFAULT_INFO_ROWS,
            'default_subject' => '',
            'session_format' => 'start',
            'attempt_label' => 'Attempt',
            'result_row_label' => 'Result',
            'equivalent_label' => 'Equivalent Percentage:-',
            'division_label' => 'Division:',
            'certificate_text' => self::DEFAULT_CERTIFICATE,
            'show_attempt_row' => true,
            'show_result_row' => true,
            'show_equivalent' => true,
            'show_division' => true,
            'show_equivalent_note' => true,
            'show_certificate' => true,
            'certificate_pass_only' => true,
            'show_footer' => true,
            'footer_items' => ['Prepared by', 'Co-ordinator', 'Head'],
            'header_align' => 'center',
            'title_align' => 'center',
            'institute_align' => 'left',
            'programme_align' => 'center',
            'logo_position' => 'left',
            'photo_position' => 'right',
            'margin_mode' => 'auto',
            'spacing_mode' => 'auto',
            'active' => true,
        ]);
    }

    // "NAAC Accredited...\nUGC-SAP" => ['NAAC Accredited...', 'UGC-SAP']
    public static function lines(?string $text): array
    {
        return array_values(array_filter(
            array_map('trim', preg_split('/\R/', (string) $text)),
            fn ($line) => $line !== ''
        ));
    }

    // the rows of the Ph.D. information table: only known fields, every row with a label
    public function infoRows(): array
    {
        $rows = collect($this->info_rows ?: self::DEFAULT_INFO_ROWS)
            ->filter(fn ($row) => isset(self::INFO_FIELDS[$row['field'] ?? '']))
            ->map(fn ($row) => [
                'field' => $row['field'],
                'label' => trim((string) ($row['label'] ?? '')) ?: self::INFO_FIELDS[$row['field']],
            ])
            ->values()
            ->all();

        return $rows ?: self::DEFAULT_INFO_ROWS;
    }

    public function marginsMm(): array
    {
        if ($this->margin_mode !== 'custom') {
            return self::AUTO_MARGINS;
        }

        return [
            (int) ($this->margin_top ?? self::AUTO_MARGINS[0]),
            (int) ($this->margin_right ?? self::AUTO_MARGINS[1]),
            (int) ($this->margin_bottom ?? self::AUTO_MARGINS[2]),
            (int) ($this->margin_left ?? self::AUTO_MARGINS[3]),
        ];
    }

    // the six spacing values in mm; an empty custom box keeps its automatic value
    public function spacingMm(): array
    {
        $values = self::AUTO_SPACING;

        if ($this->spacing_mode === 'custom') {
            foreach (self::SPACING_FIELDS as $name) {
                $value = $this->{'space_' . $name};

                if ($value !== null) {
                    $values[$name] = (float) $value;
                }
            }
        }

        return $values;
    }

    protected function logoUrl(): Attribute
    {
        return Attribute::get(fn () => $this->logo ? asset('storage/' . $this->logo) : null);
    }

    protected function marginText(): Attribute
    {
        return Attribute::get(fn () => $this->margin_mode === 'custom'
            ? implode(' / ', $this->marginsMm()) . ' mm'
            : 'Auto');
    }

    // "MBA (all sessions), BBA (2022–2025)"
    protected function assignmentText(): Attribute
    {
        return Attribute::get(function () {
            $rows = $this->assignments;

            if ($rows->isEmpty()) {
                return $this->is_default ? 'All other courses' : 'Not assigned';
            }

            $text = $rows
                ->map(fn ($a) => ($a->course?->shortname ?? '—') . ' (' . ($a->batch?->label ?? 'all sessions') . ')')
                ->implode(', ');

            return $this->is_default ? $text . ' + all other courses' : $text;
        });
    }
}