<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SeoMetadata extends Model
{
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
}
