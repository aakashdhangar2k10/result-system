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
        Schema::table('mark_session_grade', function (Blueprint $table) {
            $table->unsignedTinyInteger('max_backlogs')->default(0)->after('divtypeid');
            $table->decimal('min_sgpa', 4, 2)->nullable()->after('max_backlogs');
            $table->boolean('sgpa_with_failed')->default(true)->after('min_sgpa');
            $table->string('pass_label', 30)->default('PASS')->after('sgpa_with_failed');
            $table->string('promoted_label', 30)->default('PROMOTED')->after('pass_label');
            $table->string('fail_label', 30)->default('FAIL')->after('promoted_label');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mark_session_grade', function (Blueprint $table) {
            $table->dropColumn(['max_backlogs', 'min_sgpa', 'sgpa_with_failed', 'pass_label', 'promoted_label', 'fail_label']);
        });
    }
};
