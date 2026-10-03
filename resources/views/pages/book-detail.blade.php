@extends('layouts.app')

@section('title', $book->title)

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/flipbook/flipbook.css') }}">
<style>
    .preserve-3d { transform-style: preserve-3d; }
    @keyframes bookFlip {
        0%   { transform: rotateY(0deg) scaleX(1); }
        50%  { transform: rotateY(-150deg) scaleX(0.35); }
        100% { transform: rotateY(-360deg) scaleX(1); }
    }
    .animate-book-flip { animation: bookFlip 1.4s cubic-bezier(0.45, 0, 0.55, 1) infinite; transform-origin: left center; }

    @keyframes progressBar {
        0%   { width: 0%; }
        60%  { width: 75%; }
        100% { width: 100%; }
    }
    .animate-progress-bar { animation: progressBar 1.2s ease-out forwards; }
</style>
@endpush

@section('content')
<div class="max-w-6xl mx-auto space-y-8 pb-16" x-data="{
        tab: 'reader',
        readerMode: '3d',
        pdfLoading: true,
        init() { setTimeout(() => { this.pdfLoading = false }, 1200) }
    }">

    <!-- Editorial Hero Book Banner -->
    <div class="ks-panel p-6 sm:p-10 bg-ink-900 border border-ink-border text-paper relative overflow-hidden">
        <div class="relative z-10 flex flex-col sm:flex-row gap-8 items-center sm:items-start">
            
            <!-- Book Cover (2:3 nisbat) -->
            <div class="w-44 sm:w-48 aspect-[2/3] rounded-card overflow-hidden shadow-2xl shrink-0 border border-ink-border relative group bg-ink-950">
                <img src="{{ $book->cover_url }}" alt="{{ $book->title }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                <div class="absolute inset-0 bg-gradient-to-t from-ink-950/80 via-transparent to-transparent pointer-events-none"></div>
            </div>

            <!-- Book Info & Actions -->
            <div class="flex-1 space-y-4 text-center sm:text-left">
                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2">
                    <span class="px-2.5 py-0.5 bg-amber-500/10 border border-amber-500/25 text-amber-400 text-xs font-mono font-bold rounded-badge uppercase tracking-wider">
                        {{ $book->week_number }}-Hafta kitobi
                    </span>
                    <span class="px-2.5 py-0.5 bg-ink-950 border border-ink-border text-mist text-xs font-mono rounded-badge">
                        {{ $book->genre }}
                    </span>
                </div>

                <h1 class="text-2xl sm:text-4xl font-bold font-serif text-paper tracking-tight leading-tight">{{ $book->title }}</h1>
                <p class="text-xs sm:text-sm text-mist font-mono">Muallif: <strong class="text-paper font-sans font-semibold">{{ $book->author }}</strong></p>
                <p class="text-xs sm:text-sm text-mist leading-relaxed max-w-2xl font-sans">{{ $book->description }}</p>

                <!-- Quick Action Buttons -->
                <div class="pt-2 flex flex-wrap items-center justify-center sm:justify-start gap-2.5">
                    @if($book->pdf_path || $book->chapters()->where('is_published', true)->exists())
                        <button @click="tab = 'reader'; readerMode = '3d'; window.scrollTo({ top: document.getElementById('online-reader').offsetTop - 90, behavior: 'smooth' })"
                           class="ks-btn-primary inline-flex items-center gap-2">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                            <span>3D Varaqlab o'qish</span>
                        </button>
                    @endif

                    @if($firstChapter = $book->chapters()->orderBy('chapter_number')->first())
                        <a href="{{ route('reader.show', ['book' => $book->id, 'chapter' => $firstChapter->id]) }}"
                           class="ks-btn-ghost inline-flex items-center gap-2">
                            <svg class="w-4 h-4 text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                            <span>Boblar bo'ylab o'qish</span>
                        </a>
                    @endif

                    @if($book->pdf_path)
                        <a href="{{ route('books.flipbook', $book->id) }}"
                           class="ks-btn-ghost inline-flex items-center gap-2"
                           title="To'liq ekranda 3D kitob o'qish">
                            <span>⛶ To'liq ekran</span>
                        </a>
                    @endif

                    <a href="{{ route('audio.show', $book->id) }}"
                       class="ks-btn-ghost inline-flex items-center gap-2">
                        <svg class="w-4 h-4 text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 18v-6a9 9 0 0 1 18 0v6"/><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/></svg>
                        <span>Audio</span>
                        @if($book->audios()->count() > 0)
                            <span class="px-1.5 py-0.2 rounded-badge bg-amber-500/20 text-amber-300 text-[10px] font-mono font-bold">{{ $book->audios()->count() }}</span>
                        @endif
                    </a>

                    <a href="{{ route('videos.index', $book->id) }}"
                       class="ks-btn-ghost inline-flex items-center gap-2">
                        <svg class="w-4 h-4 text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg>
                        <span>Videolar</span>
                        @if($book->videos()->count() > 0)
                            <span class="px-1.5 py-0.2 rounded-badge bg-amber-500/20 text-amber-300 text-[10px] font-mono font-bold">{{ $book->videos()->count() }}</span>
                        @endif
                    </a>

                    <a href="{{ route('quiz.show', $book->id) }}"
                       class="ks-btn-ghost inline-flex items-center gap-2">
                        <svg class="w-4 h-4 text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                        <span>Test topshirish</span>
                        @if($book->quizzes()->count() > 0)
                            <span class="px-1.5 py-0.2 rounded-badge bg-emerald-500/20 text-emerald-400 text-[10px] font-mono font-bold">Faol</span>
                        @endif
                    </a>

                    @if($book->pdf_path || $book->chapters()->where('is_published', true)->exists())
                        <a href="{{ route('books.pdf', $book->id) }}"
                           class="ks-btn-ghost inline-flex items-center gap-2 text-rose-300 border-rose-500/30 hover:border-rose-500/50"
                           title="Butun kitobni PDF sifatida yuklab olish">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            <span>PDF yuklab olish</span>
                        </a>
                    @endif

                    @if(auth()->check() && auth()->user()->isAdminOrTeacher())
                        @php
                            $quizCreateRoute = auth()->user()->isAdmin()
                                ? route('admin.quizzes.create', ['book_id' => $book->id])
                                : route('teacher.quizzes.create', ['book_id' => $book->id]);
                        @endphp
                        <a href="{{ $quizCreateRoute }}"
                           class="ks-btn-gold inline-flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            <span>Ushbu kitobga test qo'shish</span>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- ── 1. ONLAYN O'QISH (3D Real Kitob + PDF Tanlov) ── -->
    <div id="online-reader" class="space-y-4 scroll-mt-24">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div class="flex items-center gap-3">
                <h2 class="text-lg font-bold font-serif text-paper flex items-center gap-2.5">
                    <span class="w-8 h-8 rounded-btn bg-amber-500/10 border border-amber-500/20 text-amber-400 flex items-center justify-center">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                    </span>
                    <span>Onlayn Mutolaa</span>
                </h2>

                <!-- Mode switcher: 3D Real Kitob vs PDF -->
                @if($book->pdf_path)
                    <div class="flex items-center p-1 rounded-btn bg-ink-900 border border-ink-border">
                        <button type="button"
                                @click="readerMode = '3d'; $nextTick(() => { if (window.initFlipbook) window.initFlipbook(); })"
                                :class="readerMode === '3d' ? 'bg-amber-500 text-ink-950 font-bold' : 'text-mist hover:text-paper'"
                                class="px-3 py-1 rounded-badge text-xs transition-colors flex items-center gap-1.5">
                            <span>3D Haqiqiy kitob</span>
                        </button>
                        <button type="button"
                                @click="readerMode = 'pdf'"
                                :class="readerMode === 'pdf' ? 'bg-ink-800 text-paper font-bold' : 'text-mist hover:text-paper'"
                                class="px-3 py-1 rounded-badge text-xs transition-colors flex items-center gap-1.5">
                            <span>Standart PDF</span>
                        </button>
                    </div>

                    <!-- Interactive Page Selector / Jumper -->
                    <div x-show="readerMode === '3d'" class="hidden md:flex items-center gap-1.5 px-3 py-1 rounded-btn bg-ink-900 border border-ink-border">
                        <span class="text-xs text-mist font-mono">Sahifa:</span>
                        <input type="number" 
                                id="fb-page-input" 
                                min="1" 
                                value="1" 
                                title="Sahifa raqamini yozing va Enter bosing"
                                class="w-12 px-1 py-0.5 text-center font-mono text-xs font-bold text-amber-400 bg-ink-950 border border-ink-border rounded-badge outline-none"
                                onkeydown="if(event.key === 'Enter') { this.blur(); fbJumpToPageInput(); }"
                                onchange="fbJumpToPageInput()"
                                onfocus="this.select()">
                        <span class="text-xs text-mist font-mono">/</span>
                        <span id="fb-total-pages" class="text-xs text-mist font-mono font-semibold">...</span>

                        <select id="fb-page-select" 
                                onchange="if(this.value) { fbGoToPage(parseInt(this.value)); }"
                                title="Ro'yxatdan sahifani tanlash"
                                class="ml-1 bg-ink-950 border border-ink-border text-mist rounded-badge text-xs py-0.5 px-1.5 outline-none cursor-pointer font-sans">
                            <option value="">Tanlash ▾</option>
                        </select>
                    </div>
                @endif
            </div>

            <div class="flex items-center gap-2">
                @if($book->pdf_path)
                    <a href="{{ route('books.flipbook', $book->id) }}"
                        class="ks-btn-ghost py-1.5 px-3 text-xs text-amber-400 flex items-center gap-1.5">
                        <span>⛶ To'liq ekran</span>
                    </a>
                @endif
                @if($book->pdf_path || $book->chapters()->where('is_published', true)->exists())
                    <a href="{{ route('books.pdf', $book->id) }}"
                        class="ks-btn-ghost py-1.5 px-3 text-xs text-rose-300 border-rose-500/30 hover:border-rose-500/50 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        <span>PDF</span>
                    </a>
                @endif
            </div>
        </div>

        @if($book->pdf_path)
            <!-- 3D Real Kitob Varaqlash Ko'rinishi (Default) -->
            <div x-show="readerMode === '3d'" class="space-y-4">
                <div id="fb-root" 
                     data-pdf-url="{{ route('books.pdf.stream', $book) }}" 
                     data-sound-url="{{ asset('sounds/oxidvideos-page-flip-1-178322.mp3') }}"
                     class="fb-stage-wrapper">
                    
                    <!-- Loading Overlay -->
                    <div id="fb-loading-overlay" class="fb-loading-overlay rounded-panel bg-ink-950/90">
                        <div class="w-12 h-12 rounded-btn bg-amber-500/15 border border-amber-500/30 text-amber-400 flex items-center justify-center text-2xl mb-3">
                            <svg class="w-6 h-6 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="2" x2="12" y2="6"/><line x1="12" y1="18" x2="12" y2="22"/><line x1="4.93" y1="4.93" x2="7.76" y2="7.76"/><line x1="16.24" y1="16.24" x2="19.07" y2="19.07"/><line x1="2" y1="12" x2="6" y2="12"/><line x1="18" y1="12" x2="22" y2="12"/><line x1="4.93" y1="19.07" x2="7.76" y2="16.24"/><line x1="16.24" y1="7.76" x2="19.07" y2="4.93"/></svg>
                        </div>
                        <p class="text-sm font-bold font-serif text-paper">Kitob varaqlari ochilmoqda...</p>
                        <p class="text-xs text-mist mt-1 font-mono">3D dvigateli ishga tushirilmoqda</p>
                    </div>

                    <!-- Floating Left Navigation Arrow -->
                    <button type="button" 
                            class="fb-nav-arrow fb-nav-arrow--left hidden sm:flex" 
                            onclick="fbGoPrev()" 
                            title="Oldingi sahifaga varaqlash">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                    </button>

                    <!-- Floating Right Navigation Arrow -->
                    <button type="button" 
                            class="fb-nav-arrow fb-nav-arrow--right hidden sm:flex" 
                            onclick="fbGoNext()" 
                            title="Keyingi sahifaga varaqlash">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                    </button>

                    <!-- Hardcover Binding Wrapper -->
                    <div class="fb-hardcover-casing">
                        <div class="fb-silk-ribbon" title="Xatcho'p"></div>

                        <!-- The Book Spread -->
                        <div id="fb-spread" class="fb-book-spread">
                            <div class="fb-page-pane fb-page-pane--left">
                                <canvas id="fb-canvas-left" class="fb-canvas"></canvas>
                                <div class="fb-corner-curl fb-corner-curl--bottom-left" onclick="fbGoPrev()" title="Oldingi sahifa"></div>
                            </div>

                            <div class="fb-book-spine"></div>

                            <div class="fb-page-pane fb-page-pane--right">
                                <canvas id="fb-canvas-right" class="fb-canvas"></canvas>
                                <div class="fb-corner-curl fb-corner-curl--bottom-right" onclick="fbGoNext()" title="Keyingi sahifa"></div>
                            </div>

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

                            <div class="fb-click-zone fb-click-zone--prev" onclick="fbGoPrev()" title="Oldingi sahifaga varaqlash"></div>
                            <div class="fb-click-zone fb-click-zone--next" onclick="fbGoNext()" title="Keyingi sahifaga varaqlash"></div>
                        </div>
                    </div>

                    <!-- Desk Controls Shelf -->
                    <div class="fb-controls-shelf">
                        <button type="button" 
                                onclick="fbGoPrev()" 
                                class="ks-btn-ghost py-1.5 px-3 text-xs shrink-0">
                            <span>← Oldingi</span>
                        </button>

                        <div class="flex-1 flex items-center gap-3">
                            <span id="fb-page-indicator" class="font-mono text-xs text-amber-400 font-bold whitespace-nowrap">
                                Yuklanmoqda...
                            </span>
                            <input type="range" id="fb-page-slider" min="1" max="100" value="1" class="fb-page-slider" title="Tez o'tish slayderi">
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            <div class="hidden sm:flex items-center bg-ink-950 border border-ink-border rounded-btn overflow-hidden p-0.5">
                                <button type="button" onclick="fbZoomOut()" title="Kichiklashtirish (-)" class="p-1 text-mist hover:text-paper transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                                </button>
                                <button type="button" onclick="fbZoomReset()" id="fb-zoom-level" title="100% asl o'lchamga qaytarish" class="px-1.5 text-[11px] font-mono font-bold text-amber-400">
                                    100%
                                </button>
                                <button type="button" onclick="fbZoomIn()" title="Kattalashtirish (+)" class="p-1 text-mist hover:text-paper transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                </button>
                            </div>

                            <button type="button" 
                                    id="fb-sound-btn"
                                    onclick="fbToggleSound()" 
                                    title="Varaqlash ovozi" 
                                    class="ks-btn-ghost p-1.5 text-xs">
                                🔊
                            </button>
                            <button type="button" 
                                    onclick="fbGoNext()" 
                                    class="ks-btn-primary py-1.5 px-3 text-xs">
                                <span>Keyingi →</span>
                            </button>
                        </div>
                    </div>

                </div>

                <!-- Quick Hint -->
                <div class="flex items-center justify-center gap-4 text-xs text-mist font-mono">
                    <span class="flex items-center gap-1.5">
                        <kbd class="px-1.5 py-0.5 rounded-badge bg-ink-900 border border-ink-border text-paper text-[10px]">←</kbd>
                        <kbd class="px-1.5 py-0.5 rounded-badge bg-ink-900 border border-ink-border text-paper text-[10px]">→</kbd>
                        <span>Klaviatura o'qlari yoki sahifa burchaklariga bosib varaqlang</span>
                    </span>
                </div>
            </div>

            <!-- Standart PDF Viewer -->
            <div x-show="readerMode === 'pdf'" x-cloak class="relative rounded-card overflow-hidden border border-ink-border bg-ink-900">
                <iframe id="bookPdfFrame"
                    src="{{ route('books.pdf.read', $book->id) }}#view=FitH&pagemode=none"
                    class="w-full h-[75vh] min-h-[500px] bg-white"
                    title="{{ $book->title }} — onlayn o'qish"
                    allowfullscreen></iframe>
            </div>
        @elseif($book->chapters()->where('is_published', true)->exists())
            <div class="relative rounded-card overflow-hidden border border-ink-border bg-ink-900">
                <iframe id="bookPdfFrame"
                    src="{{ route('books.pdf.read', $book->id) }}#view=FitH&pagemode=none"
                    class="w-full h-[75vh] min-h-[500px] bg-white"
                    title="{{ $book->title }} — onlayn o'qish"
                    allowfullscreen></iframe>
            </div>
        @else
            <div class="p-10 rounded-card bg-ink-900 border border-ink-border text-center space-y-2">
                <svg class="w-8 h-8 mx-auto text-amber-500 mb-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                <p class="text-sm text-paper font-serif font-bold">Bu kitob uchun hali PDF fayl yuklanmagan.</p>
                <p class="text-xs text-mist font-sans">Admin/teacher paneldan kitobga PDF fayl yuklaydi yoki boblar qo'shilganda avtomatik PDF yaratiladi.</p>
            </div>
        @endif
    </div>

    <!-- Tabs Navigation -->
    <div class="flex items-center gap-2 border-b border-ink-border pb-2 overflow-x-auto">
        <button @click="tab = 'chapters'" :class="{ 'bg-ink-800 text-paper border-b-2 border-amber-400': tab === 'chapters', 'text-mist hover:text-paper': tab !== 'chapters' }"
                class="px-4 py-2 rounded-btn text-xs font-mono font-bold transition-all whitespace-nowrap">
            Boblar ro'yxati ({{ $book->chapters()->count() }})
        </button>
        <button @click="tab = 'overview'" :class="{ 'bg-ink-800 text-paper border-b-2 border-amber-400': tab === 'overview', 'text-mist hover:text-paper': tab !== 'overview' }"
                class="px-4 py-2 rounded-btn text-xs font-mono font-bold transition-all whitespace-nowrap">
            Kitob haqida umumiy
        </button>
    </div>

    <!-- Tab 1: Chapters -->
    <div x-show="tab === 'chapters'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-3">
        @forelse($book->chapters()->where('is_published', true)->orderBy('chapter_number')->get() as $chapter)
            <div class="p-4 sm:p-5 rounded-card bg-ink-900 border border-ink-border flex items-center justify-between gap-4 transition-colors hover:border-ink-border/80 group">
                <div class="flex items-center gap-4">
                    <div class="w-9 h-9 rounded-btn bg-ink-950 border border-ink-border flex items-center justify-center text-amber-400 font-bold font-mono text-sm shrink-0">
                        {{ $chapter->chapter_number }}
                    </div>
                    <div>
                        <h4 class="text-sm font-bold font-serif text-paper group-hover:text-amber-400 transition-colors">
                            {{ $chapter->title }}
                        </h4>
                        @if($chapter->duration_minutes)
                            <span class="text-xs text-mist flex items-center gap-1 mt-0.5 font-mono">
                                ⏱ {{ $chapter->duration_minutes }} daqiqa mutolaa
                            </span>
                        @endif
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ route('reader.show', ['book' => $book->id, 'chapter' => $chapter->id]) }}"
                       class="ks-btn-ghost py-1.5 px-3 text-xs inline-flex items-center gap-1.5">
                        <span>O'qish</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </a>
                </div>
            </div>
        @empty
            <div class="p-8 rounded-card bg-ink-900 border border-ink-border text-center text-mist text-xs font-mono">
                Bu kitob uchun hozircha alohida boblar kiritilmagan. Yuqoridagi onlayn o'qish orqali to'liq kitobni mutolaa qilishingiz mumkin.
            </div>
        @endforelse
    </div>

    <!-- Tab 2: Overview -->
    <div x-show="tab === 'overview'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="p-6 rounded-card bg-ink-900 border border-ink-border space-y-4">
        <h3 class="text-base font-bold font-serif text-paper">To'liq tavsif va mazmuni</h3>
        <div class="text-sm text-mist leading-relaxed font-sans">
            {{ $book->description }}
        </div>
    </div>

</div>
@endsection

@push('scripts')
@if($book->pdf_path)
<!-- PDF.js CDN -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<!-- Flipbook Core Engine -->
<script src="{{ asset('assets/flipbook/flipbook.js') }}"></script>
@endif
@endpush
