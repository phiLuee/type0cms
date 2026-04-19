<?php

declare(strict_types=1);

namespace Type0\Media\Traits;

use Type0\Media\Models\Media;
use Type0\Media\Models\MediaReference;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;

/**
 * Ermöglicht die Wiederverwendung von Medien aus der zentralen Mediathek.
 * Anstatt Medien direkt hochzuladen, referenziert man existierende Media-Einträge.
 */
trait HasMediaReferences
{
    public function mediaReferences(): MorphMany
    {
        return $this->morphMany(MediaReference::class, 'model');
    }

    public function attachMedia(int|Media $media, string $collection = 'default', ?int $order = null): MediaReference
    {
        $mediaId = $media instanceof Media ? $media->id : $media;

        return $this->mediaReferences()->create([
            'media_id' => $mediaId,
            'collection_name' => $collection,
            'order_column' => $order,
        ]);
    }

    public function detachMedia(int|Media $media, ?string $collection = null): int
    {
        $mediaId = $media instanceof Media ? $media->id : $media;

        $query = $this->mediaReferences()->where('media_id', $mediaId);

        if ($collection) {
            $query->where('collection_name', $collection);
        }

        return $query->delete();
    }

    public function getMedia(string $collection = 'default'): Collection
    {
        return $this->mediaReferences()
            ->where('collection_name', $collection)
            ->with('media')
            ->orderBy('order_column')
            ->get()
            ->pluck('media');
    }

    public function getFirstMedia(string $collection = 'default'): ?Media
    {
        return $this->mediaReferences()
            ->where('collection_name', $collection)
            ->with('media')
            ->orderBy('order_column')
            ->first()
            ?->media;
    }

    public function getFirstMediaUrl(string $collection = 'default', string $conversion = ''): ?string
    {
        $media = $this->getFirstMedia($collection);

        if (!$media) {
            return null;
        }

        return $conversion ? $media->getUrl($conversion) : $media->getUrl();
    }

    public function hasMedia(string $collection = 'default'): bool
    {
        return $this->mediaReferences()
            ->where('collection_name', $collection)
            ->exists();
    }

    public function syncMedia(array $mediaIds, string $collection = 'default'): void
    {
        $this->mediaReferences()->where('collection_name', $collection)->delete();

        foreach ($mediaIds as $index => $mediaId) {
            $this->attachMedia($mediaId, $collection, $index);
        }
    }
}
