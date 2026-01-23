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
        // Update existing project statuses to new values
        DB::table('projects')
            ->where('status', 'active')
            ->update(['status' => 'in_progress']);

        DB::table('projects')
            ->where('status', 'completed')
            ->update(['status' => 'done']);

        DB::table('projects')
            ->where('status', 'cancelled')
            ->update(['status' => 'on_hold']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to old values if needed
        DB::table('projects')
            ->where('status', 'in_progress')
            ->update(['status' => 'active']);

        DB::table('projects')
            ->where('status', 'done')
            ->update(['status' => 'completed']);
    }
};
