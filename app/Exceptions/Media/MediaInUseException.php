<?php

declare(strict_types=1);

namespace App\Exceptions\Media;

use App\Models\Media;

/**
 * Exception wenn Medium gelöscht werden soll, aber noch verwendet wird
 */
class MediaInUseException extends MediaException
{
    public function __construct(Media $media)
    {
        $count = $media->usageCount();
        parent::__construct(
            "Cannot delete media '{$media->name}' (ID: {$media->id}). It is currently used in {$count} reference(s)."
        );
    }
}
