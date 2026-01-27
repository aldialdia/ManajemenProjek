<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update existing projects with 'on_hold' status to 'review'
        DB::table('projects')
            ->where('status', 'on_hold')
            ->update(['status' => 'review']);
        
        // Also update project_status_logs if exists
        if (Schema::hasTable('project_status_logs')) {
            DB::table('project_status_logs')
                ->where('from_status', 'on_hold')
                ->update(['from_status' => 'review']);
            
            DB::table('project_status_logs')
                ->where('to_status', 'on_hold')
                ->update(['to_status' => 'review']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert 'review' back to 'on_hold'
        DB::table('projects')
            ->where('status', 'review')
            ->update(['status' => 'on_hold']);
        
        if (Schema::hasTable('project_status_logs')) {
            DB::table('project_status_logs')
                ->where('from_status', 'review')
                ->update(['from_status' => 'on_hold']);
            
            DB::table('project_status_logs')
                ->where('to_status', 'review')
                ->update(['to_status' => 'on_hold']);
        }
    }
};

