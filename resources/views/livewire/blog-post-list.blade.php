<?php

declare(strict_types=1);

use App\Plugins\Blog\Models\Post;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public function with(): array
    {
        return [
            'posts' => Post::with(['author', 'category', 'tags'])
                ->where('is_published', true)
                ->latest('published_at')
                ->paginate(2)
        ];
    }
}; ?>

<div class="w-full">
    {{-- Blog Posts Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        @forelse($posts as $post)
            <article class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300">
                {{-- @if($post->featured_image)
                    <img 
                        src="{{ asset('storage/' . $post->featured_image) }}" 
                        alt="{{ $post->title }}"
                        class="w-full h-48 object-cover"
                    >
                @else
                    <div class="w-full h-48 bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center">
                        <svg class="w-16 h-16 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div> 
                @endif--}}

                <div class="p-6">
                    @if($post->category)
                        <span class="inline-block px-3 py-1 text-xs font-semibold text-blue-600 bg-blue-100 dark:bg-blue-900 dark:text-blue-200 rounded-full mb-3">
                            {{ $post->category->name }}
                        </span>
                    @endif

                    <h3 class="text-xl font-bold mb-3 line-clamp-2 dark:text-white">
                        <a href="{{ route('blog.show', $post) }}" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                            {{ $post->title }}
                        </a>
                    </h3>

                    @if($post->excerpt)
                        <p class="text-gray-600 dark:text-gray-400 mb-4 line-clamp-3">
                            {{ $post->excerpt }}
                        </p>
                    @endif

                    <div class="flex items-center justify-between text-sm text-gray-500 dark:text-gray-400 mb-4">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"/>
                            </svg>
                            {{ $post->author->name }}
                        </span>
                        <time datetime="{{ $post->published_at?->toISOString() }}">
                            {{ $post->published_at?->format('d.m.Y') }}
                        </time>
                    </div>

                    <a href="{{ route('blog.show', $post) }}" 
                       class="inline-flex items-center gap-2 text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 font-medium transition-colors">
                        Weiterlesen
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            </article>
        @empty
            <div class="col-span-full text-center py-16">
                <svg class="w-20 h-20 mx-auto text-gray-400 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <h3 class="text-xl font-semibold text-gray-700 dark:text-gray-300 mb-2">Keine Blog-Posts gefunden</h3>
                <p class="text-gray-500 dark:text-gray-400">Es wurden noch keine Beiträge veröffentlicht.</p>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="mt-8">
        {{ $posts->links() }}
    </div>
</div>
