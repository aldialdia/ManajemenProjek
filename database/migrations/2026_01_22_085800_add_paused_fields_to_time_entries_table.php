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
        Schema::table('time_entries', function (Blueprint $table) {
            $table->boolean('is_paused')->default(false)->after('is_running');
            $table->integer('paused_duration_seconds')->default(0)->after('is_paused');
            $table->timestamp('paused_at')->nullable()->after('paused_duration_seconds');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('time_entries', function (Blueprint $table) {
            $table->dropColumn(['is_paused', 'paused_duration_seconds', 'paused_at']);
        });
    }
};
