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
    Schema::create('mark_semester_list', function (Blueprint $table) {
        $table->id('semid');
        $table->string('name');
        $table->string('roman', 10);
        $table->date('joindate')->nullable();
        $table->timestamp('updatedate')->nullable();
        $table->boolean('active')->default(true);
        $table->boolean('is_delete')->default(false);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mark_semester_list');
    }
};
