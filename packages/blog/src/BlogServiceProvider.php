<?php

namespace Type0\Blog;

use Type0\Blog\Models\Category;
use Type0\Blog\Models\Post;
use Type0\Blog\Models\Tag;
use Type0\Blog\Observers\PostObserver;
use Illuminate\Support\ServiceProvider;

class BlogServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/blog.php', 'blog');
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');

        Post::observe(PostObserver::class);

        $this->registerSeoRelationships();

        $this->publishes([
            __DIR__ . '/../config/blog.php' => config_path('blog.php'),
        ], 'blog-config');
    }

    /**
     * Register SEO relationships on Blog models when the SEO package is installed.
     */
    protected function registerSeoRelationships(): void
    {
        if (! class_exists(\Type0\Seo\Models\SeoMetadata::class)) {
            return;
        }

        foreach ([Post::class, Category::class, Tag::class] as $modelClass) {
            $modelClass::resolveRelationUsing('seo', function ($model) {
                return $model->morphOne(\Type0\Seo\Models\SeoMetadata::class, 'model');
            });
        }
    }
}
