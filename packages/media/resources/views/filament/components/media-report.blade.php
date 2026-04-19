<div class="space-y-6">
    {{-- Zusammenfassung --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="rounded-lg border border-gray-200 bg-gradient-to-br from-primary-50 to-primary-100 p-4 dark:border-gray-700 dark:from-primary-900 dark:to-primary-800">
            <p class="text-sm font-medium text-primary-600 dark:text-primary-300">Gesamt-Medien</p>
            <p class="mt-2 text-3xl font-bold text-primary-900 dark:text-primary-100">{{ $report['total']['count'] }}</p>
            <p class="mt-1 text-sm text-primary-700 dark:text-primary-300">
                {{ Type0\Media\Filament\Resources\MediaResource::formatBytes($report['total']['size']) }}
            </p>
        </div>

        <div class="rounded-lg border border-gray-200 bg-gradient-to-br from-green-50 to-green-100 p-4 dark:border-gray-700 dark:from-green-900 dark:to-green-800">
            <p class="text-sm font-medium text-green-600 dark:text-green-300">Genutzte Medien</p>
            <p class="mt-2 text-3xl font-bold text-green-900 dark:text-green-100">
                {{ $report['total']['count'] - $report['unused']['count'] }}
            </p>
            <p class="mt-1 text-sm text-green-700 dark:text-green-300">
                {{ number_format((1 - $report['unused']['count'] / max($report['total']['count'], 1)) * 100, 1) }}% Effizienz
            </p>
        </div>

        <div class="rounded-lg border border-gray-200 bg-gradient-to-br from-red-50 to-red-100 p-4 dark:border-gray-700 dark:from-red-900 dark:to-red-800">
            <p class="text-sm font-medium text-red-600 dark:text-red-300">Ungenutzte Medien</p>
            <p class="mt-2 text-3xl font-bold text-red-900 dark:text-red-100">{{ $report['unused']['count'] }}</p>
            <p class="mt-1 text-sm text-red-700 dark:text-red-300">
                {{ Type0\Media\Filament\Resources\MediaResource::formatBytes($report['unused']['size']) }}
            </p>
        </div>
    </div>

    {{-- Nach Typ --}}
    <div>
        <h3 class="mb-3 text-lg font-semibold text-gray-900 dark:text-white">Nach Dateityp</h3>
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
            <div class="rounded-lg border border-gray-200 p-3 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Bilder</p>
                        <p class="mt-1 text-xl font-semibold text-gray-900 dark:text-white">{{ $report['by_type']['images']['count'] }}</p>
                    </div>
                    <div class="rounded-full bg-success-100 p-2 dark:bg-success-900">
                        <svg class="h-5 w-5 text-success-600 dark:text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                </div>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    {{ Type0\Media\Filament\Resources\MediaResource::formatBytes($report['by_type']['images']['size']) }}
                </p>
            </div>

            <div class="rounded-lg border border-gray-200 p-3 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Videos</p>
                        <p class="mt-1 text-xl font-semibold text-gray-900 dark:text-white">{{ $report['by_type']['videos']['count'] }}</p>
                    </div>
                    <div class="rounded-full bg-warning-100 p-2 dark:bg-warning-900">
                        <svg class="h-5 w-5 text-warning-600 dark:text-warning-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                </div>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    {{ Type0\Media\Filament\Resources\MediaResource::formatBytes($report['by_type']['videos']['size']) }}
                </p>
            </div>

            <div class="rounded-lg border border-gray-200 p-3 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Dokumente</p>
                        <p class="mt-1 text-xl font-semibold text-gray-900 dark:text-white">{{ $report['by_type']['documents']['count'] }}</p>
                    </div>
                    <div class="rounded-full bg-info-100 p-2 dark:bg-info-900">
                        <svg class="h-5 w-5 text-info-600 dark:text-info-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                </div>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    {{ Type0\Media\Filament\Resources\MediaResource::formatBytes($report['by_type']['documents']['size']) }}
                </p>
            </div>
        </div>
    </div>

    {{-- Nach Collection --}}
    <div>
        <h3 class="mb-3 text-lg font-semibold text-gray-900 dark:text-white">Nach Sammlung</h3>
        <div class="space-y-2">
            @foreach($report['by_collection'] as $collection => $data)
                <div class="flex items-center justify-between rounded-lg border border-gray-200 p-3 dark:border-gray-700">
                    <div class="flex-1">
                        <p class="font-medium text-gray-900 dark:text-white">
                            {{ match($collection) {
                                'default' => 'Standard',
                                'featured' => 'Titelbilder',
                                'gallery' => 'Galerie',
                                'attachments' => 'Anhänge',
                                'documents' => 'Dokumente',
                                default => ucfirst($collection),
                            } }}
                        </p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ $data['count'] }} Dateien • {{ Type0\Media\Filament\Resources\MediaResource::formatBytes($data['size']) }}
                        </p>
                    </div>
                    <div class="text-right">
                        <span class="text-sm font-semibold text-gray-900 dark:text-white">
                            {{ number_format($data['count'] / max($report['total']['count'], 1) * 100, 1) }}%
                        </span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Interessante Fakten --}}
    <div>
        <h3 class="mb-3 text-lg font-semibold text-gray-900 dark:text-white">Interessante Fakten</h3>
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            @if($report['oldest'])
                <div class="rounded-lg border border-gray-200 p-3 dark:border-gray-700">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Ältestes Medium</p>
                    <p class="mt-1 font-semibold text-gray-900 dark:text-white">{{ $report['oldest']->name }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ $report['oldest']->created_at->format('d.m.Y') }}
                    </p>
                </div>
            @endif

            @if($report['newest'])
                <div class="rounded-lg border border-gray-200 p-3 dark:border-gray-700">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Neuestes Medium</p>
                    <p class="mt-1 font-semibold text-gray-900 dark:text-white">{{ $report['newest']->name }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ $report['newest']->created_at->format('d.m.Y') }}
                    </p>
                </div>
            @endif

            @if($report['largest'])
                <div class="rounded-lg border border-gray-200 p-3 dark:border-gray-700">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Größtes Medium</p>
                    <p class="mt-1 font-semibold text-gray-900 dark:text-white">{{ $report['largest']->name }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ Type0\Media\Filament\Resources\MediaResource::formatBytes($report['largest']->size) }}
                    </p>
                </div>
            @endif
        </div>
    </div>
</div>
