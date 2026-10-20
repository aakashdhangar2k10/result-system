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
        // one row per exam: when the SGPA was calculated and the rule that was applied
        Schema::create('mark_semester_result', function (Blueprint $table) {
            $table->id('semresultid');
            $table->foreignId('semesterexamid')->unique()->constrained('mark_semester_exam', 'semesterexamid')->restrictOnDelete();
            $table->foreignId('courseid')->constrained('mark_course', 'courseid')->restrictOnDelete();
            $table->foreignId('batchid')->constrained('mark_batch', 'batchid')->restrictOnDelete();
            $table->foreignId('semid')->constrained('mark_semester_list', 'semid')->restrictOnDelete();
            $table->unsignedSmallInteger('student_count')->default(0);
            $table->unsignedSmallInteger('pass_count')->default(0);
            $table->unsignedSmallInteger('promoted_count')->default(0);
            $table->unsignedSmallInteger('fail_count')->default(0);

            // the rule as it was when the SGPA was calculated
            $table->unsignedTinyInteger('max_backlogs')->default(0);
            $table->decimal('min_sgpa', 4, 2)->nullable();
            $table->boolean('sgpa_with_failed')->default(true);
            $table->string('pass_label', 30);
            $table->string('promoted_label', 30);
            $table->string('fail_label', 30);

            $table->boolean('is_stale')->default(false);   // marks changed after the calculation
            $table->foreignId('calculated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('calculated_at')->nullable();
            $table->timestamps();
        });

        // one row per student
        Schema::create('mark_semester_result_item', function (Blueprint $table) {
            $table->id('semresultitemid');
            $table->foreignId('semresultid')->constrained('mark_semester_result', 'semresultid')->cascadeOnDelete();
            $table->foreignId('studentid')->constrained('mark_student', 'studentid')->restrictOnDelete();
            $table->unsignedSmallInteger('subjects_count')->default(0);
            $table->decimal('credits', 6, 1)->default(0);          // the SGPA divisor
            $table->decimal('earned_credits', 6, 1)->default(0);
            $table->decimal('credit_points', 7, 2)->default(0);    // sum of credit x grade point
            $table->decimal('sgpa', 4, 2)->default(0);
            $table->unsignedTinyInteger('backlogs')->default(0);
            $table->string('backlog_subjects')->nullable();
            $table->string('status', 10);                          // pass, promoted, fail
            $table->string('result_label', 30);
            $table->timestamps();

            $table->unique(['semresultid', 'studentid']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mark_semester_result_item');
        Schema::dropIfExists('mark_semester_result');
    }
};
