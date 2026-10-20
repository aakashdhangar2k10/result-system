<?php

namespace App\Models\CourseSession;

use App\Models\Batch\Batch;
use App\Models\Configuration\DivisionType;
use App\Models\Configuration\GradeType;
use App\Models\Course\Course;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SessionGrade extends Model
{
    protected $table = 'mark_session_grade';
    protected $primaryKey = 'sessiongradeid';

    protected $fillable = [
        'courseid', 'batchid', 'gradetypeid', 'divtypeid', 'description',
        'max_backlogs', 'min_sgpa', 'sgpa_with_failed', 'pass_label', 'promoted_label', 'fail_label',
        'active', 'is_delete',
    ];

    protected function casts(): array
    {
        return [
            'max_backlogs' => 'integer',
            'min_sgpa' => 'decimal:2',
            'sgpa_with_failed' => 'boolean',
            'active' => 'boolean',
            'is_delete' => 'boolean',
        ];
    }

    // Hide soft-deleted rows from every query automatically
    protected static function booted(): void
    {
        static::addGlobalScope('notDeleted', function (Builder $query) {
            $query->where('mark_session_grade.is_delete', false);
        });
    }

    public function getRouteKeyName(): string
    {
        return 'sessiongradeid';
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'courseid', 'courseid');
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class, 'batchid', 'batchid');
    }

    public function gradeType(): BelongsTo
    {
        return $this->belongsTo(GradeType::class, 'gradetypeid', 'coursetypeid');
    }

    public function divisionType(): BelongsTo
    {
        return $this->belongsTo(DivisionType::class, 'divtypeid', 'divtypeid');
    }

    // "4.00" => "4", "4.50" => "4.5"
    private function minText(): ?string
    {
        return $this->min_sgpa === null ? null : rtrim(rtrim((string) $this->min_sgpa, '0'), '.');
    }

    // One line for the list: "Promoted with up to 2 backlogs · minimum SGPA 4"
    protected function ruleSummary(): Attribute
    {
        return Attribute::get(function () {
            $max = (int) $this->max_backlogs;

            $text = $max === 0
                ? 'Every subject must be passed'
                : 'Promoted with up to ' . $max . ($max === 1 ? ' backlog' : ' backlogs');

            return $text . ($this->minText() !== null ? ' · minimum SGPA ' . $this->minText() : '');
        });
    }

    // The rule as lines for the screens: [['label', 'color', 'text'], ...]
    protected function ruleLines(): Attribute
    {
        return Attribute::get(function () {
            $max = (int) $this->max_backlogs;
            $min = $this->minText();
            $gate = $min !== null ? " and SGPA is at least {$min}" : '';

            $lines = [['label' => $this->pass_label, 'color' => 'success', 'text' => "no backlog{$gate}"]];

            if ($max > 0) {
                $lines[] = [
                    'label' => $this->promoted_label,
                    'color' => 'primary',
                    'text' => ($max === 1 ? '1 backlog' : "1 to {$max} backlogs") . $gate,
                ];
            }

            $over = $max === 0 ? 'any backlog' : "more than {$max} " . ($max === 1 ? 'backlog' : 'backlogs');

            $lines[] = [
                'label' => $this->fail_label,
                'color' => 'danger',
                'text' => $over . ($min !== null ? " or SGPA below {$min}" : ''),
            ];

            return $lines;
        });
    }
}