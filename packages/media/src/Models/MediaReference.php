<?php

namespace Type0\Media\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Pivot-Model für die Verknüpfung von Media mit anderen Models.
 * Ermöglicht die Wiederverwendung derselben Media-Datei für mehrere Models.
 */
class MediaReference extends Model
{
    protected $fillable = [
        'media_id',
        'model_type',
        'model_id',
        'collection_name',
        'order_column',
    ];

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }

    public function model()
    {
        return $this->morphTo();
    }
}
