<style>
/* Book Card — 3D Opening System */
.book-card-outer {
    position: relative;
    display: flex;
    flex-direction: column;
    width: 100%;
    cursor: pointer;
}

/* Expanding shadow underneath book */
.book-shadow-base {
    position: absolute;
    bottom: 112px; /* below cover, above info */
    left: 8%;
    right: 8%;
    height: 20px;
    background: rgba(0,0,0,0.5);
    border-radius: 50%;
    filter: blur(12px);
    transition: all 0.55s cubic-bezier(0.22, 1, 0.36, 1);
    z-index: 0;
}
.book-shadow-open {
    left: 12%;
    right: 2%;
    opacity: 0.8;
    filter: blur(18px);
    transform: scaleX(1.1);
}

/* The rotating cover unit */
.book-cover-wrap {
    position: relative;
    width: 100%;
    aspect-ratio: 2/3;
    transform-origin: left center;
    transform-style: preserve-3d;
    transition: transform 0.55s cubic-bezier(0.22, 1, 0.36, 1);
    border-radius: 4px 8px 8px 4px;
    z-index: 1;
}

/* Back face - visible during rotation */
.book-back-face {
    position: absolute;
    inset: 0;
    background: #161920;
    border-radius: 4px 8px 8px 4px;
    backface-visibility: hidden;
    transform: rotateY(180deg) translateZ(1px);
    overflow: hidden;
    border-left: 3px solid #C1392B;
}

