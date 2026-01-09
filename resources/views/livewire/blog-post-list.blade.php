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

<style>
    /* Cyberpunk 2077 + Nier Automata + Sekiro Styles */
    .cyber-grid-container {
        position: relative;
        z-index: 10;
    }
    
    .cyber-post-card {
        background: linear-gradient(145deg, rgba(10, 10, 10, 0.95), rgba(20, 20, 20, 0.9));
        border: 1px solid rgba(252, 238, 9, 0.2);
        position: relative;
        overflow: hidden;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        clip-path: polygon(
            0 0,
            calc(100% - 12px) 0,
            100% 12px,
            100% 100%,
            12px 100%,
            0 calc(100% - 12px)
        );
    }
    
    .cyber-post-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, 
            transparent 0%,
            rgba(252, 238, 9, 0.1) 50%,
            transparent 100%
        );
        transition: left 0.8s ease;
        pointer-events: none;
    }
    
    .cyber-post-card:hover::before {
        left: 100%;
    }
    
    .cyber-post-card:hover {
        border-color: rgba(252, 238, 9, 0.6);
        box-shadow: 
            0 0 30px rgba(252, 238, 9, 0.3),
            0 0 60px rgba(0, 255, 255, 0.2),
            inset 0 0 20px rgba(252, 238, 9, 0.05);
        transform: translateY(-4px);
    }
    
    /* Nier Terminal Scanline Effect */
    .cyber-post-card::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: repeating-linear-gradient(
            0deg,
            rgba(0, 0, 0, 0.15) 0px,
            transparent 1px,
            transparent 2px,
            rgba(0, 0, 0, 0.15) 3px
        );
        pointer-events: none;
        opacity: 0.3;
    }
    
    .terminal-category {
        display: inline-block;
        padding: 4px 12px;
        background: rgba(252, 238, 9, 0.1);
        border: 1px solid rgba(252, 238, 9, 0.4);
        color: #fcee09;
        font-family: 'IBM Plex Mono', 'Rajdhani', monospace;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 2px;
        text-transform: uppercase;
        position: relative;
        clip-path: polygon(4px 0, 100% 0, 100% calc(100% - 4px), calc(100% - 4px) 100%, 0 100%, 0 4px);
    }
    
    .terminal-category::before {
        content: '>';
        margin-right: 6px;
        animation: blink 1.5s infinite;
    }
    
    @keyframes blink {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.3; }
    }
    
    .cyber-title {
        font-family: 'Orbitron', sans-serif;
        font-weight: 700;
        font-size: 1.5rem;
        line-height: 1.3;
        background: linear-gradient(135deg, #fcee09 0%, #00ffff 50%, #fcee09 100%);
        background-size: 200% 200%;
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        position: relative;
        transition: all 0.3s ease;
        text-shadow: 0 0 20px rgba(252, 238, 9, 0.3);
    }
    
    .cyber-title:hover {
        background-position: 100% 50%;
        filter: drop-shadow(0 0 8px rgba(252, 238, 9, 0.6));
    }
    
    .hanko-seal-small {
        position: absolute;
        top: 8px;
        right: 8px;
        width: 32px;
        height: 32px;
        background: linear-gradient(135deg, rgba(220, 20, 60, 0.9), rgba(180, 20, 60, 0.8));
        border: 2px solid rgba(220, 20, 60, 0.6);
        clip-path: polygon(30% 0%, 70% 0%, 100% 30%, 100% 70%, 70% 100%, 30% 100%, 0% 70%, 0% 30%);
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: 'Noto Serif JP', serif;
        font-weight: 900;
        color: rgba(255, 255, 255, 0.95);
        font-size: 14px;
        box-shadow: 0 0 15px rgba(220, 20, 60, 0.4);
        z-index: 10;
    }
    
    .corner-brackets {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
    }
    
    .corner-brackets::before,
    .corner-brackets::after {
        content: '';
        position: absolute;
        width: 20px;
        height: 20px;
        border: 2px solid rgba(252, 238, 9, 0.4);
    }
    
    .corner-brackets::before {
        top: 0;
        left: 0;
        border-right: none;
        border-bottom: none;
    }
    
    .corner-brackets::after {
        bottom: 0;
        right: 0;
        border-left: none;
        border-top: none;
    }
    
    .excerpt-text {
        color: rgba(200, 200, 200, 0.85);
        font-family: 'Noto Sans JP', sans-serif;
        font-size: 0.95rem;
        line-height: 1.7;
        letter-spacing: 0.3px;
    }
    
    .meta-info {
        display: flex;
        align-items: center;
        gap: 12px;
        color: rgba(252, 238, 9, 0.7);
        font-family: 'IBM Plex Mono', monospace;
        font-size: 0.8rem;
        padding: 8px 12px;
        background: rgba(252, 238, 9, 0.05);
        border-left: 2px solid rgba(252, 238, 9, 0.4);
    }
    
    .meta-info svg {
        filter: drop-shadow(0 0 4px rgba(252, 238, 9, 0.4));
    }
    
    .cyber-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        background: linear-gradient(135deg, rgba(252, 238, 9, 0.1), rgba(0, 255, 255, 0.1));
        border: 1px solid rgba(252, 238, 9, 0.4);
        color: #fcee09;
        font-family: 'Rajdhani', sans-serif;
        font-weight: 600;
        font-size: 0.95rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        position: relative;
        overflow: hidden;
        transition: all 0.3s ease;
        clip-path: polygon(8px 0, 100% 0, 100% calc(100% - 8px), calc(100% - 8px) 100%, 0 100%, 0 8px);
    }
    
    .cyber-btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(252, 238, 9, 0.3), transparent);
        transition: left 0.5s ease;
    }
    
    .cyber-btn:hover::before {
        left: 100%;
    }
    
    .cyber-btn:hover {
        border-color: rgba(252, 238, 9, 0.8);
        box-shadow: 0 0 20px rgba(252, 238, 9, 0.4);
        transform: translateX(4px);
    }
    
    .cyber-btn svg {
        transition: transform 0.3s ease;
    }
    
    .cyber-btn:hover svg {
        transform: translateX(4px);
    }
    
    .empty-state {
        background: linear-gradient(145deg, rgba(10, 10, 10, 0.8), rgba(30, 30, 30, 0.6));
        border: 2px dashed rgba(252, 238, 9, 0.3);
        border-radius: 8px;
        padding: 80px 40px;
        text-align: center;
    }
    
    .empty-state svg {
        filter: drop-shadow(0 0 20px rgba(252, 238, 9, 0.2));
    }
    
    .empty-state h3 {
        font-family: 'Orbitron', sans-serif;
        color: #fcee09;
        font-size: 1.5rem;
        margin-bottom: 12px;
        text-shadow: 0 0 20px rgba(252, 238, 9, 0.4);
    }
    
    .empty-state p {
        color: rgba(200, 200, 200, 0.7);
        font-family: 'Noto Sans JP', sans-serif;
    }
    
    .glitch-text {
        position: relative;
        display: inline-block;
    }
    
    .glitch-text::before,
    .glitch-text::after {
        content: attr(data-text);
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        opacity: 0.8;
    }
    
    .glitch-text::before {
        color: #00ffff;
        animation: glitch-1 2s infinite;
        clip-path: polygon(0 0, 100% 0, 100% 45%, 0 45%);
    }
    
    .glitch-text::after {
        color: #ff00ff;
        animation: glitch-2 2s infinite;
        clip-path: polygon(0 55%, 100% 55%, 100% 100%, 0 100%);
    }
    
    @keyframes glitch-1 {
        0%, 100% { transform: translate(0); }
        33% { transform: translate(-2px, 1px); }
        66% { transform: translate(2px, -1px); }
    }
    
    @keyframes glitch-2 {
        0%, 100% { transform: translate(0); }
        33% { transform: translate(2px, -1px); }
        66% { transform: translate(-2px, 1px); }
    }
