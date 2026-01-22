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
        Schema::create('time_tracking_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('time_entry_id')->nullable()->constrained()->onDelete('cascade');
            $table->enum('action', ['started', 'paused', 'resumed', 'stopped', 'completed']);
            $table->integer('duration_at_action')->default(0); // durasi dalam detik saat aksi dilakukan
            $table->text('note')->nullable();
            $table->timestamps();

            // Index for quick queries
            $table->index(['task_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('time_tracking_logs');
    }
};
