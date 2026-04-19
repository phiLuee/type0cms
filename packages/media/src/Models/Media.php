<?php

namespace Type0\Media\Models;

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
    protected $table = 'media';

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

    public function references(): HasMany
    {
        return $this->hasMany(MediaReference::class, 'media_id');
    }

    public function getUrl(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }

    public function getPath(): string
    {
        return Storage::disk($this->disk)->path($this->path);
    }

    public function exists(): bool
    {
        return Storage::disk($this->disk)->exists($this->path);
    }

    public function isInUse(): bool
    {
        return $this->references()->exists();
    }

    public function usageCount(): int
    {
        return $this->references()->count();
    }

    public function deleteWithFile(): bool
    {
        if ($this->isInUse()) {
            return false;
        }

        if ($this->exists()) {
            Storage::disk($this->disk)->delete($this->path);
        }

        return $this->delete();
    }

    public function getFormattedSize(): string
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type ?? '', 'image/');
    }

    public function isVideo(): bool
    {
        return str_starts_with($this->mime_type ?? '', 'video/');
    }

    public function isPdf(): bool
    {
        return $this->mime_type === 'application/pdf';
    }
}
