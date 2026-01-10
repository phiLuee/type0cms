<?php

declare(strict_types=1);

namespace App\Exceptions\Media;

/**
 * Exception für ungültige Medien-Dateien
 */
class InvalidMediaFileException extends MediaException
{
    public static function invalidMimeType(string $mimeType, array $allowed): self
    {
        $allowedString = implode(', ', $allowed);
        return new self("Invalid MIME type '{$mimeType}'. Allowed types: {$allowedString}");
    }

    public static function fileTooLarge(int $size, int $maxSize): self
    {
        $sizeMB = round($size / 1024 / 1024, 2);
        $maxSizeMB = round($maxSize / 1024 / 1024, 2);
        return new self("File size {$sizeMB}MB exceeds maximum allowed size of {$maxSizeMB}MB");
    }

    public static function fileNotFound(string $path): self
    {
        return new self("File not found at path: {$path}");
    }
}
