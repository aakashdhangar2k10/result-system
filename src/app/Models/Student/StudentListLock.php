<?php

namespace App\Models\Student;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentListLock extends Model
{
    protected $table = 'mark_student_list_lock';
    protected $primaryKey = 'lockid';

    protected $fillable = ['studentlistid', 'action', 'note', 'user_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}