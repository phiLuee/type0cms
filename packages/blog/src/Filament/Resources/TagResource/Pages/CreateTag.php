<?php

namespace Type0\Blog\Filament\Resources\TagResource\Pages;

use Type0\Blog\Filament\Resources\TagResource\TagResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTag extends CreateRecord
{
    protected static string $resource = TagResource::class;
}
