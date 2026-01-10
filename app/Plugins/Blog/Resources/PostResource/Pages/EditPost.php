<?php

namespace App\Plugins\Blog\Resources\PostResource\Pages;

use App\Plugins\Blog\Resources\PostResource;
use App\Services\MediaService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPost extends EditRecord
{
    protected static string $resource = PostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        // OG Image Upload verarbeiten
        $this->handleOgImageUpload();
    }

    protected function handleOgImageUpload(): void
    {
        \Log::info('EditPost: handleOgImageUpload called', [
            'has_data' => isset($this->data),
            'has_seo_data' => isset($this->data['seo']),
        ]);

        // Zugriff auf SEO-Daten aus dem verschachtelten Formular
        $seoData = $this->data['seo'] ?? [];
        $ogImagePath = $seoData['og_image'] ?? null;

        \Log::info('EditPost: og_image path', [
            'path' => $ogImagePath,
            'is_string' => is_string($ogImagePath),
            'has_seo' => $this->record->seo !== null,
        ]);

        if (!$ogImagePath || !$this->record->seo) {
            \Log::info('EditPost: Skipped - no path or no seo');
            return;
        }

        // Wenn es ein neues Upload ist (string path)
        if (is_string($ogImagePath)) {
            // Alte Referenz entfernen
            $oldMedia = $this->record->seo->getFirstMedia('og_image');
            if ($oldMedia) {
                $this->record->seo->detachMedia($oldMedia, 'og_image');
            }

            // Erstelle Media aus Upload
            $mediaService = app(MediaService::class);
            $media = $mediaService->createFromExisting(
                sourcePath: $ogImagePath,
                sourceDisk: 'public',
                collection: 'og_image',
                originalName: basename($ogImagePath)
            );

            if ($media) {
                \Log::info('EditPost: Media created', ['media_id' => $media->id]);

                // Verknüpfe mit SEO
                $this->record->seo->attachMedia($media, 'og_image');

                // Lösche og_image Wert aus seo_metadata (nicht mehr nötig)
                $this->record->seo->update(['og_image' => null]);

                \Log::info('EditPost: Media attached and og_image cleared');
            } else {
                \Log::warning('EditPost: Media creation failed', ['path' => $ogImagePath]);
            }
        }
    }
}
