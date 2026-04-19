<?php

namespace Type0\Blog\Filament\Resources\CategoryResource\Pages;

use Type0\Blog\BlogExtensionManager;
use Type0\Blog\Filament\Resources\CategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCategory extends EditRecord
{
    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        return app(BlogExtensionManager::class)->mutateFormDataBeforeFill($data);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return app(BlogExtensionManager::class)->mutateFormDataBeforeSave($data);
    }

    protected function afterSave(): void
    {
        app(BlogExtensionManager::class)->afterSave($this->record);
    }
}
