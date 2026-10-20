<?php

namespace App\Models\Result;

use App\Models\Semester\SemesterExam;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SemesterResult extends Model
{
    protected $table = 'mark_semester_result';
    protected $primaryKey = 'semresultid';

    protected $fillable = [
        'semesterexamid',
        'courseid',
        'batchid',
        'semid',
        'student_count',
        'pass_count',
        'promoted_count',
        'fail_count',
        'max_backlogs',
        'min_sgpa',
        'sgpa_with_failed',
        'pass_label',
        'promoted_label',
        'fail_label',
        'is_stale',
        'calculated_by',
        'calculated_at',
    ];

    protected function casts(): array
    {
        return [
            'max_backlogs' => 'integer',
            'min_sgpa' => 'decimal:2',
            'sgpa_with_failed' => 'boolean',
            'is_stale' => 'boolean',
            'calculated_at' => 'datetime',
        ];
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(SemesterExam::class, 'semesterexamid', 'semesterexamid');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SemesterResultItem::class, 'semresultid', 'semresultid');
    }

    public function calculator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'calculated_by');
    }

    // Was the rule in Session Grade changed after this calculation?
    public function ruleDiffers(object $rule): bool
    {
        $min = fn($value) => $value === null ? null : round((float) $value, 2);

        return (int) $this->max_backlogs !== (int) $rule->max_backlogs
            || $min($this->min_sgpa) !== $min($rule->min_sgpa)
            || (bool) $this->sgpa_with_failed !== (bool) $rule->sgpa_with_failed
            || $this->pass_label !== $rule->pass_label
            || $this->promoted_label !== $rule->promoted_label
            || $this->fail_label !== $rule->fail_label;
    }
}
