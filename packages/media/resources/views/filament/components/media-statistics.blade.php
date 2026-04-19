<div class="space-y-4">
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
        {{-- Gesamt --}}
        <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Gesamt</p>
                    <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ $totalCount }}</p>
                    <p class="mt-1 text-sm text-gray-500">{{ Type0\Media\Filament\Resources\MediaResource::formatBytes($totalSize) }}</p>
                </div>
                <div class="rounded-full bg-primary-100 p-3 dark:bg-primary-900">
                    <svg class="h-6 w-6 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Bilder --}}
        <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Bilder</p>
                    <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ $imageCount }}</p>
                    <p class="mt-1 text-sm text-gray-500">{{ number_format($imageCount / max($totalCount, 1) * 100, 1) }}% der Medien</p>
                </div>
                <div class="rounded-full bg-success-100 p-3 dark:bg-success-900">
                    <svg class="h-6 w-6 text-success-600 dark:text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Videos --}}
        <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Videos</p>
                    <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ $videoCount }}</p>
                    <p class="mt-1 text-sm text-gray-500">{{ number_format($videoCount / max($totalCount, 1) * 100, 1) }}% der Medien</p>
                </div>
                <div class="rounded-full bg-warning-100 p-3 dark:bg-warning-900">
                    <svg class="h-6 w-6 text-warning-600 dark:text-warning-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Dokumente --}}
        <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Dokumente</p>
                    <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ $documentCount }}</p>
                    <p class="mt-1 text-sm text-gray-500">{{ number_format($documentCount / max($totalCount, 1) * 100, 1) }}% der Medien</p>
                </div>
                <div class="rounded-full bg-info-100 p-3 dark:bg-info-900">
                    <svg class="h-6 w-6 text-info-600 dark:text-info-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Ungenutzt --}}
        <div class="rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-950">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-red-600 dark:text-red-400">Ungenutzt</p>
                    <p class="mt-2 text-3xl font-bold text-red-900 dark:text-red-100">{{ $unusedCount }}</p>
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ Type0\Media\Filament\Resources\MediaResource::formatBytes($unusedSize) }}</p>
                </div>
                <div class="rounded-full bg-red-200 p-3 dark:bg-red-900">
                    <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Effizienz --}}
        <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Effizienz</p>
                    <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">
                        {{ number_format((1 - $unusedCount / max($totalCount, 1)) * 100, 1) }}%
                    </p>
                    <p class="mt-1 text-sm text-gray-500">Genutzte Medien</p>
                </div>
                <div class="rounded-full {{ $unusedCount > $totalCount * 0.2 ? 'bg-red-100 dark:bg-red-900' : 'bg-green-100 dark:bg-green-900' }} p-3">
                    <svg class="h-6 w-6 {{ $unusedCount > $totalCount * 0.2 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    @if($unusedCount > 0)
        <div class="rounded-lg border border-yellow-200 bg-yellow-50 p-4 dark:border-yellow-800 dark:bg-yellow-950">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                        Optimierungsempfehlung
                    </h3>
                    <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                        <p>
                            Sie haben {{ $unusedCount }} ungenutzte Medien, die {{ Type0\Media\Filament\Resources\MediaResource::formatBytes($unusedSize) }} Speicherplatz belegen.
                            Verwenden Sie die "Aufräumen"-Funktion, um diese zu entfernen.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
