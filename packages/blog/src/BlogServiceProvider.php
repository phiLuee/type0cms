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

        $this->app->singleton(BlogExtensionManager::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');

        Post::observe(PostObserver::class);

        $this->app->booted(fn () => $this->discoverAndRegisterExtensions());

        $this->publishes([
            __DIR__ . '/../config/blog.php' => config_path('blog.php'),
        ], 'blog-config');
    }

    /**
     * Discover extensions and register them.
     *
     * Built-in extensions (e.g. SEO) are registered automatically when their
     * package is installed. Third-party packages can tag their BlogExtension
     * implementations with 'blog.extensions' to be discovered here.
     */
    protected function discoverAndRegisterExtensions(): void
    {
        $manager = $this->app->make(BlogExtensionManager::class);

        // Register built-in optional extensions
        $this->registerBuiltInExtensions($manager);

        // Register third-party extensions via container tagging
        foreach ($this->app->tagged('blog.extensions') as $extension) {
            $manager->register($extension);
        }

        foreach ([Post::class, Category::class, Tag::class] as $modelClass) {
            $manager->registerRelationships($modelClass);
        }
    }

    /**
     * Register built-in optional extensions based on installed packages.
     */
    protected function registerBuiltInExtensions(BlogExtensionManager $manager): void
    {
        $builtIn = [
            \Type0\Seo\Models\SeoMetadata::class => Extensions\SeoExtension::class,
        ];

        foreach ($builtIn as $detector => $extension) {
            if (class_exists($detector)) {
                $manager->register(new $extension());
            }
        }
    }
}
