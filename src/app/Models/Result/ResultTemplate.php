<?php

namespace App\Models\Result;

use App\Models\Course\Course;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResultTemplate extends Model
{
    // quick-add buttons in the footer editor
    public const SIGNATURES = ['Prepared by', 'Co-ordinator', 'Head', 'Head/Director', 'Dean', 'Controller of Examinations'];
    public const MAX_SIGNATURES = 6;

    // margins in millimetres: top, right, bottom, left
    public const AUTO_MARGINS = [14, 12, 14, 12];

    public const FLAGS = [
        'is_default', 'show_header', 'show_logo', 'show_department', 'show_header_line', 'show_photo',
        'show_institute_block', 'show_marks', 'show_semester_summary', 'show_legend',
        'show_declared_date', 'show_footer', 'active',
    ];

    protected $table = 'mark_result_template';
    protected $primaryKey = 'templateid';

    protected $fillable = [
        'name', 'courseid', 'is_default', 'title', 'programme_label',
        'show_header', 'show_logo', 'logo', 'university_name', 'header_lines_top', 'show_department',
        'header_lines_bottom', 'show_header_line', 'show_photo',
        'show_institute_block', 'institute_address', 'show_marks', 'show_semester_summary',
        'show_legend', 'show_declared_date', 'show_footer', 'footer_items',
        'margin_mode', 'margin_top', 'margin_right', 'margin_bottom', 'margin_left',
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
            'show_institute_block' => 'boolean',
            'show_marks' => 'boolean',
            'show_semester_summary' => 'boolean',
            'show_legend' => 'boolean',
            'show_declared_date' => 'boolean',
            'show_footer' => 'boolean',
            'footer_items' => 'array',
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

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'courseid', 'courseid');
    }

    // Used when no template has been saved yet, and as the starting point of "Add template"
    public static function defaults(): static
    {
        return new static([
            'name' => '',
            'title' => 'GRADE-SHEET',
            'programme_label' => 'Name of the Programme:',
            'show_header' => true,
            'show_logo' => true,
            'university_name' => (string) config('result.university'),
            'header_lines_top' => '',
            'show_department' => true,
            'header_lines_bottom' => '',
            'show_header_line' => true,
            'show_photo' => false,
            'show_institute_block' => false,
            'show_marks' => false,
            'show_semester_summary' => true,
            'show_legend' => true,
            'show_declared_date' => true,
            'show_footer' => true,
            'footer_items' => ['Prepared by', 'Co-ordinator', 'Head'],
            'margin_mode' => 'auto',
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
}