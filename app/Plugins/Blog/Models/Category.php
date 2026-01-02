<?php

namespace App\Plugins\Blog\Models;

use App\Traits\HasSeoMetadata;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasSeoMetadata;

    protected $table = 'blog_categories';
    protected $guarded = [];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class, 'blog_category_id');
    }
}
