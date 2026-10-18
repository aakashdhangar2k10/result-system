<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
    Schema::table('mark_subject', function (Blueprint $table) {
        $table->unsignedSmallInteger('subjectorder')->default(0)->after('subjectcode');
    });

    // number the existing subjects 1..n inside each course + semester
    $counters = [];

    $rows = DB::table('mark_subject')
        ->where('is_delete', false)
        ->orderBy('courseid')->orderBy('semid')->orderBy('subjectid')
        ->get(['subjectid', 'courseid', 'semid']);

    foreach ($rows as $row) {
        $key = $row->courseid . '-' . $row->semid;
        $counters[$key] = ($counters[$key] ?? 0) + 1;

        DB::table('mark_subject')
            ->where('subjectid', $row->subjectid)
            ->update(['subjectorder' => $counters[$key]]);
    }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    Schema::table('mark_subject', function (Blueprint $table) {
        $table->dropColumn('subjectorder');
    });
    }
};
