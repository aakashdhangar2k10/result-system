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
    Schema::create('mark_division', function (Blueprint $table) {
        $table->id('divisionid');
        $table->foreignId('coursetypeid')->constrained('mark_coursetype', 'coursetypeid')->restrictOnDelete();
        $table->foreignId('divtypeid')->constrained('mark_division_type', 'divtypeid')->restrictOnDelete();
        $table->string('divisionname');
        $table->decimal('startpoint', 5, 2);
        $table->decimal('endpoint', 5, 2);
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
        Schema::dropIfExists('mark_division');
    }
};
