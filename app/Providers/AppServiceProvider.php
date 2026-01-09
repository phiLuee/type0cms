<?php

namespace App\Providers;

use App\Models\Media;
use App\Observers\MediaObserver;
use App\Observers\PostObserver;
use App\Plugins\Blog\Models\Post;
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

        // Observer für Media-Schutz (verhindert Löschen bei aktiven Referenzen)
        Media::observe(MediaObserver::class);
    }
}
