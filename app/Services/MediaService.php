<?php

declare(strict_types=1);

namespace App\Services;

use App\Events\Media\MediaDeleted;
use App\Events\Media\MediaReferencesSynced;
use App\Events\Media\MediaUploaded;
use App\Exceptions\Media\InvalidMediaFileException;
use App\Exceptions\Media\MediaInUseException;
use App\Exceptions\Media\MediaNotFoundException;
use App\Exceptions\Media\MediaStorageException;
use App\Models\Media;
use App\Models\MediaReference;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

/**
 * Service für Upload, Verwaltung, Referenzierung und Löschung von Medien
 */
class MediaService
{
    /**
     * Erlaubte MIME-Typen für Sicherheit
     */
    private function getAllowedMimeTypes(): array
    {
        return config('media.allowed_mime_types', [
            // Images
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',

            // Documents
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            // Videos
            'video/mp4',
            'video/webm',
            'video/ogg',
        ]);
    }

    /**
     * Maximale Dateigröße in Bytes
     */
    private function getMaxFileSize(): int
    {
        return config('media.max_file_size', 50 * 1024 * 1024);
    }
    /**
     * Lade eine Datei hoch und erstelle Media-Eintrag
     *
     * @throws InvalidMediaFileException
     * @throws MediaStorageException
     */
    public function uploadFile(
        UploadedFile $file,
        string $collection = 'default',
        string $disk = 'public',
        array $metadata = []
    ): Media {
        // Validierung
        $this->validateFile($file);

        // Generiere eindeutigen Dateinamen
        $extension = $file->getClientOriginalExtension();
        $fileName = Str::ulid() . '.' . $extension;

        try {
            return DB::transaction(function () use ($file, $fileName, $disk, $collection, $metadata) {
                // Speichere Datei
                $path = $file->storeAs('media', $fileName, $disk);

                if (!$path) {
                    throw MediaStorageException::uploadFailed('Storage returned false');
                }

                // Erstelle Media-Eintrag
                $media = Media::create([
                    'name' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                    'file_name' => $file->getClientOriginalName(),
                    'disk' => $disk,
                    'path' => $path,
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'collection_name' => $collection,
                    'metadata' => $metadata,
                ]);

                // Event dispatchen
                MediaUploaded::dispatch($media, $collection);

                Log::info('Media uploaded successfully', [
                    'media_id' => $media->id,
                    'file_name' => $media->file_name,
                    'collection' => $collection,
                ]);

                return $media;
            });
        } catch (\Exception $e) {
            // Cleanup bei Fehler
            if (isset($path) && Storage::disk($disk)->exists($path)) {
                Storage::disk($disk)->delete($path);
            }

            Log::error('Media upload failed', [
                'file_name' => $file->getClientOriginalName(),
                'error' => $e->getMessage(),
            ]);

            throw MediaStorageException::uploadFailed($e->getMessage());
        }
    }

    /**
     * Validiere Upload-Datei
     *
     * @throws InvalidMediaFileException
     */
    private function validateFile(UploadedFile $file): void
    {
        // Prüfe MIME-Type
        $mimeType = $file->getMimeType();
        $allowedMimeTypes = $this->getAllowedMimeTypes();

        if (!in_array($mimeType, $allowedMimeTypes, true)) {
            throw InvalidMediaFileException::invalidMimeType($mimeType, $allowedMimeTypes);
        }

        // Prüfe Dateigröße
        $maxFileSize = $this->getMaxFileSize();
        if ($file->getSize() > $maxFileSize) {
            throw InvalidMediaFileException::fileTooLarge($file->getSize(), $maxFileSize);
        }
    }

