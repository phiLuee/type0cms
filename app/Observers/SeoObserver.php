<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\SeoMetadata;
use App\Services\MediaService;
use Illuminate\Support\Facades\Log;

class SeoObserver
{
    private static array $pendingUploads = [];

    /**
     * Handle the SeoMetadata "saving" event (BEFORE save).
     * Verarbeitet og_image Uploads.
     */
    public function saving(SeoMetadata $seo): void
    {
        Log::info('SeoObserver: saving called', [
            'seo_id' => $seo->id,
            'has_og_image' => isset($seo->og_image),
            'og_image_value' => $seo->og_image ?? 'NOT SET',
            'attributes' => array_keys($seo->getAttributes()),
        ]);

        // Prüfe ob og_image gesetzt ist
        if (isset($seo->og_image)) {
            $value = $seo->og_image;

            // Ist es ein String und ein Livewire Upload?
            if (is_string($value) && strlen($value) > 0 && str_contains($value, 'livewire-tmp')) {
                Log::info('SeoObserver: og_image upload detected', [
                    'value' => $value,
                ]);

                // Speichere Upload-Pfad im static Array
                self::$pendingUploads[$seo->id ?? 'new'] = $value;
            }
        }

        // WICHTIG: Entferne og_image IMMER aus den Attributen,
        // da es keine DB-Spalte gibt - auch wenn nicht gesetzt
        unset($seo->og_image);
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

        Log::info('SeoObserver: Processing pending upload', ['path' => $uploadPath]);

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
            Log::info('SeoObserver: Media attached', ['id' => $media->id]);
        } else {
            Log::error('SeoObserver: moveFromTemporaryUpload returned null', ['path' => $uploadPath]);
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
