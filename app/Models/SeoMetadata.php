<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class SeoMetadata extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $table = 'seo_metadata';

    protected $fillable = [
        'meta_title',
        'meta_description',
        'og_image',
        'no_index',
        'canonical_url',
    ];

    protected $casts = [
        'no_index' => 'boolean',
    ];

    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Register media collections
     */
    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('og_image')
            ->useDisk('public') // Wichtig: public disk verwenden
            ->singleFile() // Nur ein OG Image pro SEO Record
            ->registerMediaConversions(function () {
                $this
                    ->addMediaConversion('og')
                    ->width(1200)
                    ->height(630)
                    ->format('webp')
                    ->nonQueued();
            });
    }

    /**
     * Hilfsmethode für OG Image URL
     */
    public function getOgImageUrl(string $conversion = ''): ?string
    {
        return $this->getFirstMediaUrl('og_image', $conversion);
    }
}
