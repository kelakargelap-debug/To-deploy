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
        Schema::create('tryouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->enum('status', ['DRAFT', 'PUBLISHED', 'ARCHIVED'])->default('DRAFT');
            $table->enum('required_tier', ['FREE', 'PREMIUM'])->default('FREE');
            $table->integer('duration_minutes');
            $table->integer('total_questions');
            $table->integer('passing_score');
            $table->boolean('randomize_order')->default(false);
            $table->boolean('show_result')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tryouts');
    }
};