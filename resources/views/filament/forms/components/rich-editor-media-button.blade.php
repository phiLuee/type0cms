@php
    use Spatie\MediaLibrary\MediaCollections\Models\Media;
    
    $mediaItems = Media::orderBy('created_at', 'desc')->limit(50)->get();
@endphp

<div x-data="mediaInsertModal()" x-init="init()" class="relative">
    <!-- Media Button für Toolbar -->
    <button
        type="button"
        @click="openModal"
        class="rounded-md p-1 text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-gray-200"
        title="Medien einfügen"
    >
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 15.5M14 8h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
        </svg>
    </button>

    <!-- Modal -->
    <div
        x-show="isOpen"
        x-cloak
        @keydown.escape.window="closeModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
        style="display: none;"
    >
        <div
            @click.away="closeModal"
            class="max-h-[80vh] w-full max-w-4xl overflow-y-auto rounded-lg bg-white p-6 shadow-xl dark:bg-gray-800"
        >
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                    Medien einfügen
                </h3>
                <button
                    @click="closeModal"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                >
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Search -->
            <div class="mb-4">
                <input
                    type="text"
                    x-model="searchQuery"
                    @input="filterMedia"
                    placeholder="Suche nach Dateiname..."
                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                >
            </div>

            <!-- Media Grid -->
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4">
                @foreach($mediaItems as $media)
                    <button
                        type="button"
                        @click="insertMedia(@js([
                            'id' => $media->id,
                            'name' => $media->name,
                            'url' => $media->getUrl(),
                            'mime_type' => $media->mime_type,
                        ]))"
                        class="group relative overflow-hidden rounded-lg border-2 border-gray-200 transition hover:border-primary-500 dark:border-gray-700"
                        data-search="{{ strtolower($media->name . ' ' . $media->file_name) }}"
                    >
                        @if(str_starts_with($media->mime_type, 'image/'))
                            <img
                                src="{{ $media->getUrl() }}"
                                alt="{{ $media->name }}"
                                class="aspect-square h-full w-full object-cover"
                            >
                        @else
                            <div class="flex aspect-square items-center justify-center bg-gray-100 dark:bg-gray-700">
                                <svg class="h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        @endif

                        <div class="absolute inset-0 flex items-center justify-center bg-black/0 transition group-hover:bg-black/20">
                            <svg class="h-8 w-8 text-white opacity-0 transition group-hover:opacity-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                        </div>

                        <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/60 to-transparent p-2">
                            <p class="truncate text-xs text-white">{{ $media->name }}</p>
                        </div>
                    </button>
                @endforeach
            </div>
        </div>
    </div>
</div>

<script>
function mediaInsertModal() {
    return {
        isOpen: false,
        searchQuery: '',
        editorId: null,

        init() {
            // Get editor ID from parent element
            this.editorId = this.$el.closest('[data-editor-id]')?.dataset?.editorId;
        },

        openModal() {
            this.isOpen = true;
        },

        closeModal() {
            this.isOpen = false;
            this.searchQuery = '';
            this.filterMedia();
        },

        insertMedia(media) {
            // Dispatch event to editor
            window.dispatchEvent(new CustomEvent('insert-media', {
                detail: {
                    media: media,
                    editorId: this.editorId
                }
            }));

            this.closeModal();
        },

        filterMedia() {
            const query = this.searchQuery.toLowerCase();
            const items = this.$el.querySelectorAll('[data-search]');

            items.forEach(item => {
                const searchText = item.dataset.search;
                if (searchText.includes(query)) {
                    item.classList.remove('hidden');
                } else {
                    item.classList.add('hidden');
                }
            });
        }
    }
}
</script>

<style>
[x-cloak] {
    display: none !important;
}
</style>