/* Page edges - the spine/pages side */
.book-pages-edge {
    position: absolute;
    top: 2px;
    bottom: 2px;
    left: -8px;
    width: 8px;
    background: linear-gradient(to right, #c8bfb0 0%, #e8e2d5 30%, #d4cdc4 50%, #e8e2d5 70%, #c8bfb0 100%);
    transform: rotateY(-90deg) translateZ(0px);
    transform-origin: right center;
    box-shadow:
        inset -1px 0 0 rgba(0,0,0,0.15),
        inset 1px 0 0 rgba(255,255,255,0.3),
        inset -3px 0 0 rgba(0,0,0,0.08),
        inset 3px 0 0 rgba(255,255,255,0.15);
}

/* Front face */
.book-front-face {
    position: absolute;
    inset: 0;
    backface-visibility: hidden;
    border-radius: 4px 8px 8px 4px;
    overflow: hidden;
    box-shadow:
        -4px 0 8px rgba(0,0,0,0.4),
        2px 0 0 rgba(0,0,0,0.3),
        inset 4px 0 12px rgba(0,0,0,0.25);
    transition: box-shadow 0.55s cubic-bezier(0.22, 1, 0.36, 1);
}

/* Front face shadow deepens as it opens */
.book-cover-wrap:hover .book-front-face,
[style*="rotateY(-32deg)"] .book-front-face {
    box-shadow:
        -12px 4px 24px rgba(0,0,0,0.6),
        4px 0 0 rgba(0,0,0,0.4),
        inset 6px 0 16px rgba(0,0,0,0.35);
}

/* Card info below */
.book-card-info {
    padding: 14px 2px 0;
    flex: 1;
    display: flex;
    flex-direction: column;
}

.book-title {
    font-family: 'Spectral', serif;
    font-size: 0.875rem;
    font-weight: 700;
    line-height: 1.3;
    color: #F0EDE6;
    margin: 0 0 3px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.book-author {
    font-family: 'DM Mono', monospace;
    font-size: 0.65rem;
    color: #8B9BAD;
    margin: 0 0 10px;
    letter-spacing: 0.02em;
    text-transform: uppercase;
}

.book-card-actions {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: auto;
    padding-top: 10px;
}

.book-action-primary {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 7px 14px;
    background: #C1392B;
    color: #F0EDE6;
    font-family: 'DM Sans', sans-serif;
    font-size: 0.7rem;
    font-weight: 600;
    border-radius: 4px;
    text-decoration: none;
    transition: background 0.2s ease, transform 0.15s ease;
    flex: 1;
    justify-content: center;
}
.book-action-primary:hover {
    background: #A52A1E;
    transform: translateY(-1px);
}
.book-action-primary:active {
    transform: translateY(0) scale(0.98);
}

.book-action-secondary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    background: rgba(240,237,230,0.08);
    color: #8B9BAD;
    border-radius: 4px;
    text-decoration: none;
    border: 1px solid rgba(232,226,213,0.12);
    transition: all 0.2s ease;
    flex-shrink: 0;
}
.book-action-secondary:hover {
    background: rgba(193,57,43,0.15);
    color: #C1392B;
    border-color: rgba(193,57,43,0.3);
}

/* Reduced motion fallback */
@media (prefers-reduced-motion: reduce) {
    .book-cover-wrap {
        transition: opacity 0.2s ease, transform 0.2s ease;
    }
    .book-cover-wrap:hover {
        transform: none !important;
        opacity: 0.92;
    }
    .book-shadow-base {
        display: none;
    }
}

/* Mobile: no 3D, show simple scale */
@media (hover: none) {
    .book-cover-wrap {
        transform: none !important;
    }
}
</style>
{{-- 
    Book Card Component
    Props:
      $book        - App\Models\Book instance (required)
      $showProgress - bool (optional, default false)
      $progress    - BookReadingProgress|null (optional)
      $wireKey     - string (optional, for wire:key)
--}}
@props([
    'book',
    'showProgress' => false,
    'progress' => null,
    'wireKey' => null,
])

<div 
    @if($wireKey) wire:key="book-card-{{ $wireKey }}" @endif
    x-data="{ flipped: false, reduced: window.matchMedia('(prefers-reduced-motion: reduce)').matches }"
    @touchstart.passive="if(!reduced) flipped = !flipped"
    class="book-card-outer group"
    style="perspective: 1200px;"
>
    {{-- Shadow that grows on hover --}}
    <div 
        class="book-shadow-base"
        :class="flipped ? 'book-shadow-open' : ''"
    ></div>

    {{-- The rotating book wrapper --}}
    <div 
        class="book-cover-wrap"
        :style="!reduced && flipped ? 'transform: rotateY(-32deg) translateX(4px);' : ''"
        @mouseenter="if(!reduced) flipped = true"
        @mouseleave="flipped = false"
    >
        {{-- Back face: shown during rotation --}}
        <div class="book-back-face">
            <div class="p-4 flex flex-col justify-end h-full">
                <p class="text-[11px] font-mono text-vermilion mb-1 uppercase tracking-widest">{{ $book->genre }}</p>
                <h4 class="text-sm font-bold text-paper leading-snug mb-2" style="font-family: 'Spectral', serif;">{{ $book->title }}</h4>
                <p class="text-[11px] text-mist line-clamp-3 leading-relaxed" style="font-family: 'DM Sans', sans-serif;">{{ Str::limit($book->description, 100) }}</p>
            </div>
        </div>

        {{-- Page edges (spine effect) --}}
        <div class="book-pages-edge"></div>

        {{-- Front face: the actual cover --}}
        <div class="book-front-face">
            {{-- Week Badge --}}
            <div class="absolute top-3 left-3 z-10">
                <span class="inline-block px-2 py-0.5 text-[9px] font-black uppercase tracking-widest" 
                      style="background: #B8860B; color: #0D0F14; font-family: 'DM Mono', monospace; border-radius: 3px;">
                    {{ $book->week_number }}-hafta
                </span>
            </div>

            {{-- Format badges (top right) --}}
            <div class="absolute top-3 right-3 z-10 flex flex-col gap-1 items-end">
                @if(isset($book->pdf_path) && $book->pdf_path)
                    <span class="px-1.5 py-0.5 text-[8px] font-black rounded" style="background:#C1392B; color:white; font-family:'DM Mono',monospace;">PDF</span>
                @endif
                @if(isset($book->audios_count) && $book->audios_count > 0)
                    <span class="px-1.5 py-0.5 text-[8px] font-black rounded" style="background:#2D6A4F; color:white; font-family:'DM Mono',monospace;">AUDIO</span>
                @endif
                @if(isset($book->videos_count) && $book->videos_count > 0)
                    <span class="px-1.5 py-0.5 text-[8px] font-black rounded" style="background:#7B2D8B; color:white; font-family:'DM Mono',monospace;">VIDEO</span>
                @endif
            </div>

            {{-- Cover image --}}
            <img 
                src="{{ $book->cover_url }}" 
                alt="{{ $book->title }}"
                class="w-full h-full object-cover"
                onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($book->title) }}&size=512&background=1a0a08&color=E8E2D5&bold=true&font-size=0.33'"
            >

            {{-- Bottom gradient overlay --}}
            <div class="absolute inset-x-0 bottom-0 h-24" style="background: linear-gradient(to top, #0D0F14 0%, transparent 100%);"></div>

            {{-- Genre at bottom --}}
            <div class="absolute bottom-3 left-3 right-3 flex items-center justify-between z-10">
                <span class="text-[10px] font-medium" style="color: #8B9BAD; font-family: 'DM Mono', monospace;">{{ $book->genre }}</span>
                @if(isset($book->chapters_count))
                    <span class="text-[10px]" style="color: #8B9BAD; font-family: 'DM Mono', monospace;">{{ $book->chapters_count }} bob</span>
                @endif
            </div>
        </div>
    </div>

    {{-- Card info below the book --}}
    <div class="book-card-info">
        <h3 class="book-title">{{ $book->title }}</h3>
        <p class="book-author">{{ $book->author }}</p>

        @if($showProgress && $progress)
            <div class="mt-3">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-[10px]" style="color:#8B9BAD; font-family:'DM Mono',monospace;">{{ round($progress->percent_complete) }}%</span>
                    <span class="text-[10px]" style="color:#8B9BAD; font-family:'DM Mono',monospace;">{{ $progress->chapter?->title ?? '' }}</span>
                </div>
                <div style="height:2px; background:#1a1f2a; border-radius:1px;">
                    <div style="height:2px; width:{{ $progress->percent_complete }}%; background:#C1392B; border-radius:1px; transition: width 0.6s ease;"></div>
                </div>
            </div>
        @endif

        <div class="book-card-actions">
            @if(isset($book->pdf_path) && $book->pdf_path)
                <a href="{{ route('books.flipbook', $book->id) }}" class="book-action-secondary" title="Varaqlab o'qish">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                </a>
            @endif
            <a href="{{ route('books.show', $book->slug) }}" class="book-action-primary">
                Ko'rish
                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </a>
        </div>
    </div>
</div>
