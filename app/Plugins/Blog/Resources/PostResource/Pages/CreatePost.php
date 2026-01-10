<?php

namespace App\Plugins\Blog\Resources\PostResource\Pages;

use App\Plugins\Blog\Resources\PostResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreatePost extends CreateRecord
{
    protected static string $resource = PostResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::id();

        // Cache og_image Upload für afterCreate
        if (isset($data['seo']['og_image'])) {
            $this->cachedOgImageUpload = $data['seo']['og_image'];
            unset($data['seo']['og_image']);
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        // Verarbeite OG Image Upload via Model-Methode
        if (isset($this->cachedOgImageUpload)) {
            $this->record->handleSeoOgImageUpload(
                $this->cachedOgImageUpload,
                'Post OG Image: ' . $this->record->title
            );
        }
    }
}
