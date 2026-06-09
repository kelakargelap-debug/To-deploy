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
        Schema::create('auth_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('trusted_device_id')->nullable();
            $table->string('session_token_hash')->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamp('idle_expires_at')->nullable();
            $table->timestamp('absolute_expires_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->string('revoked_reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auth_sessions');
    }
};
