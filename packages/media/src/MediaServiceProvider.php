<?php

declare(strict_types=1);

namespace Type0\Media;

use Type0\Media\Contracts\MediaServiceInterface;
use Type0\Media\Services\MediaService;
use Illuminate\Support\ServiceProvider;

class MediaServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/media.php', 'media');

        $this->app->singleton(MediaServiceInterface::class, MediaService::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'media');

        $this->publishes([
            __DIR__ . '/../config/media.php' => config_path('media.php'),
        ], 'media-config');

        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/media'),
        ], 'media-views');
    }
}
