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
        $table->unsignedBigInteger('subjecttypeid')->nullable()->after('semid');
        $table->boolean('add_grade')->default(false)->after('maxexternalmarks');
    });

    // map the old text values onto the new types
    $types = DB::table('mark_subject_type')->pluck('subjecttypeid', 'subjecttypename');
    $map = [
        'main' => 'Main',
        'optional' => 'Optional',
        'comprehensive_viva' => 'Comprehensive Viva',
        'seminar' => 'Seminar',
    ];

    foreach ($map as $old => $name) {
        DB::table('mark_subject')->where('subjecttype', $old)->update(['subjecttypeid' => $types[$name]]);
    }
    DB::table('mark_subject')->whereNull('subjecttypeid')->update(['subjecttypeid' => $types['Main']]);

    Schema::table('mark_subject', function (Blueprint $table) {
        $table->unsignedBigInteger('subjecttypeid')->nullable(false)->change();
        $table->foreign('subjecttypeid')->references('subjecttypeid')->on('mark_subject_type')->restrictOnDelete();
        $table->dropColumn('subjecttype');
    });
}

public function down(): void
{
    Schema::table('mark_subject', function (Blueprint $table) {
        $table->string('subjecttype', 30)->default('main')->after('semid');
    });

    foreach (DB::table('mark_subject_type')->get() as $type) {
        DB::table('mark_subject')
            ->where('subjecttypeid', $type->subjecttypeid)
            ->update(['subjecttype' => strtolower(str_replace(' ', '_', $type->subjecttypename))]);
    }

    Schema::table('mark_subject', function (Blueprint $table) {
        $table->dropForeign(['subjecttypeid']);
        $table->dropColumn(['subjecttypeid', 'add_grade']);
    });
}
};
