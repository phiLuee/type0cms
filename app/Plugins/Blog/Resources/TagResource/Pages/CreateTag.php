<?php

namespace App\Plugins\Blog\Resources\TagResource\Pages;

use App\Plugins\Blog\Resources\TagResource\TagResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTag extends CreateRecord
{
    protected static string $resource = TagResource::class;
}
