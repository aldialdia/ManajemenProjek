<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            // Make task_id nullable for polymorphic support
            $table->foreignId('task_id')->nullable()->change();

            // Add polymorphic columns
            $table->nullableMorphs('commentable');
        });

        // Migrate existing task_id data to polymorphic
        DB::table('comments')->whereNotNull('task_id')->update([
            'commentable_type' => 'App\\Models\\Task',
            'commentable_id' => DB::raw('task_id'),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->dropMorphs('commentable');
        });
    }
};
