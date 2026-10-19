<?php

namespace App\Models\Student;

use App\Models\Batch\Batch;
use App\Models\Course\Course;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudentImport extends Model
{
    public const STATUSES = [
        'preview' => 'Preview',
        'imported' => 'Imported',
        'cleared' => 'Cleared',
        'discarded' => 'Discarded',
    ];

    protected $table = 'mark_student_import';
    protected $primaryKey = 'importid';

    protected $fillable = [
        'courseid', 'batchid', 'file_name', 'sheet_title', 'sheet_course',
        'total_rows', 'valid_rows', 'error_rows', 'warning_rows',
        'imported_rows', 'skipped_rows', 'cleared_rows',
        'status', 'rows', 'uploaded_by', 'cleared_by', 'imported_at', 'cleared_at',
    ];

    protected function casts(): array
    {
        return [
            'rows' => 'array',
            'imported_at' => 'datetime',
            'cleared_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'importid';
    }

    // The log list does not need the (large) rows column
    public function scopeListing(Builder $query): Builder
    {
        return $query->select([
            'importid', 'courseid', 'batchid', 'file_name', 'sheet_title', 'sheet_course',
            'total_rows', 'valid_rows', 'error_rows', 'warning_rows',
            'imported_rows', 'skipped_rows', 'cleared_rows', 'status',
            'uploaded_by', 'cleared_by', 'imported_at', 'cleared_at', 'created_at', 'updated_at',
        ]);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'courseid', 'courseid');
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class, 'batchid', 'batchid');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function clearer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cleared_by');
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'importid', 'importid');
    }

    protected function statusLabel(): Attribute
    {
        return Attribute::get(fn () => self::STATUSES[$this->status] ?? $this->status);
    }

    protected function statusColor(): Attribute
    {
        return Attribute::get(fn () => [
            'preview' => 'warning',
            'imported' => 'success',
            'cleared' => 'secondary',
            'discarded' => 'dark',
        ][$this->status] ?? 'secondary');
    }

    // Does the course line in the sheet (row 2) match the course chosen on the upload form?
    protected function courseMatches(): Attribute
    {
        return Attribute::get(function () {
            if (! $this->sheet_course || ! $this->course) {
                return true;
            }

            $norm = fn ($value) => preg_replace('/[^a-z0-9]/', '', mb_strtolower((string) $value));
            $sheet = $norm($this->sheet_course);

            foreach ([$this->course->fullname, $this->course->shortname] as $name) {
                $name = $norm($name);

                if ($name !== '' && ($sheet === $name || str_contains($sheet, $name) || str_contains($name, $sheet))) {
                    return true;
                }
            }

            return false;
        });
    }
}