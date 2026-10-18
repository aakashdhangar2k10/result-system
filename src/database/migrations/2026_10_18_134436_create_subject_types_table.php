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
    Schema::create('mark_subject_type', function (Blueprint $table) {
        $table->id('subjecttypeid');
        $table->string('subjecttypename');
        $table->boolean('has_internal')->default(true);   // ask Max Internal Marks
        $table->boolean('has_external')->default(true);   // ask Max External Marks
        $table->boolean('has_grade')->default(false);     // ask "Add Grade for this subject"
        $table->boolean('active')->default(true);
        $table->boolean('is_delete')->default(false);
        $table->timestamps();
    });

    // the four types you already had
    $now = now();
    DB::table('mark_subject_type')->insert([
        ['subjecttypename' => 'Main', 'has_internal' => 1, 'has_external' => 1, 'has_grade' => 0, 'created_at' => $now, 'updated_at' => $now],
        ['subjecttypename' => 'Optional', 'has_internal' => 1, 'has_external' => 1, 'has_grade' => 0, 'created_at' => $now, 'updated_at' => $now],
        ['subjecttypename' => 'Comprehensive Viva', 'has_internal' => 0, 'has_external' => 1, 'has_grade' => 0, 'created_at' => $now, 'updated_at' => $now],
        ['subjecttypename' => 'Seminar', 'has_internal' => 0, 'has_external' => 0, 'has_grade' => 1, 'created_at' => $now, 'updated_at' => $now],
    ]);
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mark_subject_type');
    }
};
