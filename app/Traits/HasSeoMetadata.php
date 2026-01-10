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

    /**
     * Gibt das OG Image zurück
     */
    public function getSeoOgImage(): ?string
    {
        return $this->seo?->getOgImageUrl();
    }

    /**
     * Verarbeitet den Upload eines OG Images.
     * Kann von Filament Pages oder Controllern aufgerufen werden.
     *
     * @param mixed $uploadedPath String (Pfad) oder Array (bei FileUpload)
     * @param string|null $description Optionale Beschreibung für das Media
     * @return bool True wenn erfolgreich
     */
    public function handleSeoOgImageUpload($uploadedPath, ?string $description = null): bool
    {
        // Stelle sicher, dass SEO-Metadaten existieren
        if (!$this->seo) {
            $this->seo()->create([]);
        }

        $mediaService = app(\App\Services\MediaService::class);

        // Wenn Array und leer = Bild wurde gelöscht
        if (is_array($uploadedPath) && count($uploadedPath) === 0) {
            $oldMedia = $this->seo->getFirstMedia('og_image');
            if ($oldMedia) {
                $this->seo->detachMedia($oldMedia, 'og_image');
            }
            return true;
        }

        // FileUpload gibt manchmal Arrays zurück
        if (is_array($uploadedPath) && count($uploadedPath) > 0) {
            $uploadedPath = $uploadedPath[0];
        }

        // Prüfe ob es ein gültiger Upload ist
        if (is_string($uploadedPath) && strlen($uploadedPath) > 0) {
            // Entferne alte Referenz
            $oldMedia = $this->seo->getFirstMedia('og_image');
            if ($oldMedia) {
                $this->seo->detachMedia($oldMedia, 'og_image');
            }

            // Generiere Beschreibung falls nicht angegeben
            if (!$description) {
                $modelName = class_basename($this);
                $title = $this->title ?? $this->name ?? $this->id;
                $description = "{$modelName} OG Image: {$title}";
            }

            // Verschiebe zu Media und verknüpfe
            $media = $mediaService->moveFromTemporaryUpload(
                $uploadedPath,
                'media',
                $description
            );

            if ($media) {
                $this->seo->attachMedia($media, 'og_image');
                return true;
            }
        }

        return false;
    }
}
