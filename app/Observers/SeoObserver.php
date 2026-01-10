<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\SeoMetadata;
use App\Services\MediaService;

class SeoObserver
{
    private static array $pendingUploads = [];

    /**
     * Handle the SeoMetadata "saving" event (BEFORE save).
     * Verarbeitet og_image Uploads.
     */
    public function saving(SeoMetadata $seo): void
    {
        // Prüfe ob og_image ein Upload ist (von FileUpload Feld)
        if (isset($seo->og_image) && is_string($seo->og_image) && strlen($seo->og_image) > 0) {
            // Prüfe ob es ein Livewire temporärer Upload ist
            $isLivewireTemp = str_contains($seo->og_image, 'livewire-tmp');

            \Log::info('SeoObserver: og_image upload detected', [
                'value' => $seo->og_image,
                'is_livewire_temp' => $isLivewireTemp,
            ]);

            // Nur wenn es ein temporärer Upload ist, verarbeiten
            if ($isLivewireTemp) {
                // Speichere Upload-Pfad im static Array
                self::$pendingUploads[$seo->id ?? 'new'] = $seo->og_image;

                // Entferne aus Attributen (wird nicht in DB gespeichert)
                unset($seo->og_image);
            }
        }
    }

    /**
     * Handle the SeoMetadata "saved" event (AFTER save).
     * Erstellt Media und MediaReference.
     */
    public function saved(SeoMetadata $seo): void
    {
        // Hol pending Upload für dieses SeoMetadata
        $key = $seo->id ?? 'new';

        if (!isset(self::$pendingUploads[$key])) {
            return;
        }

        $uploadPath = self::$pendingUploads[$key];
        unset(self::$pendingUploads[$key]);

        \Log::info('SeoObserver: Processing pending upload', ['path' => $uploadPath]);

        $mediaService = app(MediaService::class);

        // Entferne alte Referenz
        $oldMedia = $seo->getFirstMedia('og_image');
        if ($oldMedia) {
            $seo->detachMedia($oldMedia, 'og_image');
        }

        // Verschiebe zu Media und verknüpfe
        $media = $mediaService->moveFromTemporaryUpload(
            $uploadPath,
            'media',
            'OG Image für ' . ($seo->model->title ?? $seo->model->name ?? 'Model #' . $seo->model_id)
        );

        if ($media) {
            $seo->attachMedia($media, 'og_image');
            \Log::info('SeoObserver: Media attached', ['id' => $media->id]);
        } else {
            \Log::error('SeoObserver: moveFromTemporaryUpload returned null', ['path' => $uploadPath]);
        }
    }

    /**
     * Handle the SeoMetadata "deleting" event.
     * Entfernt MediaReferences wenn SeoMetadata gelöscht wird.
     */
    public function deleting(SeoMetadata $seo): void
    {
        // Entferne alle Media-Attachments
        $ogImage = $seo->getFirstMedia('og_image');
        if ($ogImage) {
            $seo->detachMedia($ogImage, 'og_image');
        }
    }
}
