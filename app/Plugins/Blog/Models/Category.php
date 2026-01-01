<?php

namespace App\Plugins\Blog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $table = 'blog_categories';
    protected $guarded = [];

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class, 'blog_category_id');
    }
}
