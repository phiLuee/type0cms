<?php

declare(strict_types=1);

namespace App\Events\Media;

use App\Models\Media;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event wird ausgelöst wenn ein Medium gelöscht wurde
 */
class MediaDeleted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly int $mediaId,
        public readonly string $fileName,
        public readonly string $collection
    ) {}
}
