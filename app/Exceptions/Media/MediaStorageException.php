<?php

declare(strict_types=1);

namespace App\Exceptions\Media;

/**
 * Exception für Speicher-/Dateisystem-Fehler
 */
class MediaStorageException extends MediaException
{
    public static function uploadFailed(string $reason): self
    {
        return new self("Media upload failed: {$reason}");
    }

    public static function moveFailed(string $from, string $to): self
    {
        return new self("Failed to move file from '{$from}' to '{$to}'");
    }

    public static function deleteFailed(string $path): self
    {
        return new self("Failed to delete file at '{$path}'");
    }

    public static function zipCreationFailed(string $reason): self
    {
        return new self("Failed to create ZIP archive: {$reason}");
    }
}
