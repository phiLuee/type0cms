<?php

namespace App\Plugins\Blog\Resources\PostResource\Pages;

use App\Plugins\Blog\Resources\PostResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Log;

class EditPost extends EditRecord
{
    protected static string $resource = PostResource::class;

    protected mixed $cachedOgImageUpload = null;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Entferne og_image aus data, damit FileUpload leer startet
        if (isset($data['seo'])) {
            unset($data['seo']['og_image']);
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        Log::info('EditPost: mutateFormDataBeforeSave', [
            'has_seo' => isset($data['seo']),
            'seo_keys' => isset($data['seo']) ? array_keys($data['seo']) : null,
            'has_og_image' => isset($data['seo']['og_image']),
            'og_image_value' => $data['seo']['og_image'] ?? null,
        ]);

        // Cache og_image Upload für afterSave
        if (isset($data['seo']['og_image'])) {
            $this->cachedOgImageUpload = $data['seo']['og_image'];
            unset($data['seo']['og_image']);
        }

        return $data;
    }

    protected function afterSave(): void
    {
        Log::info('EditPost: afterSave called', [
            'has_cached' => isset($this->cachedOgImageUpload),
            'cached_value' => $this->cachedOgImageUpload ?? 'NOT SET',
        ]);

        // Verarbeite OG Image Upload via Model-Methode
        if (isset($this->cachedOgImageUpload)) {
            Log::info('EditPost: Calling handleSeoOgImageUpload');

            $result = $this->record->handleSeoOgImageUpload(
                $this->cachedOgImageUpload,
                'Post OG Image: ' . $this->record->title
            );

            Log::info('EditPost: handleSeoOgImageUpload result', ['success' => $result]);
        }
    }
}
