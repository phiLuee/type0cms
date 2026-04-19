<?php

declare(strict_types=1);

namespace Type0\Media\Exceptions;

class MediaNotFoundException extends MediaException
{
    public function __construct(string $identifier)
    {
        parent::__construct("Media not found: {$identifier}");
    }
}
