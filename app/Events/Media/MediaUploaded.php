<?php

declare(strict_types=1);

namespace App\Events\Media;

use App\Models\Media;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event wird ausgelöst wenn ein neues Medium hochgeladen wurde
 */
class MediaUploaded
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Media $media,
        public readonly string $collection
    ) {}
}
