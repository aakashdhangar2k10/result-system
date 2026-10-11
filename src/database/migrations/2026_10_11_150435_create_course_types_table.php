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
    Schema::create('mark_coursetype', function (Blueprint $table) {
        $table->id('coursetypeid');
        $table->string('fullname');
        $table->string('shortname', 50);
        $table->text('description')->nullable();
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
        Schema::dropIfExists('mark_coursetype');
    }
};
