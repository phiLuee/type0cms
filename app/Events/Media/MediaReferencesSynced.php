<?php

declare(strict_types=1);

namespace App\Events\Media;

use App\Models\Media;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event wird ausgelöst wenn Media-Referenzen synchronisiert wurden
 */
class MediaReferencesSynced
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Model $model,
        public readonly string $collection,
        public readonly int $syncedCount
    ) {}
}
