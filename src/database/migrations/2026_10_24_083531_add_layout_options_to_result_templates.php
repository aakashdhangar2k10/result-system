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
            $table->boolean('show_duration')->default(true);
            $table->string('institute_label', 80)->default('Name of the Institute / School of Studies :');
            $table->string('address_label', 80)->default('Address of Institute / School of Studies :');

            // left / right
            $table->string('header_align', 10)->default('center');
            $table->string('title_align', 10)->default('center');
            $table->string('institute_align', 10)->default('left');
            $table->string('programme_align', 10)->default('center');
            $table->string('logo_position', 10)->default('left');
            $table->string('photo_position', 10)->default('right');

            // spacing in millimetres (empty = automatic)
            $table->string('spacing_mode', 10)->default('auto');
            $table->decimal('space_header_top', 4, 1)->nullable();
            $table->decimal('space_header_side', 4, 1)->nullable();
            $table->decimal('space_header_lines', 4, 1)->nullable();
            $table->decimal('space_after_header', 4, 1)->nullable();
            $table->decimal('space_sections', 4, 1)->nullable();
            $table->decimal('space_footer', 4, 1)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('mark_result_template', function (Blueprint $table) {
            $table->dropColumn([
                'show_duration',
                'institute_label',
                'address_label',
                'header_align',
                'title_align',
                'institute_align',
                'programme_align',
                'logo_position',
                'photo_position',
                'spacing_mode',
                'space_header_top',
                'space_header_side',
                'space_header_lines',
                'space_after_header',
                'space_sections',
                'space_footer',
            ]);
        });
    }
};
