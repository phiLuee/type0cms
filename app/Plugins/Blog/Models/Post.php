<?php

declare(strict_types=1);

namespace App\Plugins\Blog\Models;

use App\Models\User;
use App\Traits\HasSeoMetadata;
use App\Traits\HasMediaReferences;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Post extends Model
{
    use HasSeoMetadata;
    use HasMediaReferences;

    protected $table = 'blog_posts';

    protected $fillable = [
        'user_id',
        'blog_category_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'is_published',
        'published_at',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Route-Model-Binding: nur veröffentlichte Posts im Frontend auflösen
     */
    public function resolveRouteBinding($value, $field = null): ?self
    {
        return $this->where($field ?? $this->getRouteKeyName(), $value)
            ->where('is_published', true)
            ->firstOrFail();
    }

    /**
     * Hilfsmethode für Featured Image URL
     */
    public function getFeaturedImageUrl(): ?string
    {
        $media = $this->getFirstMedia('featured_image');
        return $media?->getUrl();
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'blog_category_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(
            Tag::class,
            'blog_post_tag',
            'blog_post_id',
            'blog_tag_id'
        )->withTimestamps();
    }
}
