<?php

declare(strict_types=1);

namespace Type0\Media\Events;

use Type0\Media\Models\Media;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MediaUploaded
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Media $media,
        public readonly string $collection
    ) {}
}
