<?php

declare(strict_types=1);

namespace App\Support;

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;

class UlidPathGenerator implements PathGenerator
{
    /**
     * Get the path for the given media, using the ULID as the filename.
     */
    public function getPath(Media $media): string
    {
        return 'media/';
    }

    /**
     * Get the path for conversions of the given media.
     */
    public function getPathForConversions(Media $media): string
    {
        return 'media/conversions/';
    }

    /**
     * Get the path for responsive images of the given media.
     */
    public function getPathForResponsiveImages(Media $media): string
    {
        return 'media/responsive-images/';
    }

    /**
     * Get the base path for the media.
     * Uses only the ULID without the ID subdirectory.
     */
    protected function getBasePath(Media $media): string
    {
        return 'media';
    }
}
