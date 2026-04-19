<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\Media;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;

interface MediaServiceInterface
{
    /**
     * Lade eine Datei hoch und erstelle Media-Eintrag
     */
    public function uploadFile(
        UploadedFile $file,
        string $collection = 'default',
        string $disk = 'public',
        array $metadata = []
    ): Media;

    /**
     * Erstelle Media-Eintrag aus existierender Datei
     */
    public function createFromExisting(
        string $sourcePath,
        string $sourceDisk = 'public',
        string $collection = 'default',
        ?string $originalName = null,
        array $metadata = []
    ): Media;

    /**
     * Verschiebe Datei aus temp-uploads in die Media-Sammlung
     */
    public function moveFromTemporaryUpload(
        string $temporaryPath,
        string $collection = 'media',
        ?string $description = null,
        string $sourceDisk = 'public'
    ): Media;

    /**
     * Lösche Medium (nur wenn nicht in Verwendung)
     */
    public function deleteMedia(Media $media): bool;

    /**
     * Lösche ungenutzte Medien
     */
    public function deleteUnusedMedia(): int;

    /**
     * Hole alle ungenutzten Medien
     */
    public function getUnusedMedia(): Collection;

    /**
     * Hole Statistiken über Medien
     */
    public function getStatistics(): array;

    /**
     * Finde duplizierte Medien
     */
    public function findDuplicates(): array;

    /**
     * Generiere Nutzungsbericht
     */
    public function generateUsageReport(): array;

    /**
     * Erstelle ZIP-Archiv aus Medien
     */
    public function createZipArchive(array $mediaIds, string $zipName): string;

    /**
     * Synchronisiert MediaReferences für HTML Content
     */
    public function syncContentMedia(Model $model, string $content, string $collection = 'content'): int;

    /**
     * Löscht alle MediaReferences für ein Model
     */
    public function deleteAllReferences(Model $model, ?string $collection = null): int;

    /**
     * Hole alle Media-Objekte für ein Model
     */
    public function getMedia(Model $model, ?string $collection = null): Collection;
}
