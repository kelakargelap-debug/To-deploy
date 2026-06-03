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
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['SUPERADMIN', 'ADMIN', 'USER'])->default('USER');
            $table->enum('membership_tier', ['FREE', 'PREMIUM'])->default('FREE');
            $table->enum('membership_status', ['ACTIVE', 'EXPIRED', 'SUSPENDED'])->default('ACTIVE');
            $table->timestamp('membership_expiry')->nullable();
            $table->boolean('is_active')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'membership_tier', 'membership_status', 'membership_expiry', 'is_active']);
        });
    }
};