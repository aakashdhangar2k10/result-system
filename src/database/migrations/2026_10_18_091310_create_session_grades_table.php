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
    Schema::create('mark_session_grade', function (Blueprint $table) {
        $table->id('sessiongradeid');
        $table->foreignId('courseid')->constrained('mark_course', 'courseid')->restrictOnDelete();
        $table->foreignId('batchid')->constrained('mark_batch', 'batchid')->restrictOnDelete();
        $table->foreignId('gradetypeid')->constrained('mark_course_type', 'coursetypeid')->restrictOnDelete();
        $table->foreignId('divtypeid')->constrained('mark_division_type', 'divtypeid')->restrictOnDelete();
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
        Schema::dropIfExists('mark_session_grade');
    }
};
