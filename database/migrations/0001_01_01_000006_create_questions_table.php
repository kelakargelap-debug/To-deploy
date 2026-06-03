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
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tryout_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['SINGLE_CHOICE', 'MULTIPLE_CHOICE', 'TRUE_FALSE']);
            $table->text('content');
            $table->string('image_url')->nullable();
            $table->integer('order')->default(0);
            $table->text('explanation')->nullable();
            $table->integer('points')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};