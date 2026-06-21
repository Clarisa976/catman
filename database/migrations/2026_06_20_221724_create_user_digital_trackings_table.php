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
        Schema::create('user_digital_trackings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('digital_series_id')->constrained()->cascadeOnDelete();
            $table->boolean('follow_updates')->default(true);
            $table->boolean('notify_new_episode')->default(true);
            $table->string('reading_status')->default('reading');
            $table->decimal('last_episode_read', 8, 2)->nullable();
            $table->decimal('last_episode_seen', 8, 2)->nullable();
            $table->date('started_at')->nullable();
            $table->date('finished_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'digital_series_id']);
            $table->index(['user_id', 'reading_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_digital_trackings');
    }
};
