<div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
    @php
        $record = $getRecord();
    @endphp

    @if($record && str_starts_with($record->mime_type, 'image/'))
        <img src="{{ $record->getUrl() }}" alt="{{ $record->name }}" class="mx-auto max-h-96 rounded-lg">
    @elseif($record && str_starts_with($record->mime_type, 'video/'))
        <video controls class="mx-auto max-h-96 rounded-lg">
            <source src="{{ $record->getUrl() }}" type="{{ $record->mime_type }}">
            Ihr Browser unterstützt das Video-Tag nicht.
        </video>
    @elseif($record && $record->mime_type === 'application/pdf')
        <div class="text-center">
            <div class="mb-4">
                <svg class="mx-auto h-24 w-24 text-red-500 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                </svg>
            </div>
            <a href="{{ $record->getUrl() }}" target="_blank" class="inline-flex items-center rounded-lg bg-primary-600 px-4 py-2 text-white hover:bg-primary-700 dark:bg-primary-500 dark:hover:bg-primary-600">
                <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                </svg>
                PDF im neuen Tab öffnen
            </a>
        </div>
    @elseif($record && str_starts_with($record->mime_type, 'audio/'))
        <div class="text-center">
            <div class="mb-4">
                <svg class="mx-auto h-24 w-24 text-blue-500 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"></path>
                </svg>
            </div>
            <audio controls class="mx-auto w-full max-w-md">
                <source src="{{ $record->getUrl() }}" type="{{ $record->mime_type }}">
                Ihr Browser unterstützt das Audio-Element nicht.
            </audio>
        </div>
    @else
        <div class="text-center text-gray-500 dark:text-gray-400">
            <svg class="mx-auto h-24 w-24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
            </svg>
            <p class="mt-4">Keine Vorschau für diesen Dateityp verfügbar</p>
            <a href="{{ $record->getUrl() }}" target="_blank" class="mt-2 inline-flex items-center text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300">
                <svg class="mr-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                </svg>
                Datei herunterladen
            </a>
        </div>
    @endif
</div>
