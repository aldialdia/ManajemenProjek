<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * Drop the redundant assigned_to column since we now use task_user pivot table.
     */
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // Drop foreign key first
            $table->dropForeign(['assigned_to']);
            // Drop the column
            $table->dropColumn('assigned_to');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignId('assigned_to')->nullable()->after('project_id')->constrained('users')->nullOnDelete();
        });
    }
};
