<?php

namespace App\Models\Configuration;

use App\Models\Subject\Subject;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubjectType extends Model
{
    protected $table = 'mark_subject_type';
    protected $primaryKey = 'subjecttypeid';

    protected $fillable = [
        'subjecttypename', 'has_internal', 'has_external', 'has_grade', 'active', 'is_delete',
    ];

    protected function casts(): array
    {
        return [
            'has_internal' => 'boolean',
            'has_external' => 'boolean',
            'has_grade' => 'boolean',
            'active' => 'boolean',
            'is_delete' => 'boolean',
        ];
    }

    // Hide soft-deleted rows from every query automatically
    protected static function booted(): void
    {
        static::addGlobalScope('notDeleted', function (Builder $query) {
            $query->where('mark_subject_type.is_delete', false);
        });
    }

    public function getRouteKeyName(): string
    {
        return 'subjecttypeid';
    }

    public function subjects(): HasMany
    {
        return $this->hasMany(Subject::class, 'subjecttypeid', 'subjecttypeid');
    }
}