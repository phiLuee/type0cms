<?php

namespace App\Plugins\Blog;

use Filament\Contracts\Plugin;
use Filament\Panel;

class BlogPlugin implements Plugin
{
    public function getId(): string
    {
        return 'blog';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([
            Resources\PostResource::class,
            Resources\CategoryResource::class,
            Resources\TagResource\TagResource::class,
        ]);
    }

    public function boot(Panel $panel): void
    {
        // Boot logic
    }

    public static function make(): static
    {
        return new static();
    }
}
