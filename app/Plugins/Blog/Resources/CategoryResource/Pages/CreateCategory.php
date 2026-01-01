<?php

namespace App\Plugins\Blog\Resources\CategoryResource\Pages;

use App\Plugins\Blog\Resources\CategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCategory extends CreateRecord
{
    protected static string $resource = CategoryResource::class;
}
