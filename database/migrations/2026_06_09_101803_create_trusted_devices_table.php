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
        Schema::create('trusted_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('device_id')->unique();
            $table->string('device_name')->nullable();
            $table->string('browser')->nullable();
            $table->string('os')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('last_ip', 45)->nullable();
            $table->string('last_location')->nullable();
            $table->timestamp('trusted_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'device_id']);
            $table->index('revoked_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trusted_devices');
    }
};
