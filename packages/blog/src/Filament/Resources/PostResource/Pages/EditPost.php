<?php

namespace Type0\Blog\Filament\Resources\PostResource\Pages;

use Type0\Blog\BlogExtensionManager;
use Type0\Media\Contracts\MediaServiceInterface;
use Type0\Blog\Filament\Resources\PostResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPost extends EditRecord
{
    protected static string $resource = PostResource::class;

    protected ?string $cachedFeaturedImageUpload = null;

    protected function getMediaService(): MediaServiceInterface
    {
        return app(MediaServiceInterface::class);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data = app(BlogExtensionManager::class)->mutateFormDataBeforeFill($data);

        unset($data['featured_image']);

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data = app(BlogExtensionManager::class)->mutateFormDataBeforeSave($data);

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
        app(BlogExtensionManager::class)->afterSave($this->record);

        if ($this->cachedFeaturedImageUpload) {
            $oldMedia = $this->record->getFirstMedia('featured_image');
            if ($oldMedia) {
                $this->record->detachMedia($oldMedia, 'featured_image');
            }

            $media = $this->getMediaService()->moveFromTemporaryUpload(
                $this->cachedFeaturedImageUpload,
                'media',
                null,
                'public'
            );
            $this->record->attachMedia($media, 'featured_image');
        }

        if ($this->record->content) {
            $this->getMediaService()->syncContentMedia(
                $this->record,
                $this->record->content,
                'content'
            );
        }
    }
}
