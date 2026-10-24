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
        Schema::create('mark_result_template', function (Blueprint $table) {
            $table->id('templateid');
            $table->string('name', 100);
            $table->foreignId('courseid')->nullable()->constrained('mark_course', 'courseid')->nullOnDelete();
            $table->boolean('is_default')->default(false);

            $table->string('title', 60)->default('GRADE-SHEET');
            $table->string('programme_label', 80)->default('Name of the Programme:');

            $table->boolean('show_header')->default(true);
            $table->boolean('show_logo')->default(true);
            $table->string('logo')->nullable();
            $table->string('university_name', 150)->nullable();
            $table->text('header_lines_top')->nullable();      // lines above the department
            $table->boolean('show_department')->default(true);
            $table->text('header_lines_bottom')->nullable();   // lines below the department
            $table->boolean('show_header_line')->default(true);
            $table->boolean('show_photo')->default(false);

            $table->boolean('show_institute_block')->default(false);
            $table->string('institute_address')->nullable();
            $table->boolean('show_marks')->default(false);
            $table->boolean('show_semester_summary')->default(true);
            $table->boolean('show_legend')->default(true);
            $table->boolean('show_declared_date')->default(true);

            $table->boolean('show_footer')->default(true);
            $table->text('footer_items')->nullable();           // JSON list of signature labels

            $table->string('margin_mode', 10)->default('auto'); // auto, custom
            $table->unsignedTinyInteger('margin_top')->nullable();
            $table->unsignedTinyInteger('margin_right')->nullable();
            $table->unsignedTinyInteger('margin_bottom')->nullable();
            $table->unsignedTinyInteger('margin_left')->nullable();

            $table->boolean('active')->default(true);
            $table->boolean('is_delete')->default(false);
            $table->timestamps();
        });

        // a ready-to-use template in the style of the first sample
        DB::table('mark_result_template')->insert([
            'name' => 'Default grade sheet',
            'is_default' => true,
            'title' => 'GRADE-SHEET',
            'programme_label' => 'Name of the Programme:',
            'university_name' => 'DEVI AHILYA VISHWAVIDYALAYA, INDORE',
            'header_lines_top' => "NAAC Accredited 'A' Grade State University",
            'header_lines_bottom' => 'Takshashila Campus, Khandwa Road, Indore',
            'footer_items' => json_encode(['Prepared by', 'Co-ordinator', 'Head']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('mark_result_template');
    }
};
