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
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('file_name');
            $table->string('disk')->default('public');
            $table->string('path');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size'); // in bytes
            $table->string('collection_name')->default('default')->index();
            $table->json('metadata')->nullable(); // Alt, Beschreibung, etc.
            $table->timestamps();

            // Indexes für Performance
            $table->index('mime_type');
            $table->index('created_at');
        });

        Schema::create('media_references', function (Blueprint $table) {
            $table->id();
            $table->foreignId('media_id')->constrained()->cascadeOnDelete();
            $table->morphs('model');
            $table->string('collection_name')->default('default');
            $table->unsignedInteger('order_column')->nullable();
            $table->timestamps();

            $table->index(['model_type', 'model_id', 'collection_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media');
        Schema::dropIfExists('media_references');
    }
};
