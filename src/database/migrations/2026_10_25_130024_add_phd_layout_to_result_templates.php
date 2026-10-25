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
    Schema::table('mark_result_template', function (Blueprint $table) {
        $table->string('layout', 10)->default('semester');
        $table->text('info_rows')->nullable();
        $table->string('default_subject', 100)->nullable();
        $table->string('session_format', 10)->default('start');
        $table->string('attempt_label', 30)->default('Attempt');
        $table->string('result_row_label', 30)->default('Result');
        $table->string('equivalent_label', 40)->nullable()->default('Equivalent Percentage:-');
        $table->string('division_label', 40)->nullable()->default('Division:');
        $table->text('certificate_text')->nullable();
        $table->boolean('show_attempt_row')->default(true);
        $table->boolean('show_result_row')->default(true);
        $table->boolean('show_equivalent')->default(true);
        $table->boolean('show_division')->default(true);
        $table->boolean('show_equivalent_note')->default(true);
        $table->boolean('show_certificate')->default(true);
        $table->boolean('certificate_pass_only')->default(true);
    });

    // an optional subject / discipline on the student, printed on the Ph.D. sheet
    Schema::table('mark_student', function (Blueprint $table) {
        $table->string('subject', 150)->nullable()->after('mothername');
    });

    // a ready-to-use template in the style of the Ph.D. sample
    DB::table('mark_result_template')->insert([
        'name' => 'Ph.D. course work sheet',
        'layout' => 'phd',
        'is_default' => false,
        'title' => null,
        'programme_label' => null,
        'date_format' => 'd/m/Y',
        'show_header' => false,
        'show_logo' => false,
        'show_repeat_note' => true,
        'footer_items' => json_encode(['Co-ordinator', 'Head', 'Controller of Examinations']),
        'info_rows' => json_encode([
            ['field' => 'institute', 'label' => 'Name & Address of the Institue/ School of Studies College Where Ph.D. Course Work is Conducted'],
            ['field' => 'subject', 'label' => 'Subject'],
            ['field' => 'name', 'label' => 'Name of the Student:'],
            ['field' => 'father', 'label' => "Father's/ Husband's Name"],
            ['field' => 'mother', 'label' => "Mother's Name"],
            ['field' => 'enrollment', 'label' => 'Enrollment No.'],
            ['field' => 'roll', 'label' => 'Roll No.'],
            ['field' => 'session', 'label' => 'Academic Session'],
            ['field' => 'term', 'label' => 'Semester'],
            ['field' => 'exam', 'label' => 'Examination Month & Year'],
        ]),
        'certificate_text' => 'This is certified that Mr./Ms./Mrs. {name} has successfully completed the Ph.D. Course Work as per UGC Regulations 2016/2022 and the Ph.D. Ordinance of the University.',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

public function down(): void
{
    Schema::table('mark_student', function (Blueprint $table) {
        $table->dropColumn('subject');
    });

    Schema::table('mark_result_template', function (Blueprint $table) {
        $table->dropColumn([
            'layout', 'info_rows', 'default_subject', 'session_format', 'attempt_label', 'result_row_label',
            'equivalent_label', 'division_label', 'certificate_text',
            'show_attempt_row', 'show_result_row', 'show_equivalent', 'show_division',
            'show_equivalent_note', 'show_certificate', 'certificate_pass_only',
        ]);
    });
}
};
