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
        Schema::create('mark_group', function (Blueprint $table) {
            $table->id('groupid');
            $table->string('name');
            $table->string('description')->nullable();
            $table->boolean('active')->default(true);
            $table->boolean('is_delete')->default(false);
            $table->timestamps();
        });

        // the access of a group
        Schema::create('mark_group_permission', function (Blueprint $table) {
            $table->foreignId('groupid')->constrained('mark_group', 'groupid')->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained('permissions')->cascadeOnDelete();
            $table->primary(['groupid', 'permission_id']);
        });

        // the members of a group
        Schema::create('mark_group_user', function (Blueprint $table) {
            $table->foreignId('groupid')->constrained('mark_group', 'groupid')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->primary(['groupid', 'user_id']);
        });

        // the departments of a restricted user
        Schema::create('mark_user_department', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('deptid')->constrained('mark_department', 'deptid')->restrictOnDelete();
            $table->primary(['user_id', 'deptid']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mark_user_department');
        Schema::dropIfExists('mark_group_user');
        Schema::dropIfExists('mark_group_permission');
        Schema::dropIfExists('mark_group');
    }
};
