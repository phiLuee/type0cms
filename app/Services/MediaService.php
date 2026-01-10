<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Service für Upload, Verwaltung und Löschung von Medien
 */
class MediaService
{
    /**
     * Lade eine Datei hoch und erstelle Media-Eintrag
     */
    public function uploadFile(
        UploadedFile $file,
        string $collection = 'default',
        string $disk = 'public',
        array $metadata = []
    ): Media {
        // Generiere eindeutigen Dateinamen
        $extension = $file->getClientOriginalExtension();
        $fileName = Str::ulid() . '.' . $extension;

        // Speichere Datei
        $path = $file->storeAs('media', $fileName, $disk);

        // Erstelle Media-Eintrag
        return Media::create([
            'name' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            'file_name' => $file->getClientOriginalName(),
            'disk' => $disk,
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'collection_name' => $collection,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Erstelle Media-Eintrag aus existierender Datei (z.B. aus temp-uploads)
     */
    public function createFromExisting(
        string $sourcePath,
        string $sourceDisk = 'public',
        string $collection = 'default',
        ?string $originalName = null,
        array $metadata = []
    ): ?Media {
        if (!Storage::disk($sourceDisk)->exists($sourcePath)) {
            return null;
        }

        // Generiere neuen Dateinamen
        $originalFileName = $originalName ?? basename($sourcePath);
        $extension = pathinfo($originalFileName, PATHINFO_EXTENSION);
        $newFileName = Str::ulid() . '.' . $extension;

        // Verschiebe in media Verzeichnis
        $newPath = 'media/' . $newFileName;
        Storage::disk($sourceDisk)->move($sourcePath, $newPath);

        // Erstelle Media-Eintrag
        return Media::create([
            'name' => pathinfo($originalFileName, PATHINFO_FILENAME),
            'file_name' => $originalFileName,
            'disk' => $sourceDisk,
            'path' => $newPath,
            'mime_type' => Storage::disk($sourceDisk)->mimeType($newPath),
            'size' => Storage::disk($sourceDisk)->size($newPath),
            'collection_name' => $collection,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Lösche Medium (nur wenn nicht in Verwendung)
     */
    public function deleteMedia(Media $media): bool
    {
        return $media->deleteWithFile();
    }

    /**
     * Lösche ungenutzte Medien
     */
    public function deleteUnusedMedia(): int
    {
        $unusedMedia = Media::query()
            ->whereDoesntHave('references')
            ->get();

        $deleted = 0;
        foreach ($unusedMedia as $media) {
            if ($media->deleteWithFile()) {
                $deleted++;
            }
        }

        return $deleted;
    }

    /**
     * Hole alle ungenutzten Medien
     */
    public function getUnusedMedia()
    {
        return Media::query()
            ->whereDoesntHave('references')
            ->get();
    }

    /**
     * Hole Statistiken über Medien
     */
    public function getStatistics(): array
    {
        return [
            'total_count' => Media::count(),
            'total_size' => Media::sum('size'),
            'unused_count' => Media::whereDoesntHave('references')->count(),
            'unused_size' => Media::whereDoesntHave('references')->sum('size'),
            'by_collection' => Media::query()
                ->selectRaw('collection_name, count(*) as count, sum(size) as size')
                ->groupBy('collection_name')
                ->get()
                ->keyBy('collection_name')
                ->map(fn($item) => [
                    'count' => $item->count,
                    'size' => $item->size,
                ])
                ->toArray(),
        ];
    }

    /**
     * Finde duplizierte Medien (basierend auf Dateigröße und Namen)
     */
    public function findDuplicates(): array
    {
        $media = Media::all();
        $duplicates = [];

        $grouped = $media->groupBy(function ($item) {
            return $item->size . '_' . $item->name;
        });

        foreach ($grouped as $key => $group) {
            if ($group->count() > 1) {
                $duplicates[] = [
                    'key' => $key,
                    'media' => $group,
                ];
            }
        }

        return $duplicates;
    }

    /**
     * Generiere Nutzungsbericht
     */
    public function generateUsageReport(): array
    {
        $media = Media::with('references.model')->get();

        return [
            'total' => $media->count(),
            'used' => $media->filter(fn($m) => $m->references->count() > 0)->count(),
            'unused' => $media->filter(fn($m) => $m->references->count() === 0)->count(),
            'by_collection' => $media->groupBy('collection_name')->map(function ($group, $collection) {
                return [
                    'collection' => $collection,
                    'count' => $group->count(),
                    'used' => $group->filter(fn($m) => $m->references->count() > 0)->count(),
                    'unused' => $group->filter(fn($m) => $m->references->count() === 0)->count(),
                    'total_size' => $group->sum('size'),
                ];
            })->values()->toArray(),
            'most_used' => $media->sortByDesc(fn($m) => $m->references->count())->take(10)->values(),
        ];
    }

    /**
     * Erstelle ZIP-Archiv aus Medien
     */
    public function createZipArchive(array $mediaIds, string $zipName): string
    {
        $zip = new \ZipArchive();
        $zipPath = storage_path('app/temp/' . $zipName);

        // Erstelle temp Verzeichnis falls nicht vorhanden
        if (!is_dir(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new \Exception('Could not create ZIP archive');
        }

        $media = Media::whereIn('id', $mediaIds)->get();

        foreach ($media as $mediaItem) {
            $filePath = Storage::disk($mediaItem->disk)->path($mediaItem->path);
            if (file_exists($filePath)) {
                $zip->addFile($filePath, $mediaItem->file_name);
            }
        }

        $zip->close();

        return $zipPath;
    }
}
