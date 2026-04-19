<?php

declare(strict_types=1);

namespace Type0\Blog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    protected $table = 'blog_tags';

    protected $fillable = [
        'name',
        'slug',
        'color',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(
            Post::class,
            'blog_post_tag',
            'blog_tag_id',
            'blog_post_id'
        )->withTimestamps();
    }
}
