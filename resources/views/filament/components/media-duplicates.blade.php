<div class="space-y-4">
    @if(empty($duplicates))
        <div class="rounded-lg border border-green-200 bg-green-50 p-4 dark:border-green-800 dark:bg-green-950">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-800 dark:text-green-200">
                        Keine Duplikate gefunden
                    </p>
                    <p class="mt-1 text-sm text-green-700 dark:text-green-300">
                        Alle Ihre Medien sind einzigartig.
                    </p>
                </div>
            </div>
        </div>
    @else
        <div class="rounded-lg border border-yellow-200 bg-yellow-50 p-4 dark:border-yellow-800 dark:bg-yellow-950">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                        {{ count($duplicates) }} duplizierte Datei(en) gefunden
                    </h3>
                </div>
            </div>
        </div>

        <div class="space-y-3">
            @foreach($duplicates as $duplicate)
                <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="font-medium text-gray-900 dark:text-white">{{ $duplicate['file_name'] }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ App\Filament\Resources\Media\MediaResource::formatBytes($duplicate['size']) }} 
                                • {{ $duplicate['count'] }} Kopien
                            </p>
                        </div>
                        <div class="ml-4">
                            <span class="inline-flex items-center rounded-full bg-red-100 px-3 py-1 text-sm font-medium text-red-800 dark:bg-red-900 dark:text-red-200">
                                {{ $duplicate['count'] }}x
                            </span>
                        </div>
                    </div>
                    <div class="mt-2 flex gap-2">
                        @foreach($duplicate['ids'] as $id)
                            <a href="{{ route('filament.admin.resources.media.view', $id) }}" 
                               class="text-sm text-primary-600 hover:underline dark:text-primary-400"
                               target="_blank">
                                #{{ $id }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        <div class="rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-950">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-blue-700 dark:text-blue-300">
                        Sie können die duplizierten Medien manuell überprüfen und löschen, um Speicherplatz zu sparen.
                    </p>
                </div>
            </div>
        </div>
    @endif
</div>
