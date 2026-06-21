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
        Schema::create('digital_series', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('platform_id')->constrained('digital_platforms')->cascadeOnDelete();
            $table->string('title');
            $table->text('platform_url')->nullable();
            $table->string('language')->default('ingles');
            $table->string('status')->default('unknown');
            $table->decimal('latest_episode_detected', 8, 2)->nullable();
            $table->timestamp('last_checked_at')->nullable();
            $table->text('cover_url')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('platform_id');
            $table->index('work_id');
            $table->index(['platform_id', 'title']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('digital_series');
    }
};
