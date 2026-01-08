<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\MediaCollections\Models\Media as BaseMedia;

/**
 * Erweitert das Spatie Media Model um die Relation zu MediaReferences
 */
class Media extends BaseMedia
{
    /**
     * Alle Referenzen zu diesem Medium
     */
    public function references(): HasMany
    {
        return $this->hasMany(MediaReference::class, 'media_id');
    }
}
