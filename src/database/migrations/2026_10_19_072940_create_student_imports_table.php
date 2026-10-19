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
    Schema::create('mark_student_import', function (Blueprint $table) {
        $table->id('importid');
        $table->foreignId('courseid')->constrained('mark_course', 'courseid')->restrictOnDelete();
        $table->foreignId('batchid')->constrained('mark_batch', 'batchid')->restrictOnDelete();
        $table->string('file_name');
        $table->string('sheet_title')->nullable();    // heading in row 1 of the sheet
        $table->string('sheet_course')->nullable();   // course line in row 2 of the sheet
        $table->unsignedInteger('total_rows')->default(0);
        $table->unsignedInteger('valid_rows')->default(0);
        $table->unsignedInteger('error_rows')->default(0);
        $table->unsignedInteger('warning_rows')->default(0);
        $table->unsignedInteger('imported_rows')->default(0);
        $table->unsignedInteger('skipped_rows')->default(0);
        $table->unsignedInteger('cleared_rows')->default(0);
        $table->string('status', 20)->default('preview')->index(); // preview, imported, cleared, discarded
        $table->longText('rows')->nullable();                      // the checked sheet, kept as history
        $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
        $table->foreignId('cleared_by')->nullable()->constrained('users')->nullOnDelete();
        $table->timestamp('imported_at')->nullable();
        $table->timestamp('cleared_at')->nullable();
        $table->timestamps();
    });

    // every student remembers which sheet it came from, so one sheet can be cleared
    Schema::table('mark_student', function (Blueprint $table) {
        $table->foreignId('importid')->nullable()->after('batchid')
            ->constrained('mark_student_import', 'importid')->nullOnDelete();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    Schema::table('mark_student', function (Blueprint $table) {
        $table->dropConstrainedForeignId('importid');
    });

    Schema::dropIfExists('mark_student_import');
    }
};
