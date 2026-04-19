<?php

declare(strict_types=1);

namespace App\Plugins\Blog\Observers;

use App\Contracts\MediaServiceInterface;
use App\Plugins\Blog\Models\Post;

class PostObserver
{
    public function __construct(
        private MediaServiceInterface $mediaService
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
