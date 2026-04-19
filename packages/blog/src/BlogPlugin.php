<?php

namespace Type0\Blog;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Type0\Blog\Filament\Resources\PostResource;
use Type0\Blog\Filament\Resources\CategoryResource;
use Type0\Blog\Filament\Resources\TagResource\TagResource;

class BlogPlugin implements Plugin
{
    public function getId(): string
    {
        return 'blog';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([
            PostResource::class,
            CategoryResource::class,
            TagResource::class,
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
