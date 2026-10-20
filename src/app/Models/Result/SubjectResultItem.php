<?php

namespace App\Models\Result;

use App\Models\Student\Student;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubjectResultItem extends Model
{
    protected $table = 'mark_result_item';
    protected $primaryKey = 'resultitemid';

    protected $fillable = [
        'resultid', 'studentid',
        't1', 't2', 't3', 't1_absent', 't2_absent', 't3_absent',
        'internal', 'external', 'external_absent', 'total', 'percentage',
        'grade', 'grade_point', 'is_absent', 'is_pass',
    ];

    protected function casts(): array
    {
        return [
            't1_absent' => 'boolean',
            't2_absent' => 'boolean',
            't3_absent' => 'boolean',
            'external_absent' => 'boolean',
            'grade_point' => 'integer',
            'is_absent' => 'boolean',
            'is_pass' => 'boolean',
        ];
    }

    public function result(): BelongsTo
    {
        return $this->belongsTo(SubjectResult::class, 'resultid', 'resultid');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'studentid', 'studentid');
    }
}