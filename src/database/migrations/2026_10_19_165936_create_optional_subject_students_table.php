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
    // subject types whose subjects are taken only by the students who choose them
    Schema::table('mark_subject_type', function (Blueprint $table) {
        $table->boolean('is_optional')->default(false)->after('has_grade');
    });

    DB::table('mark_subject_type')->where('subjecttypename', 'Optional')->update(['is_optional' => true]);

    Schema::create('mark_optional_subject_student', function (Blueprint $table) {
        $table->id('optionalid');
        $table->foreignId('batchid')->constrained('mark_batch', 'batchid')->restrictOnDelete();
        $table->foreignId('subjectid')->constrained('mark_subject', 'subjectid')->restrictOnDelete();
        $table->boolean('is_delete')->default(false);
        $table->timestamps(); // created_at + updated_at, filled automatically
    });

    // the students who chose the subject
    Schema::create('mark_optional_subject_student_item', function (Blueprint $table) {
        $table->foreignId('optionalid')->constrained('mark_optional_subject_student', 'optionalid')->cascadeOnDelete();
        $table->foreignId('studentid')->constrained('mark_student', 'studentid')->restrictOnDelete();
        $table->timestamps();

        $table->primary(['optionalid', 'studentid']);
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    Schema::dropIfExists('mark_optional_subject_student_item');
    Schema::dropIfExists('mark_optional_subject_student');

    Schema::table('mark_subject_type', function (Blueprint $table) {
        $table->dropColumn('is_optional');
    });
    }
};
