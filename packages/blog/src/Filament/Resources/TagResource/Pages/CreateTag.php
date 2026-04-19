<?php

namespace Type0\Blog\Filament\Resources\TagResource\Pages;

use Type0\Blog\BlogExtensionManager;
use Type0\Blog\Filament\Resources\TagResource\TagResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTag extends CreateRecord
{
    protected static string $resource = TagResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return app(BlogExtensionManager::class)->mutateFormDataBeforeSave($data);
    }

    protected function afterCreate(): void
    {
        app(BlogExtensionManager::class)->afterSave($this->record);
    }
}
