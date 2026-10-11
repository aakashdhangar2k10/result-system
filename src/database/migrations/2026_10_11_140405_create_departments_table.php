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
    Schema::create('mark_department', function (Blueprint $table) {
        $table->id('deptid');
        $table->string('fullname');
        $table->string('shortname', 50);
        $table->string('logo')->nullable();
        $table->text('address')->nullable();
        $table->string('affilationshortname', 50)->nullable();
        $table->string('affilationname')->nullable();
        $table->unsignedSmallInteger('affilationyear')->nullable();
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
        Schema::dropIfExists('mark_department');
    }
};
