<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Erlaubte MIME-Typen
    |--------------------------------------------------------------------------
    |
    | Liste der erlaubten MIME-Typen für Medien-Uploads.
    | Kann pro Umgebung angepasst werden.
    |
    */
    'allowed_mime_types' => [
        // Images
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
        'image/svg+xml',
        'image/bmp',
        'image/tiff',

        // Documents
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',

        // Videos
        'video/mp4',
        'video/webm',
        'video/ogg',
        'video/quicktime',

        // Audio
        'audio/mpeg',
        'audio/wav',
        'audio/ogg',
    ],

    /*
    |--------------------------------------------------------------------------
    | Maximale Dateigröße
    |--------------------------------------------------------------------------
    |
    | Maximale erlaubte Dateigröße in Bytes.
    | Standard: 50MB
    |
    */
    'max_file_size' => env('MEDIA_MAX_FILE_SIZE', 50 * 1024 * 1024),

    /*
    |--------------------------------------------------------------------------
    | Standard Disk
    |--------------------------------------------------------------------------
    |
    | Welches Storage Disk standardmäßig verwendet werden soll.
    |
    */
    'default_disk' => env('MEDIA_DEFAULT_DISK', 'public'),

    /*
    |--------------------------------------------------------------------------
    | Standard Collection
    |--------------------------------------------------------------------------
    |
    | Standard Collection-Name für Medien ohne spezifische Zuordnung.
    |
    */
    'default_collection' => env('MEDIA_DEFAULT_COLLECTION', 'default'),
];
