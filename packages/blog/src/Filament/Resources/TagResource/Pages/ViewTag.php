<?php

namespace Type0\Blog\Filament\Resources\TagResource\Pages;

use Type0\Blog\Filament\Resources\TagResource\TagResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewTag extends ViewRecord
{
    protected static string $resource = TagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
