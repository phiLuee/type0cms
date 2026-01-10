<?php

declare(strict_types=1);

namespace App\Observers;

use App\Plugins\Blog\Models\Post;
use App\Services\MediaService;

class PostObserver
{
    public function __construct(
        private MediaService $mediaService
    ) {}

    /**
     * Handle the Post "saved" event.
     * Synchronisiert Content-Medien via Service.
     */
    public function saved(Post $post): void
    {
        if (!$post->content) {
            return;
        }

        $this->mediaService->syncContentMedia(
            $post,
            $post->content,
            'content'
        );
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
