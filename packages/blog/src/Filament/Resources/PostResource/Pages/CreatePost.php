<?php

namespace Type0\Blog\Filament\Resources\PostResource\Pages;

use Type0\Blog\BlogExtensionManager;
use Type0\Media\Contracts\MediaServiceInterface;
use Type0\Blog\Filament\Resources\PostResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreatePost extends CreateRecord
{
    protected static string $resource = PostResource::class;

    protected ?string $cachedFeaturedImageUpload = null;

    protected function getMediaService(): MediaServiceInterface
    {
        return app(MediaServiceInterface::class);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::id();

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

    protected function afterCreate(): void
    {
        app(BlogExtensionManager::class)->afterSave($this->record);

        if ($this->cachedFeaturedImageUpload) {
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
