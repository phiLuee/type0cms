<?php

declare(strict_types=1);

namespace App\Plugins\Blog\Observers;

use App\Plugins\Blog\Models\Post;
use App\Services\MediaService;

class PostObserver
{
    public function __construct(
        private MediaService $mediaService
    ) {}

    /**
     * Handle the Post "deleting" event.
     * Entfernt alle MediaReferences.
     */
    public function deleting(Post $post): void
    {
        $this->mediaService->deleteAllReferences($post);
    }
}
