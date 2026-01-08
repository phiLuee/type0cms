
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- SEO Meta Tags --}}
    <title>{{ $post->seo->meta_title ?? $post->title }} - {{ config('app.name') }}</title>
    <meta name="description" content="{{ $post->seo->meta_description ?? $post->excerpt }}">
    
    @if($post->seo && $post->seo->no_index)
        <meta name="robots" content="noindex, nofollow">
    @endif

    {{-- Open Graph / Social Media --}}
    <meta property="og:type" content="article">
    <meta property="og:title" content="{{ $post->seo->meta_title ?? $post->title }}">
    <meta property="og:description" content="{{ $post->seo->meta_description ?? $post->excerpt }}">
    <meta property="og:url" content="{{ route('blog.show', $post) }}">
    
    @if($post->seo && $post->seo->og_image)
        <meta property="og:image" content="{{ $post->seo->getFirstMediaUrl('og_image') }}">
    @endif

    <meta property="article:published_time" content="{{ $post->published_at?->toIso8601String() }}">
    <meta property="article:author" content="{{ $post->author->name }}">

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    {{-- Styles --}}
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <style>
            [x-cloak] { display: none !important; }
        </style>
    @endif
</head>
<body class="bg-gray-50 dark:bg-gray-900 antialiased">
    {{-- Header / Navigation --}}
    <header class="bg-white dark:bg-gray-800 shadow-sm sticky top-0 z-50">
        <div class="mx-auto max-w-7xl px-4 py-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between">
                <a href="/" class="flex items-center gap-2 text-xl font-bold text-gray-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Zurück zum Blog
                </a>

                @auth
                    <a href="{{ url('/admin') }}" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 transition-colors">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Admin
                    </a>
                @endauth
            </div>
        </div>
    </header>

    {{-- Main Content --}}
    <main class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
        <article class="overflow-hidden rounded-lg bg-white shadow-lg dark:bg-gray-800">
            {{-- Featured Image --}}
            @if($post->featured_image)
                <div class="aspect-video w-full overflow-hidden">
                    <img 
                        src="{{ asset('storage/' . $post->featured_image) }}" 
                        alt="{{ $post->title }}"
                        class="h-full w-full object-cover"
                    >
                </div>
            @endif

            {{-- Article Header --}}
            <div class="p-8">
                {{-- Category & Tags --}}
                <div class="mb-4 flex flex-wrap items-center gap-2">
                    @if($post->category)
                        <span class="inline-flex items-center rounded-full bg-blue-100 px-3 py-1 text-sm font-semibold text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                            {{ $post->category->name }}
                        </span>
                    @endif

                    @foreach($post->tags as $tag)
                        <span 
                            class="inline-flex items-center rounded-full px-3 py-1 text-sm font-semibold"
                            style="background-color: {{ $tag->color }}20; color: {{ $tag->color }};"
                        >
                            #{{ $tag->name }}
                        </span>
                    @endforeach
                </div>

                {{-- Title --}}
                <h1 class="mb-4 text-4xl font-bold leading-tight text-gray-900 dark:text-white sm:text-5xl">
                    {{ $post->title }}
                </h1>

                {{-- Excerpt --}}
                @if($post->excerpt)
                    <p class="mb-6 text-xl leading-relaxed text-gray-600 dark:text-gray-300">
                        {{ $post->excerpt }}
                    </p>
                @endif

                {{-- Meta Information --}}
                <div class="flex flex-wrap items-center gap-4 border-b border-gray-200 pb-6 text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                    {{-- Author --}}
                    <div class="flex items-center gap-2">
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                        </svg>
                        <span class="font-medium text-gray-900 dark:text-white">{{ $post->author->name }}</span>
                    </div>

                    {{-- Published Date --}}
                    @if($post->published_at)
                        <div class="flex items-center gap-2">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <time datetime="{{ $post->published_at->toIso8601String() }}">
                                {{ $post->published_at->format('d. F Y') }}
                            </time>
                        </div>
                    @endif

                    {{-- Reading Time Estimate --}}
                    @php
                        $wordCount = str_word_count(strip_tags($post->content ?? ''));
                        $readingTime = ceil($wordCount / 200); // Durchschnitt: 200 Wörter/Minute
                    @endphp
                    @if($readingTime > 0)
                        <div class="flex items-center gap-2">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>{{ $readingTime }} Min. Lesezeit</span>
                        </div>
                    @endif
                </div>

                {{-- Article Content --}}
                <div class="prose prose-lg dark:prose-invert max-w-none py-8">
                    {!! $post->content !!}
                </div>

                {{-- Tags Section --}}
                @if($post->tags->isNotEmpty())
                    <div class="mt-8 border-t border-gray-200 pt-6 dark:border-gray-700">
                        <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300">
                            Schlagwörter
                        </h3>
                        <div class="flex flex-wrap gap-2">
                            @foreach($post->tags as $tag)
                                <span 
                                    class="inline-flex items-center gap-1 rounded-lg px-3 py-1.5 text-sm font-medium transition-colors hover:opacity-80"
                                    style="background-color: {{ $tag->color }}20; color: {{ $tag->color }};"
                                >
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
                                    </svg>
                                    {{ $tag->name }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Share Buttons --}}
                <div class="mt-8 border-t border-gray-200 pt-6 dark:border-gray-700">
                    <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300">
                        Teilen
                    </h3>
                    <div class="flex flex-wrap gap-2">
                        {{-- Twitter --}}
                        <a 
                            href="https://twitter.com/intent/tweet?text={{ urlencode($post->title) }}&url={{ urlencode(route('blog.show', $post)) }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center gap-2 rounded-lg bg-[#1DA1F2] px-4 py-2 text-sm font-semibold text-white hover:bg-[#1a8cd8] transition-colors"
                        >
                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                            </svg>
                            Twitter
                        </a>

                        {{-- Facebook --}}
                        <a 
                            href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(route('blog.show', $post)) }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center gap-2 rounded-lg bg-[#4267B2] px-4 py-2 text-sm font-semibold text-white hover:bg-[#365899] transition-colors"
                        >
                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                            Facebook
                        </a>

                        {{-- LinkedIn --}}
                        <a 
                            href="https://www.linkedin.com/shareArticle?mini=true&url={{ urlencode(route('blog.show', $post)) }}&title={{ urlencode($post->title) }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center gap-2 rounded-lg bg-[#0077B5] px-4 py-2 text-sm font-semibold text-white hover:bg-[#006399] transition-colors"
                        >
                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                            </svg>
                            LinkedIn
                        </a>

                        {{-- Email --}}
                        <a 
                            href="mailto:?subject={{ urlencode($post->title) }}&body={{ urlencode('Schau dir diesen Artikel an: ' . route('blog.show', $post)) }}"
                            class="inline-flex items-center gap-2 rounded-lg bg-gray-600 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700 transition-colors dark:bg-gray-700 dark:hover:bg-gray-600"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            E-Mail
                        </a>
                    </div>
                </div>
            </div>
        </article>

        {{-- Related Posts (Optional - wenn Sie eine Relation haben) --}}
        @if($post->category && $post->category->posts()->where('id', '!=', $post->id)->where('is_published', true)->exists())
            <section class="mt-12">
                <h2 class="mb-6 text-2xl font-bold text-gray-900 dark:text-white">
                    Weitere Artikel aus "{{ $post->category->name }}"
                </h2>

                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($post->category->posts()->where('id', '!=', $post->id)->where('is_published', true)->limit(3)->get() as $relatedPost)
                        <article class="overflow-hidden rounded-lg bg-white shadow-md transition-shadow hover:shadow-xl dark:bg-gray-800">
                            @if($relatedPost->featured_image)
                                <div class="aspect-video w-full overflow-hidden">
                                    <img 
                                        src="{{ asset('storage/' . $relatedPost->featured_image) }}" 
                                        alt="{{ $relatedPost->title }}"
                                        class="h-full w-full object-cover transition-transform hover:scale-105"
                                    >
                                </div>
                            @endif

                            <div class="p-4">
                                <h3 class="mb-2 text-lg font-semibold text-gray-900 dark:text-white">
                                    <a href="{{ route('blog.show', $relatedPost) }}" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                                        {{ $relatedPost->title }}
                                    </a>
                                </h3>

                                @if($relatedPost->excerpt)
                                    <p class="mb-3 text-sm text-gray-600 dark:text-gray-400 line-clamp-2">
                                        {{ $relatedPost->excerpt }}
                                    </p>
                                @endif

                                <a 
                                    href="{{ route('blog.show', $relatedPost) }}" 
                                    class="inline-flex items-center gap-1 text-sm font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300"
                                >
                                    Weiterlesen
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif
    </main>

    {{-- Footer --}}
    <footer class="mt-16 border-t border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-800">
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <p class="text-center text-sm text-gray-500 dark:text-gray-400">
                © {{ date('Y') }} {{ config('app.name') }}. Alle Rechte vorbehalten.
            </p>
        </div>
    </footer>
</body>
</html>