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
    Schema::create('mark_grade', function (Blueprint $table) {
        $table->id('gradeid');
        $table->foreignId('coursetypeid')->constrained('mark_coursetype', 'coursetypeid')->restrictOnDelete();
        $table->foreignId('gradetypeid')->constrained('mark_course_type', 'coursetypeid')->restrictOnDelete();
        $table->string('firstattemptgrade', 10);
        $table->unsignedTinyInteger('firstattemptcredit');
        $table->string('secondattemptgrade', 10);
        $table->unsignedTinyInteger('secondattemptcredit');
        $table->unsignedSmallInteger('startscore');
        $table->unsignedSmallInteger('endscore');
        $table->string('description')->nullable();
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
        Schema::dropIfExists('mark_grade');
    }
};
