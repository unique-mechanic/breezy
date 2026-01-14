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
        Schema::create('milestones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('habit_id')->constrained()->cascadeOnDelete();
            $table->integer('streak_target'); // e.g., 7, 14, 30, 100, 365
            $table->string('icon')->default('🏆'); // Emoji or icon name
            $table->string('title'); // e.g., "Week Wonder"
            $table->text('description')->nullable();
            $table->boolean('achieved')->default(false);
            $table->timestamp('achieved_at')->nullable();
            $table->integer('order')->default(0); // For sorting
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('milestones');
    }
};
