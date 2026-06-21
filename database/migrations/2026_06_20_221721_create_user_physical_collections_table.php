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
        Schema::create('user_physical_collections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('physical_volume_id')->constrained()->cascadeOnDelete();
            $table->string('ownership_status')->default('owned');
            $table->string('reading_status')->default('not_started');
            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_price', 8, 2)->nullable();
            $table->string('purchase_currency', 3)->default('EUR');
            $table->string('store')->nullable();
            $table->boolean('is_travel_memory')->default(false);
            $table->string('purchase_country')->nullable();
            $table->string('location')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'physical_volume_id']);
            $table->index(['user_id', 'ownership_status']);
            $table->index(['user_id', 'reading_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_physical_collections');
    }
};
