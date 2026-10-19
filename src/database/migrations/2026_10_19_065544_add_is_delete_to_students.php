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
    Schema::table('mark_student', function (Blueprint $table) {
        // uniqueness is now checked by the request, among students that are not deleted
        $table->dropUnique(['rollno']);
        $table->dropUnique(['enrollmentno']);
        $table->dropUnique(['apaarid']);

        $table->boolean('is_delete')->default(false)->after('active');

        // plain indexes keep searching fast
        $table->index('rollno');
        $table->index('enrollmentno');
        $table->index('apaarid');
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
{
    Schema::table('mark_student', function (Blueprint $table) {
        $table->dropIndex(['rollno']);
        $table->dropIndex(['enrollmentno']);
        $table->dropIndex(['apaarid']);

        $table->dropColumn('is_delete');

        // fails if deleted and live students now share a number
        $table->unique('rollno');
        $table->unique('enrollmentno');
        $table->unique('apaarid');
    });
}
};
