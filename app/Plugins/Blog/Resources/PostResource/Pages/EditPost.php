<?php

namespace App\Plugins\Blog\Resources\PostResource\Pages;

use App\Plugins\Blog\Resources\PostResource;
use App\Services\MediaService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPost extends EditRecord
{
    protected static string $resource = PostResource::class;

    protected mixed $cachedOgImageUpload = null;
    protected ?string $cachedFeaturedImageUpload = null;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Entferne og_image und featured_image aus data, damit FileUpload leer startet
        if (isset($data['seo'])) {
            unset($data['seo']['og_image']);
        }
        unset($data['featured_image']);

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Cache og_image Upload für afterSave
        if (isset($data['seo']['og_image'])) {
            $this->cachedOgImageUpload = $data['seo']['og_image'];
            unset($data['seo']['og_image']);
        }

        // Cache featured_image Upload für afterSave
        if (isset($data['featured_image'])) {
            $value = is_array($data['featured_image']) ? ($data['featured_image'][0] ?? null) : $data['featured_image'];
            if (is_string($value) && str_contains($value, 'livewire-tmp')) {
                $this->cachedFeaturedImageUpload = $value;
            }
            unset($data['featured_image']);
        }

        return $data;
    }

    protected function afterSave(): void
    {
        $mediaService = app(MediaService::class);

        // Verarbeite OG Image Upload via Model-Methode
        if (isset($this->cachedOgImageUpload)) {
            $this->record->handleSeoOgImageUpload(
                $this->cachedOgImageUpload,
                'Post OG Image: ' . $this->record->title
            );
        }

        // Verarbeite Featured Image Upload
        if ($this->cachedFeaturedImageUpload) {
            // Entferne alte Referenz
            $oldMedia = $this->record->getFirstMedia('featured_image');
            if ($oldMedia) {
                $this->record->detachMedia($oldMedia, 'featured_image');
            }

            $media = $mediaService->moveFromTemporaryUpload(
                $this->cachedFeaturedImageUpload,
                'media',
                null,
                'public'
            );
            $this->record->attachMedia($media, 'featured_image');
        }

        // Content-Medien synchronisieren
        if ($this->record->content) {
            $mediaService->syncContentMedia(
                $this->record,
                $this->record->content,
                'content'
            );
        }
    }
}
