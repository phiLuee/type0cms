<?php

namespace App\Plugins\Blog\Resources\PostResource\Pages;

use App\Plugins\Blog\Resources\PostResource;
use App\Services\MediaService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreatePost extends CreateRecord
{
    protected static string $resource = PostResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::id();

        return $data;
    }

    protected function afterCreate(): void
    {
        // OG Image Upload verarbeiten
        $this->handleOgImageUpload();
    }

    protected function handleOgImageUpload(): void
    {
        \Log::info('CreatePost: handleOgImageUpload called', [
            'has_data' => isset($this->data),
            'has_seo_data' => isset($this->data['seo']),
        ]);

        // Zugriff auf SEO-Daten aus dem verschachtelten Formular
        $seoData = $this->data['seo'] ?? [];
        $ogImagePath = $seoData['og_image'] ?? null;

        \Log::info('CreatePost: og_image path', [
            'path' => $ogImagePath,
            'has_seo' => $this->record->seo !== null,
        ]);

        if (!$ogImagePath || !$this->record->seo) {
            \Log::info('CreatePost: Skipped - no path or no seo');
            return;
        }

        // Prüfe ob es ein temporärer Upload ist (String-Pfad)
        if (!is_string($ogImagePath)) {
            return;
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
            \Log::info('CreatePost: Media created', ['media_id' => $media->id]);

            // Verknüpfe mit SEO
            $this->record->seo->attachMedia($media, 'og_image');

            // Lösche og_image Wert aus seo_metadata (nicht mehr nötig)
            $this->record->seo->update(['og_image' => null]);

            \Log::info('CreatePost: Media attached and og_image cleared');
        } else {
            \Log::warning('CreatePost: Media creation failed', ['path' => $ogImagePath]);
        }
    }
}
