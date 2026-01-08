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

    /**
     * Override: Setze conversions_disk auf disk wenn null
     * Verhindert Fehler beim Löschen von Medien ohne Conversions
     */
    public function getConversionsDiskAttribute($value)
    {
        return $value ?? $this->disk;
    }

    /**
     * Override: Verhindere dass Conversions für ungebundene Medien geladen werden
     */
    public function getMediaConversionNames(): array
    {
        // Wenn kein Model gebunden ist, gibt es keine Conversions
        if (empty($this->model_type)) {
            return [];
        }

        return parent::getMediaConversionNames();
    }
}
