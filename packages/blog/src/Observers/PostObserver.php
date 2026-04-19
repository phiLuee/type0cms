<?php

declare(strict_types=1);

namespace Type0\Blog\Observers;

use Type0\Media\Contracts\MediaServiceInterface;
use Type0\Blog\Models\Post;

class PostObserver
{
    public function __construct(
        private readonly MediaServiceInterface $mediaService
    ) {}

    public function deleting(Post $post): void
    {
        $this->mediaService->deleteAllReferences($post);
    }
}
