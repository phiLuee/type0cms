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
        // Erstelle neue media Tabelle (ohne Spatie-Abhängigkeiten)
        Schema::create('custom_media', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name'); // Display Name
            $table->string('file_name'); // Original Dateiname
            $table->string('disk')->default('public');
            $table->string('path'); // Pfad zur Datei
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size'); // in bytes
            $table->string('collection_name')->default('default')->index();
            $table->json('metadata')->nullable(); // Alt, Beschreibung, etc.
            $table->timestamps();

            // Indexes für Performance
            $table->index('mime_type');
            $table->index('created_at');
        });

        // Migriere Daten von alter media zu custom_media falls vorhanden
        if (Schema::hasTable('media')) {
            DB::statement('
                INSERT INTO custom_media (id, uuid, name, file_name, disk, path, mime_type, size, collection_name, metadata, created_at, updated_at)
                SELECT 
                    id, 
                    COALESCE(uuid, UUID()),
                    name,
                    file_name,
                    disk,
                    file_name as path,
                    mime_type,
                    size,
                    collection_name,
                    custom_properties as metadata,
                    created_at,
                    updated_at
                FROM media
                WHERE model_type IS NULL
            ');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_media');
    }
};
