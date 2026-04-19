<?php

namespace Type0\Blog\Filament\Resources\TagResource\Pages;

use Type0\Blog\BlogExtensionManager;
use Type0\Blog\Filament\Resources\TagResource\TagResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditTag extends EditRecord
{
    protected static string $resource = TagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
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
