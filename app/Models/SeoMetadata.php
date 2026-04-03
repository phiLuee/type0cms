<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasMediaReferences;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SeoMetadata extends Model
{
    use HasMediaReferences;

    protected $table = 'seo_metadata';

    protected $fillable = [
        'meta_title',
        'meta_description',
        'no_index',
        'canonical_url',
    ];

    protected $casts = [
        'no_index' => 'boolean',
    ];

    protected static function booted(): void
    {
        // og_image ist keine DB-Spalte – sicherheitshalber vor dem Speichern entfernen
        static::saving(function (SeoMetadata $seo) {
            unset($seo->og_image);
        });

        // Media-Attachments aufräumen wenn SeoMetadata gelöscht wird
        static::deleting(function (SeoMetadata $seo) {
            $ogImage = $seo->getFirstMedia('og_image');
            if ($ogImage) {
                $seo->detachMedia($ogImage, 'og_image');
            }
        });
    }

    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Hilfsmethode für OG Image URL
     */
    public function getOgImageUrl(): ?string
    {
        $media = $this->getFirstMedia('og_image');
        return $media?->getUrl();
    }
}
