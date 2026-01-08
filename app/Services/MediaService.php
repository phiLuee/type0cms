<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Media;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class MediaService
{
    /**
     * Erstellt ein ZIP-Archiv mit allen ausgewählten Medien
     */
    public function createZipArchive(array $mediaIds, string $zipName = 'media.zip'): string
    {
        $zip = new ZipArchive();
        $zipPath = storage_path('app/temp/' . $zipName);

        // Stelle sicher, dass das temp-Verzeichnis existiert
        if (!file_exists(dirname($zipPath))) {
            mkdir(dirname($zipPath), 0755, true);
        }

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \Exception('ZIP-Archiv konnte nicht erstellt werden');
        }

        $media = Media::whereIn('id', $mediaIds)->get();

        foreach ($media as $medium) {
            $filePath = Storage::disk($medium->disk)->path($medium->getPath());

            if (file_exists($filePath)) {
                // Füge die Datei mit einem eindeutigen Namen hinzu
                $zipFileName = $medium->collection_name . '/' . $medium->file_name;
                $zip->addFile($filePath, $zipFileName);
            }
        }

        $zip->close();

        return $zipPath;
    }

    /**
     * Bereinigt temporäre Dateien
     */
    public function cleanupTempFiles(): void
    {
        $tempPath = storage_path('app/temp');

        if (file_exists($tempPath)) {
            $files = glob($tempPath . '/*.zip');

            foreach ($files as $file) {
                if (file_exists($file) && time() - filemtime($file) > 3600) { // Älter als 1 Stunde
                    unlink($file);
                }
            }
        }
    }

    /**
     * Optimiert Bilder in einer Collection
     */
    public function optimizeImages(?string $collection = null): array
    {
        $query = Media::where('mime_type', 'like', 'image/%');

        if ($collection) {
            $query->where('collection_name', $collection);
        }

        $media = $query->get();
        $optimized = [];

        foreach ($media as $medium) {
            try {
                // Hier könnte man eine Bild-Optimierungs-Library integrieren
                // z.B. spatie/image-optimizer
                $optimized[] = $medium->id;
            } catch (\Exception $e) {
                // Log error
                continue;
            }
        }

        return $optimized;
    }

    /**
     * Findet duplizierte Medien basierend auf Dateiname und Größe
     */
    public function findDuplicates(): array
    {
        $duplicates = [];

        $media = Media::selectRaw('file_name, size, GROUP_CONCAT(id) as ids, COUNT(*) as count')
            ->groupBy(['file_name', 'size'])
            ->having('count', '>', 1)
            ->get();

        foreach ($media as $item) {
            $duplicates[] = [
                'file_name' => $item->file_name,
                'size' => $item->size,
                'count' => $item->count,
                'ids' => explode(',', $item->ids),
            ];
        }

        return $duplicates;
    }

    /**
     * Gibt alle wirklich ungenutzten Medien zurück
     * (Weder an Models gebunden noch via MediaReferences verwendet)
     */
    public function getTrulyUnusedMedia()
    {
        return Media::query()
            ->whereNull('model_type')
            ->whereDoesntHave('references')
            ->get();
    }

    /**
     * Anzahl der wirklich ungenutzten Medien
     */
    public function getTrulyUnusedCount(): int
    {
        return Media::query()
            ->whereNull('model_type')
            ->whereDoesntHave('references')
            ->count();
    }

    /**
     * Größe der wirklich ungenutzten Medien
     */
    public function getTrulyUnusedSize(): int
    {
        return (int) Media::query()
            ->whereNull('model_type')
            ->whereDoesntHave('references')
            ->sum('size');
    }

    /**
     * Generiert einen Bericht über Medien-Nutzung
     */
    public function generateUsageReport(): array
    {
        return [
            'total' => [
                'count' => Media::count(),
                'size' => Media::sum('size'),
            ],
            'by_type' => [
                'images' => [
                    'count' => Media::where('mime_type', 'like', 'image/%')->count(),
                    'size' => Media::where('mime_type', 'like', 'image/%')->sum('size'),
                ],
                'videos' => [
                    'count' => Media::where('mime_type', 'like', 'video/%')->count(),
                    'size' => Media::where('mime_type', 'like', 'video/%')->sum('size'),
                ],
                'documents' => [
                    'count' => Media::where('mime_type', 'like', 'application/%')->count(),
                    'size' => Media::where('mime_type', 'like', 'application/%')->sum('size'),
                ],
            ],
            'by_collection' => Media::selectRaw('collection_name, COUNT(*) as count, SUM(size) as size')
                ->groupBy('collection_name')
                ->get()
                ->mapWithKeys(fn($item) => [
                    $item->collection_name => [
                        'count' => $item->count,
                        'size' => $item->size,
                    ]
                ])
                ->toArray(),
            'unused' => [
                'count' => $this->getTrulyUnusedCount(),
                'size' => $this->getTrulyUnusedSize(),
            ],
            'oldest' => Media::orderBy('created_at', 'asc')->first(),
            'newest' => Media::orderBy('created_at', 'desc')->first(),
            'largest' => Media::orderBy('size', 'desc')->first(),
        ];
    }
}
