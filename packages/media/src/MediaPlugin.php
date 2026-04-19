<?php

declare(strict_types=1);

namespace Type0\Media;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Type0\Media\Filament\Resources\MediaResource;
use Type0\Media\Filament\Widgets\MediaCollectionsWidget;
use Type0\Media\Filament\Widgets\MediaStorageWidget;

class MediaPlugin implements Plugin
{
    public function getId(): string
    {
        return 'media';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([
            MediaResource::class,
        ]);

        $panel->widgets([
            MediaStorageWidget::class,
            MediaCollectionsWidget::class,
        ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public static function make(): static
    {
        return new static();
    }
}
