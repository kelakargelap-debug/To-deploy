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
        Schema::create('verification_otps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('target_type')->default('email');
            $table->string('target_value');
            $table->string('otp_hash');
            $table->string('purpose');
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->integer('attempt_count')->default(0);
            $table->integer('resend_count')->default(0);
            $table->timestamp('last_sent_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('verification_otps');
    }
};
