<?php

namespace App\Models\Student;

use App\Models\Batch\Batch;
use App\Models\Course\Course;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Student extends Model
{
    // dropdown options: change them here and every form follows
    public const FATHER_LABELS = ['Father', 'Husband', 'Guardian'];
    public const FATHER_TITLES = ['Mr.', 'Late Mr.', 'Dr.', 'Shri'];
    public const MOTHER_TITLES = ['Mrs.', 'Late Mrs.', 'Dr.', 'Smt.'];

    protected $table = 'mark_student';
    protected $primaryKey = 'studentid';

    protected $fillable = [
    'courseid', 'batchid', 'importid', 'rollno', 'enrollmentno', 'apaarid',
    'firstname', 'lastname',
    'father_label', 'father_title', 'fathername',
    'mother_title', 'mothername',
    'address', 'mobile', 'image', 'active', 'is_delete',
    ];

    protected function casts(): array
    {
       return [
        'active' => 'boolean',
        'is_delete' => 'boolean',
        ];
        
    }
    

    public function getRouteKeyName(): string
    {
        return 'studentid';
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'courseid', 'courseid');
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class, 'batchid', 'batchid');
    }

    protected function fullName(): Attribute
    {
        return Attribute::get(fn () => trim($this->firstname . ' ' . $this->lastname));
    }

    // "Mr. JITENDRA SINGH RAJPUT"
    protected function fatherDisplay(): Attribute
    {
        return Attribute::get(fn () => trim($this->father_title . ' ' . $this->fathername));
    }

    protected function motherDisplay(): Attribute
    {
        return Attribute::get(fn () => trim($this->mother_title . ' ' . $this->mothername));
    }

    protected function imageUrl(): Attribute
    {
        return Attribute::get(fn () => $this->image ? asset('storage/' . $this->image) : null);
    }

    protected static function booted(): void
    {
    static::addGlobalScope('notDeleted', function (Builder $query) {
        $query->where('mark_student.is_delete', false);
    });
    }
}