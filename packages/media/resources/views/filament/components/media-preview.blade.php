<div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
    @if($type === 'image')
        <img src="{{ $url }}" alt="Preview" class="mx-auto max-h-96 rounded-lg">
    @elseif($type === 'video')
        <video controls class="mx-auto max-h-96 rounded-lg">
            <source src="{{ $url }}">
            Ihr Browser unterstützt das Video-Tag nicht.
        </video>
    @elseif($type === 'pdf')
        <div class="text-center">
            <div class="mb-4">
                <svg class="mx-auto h-24 w-24 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                </svg>
            </div>
            <a href="{{ $url }}" target="_blank" class="inline-flex items-center rounded-lg bg-primary-600 px-4 py-2 text-white hover:bg-primary-700">
                <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                </svg>
                PDF im neuen Tab öffnen
            </a>
        </div>
    @endif
</div>
