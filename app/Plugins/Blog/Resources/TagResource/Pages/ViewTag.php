<?php

namespace App\Plugins\Blog\Resources\TagResource\Pages;

use App\Plugins\Blog\Resources\TagResource\TagResource;
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
