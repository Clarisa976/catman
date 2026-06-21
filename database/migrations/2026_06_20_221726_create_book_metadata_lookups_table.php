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
        Schema::create('book_metadata_lookups', function (Blueprint $table) {
            $table->id();
            $table->string('provider');
            $table->string('lookup_type');
            $table->string('lookup_value');
            $table->jsonb('normalized_result')->nullable();
            $table->jsonb('raw_response')->nullable();
            $table->boolean('success')->default(true);
            $table->text('error_message')->nullable();
            $table->timestamp('fetched_at')->nullable();
            $table->timestamps();

            $table->index(['provider', 'lookup_type', 'lookup_value']);
            $table->index('fetched_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('book_metadata_lookups');
    }
};
