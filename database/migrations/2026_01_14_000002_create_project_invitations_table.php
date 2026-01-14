<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('project_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invited_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('email');
            $table->string('role')->default('member'); // manager, admin, member
            $table->enum('status', ['pending', 'accepted', 'declined', 'expired'])->default('pending');
            $table->string('token', 64)->unique();
            $table->timestamp('expires_at');
            $table->timestamps();

            // Index untuk pencarian cepat
            $table->index(['project_id', 'email']);
            $table->index(['status', 'expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_invitations');
    }
};
