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
        Schema::create('physical_volumes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_id')->constrained()->cascadeOnDelete();
            $table->decimal('volume_number', 6, 2);
            $table->string('title')->nullable();
            $table->string('language')->default('espanol');
            $table->string('country')->default('Espana');
            $table->string('publisher')->nullable();
            $table->string('edition_name')->nullable();
            $table->string('isbn')->nullable();
            $table->string('ean')->nullable();
            $table->date('release_date')->nullable();
            $table->decimal('price', 8, 2)->nullable();
            $table->string('currency', 3)->default('EUR');
            $table->text('cover_url')->nullable();
            $table->string('metadata_source')->nullable();
            $table->string('metadata_provider_id')->nullable();
            $table->text('metadata_url')->nullable();
            $table->timestamp('metadata_fetched_at')->nullable();
            $table->jsonb('raw_metadata')->nullable();
            $table->timestamps();

            $table->index('work_id');
            $table->index('release_date');
            $table->index('isbn');
            $table->index('ean');
            $table->index('metadata_source');
            $table->index('metadata_provider_id');
            $table->unique(['work_id', 'volume_number', 'language', 'country', 'publisher', 'edition_name'], 'physical_volume_identity_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('physical_volumes');
    }
};
