@extends('layouts.app')

@section('title', 'Varaqlab o\'qish — ' . $book->title)

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/flipbook/flipbook.css') }}">
@endpush

@section('content')
<div id="fb-standalone-container" class="w-full max-w-[1700px] mx-auto space-y-3 px-1 sm:px-4 pb-8">

    <!-- Top Navigation & Tools Bar -->
    <div class="flex items-center justify-between flex-wrap gap-3 border-b border-white/10 pb-3">
        <div class="flex items-center gap-3">
            <a href="{{ route('books.show', $book->slug) }}" 
               class="px-4 py-2 rounded-xl bg-ink-900/90 border border-white/10 text-slate-300 hover:text-white hover:border-amber-400/30 text-xs font-semibold transition-all flex items-center gap-2">
                <span>← Chiqish</span>
            </a>
            <div>
                <h1 class="text-base sm:text-lg font-black text-white leading-tight flex items-center gap-2">
                    <span>📖</span>
                    <span>{{ $book->title }}</span>
                </h1>
                <p class="text-[11px] text-slate-400">Muallif: {{ $book->author }} • 3D Real Kitob Mutolaasi</p>
            </div>
        </div>

        <!-- Controls: Page Indicator & Actions -->
        <div class="flex items-center gap-2 sm:gap-3 flex-wrap">
            <!-- Interactive Page Selector / Jumper -->
            <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-ink-900 border border-white/10 shadow-inner group">
                <span class="text-xs text-slate-400 font-medium">Sahifa:</span>
                <input type="number" 
                       id="fb-page-input" 
                       min="1" 
                       value="1" 
                       title="Sahifa raqamini yozing va Enter bosing"
                       class="w-14 sm:w-16 px-1.5 py-0.5 text-center font-mono text-xs font-bold text-amber-400 bg-white/5 border border-white/15 hover:border-amber-400/50 focus:border-amber-400 focus:bg-amber-400/10 focus:ring-1 focus:ring-amber-400 rounded-lg outline-none transition-all"
                       onkeydown="if(event.key === 'Enter') { this.blur(); fbJumpToPageInput(); }"
                       onchange="fbJumpToPageInput()"
                       onfocus="this.select()">
                <span class="text-xs text-slate-400 font-mono">/</span>
                <span id="fb-total-pages" class="text-xs text-slate-300 font-mono font-semibold">...</span>

                <!-- Quick Dropdown Page Selector -->
                <select id="fb-page-select" 
                        onchange="if(this.value) { fbGoToPage(parseInt(this.value)); }"
                        title="Ro'yxatdan sahifani tanlash"
                        class="ml-1 bg-white/5 hover:bg-white/10 border border-white/15 text-slate-300 hover:text-white rounded-lg text-xs py-0.5 px-1.5 outline-none cursor-pointer font-sans">
                    <option value="" class="bg-slate-900 text-slate-400">Tanlash ▾</option>
                </select>
            </div>

            <!-- Zoom Controls with Percentage -->
            <div class="flex items-center bg-ink-900 border border-white/10 rounded-xl overflow-hidden p-0.5 shadow-inner">
                <button type="button" 
                        onclick="fbZoomOut()" 
                        title="Kichiklashtirish (klaviaturada: -)" 
                        class="p-2 text-slate-400 hover:text-white hover:bg-white/10 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                </button>
                <button type="button" 
                        onclick="fbZoomReset()" 
                        id="fb-zoom-level" 
                        title="100% asl o'lchamga qaytarish" 
                        class="px-2.5 text-xs font-mono font-bold text-amber-400 hover:text-amber-300">
                    100%
                </button>
                <button type="button" 
                        onclick="fbZoomIn()" 
                        title="Kattalashtirish (klaviaturada: +)" 
                        class="p-2 text-slate-400 hover:text-white hover:bg-white/10 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                </button>
            </div>

            <!-- Sound toggle button -->
            <button type="button" 
                    id="fb-sound-btn"
                    onclick="fbToggleSound()" 
                    title="Varaqlash ovozini yoqish/o'chirish" 
                    class="px-3 py-1.5 rounded-xl bg-ink-900 border border-white/10 text-slate-300 hover:text-white text-xs font-medium transition-colors flex items-center gap-1.5">
                🔊 Ovoz
            </button>

            <!-- Ambient Music toggle button -->
            <button type="button" 
                    onclick="window.dispatchEvent(new CustomEvent('open-ambient-music'))" 
                    title="Mutolaa fon musiqasi (M klavishi)" 
                    class="px-3 py-1.5 rounded-xl bg-ink-900 border border-white/10 hover:border-amber-400/40 text-amber-400 hover:text-amber-300 text-xs font-medium transition-colors flex items-center gap-1.5">
                🎵 Musiqa
            </button>

            <!-- Fullscreen -->
            <button type="button" 
                    onclick="fbToggleFullscreen()" 
                    title="To'liq ekran (F11)" 
                    class="p-2 rounded-xl bg-ink-900 border border-white/10 text-slate-400 hover:text-white hover:border-amber-400/30 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
            </button>

            <!-- PDF Download -->
            <a href="{{ route('books.pdf', $book) }}" 
               title="PDF yuklab olish" 
               class="px-3.5 py-2 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 border border-rose-500/25 text-rose-400 text-xs font-semibold flex items-center gap-1.5 transition-all">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span class="hidden sm:inline">PDF</span>
            </a>
        </div>
    </div>

    <!-- ── Ultra-Realistic 3D Flipbook Stage ── -->
    <div id="fb-root" 
         data-pdf-url="{{ route('books.pdf.stream', $book) }}" 
         data-sound-url="{{ asset('sounds/oxidvideos-page-flip-1-178322.mp3') }}"
         class="fb-stage-wrapper">
        
        <!-- Loading Overlay -->
        <div id="fb-loading-overlay" class="fb-loading-overlay rounded-3xl">
            <div class="w-16 h-16 rounded-2xl bg-amber-500/15 border border-amber-500/30 text-amber-400 flex items-center justify-center text-3xl animate-bounce mb-3 shadow-lg shadow-amber-500/20">
                📖
            </div>
            <p class="text-base font-bold text-white tracking-wide">Kitob varaqlari ochilmoqda...</p>
            <p class="text-xs text-slate-400 mt-1">3D varaqlash dvigateli ishga tushirilmoqda</p>
        </div>

        <!-- Floating Left Navigation Arrow -->
        <button type="button" 
                class="fb-nav-arrow fb-nav-arrow--left hidden sm:flex" 
                onclick="fbGoPrev()" 
                title="Oldingi sahifaga varaqlash">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
        </button>

        <!-- Floating Right Navigation Arrow -->
        <button type="button" 
                class="fb-nav-arrow fb-nav-arrow--right hidden sm:flex" 
                onclick="fbGoNext()" 
                title="Keyingi sahifaga varaqlash">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
        </button>

        <!-- Hardcover Binding Wrapper (Qattiq charm muqova hoshiyasi) -->
        <div class="fb-hardcover-casing">

            <!-- Red Silk Ribbon Bookmark (Xatcho'p) -->
            <div class="fb-silk-ribbon" title="Xatcho'p"></div>

            <!-- The Book Spread (2-Pages Desktop, 1-Page Mobile) -->
            <div id="fb-spread" class="fb-book-spread">
                
                <!-- Left Page Canvas with Thickness Shadows -->
                <div class="fb-page-pane fb-page-pane--left">
                    <canvas id="fb-canvas-left" class="fb-canvas"></canvas>
                    <div class="fb-corner-curl fb-corner-curl--bottom-left" onclick="fbGoPrev()" title="Oldingi sahifa"></div>
                </div>

                <!-- Realistic Book Spine / Seam -->
                <div class="fb-book-spine"></div>

                <!-- Right Page Canvas with Thickness Shadows -->
                <div class="fb-page-pane fb-page-pane--right">
                    <canvas id="fb-canvas-right" class="fb-canvas"></canvas>
                    <div class="fb-corner-curl fb-corner-curl--bottom-right" onclick="fbGoNext()" title="Keyingi sahifa"></div>
                </div>

                <!-- 3D Flipping Leaf Overlay with Realistic Shadows -->
                <div id="fb-flip-container" class="fb-flip-container">
                    <div id="fb-flip-leaf" class="fb-flip-leaf">
                        <div class="fb-flip-front">
                            <canvas id="fb-flip-front" class="fb-flip-canvas"></canvas>
                        </div>
                        <div class="fb-flip-back">
                            <canvas id="fb-flip-back" class="fb-flip-canvas"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Edge Click Zones for Fast Page Turn -->
                <div class="fb-click-zone fb-click-zone--prev" onclick="fbGoPrev()" title="Oldingi sahifaga varaqlash (yoki chap o'q tugmasi)"></div>
                <div class="fb-click-zone fb-click-zone--next" onclick="fbGoNext()" title="Keyingi sahifaga varaqlash (yoki o'ng o'q tugmasi)"></div>

            </div>
        </div>

        <!-- Desk Controls Shelf: Page Slider & Turn buttons -->
        <div class="fb-controls-shelf">
            <button type="button" 
                    onclick="fbGoPrev()" 
                    class="px-4 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-white font-semibold text-xs transition-all flex items-center gap-1.5 active:scale-95 shrink-0">
                <span>← Oldingi</span>
            </button>

            <div class="flex-1 flex items-center gap-3">
                <span class="text-[11px] text-slate-400 font-mono hidden sm:inline">1</span>
                <input type="range" id="fb-page-slider" min="1" max="100" value="1" class="fb-page-slider" title="Tez o'tish slayderi">
                <span class="text-[11px] text-slate-400 font-mono hidden sm:inline">Oxiri</span>
            </div>

            <button type="button" 
                    onclick="fbGoNext()" 
                    class="px-5 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs uppercase tracking-wider transition-all flex items-center gap-1.5 shadow-md shadow-amber-500/20 active:scale-95 shrink-0">
                <span>Keyingi →</span>
            </button>
        </div>

    </div>

    <!-- Quick Keyboard Shortcut Hint -->
    <div class="flex items-center justify-center gap-4 text-xs text-slate-400 pt-1">
        <span class="flex items-center gap-1.5">
            <kbd class="px-2 py-0.5 rounded bg-white/10 text-white font-mono text-[11px]">←</kbd>
            <kbd class="px-2 py-0.5 rounded bg-white/10 text-white font-mono text-[11px]">→</kbd>
            <span>Varaqlash</span>
        </span>
        <span class="text-white/20">•</span>
        <span class="flex items-center gap-1.5">
            <kbd class="px-2 py-0.5 rounded bg-white/10 text-white font-mono text-[11px]">+</kbd>
            <kbd class="px-2 py-0.5 rounded bg-white/10 text-white font-mono text-[11px]">-</kbd>
            <span>Kattalashtirish (Zoom)</span>
        </span>
        <span class="text-white/20">•</span>
        <span>Sahifani 2 marta tez bosib yaqinlashtirish (Double-click)</span>
    </div>

    <!-- Reading Tracker & 5-minute AFK Inactivity Modal -->
    @include('components.reading-tracker', [
        'bookId' => $book->id,
        'chapterId' => null,
        'pageType' => 'flipbook'
    ])

    <!-- Ambient Background Music Player -->
    <x-ambient-music-player :book="$book" />

</div>
@endsection

@push('scripts')
<!-- PDF.js CDN -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<!-- Flipbook Core Engine -->
<script src="{{ asset('assets/flipbook/flipbook.js') }}"></script>
@endpush
