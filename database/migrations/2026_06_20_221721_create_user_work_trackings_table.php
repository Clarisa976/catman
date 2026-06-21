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
        Schema::create('user_work_trackings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('work_id')->constrained()->cascadeOnDelete();
            $table->boolean('follow_physical_releases')->default(true);
            $table->string('preferred_language')->default('espanol');
            $table->string('preferred_country')->default('Espana');
            $table->string('preferred_publisher')->nullable();
            $table->decimal('last_owned_volume_number', 6, 2)->nullable();
            $table->boolean('notify_new_volume')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'work_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_work_trackings');
    }
};