    /**
     * Erstelle Media-Eintrag aus existierender Datei (z.B. aus temp-uploads)
     *
     * @throws InvalidMediaFileException
     * @throws MediaStorageException
     */
    public function createFromExisting(
        string $sourcePath,
        string $sourceDisk = 'public',
        string $collection = 'default',
        ?string $originalName = null,
        array $metadata = []
    ): Media {
        if (!Storage::disk($sourceDisk)->exists($sourcePath)) {
            throw InvalidMediaFileException::fileNotFound($sourcePath);
        }

        // Generiere neuen Dateinamen
        $originalFileName = $originalName ?? basename($sourcePath);
        $extension = pathinfo($originalFileName, PATHINFO_EXTENSION);
        $newFileName = Str::ulid() . '.' . $extension;
        $newPath = 'media/' . $newFileName;

        try {
            return DB::transaction(function () use ($sourcePath, $sourceDisk, $newPath, $originalFileName, $collection, $metadata) {
                // Verschiebe in media Verzeichnis
                if (!Storage::disk($sourceDisk)->move($sourcePath, $newPath)) {
                    throw MediaStorageException::moveFailed($sourcePath, $newPath);
                }

                // Erstelle Media-Eintrag
                $media = Media::create([
                    'name' => pathinfo($originalFileName, PATHINFO_FILENAME),
                    'file_name' => $originalFileName,
                    'disk' => $sourceDisk,
                    'path' => $newPath,
                    'mime_type' => Storage::disk($sourceDisk)->mimeType($newPath),
                    'size' => Storage::disk($sourceDisk)->size($newPath),
                    'collection_name' => $collection,
                    'metadata' => $metadata,
                ]);

                MediaUploaded::dispatch($media, $collection);

                Log::info('Media created from existing file', [
                    'media_id' => $media->id,
                    'source_path' => $sourcePath,
                ]);

                return $media;
            });
        } catch (\Exception $e) {
            // Rollback: Versuche Datei zurückzubewegen wenn möglich
            if (Storage::disk($sourceDisk)->exists($newPath)) {
                Storage::disk($sourceDisk)->move($newPath, $sourcePath);
            }

            Log::error('Failed to create media from existing file', [
                'source_path' => $sourcePath,
                'error' => $e->getMessage(),
            ]);

            if ($e instanceof MediaStorageException) {
                throw $e;
            }

            throw MediaStorageException::moveFailed($sourcePath, $newPath);
        }
    }

    /**
     * VerschiebeDatei aus temp-uploads Verzeichnis in die Media-Sammlung
     * und erstelle Media-Eintrag.
     *
     * @param string $temporaryPath Relativer Pfad im Disk (z.B. "temp-uploads/abc.jpg")
     * @param string $collection Ziel-Collection (default: 'media')
     * @param string|null $description Optionale Beschreibung
     * @param string $sourceDisk Disk, auf der die Datei liegt
     * @return Media
     * @throws InvalidMediaFileException
     * @throws MediaStorageException
     */
    public function moveFromTemporaryUpload(
        string $temporaryPath,
        string $collection = 'media',
        ?string $description = null,
        string $sourceDisk = 'public'
    ): Media {
        $originalName = basename($temporaryPath);

        $media = $this->createFromExisting(
            $temporaryPath,
            $sourceDisk,
            $collection,
            $originalName
        );

        // Beschreibung separat in Metadata speichern
        if ($description) {
            $media->update([
                'metadata' => array_merge($media->metadata ?? [], ['description' => $description]),
            ]);
        }

        return $media;
    }

