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
    Schema::table('mark_student_list', function (Blueprint $table) {
        $table->timestamp('locked_at')->nullable()->after('is_locked');
        $table->foreignId('locked_by')->nullable()->after('locked_at')->constrained('users')->nullOnDelete();
    });

    // one row for every lock and unlock
    Schema::create('mark_student_list_lock', function (Blueprint $table) {
        $table->id('lockid');
        $table->foreignId('studentlistid')->constrained('mark_student_list', 'studentlistid')->cascadeOnDelete();
        $table->string('action', 10); // lock, unlock
        $table->string('note')->nullable();
        $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    Schema::dropIfExists('mark_student_list_lock');

    Schema::table('mark_student_list', function (Blueprint $table) {
        $table->dropConstrainedForeignId('locked_by');
        $table->dropColumn('locked_at');
    });
    }
};
