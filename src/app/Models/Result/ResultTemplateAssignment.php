<?php

namespace App\Models\Result;

use App\Models\Batch\Batch;
use App\Models\Course\Course;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResultTemplateAssignment extends Model
{
    protected $table = 'mark_result_template_assignment';
    protected $primaryKey = 'assignmentid';

    protected $fillable = ['templateid', 'courseid', 'batchid'];

    public function template(): BelongsTo
    {
        return $this->belongsTo(ResultTemplate::class, 'templateid', 'templateid');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'courseid', 'courseid');
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class, 'batchid', 'batchid');
    }
}