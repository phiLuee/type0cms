<?php

declare(strict_types=1);

namespace Type0\Media\Exceptions;

use Type0\Media\Models\Media;

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
