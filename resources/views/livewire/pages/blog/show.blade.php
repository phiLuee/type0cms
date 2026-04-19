<?php

use Type0\Blog\Models\Post;
use function Livewire\Volt\{layout, state, mount};

layout('components.layouts.app');

state(['post']);

mount(function (Post $post) {
    // Nur veröffentlichte Posts anzeigen
    if (!$post->is_published) {
        abort(404);
    }
    
    $this->post = $post->load(['author', 'category', 'tags', 'seo']);
    
    // Set dynamic title
    $title = ($this->post->seo->meta_title ?? $this->post->title) . ' - ' . config('app.name');
    $this->title = $title;
});

?>

<div>
    {{-- SEO Meta Tags (moved to head via Livewire) --}}
    @push('meta')
        <meta name="description" content="{{ $post->seo->meta_description ?? $post->excerpt }}">
        
        @if($post->seo && $post->seo->no_index)
            <meta name="robots" content="noindex, nofollow">
        @endif

        <meta property="og:type" content="article">
        <meta property="og:title" content="{{ $post->seo->meta_title ?? $post->title }}">
        <meta property="og:description" content="{{ $post->seo->meta_description ?? $post->excerpt }}">
        <meta property="og:url" content="{{ route('blog.show', $post) }}">
        
        @if($post->seo && $post->seo->og_image)
            <meta property="og:image" content="{{ $post->seo->getFirstMediaUrl('og_image') }}">
        @endif

        <meta property="article:published_time" content="{{ $post->published_at?->toIso8601String() }}">
        <meta property="article:author" content="{{ $post->author->name }}">
    @endpush

    <style>
        .cyber-title {
            font-family: 'Noto Sans JP', 'Orbitron', sans-serif;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            background: linear-gradient(135deg, 
                #fcee09 0%,
                #00ffff 25%,
                #ff00ff 50%,
                #00ffff 75%,
                #fcee09 100%);
            background-size: 200% auto;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: gradient-shift 8s linear infinite;
            position: relative;
            filter: drop-shadow(0 0 20px rgba(252, 238, 9, 0.5));
        }

        .cyber-card {
            background: linear-gradient(135deg, 
                rgba(10, 10, 10, 0.95) 0%,
                rgba(20, 20, 30, 0.9) 50%,
                rgba(5, 5, 5, 0.9) 100%);
            backdrop-filter: blur(15px);
            border: 2px solid rgba(252, 238, 9, 0.3);
            box-shadow: 
                0 0 20px rgba(252, 238, 9, 0.2),
                0 0 40px rgba(0, 255, 255, 0.1),
                inset 0 0 30px rgba(252, 238, 9, 0.05);
            position: relative;
        }

        .neon-text {
            color: #fcee09;
            text-shadow: 
                0 0 10px rgba(252, 238, 9, 0.5),
                0 0 20px rgba(252, 238, 9, 0.3);
        }

        .neon-samurai {
            color: #dc143c;
            text-shadow: 
                0 0 10px rgba(220, 20, 60, 0.8),
                0 0 20px rgba(220, 20, 60, 0.4);
            font-family: 'Noto Sans JP', sans-serif;
        }

        .cyber-btn {
            position: relative;
            background: linear-gradient(135deg, rgba(252, 238, 9, 0.1), rgba(0, 255, 255, 0.1));
            border: 2px solid #fcee09;
            color: #fcee09;
            font-family: 'Orbitron', sans-serif;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.15em;
            padding: 0.75rem 2rem;
            cursor: pointer;
            overflow: hidden;
            transition: all 0.3s ease;
            box-shadow: 
                0 0 20px rgba(252, 238, 9, 0.2),
                inset 0 0 20px rgba(252, 238, 9, 0.05);
            clip-path: polygon(0 0, calc(100% - 10px) 0, 100% 10px, 100% 100%, 10px 100%, 0 calc(100% - 10px));
        }

        .cyber-btn:hover {
            border-color: #fcee09;
            box-shadow: 
                0 0 40px rgba(252, 238, 9, 0.4),
                inset 0 0 30px rgba(252, 238, 9, 0.15);
            transform: translateY(-2px);
        }

        .cyber-tag {
            background: linear-gradient(135deg, rgba(252, 238, 9, 0.15), rgba(0, 255, 255, 0.15));
            border: 1px solid rgba(252, 238, 9, 0.5);
            color: #fcee09;
            padding: 0.4rem 1rem;
            clip-path: polygon(5px 0, 100% 0, 100% calc(100% - 5px), calc(100% - 5px) 100%, 0 100%, 0 5px);
            transition: all 0.3s ease;
        }

        .prose {
            color: rgba(200, 200, 200, 0.95);
            font-family: 'Noto Sans JP', sans-serif;
        }

        .prose h1, .prose h2, .prose h3, .prose h4 {
            color: #fcee09;
            font-family: 'Orbitron', sans-serif;
        }

        @keyframes gradient-shift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
    </style>

    {{-- Cyber Header --}}
    <header class="cyber-card border-b-2 border-cyan-500/30 sticky top-0 z-50">
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between">
                <a href="/" class="flex items-center gap-3 cyber-title text-2xl group">
                    <svg class="w-5 h-5 neon-text" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    <span>Zurück zum Blog</span>
                </a>

                @auth
                    <a href="{{ url('/admin') }}" class="cyber-btn text-sm inline-flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Admin
                    </a>
                @endauth
            </div>
        </div>
    </header>

    {{-- Main Content --}}
    <main class="mx-auto max-w-4xl px-4 py-12 sm:px-6 lg:px-8">
        <article class="cyber-card rounded-xl overflow-hidden">
            {{-- Featured Image --}}
            @if($post->featured_image)
                <div class="aspect-video w-full overflow-hidden relative">
                    <img 
                        src="{{ asset('storage/' . $post->featured_image) }}" 
                        alt="{{ $post->title }}"
                        class="h-full w-full object-cover transition-transform duration-700 hover:scale-110"
                    >
                </div>
            @endif

            {{-- Article Header --}}
            <div class="p-8 sm:p-12">
                {{-- Category & Tags --}}
                <div class="mb-6 flex flex-wrap items-center gap-3">
                    @if($post->category)
                        <span class="cyber-tag">
                            {{ $post->category->name }}
                        </span>
                    @endif

                    @foreach($post->tags as $tag)
                        <span class="cyber-tag">
                            #{{ $tag->name }}
                        </span>
                    @endforeach
                </div>

                {{-- Title --}}
                <h1 class="mb-6 text-4xl font-bold leading-tight sm:text-5xl lg:text-6xl cyber-title">
                    {{ $post->title }}
                </h1>

                {{-- Excerpt --}}
                @if($post->excerpt)
                    <p class="mb-8 text-xl leading-relaxed neon-text font-medium">
                        {{ $post->excerpt }}
                    </p>
                @endif

                {{-- Meta Information --}}
                <div class="flex flex-wrap items-center gap-6 border-t border-b border-cyan-500/30 py-4 text-sm">
                    {{-- Author --}}
                    <div class="flex items-center gap-2 neon-samurai font-semibold">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                        </svg>
                        <span>{{ $post->author->name }}</span>
                    </div>

                    {{-- Published Date --}}
                    @if($post->published_at)
                        <div class="flex items-center gap-2 neon-text font-semibold">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <time datetime="{{ $post->published_at->toIso8601String() }}">
                                {{ $post->published_at->format('d. F Y') }}
                            </time>
                        </div>
                    @endif

                    {{-- Reading Time --}}
                    @php
                        $wordCount = str_word_count(strip_tags($post->content ?? ''));
                        $readingTime = ceil($wordCount / 200);
                    @endphp
                    @if($readingTime > 0)
                        <div class="flex items-center gap-2 text-yellow-400 font-semibold">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>{{ $readingTime }} Min. Lesezeit</span>
                        </div>
                    @endif
                </div>

                {{-- Article Content --}}
                <div class="prose prose-lg max-w-none py-8">
                    {!! $post->content !!}
                </div>

                {{-- Tags Section --}}
                @if($post->tags->isNotEmpty())
                    <div class="mt-8 border-t border-cyan-500/30 pt-8">
                        <h3 class="mb-4 text-lg font-bold uppercase tracking-wide neon-text">
                            Schlagwörter
                        </h3>
                        <div class="flex flex-wrap gap-3">
                            @foreach($post->tags as $tag)
                                <span class="cyber-tag inline-flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
                                    </svg>
                                    {{ $tag->name }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Share Buttons --}}
                <div class="mt-8 border-t border-cyan-500/30 pt-8">
                    <h3 class="mb-4 text-lg font-bold uppercase tracking-wide neon-text">
                        Teilen
                    </h3>
                    <div class="flex flex-wrap gap-3">
                        <a 
                            href="https://twitter.com/intent/tweet?text={{ urlencode($post->title) }}&url={{ urlencode(route('blog.show', $post)) }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="cyber-btn text-xs inline-flex items-center gap-2"
                        >
                            Twitter
                        </a>

                        <a 
                            href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(route('blog.show', $post)) }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="cyber-btn text-xs inline-flex items-center gap-2"
                        >
                            Facebook
                        </a>

                        <a 
                            href="https://www.linkedin.com/shareArticle?mini=true&url={{ urlencode(route('blog.show', $post)) }}&title={{ urlencode($post->title) }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="cyber-btn text-xs inline-flex items-center gap-2"
                        >
                            LinkedIn
                        </a>
                    </div>
                </div>
            </div>
        </article>

        {{-- Related Posts --}}
        @if($post->category && $post->category->posts()->where('id', '!=', $post->id)->where('is_published', true)->exists())
            <section class="cyber-card rounded-xl p-8 mt-12">
                <h2 class="mb-8 text-3xl font-bold cyber-title">
                    Weitere Artikel aus "{{ $post->category->name }}"
                </h2>

                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($post->category->posts()->where('id', '!=', $post->id)->where('is_published', true)->limit(3)->get() as $relatedPost)
                        <article class="cyber-card rounded-lg overflow-hidden group hover:scale-105 transition-transform duration-300">
                            @if($relatedPost->featured_image)
                                <div class="aspect-video w-full overflow-hidden">
                                    <img 
                                        src="{{ asset('storage/' . $relatedPost->featured_image) }}" 
                                        alt="{{ $relatedPost->title }}"
                                        class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-110"
                                    >
                                </div>
                            @endif

                            <div class="p-4">
                                <h3 class="mb-3 text-lg font-bold neon-text">
                                    <a href="{{ route('blog.show', $relatedPost) }}">
                                        {{ $relatedPost->title }}
                                    </a>
                                </h3>

                                @if($relatedPost->excerpt)
                                    <p class="mb-4 text-sm text-gray-300 line-clamp-2">
                                        {{ $relatedPost->excerpt }}
                                    </p>
                                @endif

                                <a 
                                    href="{{ route('blog.show', $relatedPost) }}" 
                                    class="inline-flex items-center gap-2 text-sm font-bold neon-text"
                                >
                                    Weiterlesen
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
    <footer class="mt-16 border-t-2 border-cyan-500/30 cyber-card">
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <p class="text-center font-bold neon-text text-lg">
                © {{ date('Y') }} {{ config('app.name') }} - Alle Rechte vorbehalten
            </p>
        </div>
    </footer>
</div>
