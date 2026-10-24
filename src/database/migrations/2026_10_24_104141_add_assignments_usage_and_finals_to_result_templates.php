<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // which courses / sessions a template is used for (many per template)
        Schema::create('mark_result_template_assignment', function (Blueprint $table) {
            $table->id('assignmentid');
            $table->foreignId('templateid')->constrained('mark_result_template', 'templateid')->cascadeOnDelete();
            $table->foreignId('courseid')->constrained('mark_course', 'courseid')->restrictOnDelete();
            $table->foreignId('batchid')->nullable()->constrained('mark_batch', 'batchid')->restrictOnDelete(); // empty = every session
            $table->timestamps();
            $table->index(['courseid', 'batchid']);
        });

        // every printout made with a template: a used template is locked
        Schema::create('mark_result_template_usage', function (Blueprint $table) {
            $table->id('usageid');
            $table->foreignId('templateid')->constrained('mark_result_template', 'templateid')->restrictOnDelete();
            $table->foreignId('semesterexamid')->nullable()->constrained('mark_semester_exam', 'semesterexamid')->nullOnDelete();
            $table->unsignedSmallInteger('sheets')->default(0);
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // the final result points of a course (dual degree, exit after a semester)
        Schema::create('mark_course_final', function (Blueprint $table) {
            $table->id('finalid');
            $table->foreignId('courseid')->constrained('mark_course', 'courseid')->cascadeOnDelete();
            $table->unsignedTinyInteger('from_term')->default(1);
            $table->unsignedTinyInteger('to_term');
            $table->string('degree_label', 100)->nullable();
            $table->timestamps();
            $table->unique(['courseid', 'to_term']);
        });

        Schema::table('mark_result_template', function (Blueprint $table) {
            $table->string('title', 60)->nullable()->change();
            $table->string('programme_label', 80)->nullable()->change();
            $table->string('exam_label', 30)->default('Examination');
            $table->string('date_format', 12)->default('j F, Y');
            $table->boolean('show_repeat_note')->default(true);
            $table->boolean('show_final_result')->default(true);
        });

        // templates that were tied to one course become assignments, then the old column goes
        $rows = DB::table('mark_result_template')->whereNotNull('courseid')->where('is_delete', false)->get(['templateid', 'courseid']);

        foreach ($rows as $row) {
            DB::table('mark_result_template_assignment')->insert([
                'templateid' => $row->templateid,
                'courseid' => $row->courseid,
                'batchid' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        Schema::table('mark_result_template', function (Blueprint $table) {
            $table->dropConstrainedForeignId('courseid');
        });
    }

    public function down(): void
    {
        Schema::table('mark_result_template', function (Blueprint $table) {
            $table->foreignId('courseid')->nullable()->constrained('mark_course', 'courseid')->nullOnDelete();
            $table->dropColumn(['exam_label', 'date_format', 'show_repeat_note', 'show_final_result']);
        });

        Schema::dropIfExists('mark_course_final');
        Schema::dropIfExists('mark_result_template_usage');
        Schema::dropIfExists('mark_result_template_assignment');
    }
};
