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
    Schema::create('mark_session_subject', function (Blueprint $table) {
        $table->id('sessionsubjectid');
        $table->foreignId('courseid')->constrained('mark_course', 'courseid')->restrictOnDelete();
        $table->foreignId('batchid')->constrained('mark_batch', 'batchid')->restrictOnDelete();
        $table->foreignId('semid')->constrained('mark_semester_list', 'semid')->restrictOnDelete();
        $table->boolean('is_delete')->default(false);
        $table->timestamps(); // created_at + updated_at, filled automatically
    });

    // the subjects chosen for each course + session + semester
    Schema::create('mark_session_subject_item', function (Blueprint $table) {
        $table->foreignId('sessionsubjectid')->constrained('mark_session_subject', 'sessionsubjectid')->cascadeOnDelete();
        $table->foreignId('subjectid')->constrained('mark_subject', 'subjectid')->restrictOnDelete();
        $table->primary(['sessionsubjectid', 'subjectid']);
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    Schema::dropIfExists('mark_session_subject_item');
    Schema::dropIfExists('mark_session_subject');
    }
};
