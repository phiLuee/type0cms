<?php

namespace App\Plugins\Blog\Resources\PostResource\Pages;

use App\Contracts\MediaServiceInterface;
use App\Plugins\Blog\Resources\PostResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreatePost extends CreateRecord
{
    protected static string $resource = PostResource::class;

    protected ?string $cachedOgImageUpload = null;
    protected ?string $cachedFeaturedImageUpload = null;

    public function __construct(
        private readonly MediaServiceInterface $mediaService,
    ) {}

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::id();

        // Cache og_image Upload für afterCreate
        if (isset($data['seo']['og_image'])) {
            $this->cachedOgImageUpload = is_array($data['seo']['og_image']) ? ($data['seo']['og_image'][0] ?? null) : $data['seo']['og_image'];
            unset($data['seo']['og_image']);
        }

        // Cache featured_image Upload für afterCreate
        if (isset($data['featured_image'])) {
            $value = is_array($data['featured_image']) ? ($data['featured_image'][0] ?? null) : $data['featured_image'];
            if (is_string($value) && str_contains($value, 'livewire-tmp')) {
                $this->cachedFeaturedImageUpload = $value;
            }
            unset($data['featured_image']);
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        // Verarbeite OG Image Upload via Model-Methode
        if (isset($this->cachedOgImageUpload)) {
            $this->record->handleSeoOgImageUpload(
                $this->cachedOgImageUpload,
                'Post OG Image: ' . $this->record->title
            );
        }

        // Verarbeite Featured Image Upload
        if ($this->cachedFeaturedImageUpload) {
            $media = $this->mediaService->moveFromTemporaryUpload(
                $this->cachedFeaturedImageUpload,
                'media',
                null,
                'public'
            );
            $this->record->attachMedia($media, 'featured_image');
        }

        // Content-Medien synchronisieren
        if ($this->record->content) {
            $this->mediaService->syncContentMedia(
                $this->record,
                $this->record->content,
                'content'
            );
        }
    }
}
