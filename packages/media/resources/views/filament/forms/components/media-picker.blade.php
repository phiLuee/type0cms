@php
    $availableMedia = $getAvailableMedia();
    $isMultiple = $isMultiple();
    $state = $getState() ?? ($isMultiple ? [] : null);
@endphp

<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div x-data="{
        selected: @js($state),
        isMultiple: @js($isMultiple),
        toggle(id) {
            if (this.isMultiple) {
                if (this.selected.includes(id)) {
                    this.selected = this.selected.filter(i => i !== id);
                } else {
                    this.selected.push(id);
                }
            } else {
                this.selected = this.selected === id ? null : id;
            }
            $wire.set('{{ $getStatePath() }}', this.selected);
        },
        isSelected(id) {
            if (this.isMultiple) {
                return this.selected.includes(id);
            }
            return this.selected === id;
        }
    }">
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
            @foreach($availableMedia as $media)
                <div 
                    @click="toggle({{ $media->id }})"
                    :class="isSelected({{ $media->id }}) ? 'ring-2 ring-primary-500' : 'ring-1 ring-gray-300 dark:ring-gray-600'"
                    class="relative cursor-pointer overflow-hidden rounded-lg transition hover:ring-2 hover:ring-primary-400"
                >
                    @if($media->mime_type && str_starts_with($media->mime_type, 'image/'))
                        <img 
                            src="{{ $media->getUrl() }}" 
                            alt="{{ $media->name }}"
                            class="aspect-square h-full w-full object-cover"
                        >
                    @else
                        <div class="flex aspect-square items-center justify-center bg-gray-100 dark:bg-gray-800">
                            <svg class="h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                    @endif
                    
                    <div 
                        x-show="isSelected({{ $media->id }})"
                        class="absolute inset-0 flex items-center justify-center bg-primary-500/20"
                    >
                        <svg class="h-8 w-8 text-primary-600 dark:text-primary-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    
                    <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/60 to-transparent p-2">
                        <p class="truncate text-xs text-white">{{ $media->name }}</p>
                    </div>
                </div>
            @endforeach
        </div>
        
        @if($availableMedia->isEmpty())
            <div class="rounded-lg border-2 border-dashed border-gray-300 p-12 text-center dark:border-gray-700">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 15.5M14 8h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-gray-100">Keine Medien vorhanden</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Laden Sie zuerst Medien hoch.</p>
            </div>
        @endif
    </div>
</x-dynamic-component>
