<?php

declare(strict_types=1);

namespace App\Observers;

use App\Plugins\Blog\Models\Post;
use App\Services\MediaService;

class PostObserver
{
    private static array $pendingUploads = [];

    public function __construct(
        private MediaService $mediaService
    ) {}

    /**
     * Handle the Post "saving" event (BEFORE save).
     * Verarbeitet featured_image Uploads.
     */
    public function saving(Post $post): void
    {
        \Log::info('PostObserver: saving called', [
            'post_id' => $post->id,
            'has_featured_image' => isset($post->featured_image),
            'featured_image_value' => $post->featured_image ?? 'NOT SET',
            'attributes' => array_keys($post->getAttributes()),
        ]);

        // Prüfe ob featured_image gesetzt ist
        if (isset($post->featured_image)) {
            $value = $post->featured_image;

            // Ist es ein String und ein Livewire Upload?
            if (is_string($value) && strlen($value) > 0 && str_contains($value, 'livewire-tmp')) {
                \Log::info('PostObserver: featured_image upload detected', ['value' => $value]);
                // Speichere Upload-Pfad im static Array
                self::$pendingUploads[$post->id ?? 'new'] = $value;
            }
        }

        // WICHTIG: Entferne featured_image IMMER aus den Attributen,
        // da es keine DB-Spalte gibt
        unset($post->featured_image);
    }

    /**
     * Handle the Post "saved" event.
     * Verarbeitet Featured Image Upload und synchronisiert Content-Medien.
     */
    public function saved(Post $post): void
    {
        // 1. Featured Image Upload verarbeiten
        $key = $post->id ?? 'new';
        if (isset(self::$pendingUploads[$key])) {
            $uploadPath = self::$pendingUploads[$key];
            unset(self::$pendingUploads[$key]);

            \Log::info('PostObserver: Processing pending featured_image upload', ['path' => $uploadPath]);

            // Entferne alte Referenz
            $oldMedia = $post->getFirstMedia('featured_image');
            if ($oldMedia) {
                $post->detachMedia($oldMedia, 'featured_image');
            }

            // Verschiebe zu Media und verknüpfe
            $media = $this->mediaService->moveFromTemporaryUpload(
                $uploadPath,
                'media',
                'Featured Image für ' . $post->title
            );

            if ($media) {
                $post->attachMedia($media, 'featured_image');
                \Log::info('PostObserver: Featured image attached', ['media_id' => $media->id]);
            } else {
                \Log::error('PostObserver: moveFromTemporaryUpload returned null', ['path' => $uploadPath]);
            }
        }

        // 2. Content-Medien synchronisieren
        if ($post->content) {
            $this->mediaService->syncContentMedia(
                $post,
                $post->content,
                'content'
            );
        }
    }

    /**
     * Handle the Post "deleting" event.
     * Entfernt alle MediaReferences.
     */
    public function deleting(Post $post): void
    {
        $this->mediaService->deleteAllReferences($post);
    }
}
