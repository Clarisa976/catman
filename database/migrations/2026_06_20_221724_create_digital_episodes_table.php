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
        Schema::create('digital_episodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('digital_series_id')->constrained()->cascadeOnDelete();
            $table->decimal('episode_number', 8, 2);
            $table->string('title')->nullable();
            $table->date('release_date')->nullable();
            $table->text('episode_url')->nullable();
            $table->boolean('is_free')->nullable();
            $table->boolean('is_locked')->nullable();
            $table->timestamps();

            $table->unique(['digital_series_id', 'episode_number']);
            $table->index('release_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('digital_episodes');
    }
};
