<?php

namespace App\Providers;

use App\Observers\PostObserver;
use App\Observers\SeoObserver;
use App\Plugins\Blog\Models\Post;
use App\Models\SeoMetadata;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Observer für automatische MediaReferences bei Content-Medien
        Post::observe(PostObserver::class);
        SeoMetadata::observe(SeoObserver::class);
    }
}
