<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\Media;
use App\Models\MediaReference;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;

/**
 * Ermöglicht die Wiederverwendung von Medien aus der zentralen Mediathek.
 * Anstatt Medien direkt hochzuladen, referenziert man existierende Media-Einträge.
 */
trait HasMediaReferences
{
    /**
     * Alle Media-Referenzen dieses Models.
     */
    public function mediaReferences(): MorphMany
    {
        return $this->morphMany(MediaReference::class, 'model');
    }

    /**
     * Hängt eine existierende Media an dieses Model.
     *
     * @param int|Media $media Die Media-ID oder Media-Instanz
     * @param string $collection Die Collection, in der die Media gespeichert werden soll
     * @param int|null $order Optional: Die Sortierung
     */
    public function attachMedia(int|Media $media, string $collection = 'default', ?int $order = null): MediaReference
    {
        $mediaId = $media instanceof Media ? $media->id : $media;

        return $this->mediaReferences()->create([
            'media_id' => $mediaId,
            'collection_name' => $collection,
            'order_column' => $order,
        ]);
    }

    /**
     * Entfernt eine Media-Referenz von diesem Model.
     */
    public function detachMedia(int|Media $media, ?string $collection = null): int
    {
        $mediaId = $media instanceof Media ? $media->id : $media;

        $query = $this->mediaReferences()->where('media_id', $mediaId);

        if ($collection) {
            $query->where('collection_name', $collection);
        }

        return $query->delete();
    }

    /**
     * Gibt alle Medien einer Collection zurück.
     */
    public function getMedia(string $collection = 'default'): Collection
    {
        return $this->mediaReferences()
            ->where('collection_name', $collection)
            ->with('media')
            ->orderBy('order_column')
            ->get()
            ->pluck('media');
    }

    /**
     * Gibt das erste Medium einer Collection zurück.
     */
    public function getFirstMedia(string $collection = 'default'): ?Media
    {
        return $this->mediaReferences()
            ->where('collection_name', $collection)
            ->with('media')
            ->orderBy('order_column')
            ->first()
            ?->media;
    }

    /**
     * Gibt die URL des ersten Mediums einer Collection zurück.
     */
    public function getFirstMediaUrl(string $collection = 'default', string $conversion = ''): ?string
    {
        $media = $this->getFirstMedia($collection);

        if (!$media) {
            return null;
        }

        return $conversion ? $media->getUrl($conversion) : $media->getUrl();
    }

    /**
     * Prüft, ob dieses Model Medien in der Collection hat.
     */
    public function hasMedia(string $collection = 'default'): bool
    {
        return $this->mediaReferences()
            ->where('collection_name', $collection)
            ->exists();
    }

    /**
     * Synchronisiert die Medien einer Collection (entfernt alte, fügt neue hinzu).
     *
     * @param array $mediaIds Array von Media-IDs
     * @param string $collection
     */
    public function syncMedia(array $mediaIds, string $collection = 'default'): void
    {
        // Entferne alle existierenden Referenzen dieser Collection
        $this->mediaReferences()->where('collection_name', $collection)->delete();

        // Füge neue Referenzen hinzu
        foreach ($mediaIds as $index => $mediaId) {
            $this->attachMedia($mediaId, $collection, $index);
        }
    }
}
