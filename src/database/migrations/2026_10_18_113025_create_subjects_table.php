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
    Schema::create('mark_subject', function (Blueprint $table) {
        $table->id('subjectid');
        $table->foreignId('courseid')->constrained('mark_course', 'courseid')->restrictOnDelete();
        $table->foreignId('semid')->constrained('mark_semester_list', 'semid')->restrictOnDelete();
        $table->enum('subjecttype', ['main', 'optional', 'comprehensive_viva', 'seminar'])->default('main');
        $table->string('subjectname');
        $table->decimal('subjectcredit', 4, 1);
        $table->string('subjectcode', 50);
        $table->unsignedSmallInteger('maxinternalmarks');
        $table->unsignedSmallInteger('maxexternalmarks');
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
        Schema::dropIfExists('mark_subject');
    }
};
