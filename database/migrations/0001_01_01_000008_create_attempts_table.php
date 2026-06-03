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
        Schema::create('attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tryout_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['IN_PROGRESS', 'SUBMITTED', 'EXPIRED']);
            $table->timestamp('started_at');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('expires_at');
            $table->integer('score')->nullable();
            $table->integer('total_correct')->nullable();
            $table->integer('total_answered')->nullable();
            $table->json('snapshot')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attempts');
    }
};