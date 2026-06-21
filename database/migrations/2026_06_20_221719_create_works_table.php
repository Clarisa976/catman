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
        Schema::create('works', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('original_title')->nullable();
            $table->string('author')->nullable();
            $table->string('type')->default('manga');
            $table->string('status')->default('unknown');
            $table->unsignedInteger('total_volumes')->nullable();
            $table->text('cover_url')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['title', 'author']);
            $table->index('type');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('works');
    }
};