    /**
     * Lösche Medium (nur wenn nicht in Verwendung)
     *
     * @throws MediaInUseException
     */
    public function deleteMedia(Media $media): bool
    {
        if ($media->isInUse()) {
            throw new MediaInUseException($media);
        }

        $mediaId = $media->id;
        $fileName = $media->file_name;
        $collection = $media->collection_name;

        $deleted = $media->deleteWithFile();

        if ($deleted) {
            MediaDeleted::dispatch($mediaId, $fileName, $collection);

            Log::info('Media deleted successfully', [
                'media_id' => $mediaId,
                'file_name' => $fileName,
            ]);
        }

        return $deleted;
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
            try {
                if ($media->deleteWithFile()) {
                    MediaDeleted::dispatch($media->id, $media->file_name, $media->collection_name);
                    $deleted++;
                }
            } catch (\Exception $e) {
                Log::warning('Failed to delete unused media', [
                    'media_id' => $media->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('Deleted unused media', ['count' => $deleted]);

        return $deleted;
    }

    /**
     * Hole alle ungenutzten Medien
     */
    public function getUnusedMedia(): Collection
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
        // Finde Duplikate per SQL statt alle Zeilen in den Speicher zu laden
        $duplicateKeys = Media::query()
            ->selectRaw('size, name, COUNT(*) as cnt')
            ->groupBy('size', 'name')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        if ($duplicateKeys->isEmpty()) {
            return [];
        }

        $duplicates = [];
        foreach ($duplicateKeys as $dup) {
            $group = Media::where('size', $dup->size)->where('name', $dup->name)->get();
            $duplicates[] = [
                'key' => $dup->size . '_' . $dup->name,
                'media' => $group,
            ];
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
     *
     * @throws MediaStorageException
     */
    public function createZipArchive(array $mediaIds, string $zipName): string
    {
        $zip = new ZipArchive();
        $zipPath = storage_path('app/temp/' . $zipName);

        // Erstelle temp Verzeichnis falls nicht vorhanden
        if (!is_dir(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw MediaStorageException::zipCreationFailed('Could not open ZIP file');
        }

        try {
            $media = Media::whereIn('id', $mediaIds)->get();

            if ($media->isEmpty()) {
                throw MediaStorageException::zipCreationFailed('No media found for given IDs');
            }

            foreach ($media as $mediaItem) {
                $filePath = Storage::disk($mediaItem->disk)->path($mediaItem->path);
                if (file_exists($filePath)) {
                    $zip->addFile($filePath, $mediaItem->file_name);
                }
            }

            $zip->close();

            Log::info('ZIP archive created', [
                'zip_name' => $zipName,
                'media_count' => $media->count(),
            ]);

            return $zipPath;
        } catch (\Exception $e) {
            $zip->close();
            if (file_exists($zipPath)) {
                unlink($zipPath);
            }

            Log::error('ZIP archive creation failed', [
                'zip_name' => $zipName,
                'error' => $e->getMessage(),
            ]);

            throw MediaStorageException::zipCreationFailed($e->getMessage());
        }
    }

    /**
     * Synchronisiert MediaReferences für HTML Content
     * Findet alle Media-URLs im Content und erstellt entsprechende References
     *
     * @param Model $model Das Model (z.B. Post)
     * @param string $content HTML Content
     * @param string $collection Collection-Name (z.B. 'content')
     * @return int Anzahl synchronisierter Medien
     */
    public function syncContentMedia(Model $model, string $content, string $collection = 'content'): int
    {
        return DB::transaction(function () use ($model, $content, $collection) {
            // Lösche alte Referenzen für diese Collection
            MediaReference::where('model_type', get_class($model))
                ->where('model_id', $model->id)
                ->where('collection_name', $collection)
                ->delete();

            // Finde alle Media-URLs im Content
            preg_match_all('/\/storage\/media\/([^"\'>\s]+)/', $content, $matches);

            if (empty($matches[1])) {
                return 0;
            }

            $processedIds = [];

            foreach ($matches[1] as $filename) {
                $path = 'media/' . $filename;
                $media = Media::where('path', $path)->first();

                if ($media && !in_array($media->id, $processedIds, true)) {
                    MediaReference::create([
                        'media_id' => $media->id,
                        'model_type' => get_class($model),
                        'model_id' => $model->id,
                        'collection_name' => $collection,
                    ]);

                    $processedIds[] = $media->id;
                }
            }

            MediaReferencesSynced::dispatch($model, $collection, count($processedIds));

            Log::info('Media references synced', [
                'model_type' => get_class($model),
                'model_id' => $model->id,
                'collection' => $collection,
                'count' => count($processedIds),
            ]);

            return count($processedIds);
        });
    }

    /**
     * Löscht alle MediaReferences für ein Model
     *
     * @param Model $model
     * @param string|null $collection Optional: nur für bestimmte Collection
     * @return int Anzahl gelöschter References
     */
    public function deleteAllReferences(Model $model, ?string $collection = null): int
    {
        $query = MediaReference::where('model_type', get_class($model))
            ->where('model_id', $model->id);

        if ($collection) {
            $query->where('collection_name', $collection);
        }

        return $query->delete();
    }

    /**
     * Hole alle Media-Objekte für ein Model
     *
     * @param Model $model
     * @param string|null $collection
     * @return Collection
     */
    public function getMedia(Model $model, ?string $collection = null): Collection
    {
        $query = MediaReference::where('model_type', get_class($model))
            ->where('model_id', $model->id);

        if ($collection) {
            $query->where('collection_name', $collection);
        }

        return $query->with('media')->get()->pluck('media');
    }
}
