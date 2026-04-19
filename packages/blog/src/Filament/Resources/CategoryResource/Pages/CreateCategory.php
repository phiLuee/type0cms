<?php

namespace Type0\Blog\Filament\Resources\CategoryResource\Pages;

use Type0\Blog\BlogExtensionManager;
use Type0\Blog\Filament\Resources\CategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCategory extends CreateRecord
{
    protected static string $resource = CategoryResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return app(BlogExtensionManager::class)->mutateFormDataBeforeSave($data);
    }

    protected function afterCreate(): void
    {
        app(BlogExtensionManager::class)->afterSave($this->record);
    }
}
