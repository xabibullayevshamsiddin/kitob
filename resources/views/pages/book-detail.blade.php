@extends('layouts.app')

@section('title', $book->title)

@php
    $bookAiReady = (bool) config('ai.enabled', true) && $book->ai_enabled && in_array($book->ai_index_status, ['ready', 'partial'], true);
@endphp

@section('seo')
    <x-seo-meta 
        :title="$book->title . ' — ' . $book->author"
        :description="Str::limit(strip_tags($book->description ?? ''), 180)"
        :image="$book->cover_url"
        :book="$book"
        type="book"
    />
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/flipbook/flipbook.css') }}?v={{ filemtime(public_path('assets/flipbook/flipbook.css')) }}">
@if($bookAiReady)
<link rel="stylesheet" href="{{ asset('vendor/katex/katex.min.css') }}">
@endif
<style>
    .preserve-3d { transform-style: preserve-3d; }
    @keyframes pulseGlow {
        0%, 100% { opacity: 0.4; transform: scale(1); }
        50% { opacity: 0.8; transform: scale(1.05); }
    }
    .animate-pulse-glow { animation: pulseGlow 2.5s ease-in-out infinite; }
</style>
@endpush

@section('content')
<div class="max-w-6xl mx-auto space-y-8 pb-16" x-data="{
        readerMode: '3d',
        isSaved: {{ $isSaved ? 'true' : 'false' }},
        savedCount: {{ (int) $savedCount }},
        savingBookmark: false,
        paperTheme: 'ivory',
        autoPlayActive: false,
        autoPlayDelay: 7000,
        showShortcutsModal: false,
        showDownloadModal: false,
        showReviewModal: false,
        showChatDrawer: false,
        showAiDrawer: {{ $bookAiReady ? 'true' : 'false' }},
        showAiSummaryModal: false,
        aiSummaryLoading: false,
        aiSummaryCopied: false,
        aiSummaryText: @json($book->ai_summary ?? ''),
        async openAiSummary() {
            this.showAiSummaryModal = true;
            if (this.aiSummaryText && this.aiSummaryText.length > 30) {
                return;
            }
            this.aiSummaryLoading = true;
            try {
                const res = await fetch('{{ route('books.ai-summary', $book->id) }}');
                const data = await res.json();
                if (data.ok && data.summary) {
                    this.aiSummaryText = data.summary;
                }
            } catch (err) {
                this.aiSummaryText = 'Konspektni yuklashda xatolik yuz berdi. Iltimos, qaytadan urinib ko\'ring.';
            } finally {
                this.aiSummaryLoading = false;
            }
        },
        copyAiSummary() {
            if (!this.aiSummaryText) return;
            navigator.clipboard.writeText(this.aiSummaryText);
            this.aiSummaryCopied = true;
            setTimeout(() => { this.aiSummaryCopied = false; }, 2500);
            if (window.showToast) window.showToast('AI konspekt nusxalandi! 📋', 'success');
        },
        activeTab: 'desc',
        totalComments: {{ (int) $totalCommentsCount }},
        selectedRating: 5,
        hoverRating: 0,
        replyToId: null,

        async toggleCommentLike(commentId) {
            try {
                const res = await fetch('/book-comments/' + commentId + '/like', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();
                if (data.success) {
                    const countEl = document.getElementById('comment-likes-count-' + commentId);
                    const iconEl = document.getElementById('comment-like-icon-' + commentId);
                    if (countEl) countEl.innerText = data.likes_count;
                    if (iconEl) {
                        if (data.liked) {
                            iconEl.classList.add('text-rose-500', 'fill-rose-500');
                            iconEl.classList.remove('text-mist');
                        } else {
                            iconEl.classList.remove('text-rose-500', 'fill-rose-500');
                            iconEl.classList.add('text-mist');
                        }
                    }
                }
            } catch(e) {
                console.error(e);
            }
        },

        async toggleSaveBook() {
            if (this.savingBookmark) return;
            this.savingBookmark = true;
            try {
                const res = await fetch('{{ route('books.toggle-save', $book) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();
                if (data.success) {
                    this.isSaved = data.is_saved;
                    this.savedCount = data.total_saved;
                    if (window.showToast) {
                        window.showToast(data.message, this.isSaved ? 'success' : 'info');
                    }
                }
            } catch(e) {
                console.error(e);
            } finally {
                this.savingBookmark = false;
            }
        },

        setPaperTheme(theme) {
            this.paperTheme = theme;
            if (window.fbSetPaperTheme) {
                window.fbSetPaperTheme(theme);
            }
        },

        toggleAutoPlay(delay) {
            if (delay) this.autoPlayDelay = delay;
            if (window.fbToggleAutoPlay) {
                this.autoPlayActive = window.fbToggleAutoPlay(this.autoPlayDelay);
            }
        },

        init() {
            if (window.location.hash === '#book-comments') {
                this.activeTab = 'comments';
            }
            window.addEventListener('flipbook:autoplay', (e) => {
                this.autoPlayActive = !!(e.detail && e.detail.active);
            });
            window.addEventListener('flipbook:themechange', (e) => {
                if (e.detail && e.detail.theme) this.paperTheme = e.detail.theme;
            });
            window.addEventListener('book-ai-jump-page', (e) => {
                const p = (e.detail && e.detail.page) ? parseInt(e.detail.page, 10) : 1;
                if (typeof window.fbGoToPage === 'function') {
                    setTimeout(() => window.fbGoToPage(p), 180);
                }
                const pdfFrame = document.getElementById('bookPdfFrame');
                if (pdfFrame && pdfFrame.src) {
                    const base = pdfFrame.src.split('#')[0];
                    pdfFrame.src = base + '#page=' + p + '&view=FitH&pagemode=none';
                }
            });
        }
    }">

    <!-- ── 1. Editorial Hero Book Banner ── -->
    <div class="ks-panel p-6 sm:p-10 bg-ink-900 border border-ink-border text-paper relative overflow-hidden shadow-2xl">
        <div class="absolute -top-24 -right-24 w-96 h-96 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col sm:flex-row gap-8 items-center sm:items-start">
            
            <!-- Book Cover (2:3 nisbat) with Bookmark Ribbon indicator -->
            <div class="w-36 sm:w-48 aspect-[2/3] rounded-card overflow-hidden shadow-2xl shrink-0 border border-ink-border relative group bg-ink-950">
                <img src="{{ $book->cover_url }}" alt="{{ $book->title }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                <div class="absolute inset-0 bg-gradient-to-t from-ink-950/85 via-transparent to-transparent pointer-events-none"></div>

                <!-- Ribbon badge when saved to 'Keyinroq o'qiyman' -->
                <div x-show="isSaved" x-cloak
                     class="absolute top-0 right-3 w-7 h-10 bg-gradient-to-b from-amber-400 to-amber-600 shadow-lg flex items-center justify-center text-ink-950 font-bold transition-all"
                     style="clip-path: polygon(0% 0%, 100% 0%, 100% 100%, 50% 80%, 0% 100%);"
                     title="{{ __('site.saved_books.ribbon_title') }}">
                    <span class="text-xs">★</span>
                </div>
            </div>

            <!-- Book Info & Actions -->
            <div class="flex-1 min-w-0 space-y-4 text-center sm:text-left">
                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2">
                    <span class="px-2.5 py-0.5 bg-amber-500/10 border border-amber-500/25 text-amber-600 dark:text-amber-400 text-xs font-mono font-bold rounded-badge uppercase tracking-wider">
                        {{ $book->week_number }}-Hafta kitobi
                    </span>
                    <span class="px-2.5 py-0.5 bg-ink-950 border border-ink-border text-mist text-xs font-mono rounded-badge">
                        {{ $book->genre }}
                    </span>
                    @if($averageRating > 0)
                        <span class="px-2.5 py-0.5 bg-amber-500/10 text-amber-600 dark:text-amber-400 text-xs font-mono rounded-badge flex items-center gap-1 border border-amber-500/25">
                            <span>⭐</span>
                            <span class="font-bold">{{ $averageRating }}</span>
                            <span class="text-mist text-[10px]">({{ $ratingsCount }} baho)</span>
                        </span>
                    @endif
                    <button type="button"
                            @click="activeTab = 'comments'; $nextTick(() => { document.getElementById('book-comments')?.scrollIntoView({ behavior: 'smooth' }); })"
                            class="px-2.5 py-0.5 bg-ink-950 text-mist hover:text-paper text-xs font-mono rounded-badge flex items-center gap-1 border border-ink-border transition-colors">
                        <span>💬</span>
                        <span x-text="totalComments + ' ta izoh'">{{ $totalCommentsCount }} ta izoh</span>
                    </button>
                    <template x-if="savedCount > 0">
                        <span class="px-2.5 py-0.5 bg-amber-500/10 text-amber-700 dark:text-amber-300 text-xs font-mono rounded-badge flex items-center gap-1 border border-amber-500/20">
                            <span>📌</span>
                            <span x-text="@js(__('site.saved_books.saved_by_readers', ['count' => '___CNT___'])).replace('___CNT___', savedCount)"></span>
                        </span>
                    </template>
                </div>

                <h1 class="text-2xl sm:text-4xl font-bold font-serif text-paper tracking-tight leading-tight break-words">{{ $book->title }}</h1>
                <p class="text-xs sm:text-sm text-mist font-mono break-words">Muallif: <strong class="text-paper font-sans font-semibold">{{ $book->author }}</strong></p>
                <p class="text-xs sm:text-sm text-mist leading-relaxed max-w-2xl font-sans break-words whitespace-pre-line">{{ $book->description }}</p>

                <!-- ── Action Buttons Toolbar ── -->
                <div class="pt-2 flex flex-wrap items-center justify-center sm:justify-start gap-2.5">
                    @if($book->pdf_path || $book->chapters()->where('is_published', true)->exists())
                        <button type="button"
                                @click="readerMode = '3d'; window.scrollTo({ top: document.getElementById('online-reader').offsetTop - 80, behavior: 'smooth' })"
                                class="ks-btn-primary inline-flex items-center gap-2 shadow-lg shadow-amber-500/20">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                            <span>{{ __('site.books.read_cta') }}</span>
                        </button>
                    @endif

                    @if($bookAiReady)
                        <button type="button"
                                @click="showAiDrawer = true; $nextTick(() => { document.getElementById('book-ai-section')?.scrollIntoView({ behavior: 'smooth', block: 'center' }); })"
                                class="px-3.5 py-2 rounded-btn bg-emerald-500/15 hover:bg-emerald-500 text-emerald-700 dark:text-emerald-300 hover:text-ink-950 border border-emerald-500/40 font-bold text-xs inline-flex items-center gap-1.5 transition-all shadow-md"
                                title="{{ __('site.books.ai_assistant_tooltip') }}">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M12 3l1.912 5.813a2 2 0 001.275 1.275L21 12l-5.813 1.912a2 2 0 00-1.275 1.275L12 21l-1.912-5.813a2 2 0 00-1.275-1.275L3 12l5.813-1.912a2 2 0 001.275-1.275L12 3z"/>
                            </svg>
                            <span>{{ __('ai.title') }}</span>
                        </button>
                    @elseif(auth()->check() && auth()->user()->isAdmin())
                        <button type="button"
                                @click="showAiDrawer = true; $nextTick(() => { document.getElementById('book-ai-section')?.scrollIntoView({ behavior: 'smooth', block: 'center' }); })"
                                class="px-3.5 py-2 rounded-btn bg-amber-500/15 hover:bg-amber-500/25 text-amber-700 dark:text-amber-300 border border-amber-500/40 text-xs font-bold inline-flex items-center gap-1.5 transition-all"
                                title="{{ __('site.books.ai_assistant_tooltip') }}">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M12 3l1.912 5.813a2 2 0 001.275 1.275L21 12l-5.813 1.912a2 2 0 00-1.275 1.275L12 21l-1.912-5.813a2 2 0 00-1.275-1.275L3 12l5.813-1.912a2 2 0 001.275-1.275L12 3z"/>
                            </svg>
                            <span>{{ __('ai.title') }}</span>
                        </button>
                    @endif

                    <!-- Izoh qoldirish (Smooth scroll to comments) -->
                    <button type="button"
                            @click="activeTab = 'comments'; $nextTick(() => { document.getElementById('book-comments')?.scrollIntoView({ behavior: 'smooth' }); document.getElementById('comment-textarea')?.focus(); })"
                            class="ks-btn-ghost inline-flex items-center gap-1.5 text-xs text-amber-700 dark:text-amber-300 border-amber-500/30 hover:border-amber-400"
                            title="{{ __('site.books.leave_comment') }}">
                        <span>💬</span>
                        <span>{{ __('site.books.leave_comment') }}</span>
                    </button>

                    <!-- Keyinroq o'qiyman (1-Click Toggle Bookmark) -->
                    <button type="button"
                            @click="toggleSaveBook()"
                            :disabled="savingBookmark"
                            :class="isSaved 
                                ? 'bg-amber-500 text-ink-950 font-bold border-amber-400 shadow-md shadow-amber-500/20' 
                                : 'ks-btn-ghost text-amber-600 dark:text-amber-400 border-amber-500/30 hover:border-amber-400'"
                            class="px-3.5 py-2 rounded-btn text-xs font-semibold inline-flex items-center gap-2 transition-all active:scale-95"
                            :title="isSaved ? @js(__('site.saved_books.remove_title')) : @js(__('site.saved_books.save_title'))">
                        <span x-show="!isSaved">📌</span>
                        <span x-show="isSaved">✓</span>
                        <span x-text="isSaved ? @js(__('site.saved_books.saved_btn')) : @js(__('site.saved_books.save_btn'))">{{ __('site.saved_books.save_btn') }}</span>
                    </button>

                    <!-- PDF Yuklab olish Modal / Direct Option -->
                    @if($book->pdf_path || $book->chapters()->where('is_published', true)->exists())
                        <button type="button"
                                @click="showDownloadModal = true"
                                class="ks-btn-ghost inline-flex items-center gap-2 text-rose-600 dark:text-rose-300 border-rose-500/30 hover:border-rose-500/50"
                                title="Kitobni PDF sifatida yuklab olish">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            <span>PDF yuklab olish</span>
                        </button>
                    @endif

                    <!-- Tugmalar boshqaruvi (Shortcuts cheat sheet modal trigger) -->
                    <button type="button"
                            @click="showShortcutsModal = true"
                            class="ks-btn-ghost inline-flex items-center gap-1.5 text-xs text-mist hover:text-paper"
                            title="Klaviatura tugmalari yordami">
                        <span>⌨️</span>
                        <span>Tugmalar boshqaruvi</span>
                    </button>

                    <!-- 1 daqiqalik AI Konspekt Tugmasi -->
                    <button type="button"
                            @click="openAiSummary()"
                            class="px-3.5 py-2 rounded-btn bg-gradient-to-r from-violet-600/20 via-amber-500/15 to-violet-600/20 hover:from-violet-600/30 hover:to-amber-500/30 text-amber-300 dark:text-amber-200 border border-violet-400/40 hover:border-amber-400 font-bold font-sans text-xs inline-flex items-center gap-1.5 shadow-md shadow-violet-500/10 transition-all active:scale-95 group"
                            title="Kitobning 1 daqiqalik qisqacha mazmuni va asosiy saboqlarini o'qish">
                        <span class="text-sm group-hover:scale-110 transition-transform">🧠</span>
                        <span>AI Konspekt</span>
                        <span class="px-1 py-0.2 rounded-badge bg-amber-400/20 text-amber-300 text-[10px] font-mono font-bold">1 daqiqa</span>
                    </button>

                    @if($firstAudio = $book->audios->first())
                        <button type="button"
                                @click="window.playGlobalAudio({
                                    id: {{ $firstAudio->id }},
                                    bookId: {{ $book->id }},
                                    title: '{{ addslashes($book->title) }}',
                                    author: '{{ addslashes($book->author) }}',
                                    coverUrl: '{{ $book->cover_url }}',
                                    audioUrl: '{{ $firstAudio->file_url }}',
                                    chapterTitle: '{{ addslashes($firstAudio->title ?: '1-qism') }}',
                                    duration: {{ (int) ($firstAudio->duration ?? 0) }},
                                    shareUrl: '{{ route('books.show', $book->slug) }}'
                                })"
                                class="px-3.5 py-2 rounded-btn bg-amber-400 hover:bg-amber-300 text-ink-950 font-bold font-sans text-xs inline-flex items-center gap-1.5 shadow-md transition-all active:scale-95"
                                title="Pastki audio pleyerda tinglash">
                            <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><polygon points="6 3 20 12 6 21 6 3"/></svg>
                            <span>Tinglash</span>
                            @if($book->audios->count() > 1)
                                <span class="px-1 py-0.2 rounded-badge bg-ink-950/20 text-ink-950 text-[10px] font-mono font-bold">{{ $book->audios->count() }} ta</span>
                            @endif
                        </button>
                    @endif

                    @if($book->quizzes()->count() > 0)
                        <a href="{{ route('quiz.show', $book->id) }}"
                           class="ks-btn-ghost inline-flex items-center gap-2">
                            <svg class="w-4 h-4 text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                            <span>Test topshirish</span>
                            <span class="px-1.5 py-0.2 rounded-badge bg-emerald-500/20 text-emerald-400 text-[10px] font-mono font-bold">Faol</span>
                        </a>
                    @elseif(auth()->check() && (auth()->user()->isAdmin() || (auth()->user()->isTeacher() && (int) $book->created_by === (int) auth()->id())))
                        <a href="{{ route('teacher.quizzes.create', ['book_id' => $book->id]) }}"
                           class="ks-btn-ghost inline-flex items-center gap-2">
                            <span>+ {{ "Ushbu kitobga test qo'shish" }}</span>
                        </a>
                    @endif

                    @if($book->videos()->count() > 0)
                        <a href="{{ route('videos.index', $book->id) }}"
                           class="ks-btn-ghost inline-flex items-center gap-2">
                            <svg class="w-4 h-4 text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg>
                            <span>Videolar</span>
                            <span class="px-1.5 py-0.2 rounded-badge bg-amber-500/20 text-amber-300 text-[10px] font-mono font-bold">{{ $book->videos()->count() }}</span>
                        </a>
                    @endif

                    <!-- Share Button (Mutolaa uslubida) -->
                    <button type="button"
                            onclick="window.openBookShare({
                                title: '{{ addslashes($book->title) }}',
                                author: '{{ addslashes($book->author) }}',
                                url: '{{ route('books.show', $book->slug) }}',
                                coverUrl: '{{ $book->cover_url }}',
                                description: '{{ addslashes(\Illuminate\Support\Str::limit($book->description, 150)) }}'
                            })"
                            class="p-2 sm:p-2.5 rounded-btn bg-ink-950 hover:bg-white/10 border border-ink-border hover:border-amber-400/40 text-mist hover:text-amber-400 transition-all flex items-center justify-center shadow"
                            title="Kitobni ulashish">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="18" cy="5" r="3"/>
                            <circle cx="6" cy="12" r="3"/>
                            <circle cx="18" cy="19" r="3"/>
                            <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/>
                            <line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ── 2. ONLAYN O'QISH & BOSHQARUV PANELI ── -->
    <div id="online-reader" class="space-y-4 scroll-mt-24">
        
        <!-- Header & Top Command Shelf -->
        <div class="flex items-center justify-between flex-wrap gap-3 bg-ink-900/90 border border-ink-border p-3 sm:p-4 rounded-card backdrop-blur-md">
            <div class="flex items-center gap-3 flex-wrap">
                <h2 class="text-base sm:text-lg font-bold font-serif text-paper flex items-center gap-2">
                    <span class="w-8 h-8 rounded-btn bg-amber-500/10 border border-amber-500/20 text-amber-400 flex items-center justify-center">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                    </span>
                    <span>Onlayn Mutolaa</span>
                </h2>

                <!-- Mode switcher: 3D Real Kitob vs PDF -->
                @if($book->pdf_path)
                    <div class="flex items-center p-0.5 rounded-btn bg-ink-950 border border-ink-border shrink-0">
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
                    <div x-show="readerMode === '3d'" class="hidden md:flex items-center gap-1.5 px-3 py-1 rounded-btn bg-ink-950 border border-ink-border">
                        <span class="text-xs text-mist font-mono">Sahifa:</span>
                        <input type="number" 
                                id="fb-page-input" 
                                min="1" 
                                value="1" 
                                title="Sahifa raqamini yozing va Enter bosing"
                                class="w-12 px-1 py-0.5 text-center font-mono text-xs font-bold text-amber-400 bg-ink-900 border border-ink-border rounded-badge outline-none focus:border-amber-400"
                                onkeydown="if(event.key === 'Enter') { this.blur(); fbJumpToPageInput(); }"
                                onchange="fbJumpToPageInput()"
                                onfocus="this.select()">
                        <span class="text-xs text-mist font-mono">/</span>
                        <span id="fb-total-pages" class="text-xs text-mist font-mono font-semibold">...</span>

                        <select id="fb-page-select" 
                                onchange="if(this.value) { fbGoToPage(parseInt(this.value)); }"
                                title="Ro'yxatdan sahifani tanlash"
                                class="ml-1 bg-ink-900 border border-ink-border text-mist rounded-badge text-xs py-0.5 px-1.5 outline-none cursor-pointer font-sans">
                            <option value="">Tanlash ▾</option>
                        </select>
                    </div>
                @endif
            </div>

            <!-- Top Right Action Controls -->
            <div class="flex items-center gap-2 flex-wrap">
                <!-- Avto-varaqlash (Hands-free reading) -->
                <button type="button"
                        x-show="readerMode === '3d'"
                        @click="toggleAutoPlay()"
                        :class="autoPlayActive ? 'bg-emerald-500 text-ink-950 font-bold border-emerald-400' : 'ks-btn-ghost text-mist hover:text-paper'"
                        class="py-1.5 px-3 text-xs rounded-btn flex items-center gap-1.5 transition-all"
                        title="{{ __('site.books.flipbook_auto_title') }}">
                    <span x-show="!autoPlayActive">▶ {{ __('site.books.flipbook_auto_label') }}</span>
                    <span x-show="autoPlayActive" class="flex items-center gap-1">
                        <span class="w-2 h-2 rounded-full bg-ink-950 animate-ping"></span>
                        <span>To'xtatish</span>
                    </span>
                </button>

                <!-- Qog'oz ohangi (Paper Theme Selector) -->
                <div x-show="readerMode === '3d'" class="hidden sm:flex items-center bg-ink-950 border border-ink-border rounded-btn p-0.5">
                    <button type="button" @click="setPaperTheme('ivory')" :class="paperTheme === 'ivory' ? 'bg-amber-500/20 text-amber-300 font-bold' : 'text-mist hover:text-paper'" class="px-2 py-1 text-[11px] rounded transition-all" title="Tabiiy krem qog'oz">📜 Asl</button>
                    <button type="button" @click="setPaperTheme('sepia')" :class="paperTheme === 'sepia' ? 'bg-amber-500/20 text-amber-300 font-bold' : 'text-mist hover:text-paper'" class="px-2 py-1 text-[11px] rounded transition-all" title="Klassik sepia ohangi">🍵 Sepia</button>
                    <button type="button" @click="setPaperTheme('night')" :class="paperTheme === 'night' ? 'bg-amber-500/20 text-amber-300 font-bold' : 'text-mist hover:text-paper'" class="px-2 py-1 text-[11px] rounded transition-all" title="Tungi qorong'i mutolaa">🌙 Tun</button>
                </div>

                @if($book->pdf_path)
                    <a href="{{ route('books.flipbook', $book->id) }}"
                        class="ks-btn-ghost py-1.5 px-3 text-xs text-amber-600 dark:text-amber-400 flex items-center gap-1.5"
                        title="To'liq ekranda ochish">
                        <span>⛶ To'liq ekran</span>
                    </a>
                @endif

                @if($bookAiReady)
                    <button type="button"
                            @click="showAiDrawer = !showAiDrawer; if (showAiDrawer) { $nextTick(() => { document.getElementById('book-ai-section')?.scrollIntoView({ behavior: 'smooth', block: 'nearest' }); }); }"
                            :class="showAiDrawer ? 'bg-emerald-500 text-ink-950 font-bold border-emerald-400' : 'ks-btn-ghost text-emerald-700 dark:text-emerald-300 border-emerald-500/35 hover:border-emerald-400'"
                            class="py-1.5 px-3 text-xs rounded-btn flex items-center gap-1.5 transition-all shadow-md"
                            title="Kitob bo'yicha AI yordamchi oynasini ochish / yopish">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M12 3l1.912 5.813a2 2 0 001.275 1.275L21 12l-5.813 1.912a2 2 0 00-1.275 1.275L12 21l-1.912-5.813a2 2 0 00-1.275-1.275L3 12l5.813-1.912a2 2 0 001.275-1.275L12 3z"/>
                        </svg>
                        <span>{{ __('ai.title') }}</span>
                    </button>
                @endif

                <!-- Kitobxonlar bilan suhbatlashish (Chat Drawer toggle) -->
                <button type="button"
                        @click="showChatDrawer = !showChatDrawer; $nextTick(() => { window.dispatchEvent(new CustomEvent('book-chat-scroll-bottom')); })"
                        :class="showChatDrawer ? 'bg-amber-500 text-ink-950 font-bold border-amber-400' : 'ks-btn-ghost text-amber-700 dark:text-amber-300 border-amber-500/30 hover:border-amber-400'"
                        class="py-1.5 px-3 text-xs rounded-btn flex items-center gap-1.5 transition-all shadow-md"
                        title="Kitobxonlar bilan jonli suhbatlashish va izohlar">
                    <span>💬</span>
                    <span>Suhbat</span>
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                </button>

                <button type="button"
                        @click="showShortcutsModal = true"
                        class="ks-btn-ghost py-1.5 px-2.5 text-xs text-mist hover:text-paper"
                        title="Tugmalar boshqaruvi">
                    <span>⌨️</span>
                </button>
            </div>
        </div>

        @if($book->pdf_path)
            <!-- 3D Real Kitob Varaqlash Ko'rinishi (Default) -->
            <div x-show="readerMode === '3d'" class="space-y-4">
                
                <!-- The 3D Flipbook Stage -->
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
                            title="{{ __('site.books.flipbook_prev_title') }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                    </button>

                    <!-- Floating Right Navigation Arrow -->
                    <button type="button" 
                            class="fb-nav-arrow fb-nav-arrow--right hidden sm:flex" 
                            onclick="fbGoNext()" 
                            title="{{ __('site.books.flipbook_next_title') }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                    </button>

                    <!-- Hardcover Binding Wrapper -->
                    <div class="fb-hardcover-casing">
                        <div class="fb-silk-ribbon" title="Xatcho'p"></div>

                        <!-- The Book Spread (Powered by Nodlik/StPageFlip) -->
                        <div id="fb-spread" class="fb-book-spread">
                            <!-- Injected by flipbook.js -->
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
                            <!-- Shrift & Masshtab (Font & Zoom) Controls -->
                            <div class="hidden sm:flex items-center bg-ink-950 border border-ink-border rounded-btn overflow-hidden p-0.5">
                                <button type="button" onclick="fbZoomOut()" title="Shrift va sahifani kichiklashtirish (A- / -)" class="px-1.5 py-0.5 text-xs font-mono font-bold text-mist hover:text-paper transition-colors">
                                    A-
                                </button>
                                <button type="button" onclick="fbZoomReset()" id="fb-zoom-level" title="100% asl o'lchamga qaytarish (0)" class="px-1.5 text-[11px] font-mono font-bold text-amber-400">
                                    100%
                                </button>
                                <button type="button" onclick="fbZoomIn()" title="Shrift va sahifani kattalashtirish (A+ / +)" class="px-1.5 py-0.5 text-xs font-mono font-bold text-mist hover:text-paper transition-colors">
                                    A+
                                </button>
                            </div>

                            <!-- Paper Theme Selector -->
                            <div class="hidden sm:flex items-center bg-ink-950 border border-ink-border rounded-btn p-0.5">
                                <button type="button" 
                                        data-paper-theme-btn="ivory"
                                        onclick="fbSetPaperTheme('ivory')" 
                                        class="px-1.5 py-0.5 text-[10px] rounded transition-all text-mist hover:text-paper" 
                                        title="Tabiiy krem qog'oz">📜 Asl</button>
                                <button type="button" 
                                        data-paper-theme-btn="sepia"
                                        onclick="fbSetPaperTheme('sepia')" 
                                        class="px-1.5 py-0.5 text-[10px] rounded transition-all text-mist hover:text-paper" 
                                        title="Klassik sepia ohangi">🍵 Sepia</button>
                                <button type="button" 
                                        data-paper-theme-btn="night"
                                        onclick="fbSetPaperTheme('night')" 
                                        class="px-1.5 py-0.5 text-[10px] rounded transition-all text-mist hover:text-paper" 
                                        title="Tungi qorong'i mutolaa">🌙 Tun</button>
                            </div>

                            <!-- Sound toggle -->
                            <button type="button" 
                                    id="fb-sound-btn"
                                    onclick="fbToggleSound()" 
                                    title="{{ __('site.books.flipbook_sound_title') }}" 
                                    class="ks-btn-ghost p-1.5 text-xs">
                                🔊
                            </button>

                            <!-- Next button -->
                            <button type="button" 
                                    onclick="fbGoNext()" 
                                    class="ks-btn-primary py-1.5 px-3 text-xs">
                                <span>Keyingi →</span>
                            </button>
                        </div>
                    </div>

                </div>

                <!-- Kitobxonlar jonli suhbati paneli (Reader Live Discussion Drawer) -->
                <div x-show="showChatDrawer" x-cloak
                     class="mt-4 rounded-card border border-amber-500/30 bg-ink-950/95 backdrop-blur-md shadow-2xl p-3 sm:p-5 space-y-3">
                    <div class="flex items-center justify-between border-b border-ink-border pb-3">
                        <div class="flex items-center gap-2">
                            <span class="text-xl">💬</span>
                            <div>
                                <h3 class="text-sm font-bold font-serif text-paper">Kitobxonlar jonli suhbati va muhokamasi</h3>
                                <p class="text-[11px] text-mist font-mono">Kitobni o'qiyotgan paytda birga fikr almashing va gaplashing</p>
                            </div>
                        </div>
                        <button type="button" @click="showChatDrawer = false" class="text-mist hover:text-paper font-mono px-2.5 py-1 text-xs bg-ink-900 border border-ink-border rounded-btn transition-colors">
                            Yopish ✕
                        </button>
                    </div>
                    <div>
                        @livewire('book.book-discussion-chat', ['book' => $book, 'isDrawerMode' => true], key('drawer-chat-'.$book->id))
                    </div>
                </div>

                <!-- Quick Hint Bar -->
                <div class="flex items-center justify-between text-xs text-mist font-mono px-2 flex-wrap gap-2">
                    <span class="flex items-center gap-1.5 text-amber-400 font-semibold">
                        <span>🖱️ / 👆</span>
                        <span>{{ __('site.books.flipbook_corner_hint') }}</span>
                    </span>
                    <span class="flex items-center gap-2">
                        <kbd class="px-1.5 py-0.5 rounded-badge bg-ink-900 border border-ink-border text-paper text-[10px]">←</kbd>
                        <kbd class="px-1.5 py-0.5 rounded-badge bg-ink-900 border border-ink-border text-paper text-[10px]">→</kbd>
                        <span>{{ __('site.books.flipbook_flip_label') }}</span>
                        <span class="text-ink-border">•</span>
                        <kbd class="px-1.5 py-0.5 rounded-badge bg-ink-900 border border-ink-border text-paper text-[10px]">A</kbd>
                        <span>{{ __('site.books.flipbook_auto_label') }}</span>
                        <span class="text-ink-border">•</span>
                        <kbd class="px-1.5 py-0.5 rounded-badge bg-ink-900 border border-ink-border text-paper text-[10px]">F</kbd>
                        <span>{{ __('site.books.flipbook_fullscreen_title') }}</span>
                    </span>
                </div>

                <!-- PDF Annotatsiyalar va Bet belgilari (Kitobning pastida) -->
                <x-reader.pdf-annotations :book="$book" :annotations="$pdfAnnotations" />
            </div>

            <!-- Standart PDF Viewer -->
            <div x-show="readerMode === 'pdf'" x-cloak style="display: none;" class="relative rounded-card overflow-hidden border border-ink-border bg-ink-900">
                <iframe id="bookPdfFrame"
                    :src="readerMode === 'pdf' ? '{{ route('books.pdf.read', $book->id) }}#view=FitH&pagemode=none' : ''"
                    class="w-full h-[75vh] min-h-[500px] bg-ink-950"
                    title="{{ $book->title }} — onlayn o'qish"
                    allowfullscreen></iframe>
            </div>
        @elseif($book->chapters()->where('is_published', true)->exists())
            <div class="relative rounded-card overflow-hidden border border-ink-border bg-ink-900">
                <iframe id="bookPdfFrame"
                    src="{{ route('books.pdf.read', $book->id) }}#view=FitH&pagemode=none"
                    class="w-full h-[75vh] min-h-[500px] bg-ink-950"
                    title="{{ $book->title }} — onlayn o'qish"
                    allowfullscreen></iframe>
            </div>
        @else
            <div class="p-10 rounded-card bg-ink-900 border border-ink-border text-center space-y-2">
                <svg class="w-8 h-8 mx-auto text-amber-500 mb-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                <p class="text-sm text-paper font-serif font-bold">{{ __('site.books.no_pdf_uploaded') }}</p>
                <p class="text-xs text-mist font-sans">{{ __('site.books.no_pdf_hint') }}</p>
            </div>
        @endif

        {{-- Kitob ichidagi AI Yordamchi (RAG) bo'limi --}}
        @if($bookAiReady)
            <div id="book-ai-section" x-show="showAiDrawer" x-cloak class="pt-2 scroll-mt-24">
                @livewire('book.book-assistant', ['book' => $book], key('book-ai-'.$book->id))
            </div>
        @elseif(auth()->check() && auth()->user()->isAdmin())
            @php
                $sampleReadyBook = \App\Models\Book::where('is_active', true)
                    ->where('ai_enabled', true)
                    ->where('ai_index_status', 'ready')
                    ->where('id', '!=', $book->id)
                    ->first(['id', 'title', 'slug']);
            @endphp
            <div id="book-ai-section" x-show="showAiDrawer" x-cloak class="pt-2 scroll-mt-24">
                <div class="rounded-card bg-ink-900 border border-amber-500/35 p-5 sm:p-6 space-y-4">
                    <div class="flex items-start justify-between gap-4 flex-wrap">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 rounded-btn bg-amber-500/15 border border-amber-500/30 text-amber-400 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path d="M12 3l1.912 5.813a2 2 0 001.275 1.275L21 12l-5.813 1.912a2 2 0 00-1.275 1.275L12 21l-1.912-5.813a2 2 0 00-1.275-1.275L3 12l5.813-1.912a2 2 0 001.275-1.275L12 3z"/>
                                </svg>
                            </div>
                            <div class="space-y-1">
                                <h3 class="text-sm sm:text-base font-bold font-serif text-paper">
                                    «{{ $book->title }}» kitobi uchun AI yordamchi holati (faqat Adminga ko'rinadi)
                                </h3>
                                @if($book->ai_index_status === 'failed')
                                    <p class="text-xs text-rose-300 leading-relaxed">
                                        <strong>Sababi:</strong> Ushbu kitobga yuklangan PDF fayl <strong>rasm sifatida skanerlangan</strong> (ichida matn qatlami yo'q, matn sifati: 0%). Qoidaga binoan AI yordamchi yolg'on ma'lumot to'qimasligi uchun skanerlangan (matnsiz) PDF'larni avtomatik o'qimaydi.
                                    </p>
                                @else
                                    <p class="text-xs text-mist leading-relaxed">
                                        Ushbu kitob uchun AI yordamchi hali yoqilmagan yoki indekslanmagan (holati: <span class="font-mono text-amber-300">{{ $book->ai_index_status ?? 'none' }}</span>).
                                    </p>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center gap-2 flex-wrap">
                            @if($sampleReadyBook)
                                <a href="{{ route('books.show', $sampleReadyBook->slug) }}#book-ai-section"
                                   class="ks-btn-primary py-2 px-3.5 text-xs font-bold inline-flex items-center gap-1.5">
                                    <span>Tayyor kitobda sinab ko'rish («{{ $sampleReadyBook->title }}»)</span>
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <line x1="5" y1="12" x2="19" y2="12"/>
                                        <polyline points="12 5 19 12 12 19"/>
                                    </svg>
                                </a>
                            @endif
                            <a href="{{ route('admin.books.edit', $book->id) }}#ai-assistant-panel"
                               class="ks-btn-ghost py-2 px-3 text-xs text-amber-300 border-amber-500/35 hover:border-amber-400 inline-flex items-center gap-1.5">
                                <span>Admin sozlamalari</span>
                            </a>
                        </div>
                    </div>

                    {{-- Skaner PDF bo'lsa ham Admin shu yerning o'zidan qisqa mazmun kiritib AI ni yoqib yuborishi mumkin --}}
                    <form action="{{ route('admin.books.ai.settings', $book->id) }}" method="POST" class="pt-3 border-t border-ink-border space-y-3">
                        @csrf
                        <input type="hidden" name="ai_enabled" value="1">
                        <input type="hidden" name="redirect_to_book" value="1">
                        <label class="block text-xs font-semibold text-paper">
                            Yoki shu kitobning o'zida AI ishlashi uchun kitob mazmunini (kamida 2–3 gap) kiriting va yoqing:
                        </label>
                        <textarea name="ai_summary" rows="3" required minlength="60"
                                  placeholder="Kitobning qisqa mazmuni (kamida 2-3 gap)..."
                                  class="ks-input w-full text-xs">{{ old('ai_summary', $book->ai_summary) }}</textarea>
                        <div class="flex justify-end">
                            <button type="submit" class="ks-btn-primary py-2 px-4 text-xs font-bold">
                                Mazmunni saqlash va AI yordamchini yoqish
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>

    <!-- ── 3. TABS: Tavsif va Izohlar ── -->
    <div id="book-comments" class="space-y-4 scroll-mt-20">
        <!-- Tabs Header -->
        <div class="flex items-center gap-2 sm:gap-4 border-b border-ink-border pb-2 overflow-x-auto">
            <button type="button"
                    @click="activeTab = 'desc'"
                    :class="activeTab === 'desc' ? 'border-b-2 border-amber-400 text-amber-400 font-bold' : 'text-mist hover:text-paper font-medium'"
                    class="pb-2 text-xs sm:text-sm font-serif flex items-center gap-1.5 transition-colors shrink-0">
                <span>📖</span>
                <span>Kitob tavsifi va mazmuni</span>
            </button>
            <button type="button"
                    @click="activeTab = 'comments'"
                    :class="activeTab === 'comments' ? 'border-b-2 border-amber-400 text-amber-400 font-bold' : 'text-mist hover:text-paper font-medium'"
                    class="pb-2 text-xs sm:text-sm font-serif flex items-center gap-1.5 transition-colors shrink-0">
                <span>💬</span>
                <span>Kitobxonlar izohlari</span>
                <span class="px-2 py-0.2 rounded-badge bg-amber-500/15 text-amber-400 text-xs font-mono font-bold" x-text="totalComments">{{ $totalCommentsCount }}</span>
            </button>
        </div>

        <!-- Tab 1: Tavsif -->
        <div x-show="activeTab === 'desc'" class="p-6 sm:p-8 rounded-card bg-ink-900 border border-ink-border space-y-4">
            <div class="text-sm text-mist leading-relaxed font-sans break-words whitespace-pre-line">
                {{ $book->description }}
            </div>
        </div>

        <!-- Tab 2: Kitobxonlar suhbati & Jonli izohlari (Live Discussion & Chat) -->
        <div x-show="activeTab === 'comments'" x-cloak class="space-y-4">
            @livewire('book.book-discussion-chat', ['book' => $book], key('tab-chat-'.$book->id))
        </div>
    </div>

    <!-- ── 4. MODALS: Tugmalar boshqaruvi & PDF yuklash ── -->
    <!-- Shortcuts Modal -->
    <div x-show="showShortcutsModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-ink-950/80 backdrop-blur-sm"
         @keydown.escape.window="showShortcutsModal = false">
        <div class="w-full max-w-md rounded-panel bg-ink-900 border border-ink-border p-6 shadow-2xl space-y-4"
             @click.away="showShortcutsModal = false">
            <div class="flex items-center justify-between border-b border-ink-border pb-3">
                <h3 class="text-base font-bold font-serif text-paper flex items-center gap-2">
                    <span>⌨️</span>
                    <span>Tugmalar orqali boshqaruv</span>
                </h3>
                <button type="button" @click="showShortcutsModal = false" class="text-mist hover:text-paper text-lg font-mono">×</button>
            </div>

            <div class="space-y-2.5 text-xs text-mist">
                <div class="flex items-center justify-between p-2 rounded-btn bg-ink-950 border border-ink-border">
                    <span class="text-paper">Varaqlash (Keyingi / Oldingi)</span>
                    <div class="flex items-center gap-1">
                        <kbd class="px-2 py-1 rounded bg-ink-900 border border-ink-border text-paper font-mono">←</kbd>
                        <kbd class="px-2 py-1 rounded bg-ink-900 border border-ink-border text-paper font-mono">→</kbd>
                        <span class="text-slate-500">yoki</span>
                        <kbd class="px-2 py-1 rounded bg-ink-900 border border-ink-border text-paper font-mono">Space</kbd>
                    </div>
                </div>

                <div class="flex items-center justify-between p-2 rounded-btn bg-ink-950 border border-ink-border">
                    <span class="text-paper">Masshtab (Zoom +/-)</span>
                    <div class="flex items-center gap-1">
                        <kbd class="px-2 py-1 rounded bg-ink-900 border border-ink-border text-paper font-mono">+</kbd>
                        <kbd class="px-2 py-1 rounded bg-ink-900 border border-ink-border text-paper font-mono">-</kbd>
                        <kbd class="px-2 py-1 rounded bg-ink-900 border border-ink-border text-paper font-mono">0</kbd>
                    </div>
                </div>

                <div class="flex items-center justify-between p-2 rounded-btn bg-ink-950 border border-ink-border">
                    <span class="text-paper">Avto-varaqlash (Avto-o'qish)</span>
                    <kbd class="px-2.5 py-1 rounded bg-ink-900 border border-ink-border text-amber-400 font-mono font-bold">A</kbd>
                </div>

                <div class="flex items-center justify-between p-2 rounded-btn bg-ink-950 border border-ink-border">
                    <span class="text-paper">To'liq ekran (Fullscreen)</span>
                    <kbd class="px-2.5 py-1 rounded bg-ink-900 border border-ink-border text-paper font-mono font-bold">F</kbd>
                </div>

                <div class="flex items-center justify-between p-2 rounded-btn bg-ink-950 border border-ink-border">
                    <span class="text-paper">Varaqlash ovozi (Sound FX)</span>
                    <kbd class="px-2.5 py-1 rounded bg-ink-900 border border-ink-border text-paper font-mono font-bold">S</kbd>
                </div>

                <div class="flex items-center justify-between p-2 rounded-btn bg-ink-950 border border-ink-border">
                    <span class="text-paper">Fon musiqasi (Ambient dock)</span>
                    <kbd class="px-2.5 py-1 rounded bg-ink-900 border border-ink-border text-paper font-mono font-bold">M</kbd>
                </div>

                <div class="flex items-center justify-between p-2 rounded-btn bg-ink-950 border border-ink-border">
                    <span class="text-paper">To'xtatish / Chiqish</span>
                    <kbd class="px-2.5 py-1 rounded bg-ink-900 border border-ink-border text-paper font-mono font-bold">Esc</kbd>
                </div>
            </div>

            <div class="pt-2 flex justify-end">
                <button type="button" @click="showShortcutsModal = false" class="ks-btn-primary py-1.5 px-4 text-xs font-semibold">Tushunarli</button>
            </div>
        </div>
    </div>

    <!-- Download Options Modal -->
    <div x-show="showDownloadModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-ink-950/80 backdrop-blur-sm"
         @keydown.escape.window="showDownloadModal = false">
        <div class="w-full max-w-md rounded-panel bg-ink-900 border border-ink-border p-6 shadow-2xl space-y-4"
             @click.away="showDownloadModal = false">
            <div class="flex items-center justify-between border-b border-ink-border pb-3">
                <h3 class="text-base font-bold font-serif text-paper flex items-center gap-2">
                    <span class="text-rose-400">📥</span>
                    <span>PDF yuklab olish</span>
                </h3>
                <button type="button" @click="showDownloadModal = false" class="text-mist hover:text-paper text-lg font-mono">×</button>
            </div>

            <p class="text-xs text-mist leading-relaxed">
                Kitobning elektron PDF nusxasini qurilmangizga yuklab oling va internetsiz ham istalgan PDF dasturida o'qing.
            </p>

            <div class="space-y-3">
                @if($book->pdf_path)
                    <a href="{{ route('books.pdf', $book->id) }}" 
                       class="w-full p-3 rounded-btn bg-rose-500/10 border border-rose-500/25 hover:bg-rose-500/20 text-rose-300 flex items-center justify-between transition-all group">
                        <div class="flex items-center gap-3">
                            <span class="w-9 h-9 rounded-btn bg-rose-500/20 flex items-center justify-center text-rose-400 text-lg">📄</span>
                            <div class="text-left">
                                <h4 class="text-xs font-bold text-paper group-hover:text-rose-300">Asl PDF fayl</h4>
                                <span class="text-[11px] text-mist font-mono">To'liq kitob matni va sahifalari</span>
                            </div>
                        </div>
                        <span class="text-xs text-rose-400 font-bold font-mono">Yuklab olish ➔</span>
                    </a>

                    <a href="{{ route('books.pdf.stream', $book->id) }}" target="_blank"
                       class="w-full p-3 rounded-btn bg-ink-950 border border-ink-border hover:border-amber-400/40 text-mist hover:text-paper flex items-center justify-between transition-all">
                        <div class="flex items-center gap-3">
                            <span class="w-9 h-9 rounded-btn bg-ink-900 flex items-center justify-center text-amber-400 text-lg">🌐</span>
                            <div class="text-left">
                                <h4 class="text-xs font-bold text-paper">Yangi oynada ochish</h4>
                                <span class="text-[11px] text-mist font-mono">Brauzerning o'zida ko'rish</span>
                            </div>
                        </div>
                        <span class="text-xs text-amber-400 font-bold font-mono">Ochish ➔</span>
                    </a>
                @else
                    <a href="{{ route('books.pdf', $book->id) }}" 
                       class="w-full p-3 rounded-btn bg-rose-500/10 border border-rose-500/25 hover:bg-rose-500/20 text-rose-300 flex items-center justify-between transition-all group">
                        <div class="flex items-center gap-3">
                            <span class="w-9 h-9 rounded-btn bg-rose-500/20 flex items-center justify-center text-rose-400 text-lg">📄</span>
                            <div class="text-left">
                                <h4 class="text-xs font-bold text-paper">Generatsiya qilingan PDF</h4>
                                <span class="text-[11px] text-mist font-mono">Barcha nashr etilgan boblar</span>
                            </div>
                        </div>
                        <span class="text-xs text-rose-400 font-bold font-mono">Yuklab olish ➔</span>
                    </a>
                @endif
            </div>

            <div class="pt-2 flex justify-end">
                <button type="button" @click="showDownloadModal = false" class="ks-btn-ghost py-1.5 px-4 text-xs font-semibold">Yopish</button>
            </div>
        </div>
    </div>

    <!-- ── 5. MODAL: 1 daqiqalik AI Kitob Konspekti ── -->
    <div x-show="showAiSummaryModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-ink-950/85 backdrop-blur-md"
         @keydown.escape.window="showAiSummaryModal = false">
        <div class="w-full max-w-xl max-h-[85vh] rounded-2xl bg-ink-900 border border-violet-500/30 shadow-[0_25px_60px_rgba(0,0,0,0.9)] flex flex-col overflow-hidden text-paper"
             @click.away="showAiSummaryModal = false">
            
            <!-- Modal Header -->
            <div class="px-5 py-4 border-b border-ink-border/80 flex items-center justify-between bg-gradient-to-r from-violet-950/40 via-ink-900 to-amber-950/30">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-violet-500/20 border border-violet-400/30 flex items-center justify-center text-lg shadow-inner">
                        🧠
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-sm sm:text-base font-bold font-serif text-paper">1 daqiqalik AI Konspekt</h3>
                            <span class="px-1.5 py-0.5 rounded-full bg-amber-400/15 border border-amber-400/30 text-amber-300 text-[10px] font-mono font-bold">Tezkor</span>
                        </div>
                        <p class="text-[11px] font-mono text-mist truncate max-w-xs">{{ $book->title }} • {{ $book->author }}</p>
                    </div>
                </div>
                <button type="button" @click="showAiSummaryModal = false" class="text-mist hover:text-paper text-xl font-mono p-1">×</button>
            </div>

            <!-- Modal Body -->
            <div class="p-5 sm:p-6 overflow-y-auto space-y-4 text-xs sm:text-sm leading-relaxed">
                <!-- Loading State -->
                <div x-show="aiSummaryLoading" class="py-12 text-center space-y-4">
                    <div class="w-12 h-12 mx-auto rounded-full border-2 border-amber-400 border-t-transparent animate-spin"></div>
                    <div class="space-y-1">
                        <p class="font-bold text-paper font-serif">Sun'iy intellekt kitobni tahlil qilmoqda...</p>
                        <p class="text-xs text-mist font-mono">1 daqiqada o'qiladigan asosiy g'oya va 3 ta eng muhim saboq shakllantirilmoqda</p>
                    </div>
                </div>

                <!-- Content State -->
                <div x-show="!aiSummaryLoading" class="space-y-4">
                    <div class="p-4 rounded-xl bg-ink-950/70 border border-ink-border/70 space-y-3 font-sans">
                        <div class="whitespace-pre-line text-paper/90 leading-relaxed font-sans" x-text="aiSummaryText"></div>
                    </div>

                    <div class="p-3 rounded-xl bg-amber-500/10 border border-amber-500/20 text-[11px] text-amber-200/80 flex items-center gap-2">
                        <span>💡</span>
                        <span>Ushbu konspekt sun'iy intellekt tomonidan kitob mazmunini tez anglab olishingiz uchun tuzildi. To'liq mutolaa zavqi uchun kitobni o'qishni tavsiya etamiz!</span>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="px-5 py-3 border-t border-ink-border/80 bg-ink-950/60 flex items-center justify-between">
                <button type="button"
                        @click="copyAiSummary()"
                        :disabled="aiSummaryLoading || !aiSummaryText"
                        class="ks-btn-ghost py-1.5 px-3 text-xs inline-flex items-center gap-1.5 text-mist hover:text-amber-400">
                    <span x-show="!aiSummaryCopied">📋 Nusxa olish</span>
                    <span x-show="aiSummaryCopied" class="text-amber-400 font-bold">✓ Nusxalandi!</span>
                </button>
                <div class="flex items-center gap-2">
                    <button type="button" @click="showAiSummaryModal = false" class="ks-btn-ghost py-1.5 px-4 text-xs font-semibold">Yopish</button>
                    @if($book->chapters->first())
                        <a href="{{ route('reader.show', ['book' => $book->id, 'chapter' => $book->chapters->first()->id]) }}"
                           class="ks-btn-primary py-1.5 px-4 text-xs font-bold inline-flex items-center gap-1">
                            <span>Kitobni o'qish ➔</span>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Ambient Background Music Player -->
    <x-ambient-music-player :book="$book" />

</div>
@endsection

@push('scripts')
@if($book->pdf_path)
<!-- PDF.js CDN -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<!-- Nodlik/StPageFlip 3D Engine -->
<script src="{{ asset('assets/flipbook/page-flip.browser.js') }}?v={{ filemtime(public_path('assets/flipbook/page-flip.browser.js')) }}"></script>
<!-- Flipbook Core Engine -->
<script src="{{ asset('assets/flipbook/flipbook.js') }}?v={{ filemtime(public_path('assets/flipbook/flipbook.js')) }}"></script>
@endif

@if($bookAiReady)
<script src="{{ asset('vendor/katex/katex.min.js') }}"></script>
<script src="{{ asset('vendor/katex/auto-render.min.js') }}"></script>
<script>
    window.renderBookAiMath = function(rootEl) {
        if (typeof renderMathInElement !== 'function') return;
        const container = rootEl || document.getElementById('book-ai-section');
        if (!container) return;
        try {
            renderMathInElement(container, {
                delimiters: [
                    { left: '$$', right: '$$', display: true },
                    { left: '$', right: '$', display: false },
                    { left: '\\(', right: '\\)', display: false },
                    { left: '\\[', right: '\\]', display: true }
                ],
                throwOnError: false
            });
        } catch (e) {}
    };
    document.addEventListener('DOMContentLoaded', function() {
        window.renderBookAiMath();
    });
</script>
@endif
@endpush
