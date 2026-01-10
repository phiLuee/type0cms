<?php

declare(strict_types=1);

namespace App\Exceptions\Media;

/**
 * Exception wenn Medium nicht gefunden wurde
 */
class MediaNotFoundException extends MediaException
{
    public function __construct(string $identifier)
    {
        parent::__construct("Media not found: {$identifier}");
    }
}
