<?php

namespace App\Models\Course;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseFinal extends Model
{
    protected $table = 'mark_course_final';
    protected $primaryKey = 'finalid';

    protected $fillable = ['courseid', 'from_term', 'to_term', 'degree_label'];

    protected function casts(): array
    {
        return [
            'from_term' => 'integer',
            'to_term' => 'integer',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'courseid', 'courseid');
    }
}