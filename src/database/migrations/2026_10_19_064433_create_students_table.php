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
    Schema::create('mark_student', function (Blueprint $table) {
        $table->id('studentid');
        $table->foreignId('courseid')->constrained('mark_course', 'courseid')->restrictOnDelete();
        $table->foreignId('batchid')->constrained('mark_batch', 'batchid')->restrictOnDelete();
        $table->string('rollno', 50)->unique();
        $table->string('enrollmentno', 50)->unique();
        $table->string('apaarid', 30)->nullable()->unique();
        $table->string('firstname', 100);
        $table->string('lastname', 100)->nullable();
        $table->string('father_label', 20)->nullable();
        $table->string('father_title', 20)->nullable();
        $table->string('fathername', 150);
        $table->string('mother_title', 20)->nullable();
        $table->string('mothername', 150);
        $table->text('address')->nullable();
        $table->string('mobile', 15)->nullable();
        $table->string('image')->nullable();
        $table->boolean('active')->default(true);
        $table->timestamps(); // created_at + updated_at, filled automatically
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mark_student');
    }
};
