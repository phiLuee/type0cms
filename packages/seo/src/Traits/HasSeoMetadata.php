<?php

declare(strict_types=1);

namespace Type0\Seo\Traits;

use Type0\Media\Contracts\MediaServiceInterface;
use Type0\Seo\Models\SeoMetadata;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasSeoMetadata
{
    public function seo(): MorphOne
    {
        return $this->morphOne(SeoMetadata::class, 'model');
    }

    public function updateSeo(array $attributes): void
    {
        $this->seo()->updateOrCreate([], $attributes);
    }

    public function getSeoTitle(): ?string
    {
        return $this->seo?->meta_title
            ?? $this->getAttribute('title')
            ?? $this->getAttribute('name');
    }

    public function getSeoDescription(): ?string
    {
        return $this->seo?->meta_description
            ?? $this->getAttribute('excerpt');
    }

    public function getSeoOgImage(): ?string
    {
        return $this->seo?->getOgImageUrl();
    }

    public function handleSeoOgImageUpload($uploadedPath, ?string $description = null): bool
    {
        if (!$this->seo) {
            $this->seo()->create([]);
        }

        if (!app()->bound(MediaServiceInterface::class)) {
            return false;
        }

        $mediaService = app(MediaServiceInterface::class);

        if (is_array($uploadedPath) && count($uploadedPath) === 0) {
            $oldMedia = $this->seo->getFirstMedia('og_image');
            if ($oldMedia) {
                $this->seo->detachMedia($oldMedia, 'og_image');
            }
            return true;
        }

        if (is_array($uploadedPath) && count($uploadedPath) > 0) {
            $uploadedPath = $uploadedPath[0];
        }

        if (is_string($uploadedPath) && strlen($uploadedPath) > 0) {
            $oldMedia = $this->seo->getFirstMedia('og_image');
            if ($oldMedia) {
                $this->seo->detachMedia($oldMedia, 'og_image');
            }

            if (!$description) {
                $modelName = class_basename($this);
                $title = $this->title ?? $this->name ?? $this->id;
                $description = "{$modelName} OG Image: {$title}";
            }

            $media = $mediaService->moveFromTemporaryUpload(
                $uploadedPath,
                'media',
                $description
            );

            if ($media) {
                $this->seo->attachMedia($media, 'og_image');
                return true;
            }
        }

        return false;
    }
}
