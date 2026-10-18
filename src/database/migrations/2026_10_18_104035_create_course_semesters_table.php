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
    Schema::create('mark_semester', function (Blueprint $table) {
        $table->id('semesterid');
        $table->foreignId('courseid')->constrained('mark_course', 'courseid')->restrictOnDelete();
        $table->foreignId('batchid')->nullable()->constrained('mark_batch', 'batchid')->restrictOnDelete();
        $table->foreignId('semid')->constrained('mark_semester_list', 'semid')->restrictOnDelete();
        $table->date('semestermonth');
        $table->unsignedSmallInteger('validcredit');
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
        Schema::dropIfExists('mark_semester');
    }
};
