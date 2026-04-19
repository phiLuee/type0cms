<?php

declare(strict_types=1);

namespace Type0\Media\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MediaReferencesSynced
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Model $model,
        public readonly string $collection,
        public readonly int $syncedCount
    ) {}
}
