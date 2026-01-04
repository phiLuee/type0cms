<?php

declare(strict_types=1);

namespace App\Plugins\Blog\Models;

use App\Models\User;
use App\Traits\HasSeoMetadata;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Post extends Model
{
    use HasSeoMetadata;

    protected $table = 'blog_posts';
    protected $guarded = [];

    public function getRouteKeyName(): string
    {
        return 'slug';
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
