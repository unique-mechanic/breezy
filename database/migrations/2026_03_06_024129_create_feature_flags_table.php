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
        Schema::create('feature_flags', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // e.g., 'dark_mode', 'social_sharing'
            $table->text('description')->nullable();
            $table->boolean('enabled')->default(false); // Global flag status
            $table->timestamps();
        });

        // User-specific feature flags (per-user override)
        Schema::create('user_feature_flags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('feature_flag_id')->constrained()->cascadeOnDelete();
            $table->boolean('enabled');
            $table->timestamps();
            
            $table->unique(['user_id', 'feature_flag_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_feature_flags');
        Schema::dropIfExists('feature_flags');
    }
};
