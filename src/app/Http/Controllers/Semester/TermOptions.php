<?php

namespace App\Http\Controllers\Semester;

use App\Models\Batch\Batch;
use App\Models\Configuration\SemesterList;
use App\Models\Course\Course;
use Illuminate\Database\Eloquent\Model;

trait TermOptions
{
    // Active courses, sessions and terms. When editing, the record's current values
    // stay selectable even if they were deactivated later.
    protected function termOptions(?Model $current = null): array
    {
        $active = fn ($query, string $key, $id) => $query->where(function ($q) use ($key, $id) {
            $q->where('active', true);

            if ($id) {
                $q->orWhere($key, $id);
            }
        });

        return [
            'courses' => $active(Course::query(), 'courseid', $current?->courseid)
                ->orderBy('shortname')->get(),
            'batches' => $active(Batch::query()->with(['course', 'branch']), 'batchid', $current?->batchid)
                ->orderByDesc('startyear')->get(),
            'terms' => $active(SemesterList::query(), 'semid', $current?->semid)
                ->orderBy('semid')->get(),
        ];
    }
}