<?php

namespace App\Models\Result;

use App\Models\Student\Student;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SemesterResultItem extends Model
{
    protected $table = 'mark_semester_result_item';
    protected $primaryKey = 'semresultitemid';

    protected $fillable = [
        'semresultid',
        'studentid',
        'subjects_count',
        'credits',
        'earned_credits',
        'credit_points',
        'sgpa',
        'backlogs',
        'backlog_subjects',
        'status',
        'result_label',
    ];

    protected function casts(): array
    {
        return [
            'credits' => 'decimal:1',
            'earned_credits' => 'decimal:1',
            'credit_points' => 'decimal:2',
            'sgpa' => 'decimal:2',
            'backlogs' => 'integer',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'studentid', 'studentid');
    }
}
