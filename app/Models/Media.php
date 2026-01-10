<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Zentrale Mediathek - Eigenständiges System ohne externe Abhängigkeiten
 * 
 * Verwendung erfolgt über MediaReferences (Pivot-Tabelle)
 */
class Media extends Model
{
    protected $table = 'custom_media';

    protected $fillable = [
        'uuid',
        'name',
        'file_name',
        'disk',
        'path',
        'mime_type',
        'size',
        'collection_name',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'size' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (Media $media) {
            if (empty($media->uuid)) {
                $media->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Alle Referenzen zu diesem Medium
     */
    public function references(): HasMany
    {
        return $this->hasMany(MediaReference::class, 'media_id');
    }

    /**
     * Hole die vollständige URL zum Medium
     */
    public function getUrl(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }

    /**
     * Hole den vollständigen Pfad auf dem Disk
     */
    public function getPath(): string
    {
        return Storage::disk($this->disk)->path($this->path);
    }

    /**
     * Prüfe ob Datei existiert
     */
    public function exists(): bool
    {
        return Storage::disk($this->disk)->exists($this->path);
    }

    /**
     * Prüft ob dieses Medium über MediaReferences in Verwendung ist
     */
    public function isInUse(): bool
    {
        return $this->references()->exists();
    }

    /**
     * Anzahl der Verwendungen über MediaReferences
     */
    public function usageCount(): int
    {
        return $this->references()->count();
    }

    /**
     * Lösche das Medium und die Datei
     */
    public function deleteWithFile(): bool
    {
        // Prüfe ob noch in Verwendung
        if ($this->isInUse()) {
            return false;
        }

        // Lösche Datei vom Storage
        if ($this->exists()) {
            Storage::disk($this->disk)->delete($this->path);
        }

        // Lösche Datenbank-Eintrag
        return $this->delete();
    }

    /**
     * Hole Dateigröße formatiert
     */
    public function getFormattedSize(): string
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Prüfe ob Medium ein Bild ist
     */
    public function isImage(): bool
    {
        return str_starts_with($this->mime_type ?? '', 'image/');
    }

    /**
     * Prüfe ob Medium ein Video ist
     */
    public function isVideo(): bool
    {
        return str_starts_with($this->mime_type ?? '', 'video/');
    }

    /**
     * Prüfe ob Medium ein PDF ist
     */
    public function isPdf(): bool
    {
        return $this->mime_type === 'application/pdf';
    }
}
