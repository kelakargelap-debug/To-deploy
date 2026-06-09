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
        Schema::table('attempts', function (Blueprint $table) {
            $table->string('trusted_device_id')->nullable()->after('tryout_id');
            $table->timestamp('last_heartbeat_at')->nullable()->after('expires_at');
            $table->timestamp('force_closed_at')->nullable()->after('last_heartbeat_at');
            $table->string('force_closed_reason')->nullable()->after('force_closed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attempts', function (Blueprint $table) {
            $table->dropColumn([
                'trusted_device_id',
                'last_heartbeat_at',
                'force_closed_at',
                'force_closed_reason'
            ]);
        });
    }
};
