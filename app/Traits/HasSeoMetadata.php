<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\SeoMetadata;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasSeoMetadata
{
    /**
     * Die polymorphe Beziehung zu den SEO-Metadaten.
     */
    public function seo(): MorphOne
    {
        return $this->morphOne(SeoMetadata::class, 'model');
    }

    /**
     * Hilfsmethode zum Speichern oder Aktualisieren der SEO-Daten.
     */
    public function updateSeo(array $attributes): void
    {
        $this->seo()->updateOrCreate([], $attributes);
    }

    /**
     * Gibt den Meta-Titel zurück oder fällt auf den Titel des Models zurück.
     */
    public function getSeoTitle(): ?string
    {
        return $this->seo?->meta_title
            ?? $this->getAttribute('title')
            ?? $this->getAttribute('name');
    }

    /**
     * Gibt die Meta-Beschreibung zurück oder fällt auf den Auszug (Excerpt) zurück.
     */
    public function getSeoDescription(): ?string
    {
        return $this->seo?->meta_description
            ?? $this->getAttribute('excerpt');
    }
}
