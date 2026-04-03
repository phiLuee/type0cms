<?php

namespace App\Providers;

use App\Observers\SeoObserver;
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
        SeoMetadata::observe(SeoObserver::class);
    }
}
