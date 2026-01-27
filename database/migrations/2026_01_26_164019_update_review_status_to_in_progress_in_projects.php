<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Convert any 'review' status projects to 'in_progress' since project kanban only has 3 statuses.
     */
    public function up(): void
    {
        // Update projects with 'review' status to 'in_progress'
        DB::table('projects')
            ->where('status', 'review')
            ->update(['status' => 'in_progress']);
        
        // Also update project_status_logs if exists
        if (Schema::hasTable('project_status_logs')) {
            DB::table('project_status_logs')
                ->where('from_status', 'review')
                ->update(['from_status' => 'in_progress']);
            
            DB::table('project_status_logs')
                ->where('to_status', 'review')
                ->update(['to_status' => 'in_progress']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration is irreversible - cannot determine which projects were previously 'review'
    }
};
