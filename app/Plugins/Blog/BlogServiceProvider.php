<?php

namespace App\Plugins\Blog;

use App\Plugins\Blog\Models\Post;
use App\Plugins\Blog\Observers\PostObserver;
use Illuminate\Support\ServiceProvider;

class BlogServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');

        // Observer für automatische MediaReferences bei Content-Medien
        Post::observe(PostObserver::class);
    }
}
