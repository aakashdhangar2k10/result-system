<?php

namespace App\Models\Result;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResultTemplateUsage extends Model
{
    protected $table = 'mark_result_template_usage';
    protected $primaryKey = 'usageid';

    protected $fillable = ['templateid', 'semesterexamid', 'sheets', 'user_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}