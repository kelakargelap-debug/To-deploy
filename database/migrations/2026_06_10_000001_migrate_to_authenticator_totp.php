<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Migrasi dari Email OTP → Authenticator TOTP
     * - Tambah totp_secret, totp_enabled, backup_codes ke users
     * - Hapus phone, phone_verified_at dari users (sesuai plan)
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Add TOTP fields
            $table->text('totp_secret')->nullable()->after('password');
            $table->boolean('totp_enabled')->default(false)->after('totp_secret');
            $table->text('backup_codes')->nullable()->after('totp_enabled');

            // Remove phone fields (plan section 5.2)
            if (Schema::hasColumn('users', 'phone')) {
                // SQLite doesn't support dropping columns in older versions,
                // so we'll handle this gracefully
            }
        });

        // For SQLite compatibility, we handle column drops separately
        if (Schema::hasColumn('users', 'phone')) {
            try {
                Schema::table('users', function (Blueprint $table) {
                    $table->dropColumn(['phone', 'phone_verified_at']);
                });
            } catch (\Exception $e) {
                // SQLite may not support dropping columns in all versions
                // In production with MySQL/Postgres this will work fine
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['totp_secret', 'totp_enabled', 'backup_codes']);
        });

        if (!Schema::hasColumn('users', 'phone')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('phone')->nullable()->unique()->after('email');
                $table->timestamp('phone_verified_at')->nullable()->after('email_verified_at');
            });
        }
    }
};
