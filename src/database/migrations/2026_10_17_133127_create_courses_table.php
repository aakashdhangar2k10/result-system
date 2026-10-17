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
    Schema::create('mark_course', function (Blueprint $table) {
        $table->id('courseid');
        $table->foreignId('deptid')->constrained('mark_department', 'deptid')->restrictOnDelete();
        $table->foreignId('coursetypeid')->constrained('mark_course_type', 'coursetypeid')->restrictOnDelete();
        $table->string('fullname');                       // B.Tech, BCA, MBA...
        $table->string('shortname', 50);
        $table->enum('structure_type', ['semester', 'year'])->default('semester');
        $table->unsignedTinyInteger('total_terms');       // number of semesters or years
        $table->boolean('has_branch')->default(false);    // does this course have branches?
        $table->text('description')->nullable();
        $table->boolean('active')->default(true);
        $table->boolean('is_delete')->default(false);
        $table->timestamps(); // created_at + updated_at, filled automatically
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mark_course');
    }
};
