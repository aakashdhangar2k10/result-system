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
    Schema::create('mark_student_list', function (Blueprint $table) {
        $table->id('studentlistid');
        // the Semester record gives the course, session, semester and semester month
        $table->foreignId('semesterid')->constrained('mark_semester', 'semesterid')->restrictOnDelete();
        $table->boolean('is_locked')->default(false);   // set when the result of this semester is generated
        $table->boolean('is_delete')->default(false);
        $table->timestamps(); // created_at + updated_at, filled automatically
    });

    Schema::create('mark_student_list_item', function (Blueprint $table) {
        $table->foreignId('studentlistid')->constrained('mark_student_list', 'studentlistid')->cascadeOnDelete();
        $table->foreignId('studentid')->constrained('mark_student', 'studentid')->restrictOnDelete();
        $table->string('entry_type', 10)->default('regular'); // regular, back, repeat
        $table->string('remark')->nullable();
        $table->timestamps();

        $table->primary(['studentlistid', 'studentid']);
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    Schema::dropIfExists('mark_student_list_item');
    Schema::dropIfExists('mark_student_list');
    }
};
