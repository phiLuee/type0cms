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
        Schema::table('media_references', function (Blueprint $table) {
            // Entferne alten Foreign Key zur media Tabelle
            $table->dropForeign(['media_id']);

            // Erstelle neuen Foreign Key zur custom_media Tabelle
            $table->foreign('media_id')
                ->references('id')
                ->on('custom_media')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('media_references', function (Blueprint $table) {
            // Zurück zum alten Foreign Key
            $table->dropForeign(['media_id']);

            $table->foreign('media_id')
                ->references('id')
                ->on('media')
                ->cascadeOnDelete();
        });
    }
};