</style>

<div class="w-full cyber-grid-container">
    {{-- Cyberpunk Blog Posts Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-12">
        @forelse($posts as $post)
            <article class="cyber-post-card group">
                <div class="corner-brackets"></div>
                <div class="hanko-seal-small">刀</div>

                <div class="p-8 relative z-10">
                    @if($post->category)
                        <span class="terminal-category mb-4">
                            {{ $post->category->name }}
                        </span>
                    @endif

                    <h3 class="mb-4">
                        <a href="{{ route('blog.show', $post) }}" class="cyber-title block line-clamp-2">
                            {{ $post->title }}
                        </a>
                    </h3>

                    @if($post->excerpt)
                        <p class="excerpt-text mb-6 line-clamp-3">
                            {{ $post->excerpt }}
                        </p>
                    @endif

                    <div class="meta-info mb-6">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"/>
                        </svg>
                        <span>{{ $post->author->name }}</span>
                        <span class="opacity-50">|</span>
                        <time datetime="{{ $post->published_at?->toISOString() }}">
                            {{ $post->published_at?->format('d.m.Y') }}
                        </time>
                    </div>

                    <a href="{{ route('blog.show', $post) }}" class="cyber-btn">
                        <span>Weiterlesen</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </a>
                </div>
            </article>
        @empty
            <div class="col-span-full empty-state">
                <svg class="w-16 h-16 mx-auto mb-6" fill="none" stroke="#fcee09" viewBox="0 0 24 24" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <h3 class="glitch-text" data-text="KEINE DATEN GEFUNDEN">KEINE DATEN GEFUNDEN</h3>
                <p class="mt-3">Es wurden noch keine Beiträge veröffentlicht.</p>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="mt-8">
        {{ $posts->links() }}
    </div>
</div>
