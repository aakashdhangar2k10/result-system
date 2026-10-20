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
        // one row per subject and exam
        Schema::create('mark_result', function (Blueprint $table) {
            $table->id('resultid');
            $table->foreignId('courseid')->constrained('mark_course', 'courseid')->restrictOnDelete();
            $table->foreignId('batchid')->constrained('mark_batch', 'batchid')->restrictOnDelete();
            $table->foreignId('semid')->constrained('mark_semester_list', 'semid')->restrictOnDelete();
            $table->foreignId('semesterexamid')->constrained('mark_semester_exam', 'semesterexamid')->restrictOnDelete();
            $table->foreignId('subjectid')->constrained('mark_subject', 'subjectid')->restrictOnDelete();
            $table->foreignId('studentlistid')->nullable()->constrained('mark_student_list', 'studentlistid')->nullOnDelete();
            $table->string('entry_mode', 10)->default('marks');   // marks, grade
            $table->unsignedTinyInteger('attempt')->default(1);

            // the subject as it was when the result was entered
            $table->unsignedSmallInteger('max_internal')->default(0);
            $table->unsignedSmallInteger('max_external')->default(0);
            $table->decimal('subject_credit', 4, 1)->default(0);
            $table->foreignId('gradetypeid')->nullable()->constrained('mark_course_type', 'coursetypeid')->nullOnDelete();

            $table->boolean('is_delete')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps(); // created_at + updated_at, filled automatically

            $table->index(['semesterexamid', 'subjectid']);
        });

        // one row per student of that result
        Schema::create('mark_result_item', function (Blueprint $table) {
            $table->id('resultitemid');
            $table->foreignId('resultid')->constrained('mark_result', 'resultid')->cascadeOnDelete();
            $table->foreignId('studentid')->constrained('mark_student', 'studentid')->restrictOnDelete();

            $table->decimal('t1', 5, 2)->nullable();
            $table->decimal('t2', 5, 2)->nullable();
            $table->decimal('t3', 5, 2)->nullable();
            $table->boolean('t1_absent')->default(false);
            $table->boolean('t2_absent')->default(false);
            $table->boolean('t3_absent')->default(false);
            $table->decimal('internal', 5, 2)->nullable();   // best two of T1, T2, T3
            $table->decimal('external', 5, 2)->nullable();
            $table->boolean('external_absent')->default(false);
            $table->decimal('total', 6, 2)->nullable();
            $table->decimal('percentage', 5, 2)->nullable();

            $table->string('grade', 10)->nullable();
            $table->unsignedTinyInteger('grade_point')->default(0);
            $table->boolean('is_absent')->default(false);
            $table->boolean('is_pass')->default(false);
            $table->timestamps();

            $table->unique(['resultid', 'studentid']);
            $table->index(['studentid', 'is_pass']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mark_result_item');
        Schema::dropIfExists('mark_result');
    }
};
