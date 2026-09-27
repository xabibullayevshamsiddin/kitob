@extends('layouts.app')

@section('title', $book->title)

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/flipbook/flipbook.css') }}">
<style>
    /* 3D kitob sahifa burilish animatsiyasi */
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

    <!-- Hero Book Banner -->
    <div class="p-6 sm:p-10 rounded-3xl bg-gradient-to-br from-indigo-950 via-slate-900 to-slate-900 border border-indigo-900/40 text-white relative overflow-hidden shadow-xl animate-fade-in">
        <div class="absolute -right-20 -top-20 w-96 h-96 bg-indigo-500/15 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col sm:flex-row gap-8 items-center sm:items-start">
            <div class="w-44 h-64 rounded-2xl overflow-hidden shadow-2xl shrink-0 ring-2 ring-white/10 relative group">
                <img src="{{ $book->cover_url }}" alt="{{ $book->title }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                <!-- 3D kitob ochilish effekti -->
                <div class="absolute inset-0 bg-gradient-to-r from-black/40 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
            </div>

            <div class="flex-1 space-y-4 text-center sm:text-left">
                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2">
                    <span class="px-3 py-1 bg-amber-500 text-slate-950 text-xs font-black rounded-lg uppercase">
                        {{ $book->week_number }}-Hafta kitobi
                    </span>
                    <span class="px-3 py-1 bg-indigo-500/20 text-indigo-300 text-xs font-semibold rounded-lg">
                        {{ $book->genre }}
                    </span>
                </div>

                <h1 class="text-2xl sm:text-4xl font-black font-manrope tracking-tight leading-tight">{{ $book->title }}</h1>
                <p class="text-sm text-indigo-200">Muallif: <strong class="text-white font-bold">{{ $book->author }}</strong></p>
                <p class="text-xs sm:text-sm text-slate-300 leading-relaxed max-w-2xl">{{ $book->description }}</p>

                <!-- Quick Action Buttons -->
                <div class="pt-2 flex flex-wrap items-center justify-center sm:justify-start gap-3">
                    @if($book->pdf_path || $book->chapters()->where('is_published', true)->exists())
                        <button @click="tab = 'reader'; readerMode = '3d'; window.scrollTo({ top: document.getElementById('online-reader').offsetTop - 90, behavior: 'smooth' })"
                           class="px-6 py-2.5 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs rounded-xl shadow-md shadow-amber-500/30 transition-all flex items-center gap-2 active:scale-95">
                            <span>📖 3D Varaqlab o'qish</span>
                        </button>
                    @endif

                    @if($firstChapter = $book->chapters()->orderBy('chapter_number')->first())
                        <a href="{{ route('reader.show', ['book' => $book->id, 'chapter' => $firstChapter->id]) }}"
                           class="px-5 py-2.5 bg-white/10 hover:bg-white/20 text-white font-semibold text-xs rounded-xl backdrop-blur-md transition-all flex items-center gap-2">
                            <span>✍️ Boblar bo'ylab o'qish</span>
                        </a>
                    @endif

                    @if($book->pdf_path)
                        <a href="{{ route('books.flipbook', $book->id) }}"
                           class="px-5 py-2.5 bg-white/10 hover:bg-white/20 border border-white/20 text-white font-semibold text-xs rounded-xl backdrop-blur-md transition-all flex items-center gap-2"
                           title="To'liq ekranda 3D kitob o'qish">
                            <span>⛶ To'liq ekran 3D</span>
                        </a>
                    @endif

                    <a href="{{ route('audio.show', $book->id) }}"
                       class="px-5 py-2.5 bg-white/10 hover:bg-white/20 text-white font-semibold text-xs rounded-xl backdrop-blur-md transition-all flex items-center gap-2">
                        <span>🎧 Audio</span>
                        @if($book->audios()->count() > 0)
                            <span class="px-1.5 py-0.5 rounded-full bg-amber-500/20 text-amber-300 text-[10px] font-mono font-bold">{{ $book->audios()->count() }}</span>
                        @endif
                    </a>

                    <a href="{{ route('videos.index', $book->id) }}"
                       class="px-5 py-2.5 bg-white/10 hover:bg-white/20 text-white font-semibold text-xs rounded-xl backdrop-blur-md transition-all flex items-center gap-2">
                        <span>🎬 Videolar</span>
                        @if($book->videos()->count() > 0)
                            <span class="px-1.5 py-0.5 rounded-full bg-amber-500/20 text-amber-300 text-[10px] font-mono font-bold">{{ $book->videos()->count() }}</span>
                        @endif
                    </a>

                    <a href="{{ route('quiz.show', $book->id) }}"
                       class="px-5 py-2.5 bg-white/10 hover:bg-white/20 text-white font-semibold text-xs rounded-xl backdrop-blur-md transition-all flex items-center gap-2">
                        <span>🧠 Test topshirish</span>
                        @if($book->quizzes()->count() > 0)
                            <span class="px-1.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 text-[10px] font-mono font-bold">Faol</span>
                        @endif
                    </a>

                    @if($book->pdf_path || $book->chapters()->where('is_published', true)->exists())
                        <a href="{{ route('books.pdf', $book->id) }}"
                           class="px-5 py-2.5 bg-rose-500/15 hover:bg-rose-500/25 border border-rose-500/30 text-rose-300 font-semibold text-xs rounded-xl transition-all flex items-center gap-2"
                           title="Butun kitobni PDF sifatida yuklab olish">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            <span>PDF yuklab olish</span>
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
                <h2 class="text-lg font-black text-slate-900 dark:text-white font-manrope flex items-center gap-2.5">
                    <span class="w-9 h-9 rounded-2xl bg-amber-500/15 text-amber-500 flex items-center justify-center text-lg">📖</span>
                    <span>Onlayn Mutolaa</span>
                </h2>

                <!-- Mode switcher: 3D Real Kitob vs PDF -->
                @if($book->pdf_path)
                    <div class="flex items-center p-1 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700/60 shadow-inner">
                        <button type="button"
                                @click="readerMode = '3d'; $nextTick(() => { if (window.initFlipbook) window.initFlipbook(); })"
                                :class="readerMode === '3d' ? 'bg-amber-500 text-slate-950 font-bold shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:text-white'"
                                class="px-3 py-1.5 rounded-lg text-xs transition-all flex items-center gap-1.5">
                            <span>📖 3D Haqiqiy kitob</span>
                        </button>
                        <button type="button"
                                @click="readerMode = 'pdf'"
                                :class="readerMode === 'pdf' ? 'bg-indigo-600 text-white font-bold shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:text-white'"
                                class="px-3 py-1.5 rounded-lg text-xs transition-all flex items-center gap-1.5">
                            <span>📄 Standart PDF</span>
                        </button>
                    </div>
                @endif
            </div>

            <div class="flex items-center gap-2">
                @if($book->pdf_path)
                    <a href="{{ route('books.flipbook', $book->id) }}"
                        class="px-4 py-2 rounded-xl bg-amber-500/15 hover:bg-amber-500/25 border border-amber-500/30 text-amber-400 font-bold text-xs flex items-center gap-1.5 transition-all">
                        ⛶ To'liq ekran (3D)
                    </a>
                @endif
                @if($book->pdf_path || $book->chapters()->where('is_published', true)->exists())
                    <a href="{{ route('books.pdf', $book->id) }}"
                        class="px-4 py-2 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 border border-rose-500/25 text-rose-500 dark:text-rose-400 font-bold text-xs flex items-center gap-1.5 transition-all">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        PDF yuklab olish
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
                            <div class="fb-click-zone fb-click-zone--prev" onclick="fbGoPrev()" title="Oldingi sahifaga varaqlash"></div>
                            <div class="fb-click-zone fb-click-zone--next" onclick="fbGoNext()" title="Keyingi sahifaga varaqlash"></div>

                        </div>
                    </div>

                    <!-- Desk Controls Shelf: Page Indicator, Slider & Turn buttons -->
                    <div class="fb-controls-shelf">
                        <button type="button" 
                                onclick="fbGoPrev()" 
                                class="px-4 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-white font-semibold text-xs transition-all flex items-center gap-1.5 active:scale-95 shrink-0">
                            <span>← Oldingi</span>
                        </button>

                        <div class="flex-1 flex items-center gap-3">
                            <span id="fb-page-indicator" class="font-mono text-xs text-amber-400 font-bold whitespace-nowrap">
                                Yuklanmoqda...
                            </span>
                            <input type="range" id="fb-page-slider" min="1" max="100" value="1" class="fb-page-slider" title="Tez o'tish slayderi">
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            <!-- Zoom Controls -->
                            <div class="hidden sm:flex items-center bg-black/40 border border-white/10 rounded-xl overflow-hidden p-0.5">
                                <button type="button" onclick="fbZoomOut()" title="Kichiklashtirish (-)" class="p-1.5 text-slate-400 hover:text-white transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                                </button>
                                <button type="button" onclick="fbZoomReset()" id="fb-zoom-level" title="100% asl o'lchamga qaytarish" class="px-1.5 text-[11px] font-mono font-bold text-amber-400 hover:text-amber-300">
                                    100%
                                </button>
                                <button type="button" onclick="fbZoomIn()" title="Kattalashtirish (+)" class="p-1.5 text-slate-400 hover:text-white transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                </button>
                            </div>

                            <button type="button" 
                                    id="fb-sound-btn"
                                    onclick="fbToggleSound()" 
                                    title="Varaqlash ovozi" 
                                    class="p-2 rounded-xl bg-white/10 hover:bg-white/20 text-white text-xs transition-all">
                                🔊
                            </button>
                            <button type="button" 
                                    onclick="fbGoNext()" 
                                    class="px-5 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs uppercase tracking-wider transition-all flex items-center gap-1.5 shadow-md shadow-amber-500/20 active:scale-95">
                                <span>Keyingi →</span>
                            </button>
                        </div>
                    </div>

                </div>

                <!-- Quick Hint -->
                <div class="flex items-center justify-center gap-4 text-xs text-slate-400">
                    <span class="flex items-center gap-1.5">
                        <kbd class="px-2 py-0.5 rounded bg-white/10 text-white font-mono text-[11px]">←</kbd>
                        <kbd class="px-2 py-0.5 rounded bg-white/10 text-white font-mono text-[11px]">→</kbd>
                        <span>Klaviatura o'qlari yoki sahifa burchaklariga bosib varaqlang</span>
                    </span>
                </div>
            </div>

            <!-- Standart PDF Viewer (Ixtiyoriy) -->
            <div x-show="readerMode === 'pdf'" x-cloak class="relative rounded-3xl overflow-hidden border border-slate-200/80 dark:border-slate-800 shadow-soft bg-slate-100 dark:bg-slate-900">
                <iframe id="bookPdfFrame"
                    src="{{ route('books.pdf.read', $book->id) }}#view=FitH&pagemode=none"
                    class="w-full h-[75vh] min-h-[500px] bg-white"
                    title="{{ $book->title }} — onlayn o'qish"
                    allowfullscreen></iframe>
            </div>
        @elseif($book->chapters()->where('is_published', true)->exists())
            <div class="relative rounded-3xl overflow-hidden border border-slate-200/80 dark:border-slate-800 shadow-soft bg-slate-100 dark:bg-slate-900">
                <iframe id="bookPdfFrame"
                    src="{{ route('books.pdf.read', $book->id) }}#view=FitH&pagemode=none"
                    class="w-full h-[75vh] min-h-[500px] bg-white"
                    title="{{ $book->title }} — onlayn o'qish"
                    allowfullscreen></iframe>
            </div>
        @else
            <div class="p-10 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-soft text-center space-y-3">
                <span class="text-4xl block">📕</span>
                <p class="text-sm text-slate-500 dark:text-slate-400 font-semibold">Bu kitob uchun hali PDF fayl yuklanmagan.</p>
                <p class="text-xs text-slate-400">Admin/teacher paneldan kitobga PDF fayl yuklaydi yoki boblar qo'shilganda avtomatik PDF yaratiladi.</p>
            </div>
        @endif
    </div>

    <!-- Tabs Navigation -->
    <div class="flex items-center gap-2 border-b border-slate-200 dark:border-slate-800 pb-2 overflow-x-auto">
        <button @click="tab = 'chapters'" :class="{ 'bg-indigo-600 text-white shadow-md': tab === 'chapters', 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800': tab !== 'chapters' }"
                class="px-4 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap">
            Boblar ro'yxati ({{ $book->chapters()->count() }})
        </button>
        <button @click="tab = 'overview'" :class="{ 'bg-indigo-600 text-white shadow-md': tab === 'overview', 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800': tab !== 'overview' }"
                class="px-4 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap">
            Kitob haqida umumiy
        </button>
    </div>

    <!-- Tab 1: Chapters -->
    <div x-show="tab === 'chapters'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-3">
        @forelse($book->chapters()->where('is_published', true)->orderBy('chapter_number')->get() as $chapter)
            <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-soft flex items-center justify-between gap-4 transition-all hover:border-indigo-500/50 hover:shadow-md group">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-950/50 border border-indigo-100 dark:border-indigo-900/50 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold font-mono text-sm shrink-0 group-hover:scale-105 transition-transform">
                        {{ $chapter->chapter_number }}
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-slate-800 dark:text-slate-200 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                            {{ $chapter->title }}
                        </h4>
                        @if($chapter->duration_minutes)
                            <span class="text-xs text-slate-400 flex items-center gap-1 mt-0.5">
                                ⏱️ {{ $chapter->duration_minutes }} daqiqa mutolaa
                            </span>
                        @endif
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ route('reader.show', ['book' => $book->id, 'chapter' => $chapter->id]) }}"
                       class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs flex items-center gap-1.5 transition-all shadow-sm active:scale-95">
                        <span>O'qish</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </a>
                </div>
            </div>
        @empty
            <div class="p-8 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-soft text-center text-slate-400 text-xs">
                Bu kitob uchun hozircha alohida boblar kiritilmagan. Yuqoridagi onlayn o'qish orqali to'liq kitobni mutolaa qilishingiz mumkin.
            </div>
        @endforelse
    </div>

    <!-- Tab 2: Overview -->
    <div x-show="tab === 'overview'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-soft space-y-4">
        <h3 class="text-base font-bold text-slate-900 dark:text-white">To'liq tavsif va mazmuni</h3>
        <div class="prose dark:prose-invert text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
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
