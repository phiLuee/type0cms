<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seo_metadata', function (Blueprint $table) {
            $table->id();

            $table->morphs('model');

            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('og_image')->nullable(); // Bild für Social Media (Facebook/Twitter)
            $table->boolean('no_index')->default(false); // Soll Google diese Seite ignorieren?
            $table->string('canonical_url')->nullable(); // Falls Duplicate Content vermieden werden soll

            $table->timestamps();

            // Ein Model sollte nur einen SEO-Eintrag haben
            $table->unique(['model_type', 'model_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_metadata');
    }
};
