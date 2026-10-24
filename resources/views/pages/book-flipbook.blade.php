@extends('layouts.app')

@section('title', 'Varaqlab o\'qish — ' . $book->title)

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Merriweather:ital,wght@0,300;0,400;0,700;1,400&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('assets/flipbook/flipbook.css') }}?v={{ filemtime(public_path('assets/flipbook/flipbook.css')) }}">
@endpush

@section('content')
<div id="fb-standalone-container" x-data="{ showChatDrawer: false }" class="w-full max-w-[1700px] mx-auto space-y-3 px-1 sm:px-4 pb-8">

    <!-- Top Navigation & Tools Bar -->
    <div class="flex items-center justify-between flex-wrap gap-3 border-b border-ink-border pb-3">
        <div class="flex items-center gap-3">
            <a href="{{ route('books.show', $book->slug) }}" 
               class="px-4 py-2 rounded-xl bg-ink-900/90 border border-ink-border text-paper hover:text-amber-500 hover:border-amber-400/30 text-xs font-semibold transition-all flex items-center gap-2 shrink-0">
                <span>← Chiqish</span>
            </a>
            <div class="min-w-0">
                <h1 class="text-base sm:text-lg font-black text-paper leading-tight flex items-center gap-2 truncate">
                    <span>📖</span>
                    <span class="truncate">{{ $book->title }}</span>
                </h1>
                <p class="text-[11px] text-mist truncate">Muallif: {{ $book->author }} • 3D Real Kitob Mutolaasi</p>
            </div>
        </div>

        <!-- Controls: Page Indicator & Actions -->
        <div class="flex items-center gap-2 sm:gap-3 flex-wrap">
            <!-- Interactive Page Selector / Jumper -->
            <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-ink-900 border border-ink-border shadow-inner group">
                <span class="text-xs text-mist font-medium">Sahifa:</span>
                <input type="number" 
                       id="fb-page-input" 
                       min="1" 
                       value="1" 
                       title="Sahifa raqamini yozing va Enter bosing"
                       class="w-14 sm:w-16 px-1.5 py-0.5 text-center font-mono text-xs font-bold text-amber-600 dark:text-amber-400 bg-ink-950 border border-ink-border hover:border-amber-400/50 focus:border-amber-400 focus:bg-amber-400/10 focus:ring-1 focus:ring-amber-400 rounded-lg outline-none transition-all"
                       onkeydown="if(event.key === 'Enter') { this.blur(); fbJumpToPageInput(); }"
                       onchange="fbJumpToPageInput()"
                       onfocus="this.select()">
                <span class="text-xs text-mist font-mono">/</span>
                <span id="fb-total-pages" class="text-xs text-paper font-mono font-semibold">...</span>

                <!-- Quick Dropdown Page Selector -->
                <select id="fb-page-select" 
                        onchange="if(this.value) { fbGoToPage(parseInt(this.value)); }"
                        title="Ro'yxatdan sahifani tanlash"
                        class="ml-1 bg-ink-950 hover:bg-ink-800 border border-ink-border text-paper rounded-lg text-xs py-0.5 px-1.5 outline-none cursor-pointer font-sans">
                    <option value="">Tanlash ▾</option>
                </select>
            </div>

            <!-- Sahifa masshtabi (rasm kattalashadi) -->
            <div class="flex items-center bg-ink-900 border border-ink-border rounded-xl overflow-hidden p-0.5 shadow-inner">
                <button type="button" 
                        onclick="fbZoomOut()" 
                        title="Sahifani kichiklashtirish (masshtab) — klaviaturada: -" 
                        class="px-2 sm:px-2.5 py-1 text-xs font-mono font-bold text-paper hover:bg-ink-800 transition-colors">
                    🔍−
                </button>
                <button type="button" 
                        onclick="fbZoomReset()" 
                        id="fb-zoom-level" 
                        title="100% asl o'lchamga qaytarish" 
                        class="px-2 text-xs font-mono font-bold text-amber-600 dark:text-amber-400 hover:text-amber-500">
                    100%
                </button>
                <button type="button" 
                        onclick="fbZoomIn()" 
                        title="Sahifani kattalashtirish (masshtab) — klaviaturada: +" 
                        class="px-2 sm:px-2.5 py-1 text-xs font-mono font-bold text-paper hover:bg-ink-800 transition-colors">
                    🔍+
                </button>
            </div>

            <!-- Matn rejimi: shrift turi va o'lchamini jonli o'zgartirish -->
            <button type="button"
                    onclick="fbtOpen()"
                    title="Matn rejimi — shrift turi, o'lchami va qator oralig'ini jonli o'zgartirish"
                    class="px-3 py-1.5 rounded-xl bg-amber-500/10 hover:bg-amber-500/20 border border-amber-500/30 text-amber-700 dark:text-amber-300 text-xs font-bold flex items-center gap-1.5 transition-all">
                <span class="font-serif text-sm leading-none">Aa</span>
                <span>Matn</span>
            </button>

            <!-- Paper Theme Selector -->
            <div class="flex items-center bg-ink-900 border border-ink-border rounded-xl p-0.5 shadow-inner">
                <button type="button" 
                        data-paper-theme-btn="ivory"
                        onclick="fbSetPaperTheme('ivory')" 
                        class="px-2.5 py-1 text-[11px] rounded transition-all text-paper hover:bg-ink-800" 
                        title="Tabiiy krem qog'oz">📜 Asl</button>
                <button type="button" 
                        data-paper-theme-btn="sepia"
                        onclick="fbSetPaperTheme('sepia')" 
                        class="px-2.5 py-1 text-[11px] rounded transition-all text-paper hover:bg-ink-800" 
                        title="Klassik sepia ohangi">🍵 Sepia</button>
                <button type="button" 
                        data-paper-theme-btn="night"
                        onclick="fbSetPaperTheme('night')" 
                        class="px-2.5 py-1 text-[11px] rounded transition-all text-paper hover:bg-ink-800" 
                        title="Tungi qorong'i mutolaa">🌙 Tun</button>
            </div>

            @if($book->chapters()->exists())
            <!-- Text Mode Link with Custom Font Families -->
            <a href="{{ route('reader.show', [$book, $book->chapters->first()]) }}" 
               title="Matnli mutolaa rejimiga o'tish (Shrift turi va o'lchamini erkin o'zgartirish)" 
               class="hidden md:flex px-3 py-1.5 rounded-xl bg-amber-500/10 hover:bg-amber-500/20 border border-amber-500/30 text-amber-700 dark:text-amber-300 text-xs font-semibold items-center gap-1.5 transition-all">
                <span>🔤 Shriftlar</span>
            </a>
            @endif

            <!-- Auto-play toggle button -->
            <button type="button" 
                    id="fb-autoplay-btn"
                    onclick="fbToggleAutoPlay()" 
                    title="Avtomatik varaqlash (A klavishi)" 
                    class="px-3 py-1.5 rounded-xl bg-ink-900 border border-ink-border hover:border-emerald-400/50 text-paper hover:text-emerald-600 dark:hover:text-emerald-400 text-xs font-medium transition-colors flex items-center gap-1.5">
                <span>▶ Avto</span>
            </button>

            <!-- Sound toggle button -->
            <button type="button" 
                    id="fb-sound-btn"
                    onclick="fbToggleSound()" 
                    title="Varaqlash ovozini yoqish/o'chirish (S klavishi)" 
                    class="px-3 py-1.5 rounded-xl bg-ink-900 border border-ink-border text-paper hover:bg-ink-800 text-xs font-medium transition-colors flex items-center gap-1.5">
                🔊 Ovoz
            </button>

            <!-- Ambient Music toggle button -->
            <button type="button" 
                    onclick="window.dispatchEvent(new CustomEvent('open-ambient-music'))" 
                    title="Mutolaa fon musiqasi (M klavishi)" 
                    class="px-3 py-1.5 rounded-xl bg-ink-900 border border-ink-border hover:border-amber-400/40 text-amber-600 dark:text-amber-400 hover:text-amber-500 text-xs font-medium transition-colors flex items-center gap-1.5">
                🎵 Musiqa
            </button>

            <!-- Fullscreen -->
            <button type="button" 
                    onclick="fbToggleFullscreen()" 
                    title="To'liq ekran (F11 yoki F)" 
                    class="p-2 rounded-xl bg-ink-900 border border-ink-border text-mist hover:text-paper hover:border-amber-400/30 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
            </button>

            <!-- PDF Download -->
            <a href="{{ route('books.pdf', $book) }}" 
               title="PDF yuklab olish" 
               class="px-3.5 py-2 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 border border-rose-500/25 text-rose-600 dark:text-rose-400 text-xs font-semibold flex items-center gap-1.5 transition-all">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span class="hidden sm:inline">PDF</span>
            </a>
            <!-- Suhbat toggle button -->
            <button type="button" 
                    @click="showChatDrawer = !showChatDrawer; $nextTick(() => { window.dispatchEvent(new CustomEvent('book-chat-scroll-bottom')); })"
                    :class="showChatDrawer ? 'bg-amber-500 text-slate-950 font-bold border-amber-400' : 'bg-ink-900 border-ink-border text-amber-700 dark:text-amber-300 hover:border-amber-400/40'"
                    class="px-3 py-1.5 rounded-xl border text-xs font-medium transition-colors flex items-center gap-1.5 shadow-md">
                <span>💬</span>
                <span>Suhbat</span>
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
            </button>
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
                    <div class="fb-corner-curl fb-corner-curl--bottom-left" title="Oldingi sahifa"></div>
                </div>

                <!-- Realistic Book Spine / Seam -->
                <div class="fb-book-spine"></div>

                <!-- Right Page Canvas with Thickness Shadows -->
                <div class="fb-page-pane fb-page-pane--right">
                    <canvas id="fb-canvas-right" class="fb-canvas"></canvas>
                    <div class="fb-corner-curl fb-corner-curl--bottom-right" title="Keyingi sahifa"></div>
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
                <div class="fb-click-zone fb-click-zone--prev" title="Oldingi sahifaga varaqlash (yoki chap o'q tugmasi)"></div>
                <div class="fb-click-zone fb-click-zone--next" title="Keyingi sahifaga varaqlash (yoki o'ng o'q tugmasi)"></div>

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

    <!-- Quick Gestures & Keyboard Hint -->
    <div class="flex items-center justify-center gap-3 sm:gap-4 text-xs text-mist pt-1 flex-wrap">
        <span class="flex items-center gap-1.5 text-amber-600 dark:text-amber-400 font-medium">
            <span>🖱️ / 👆</span>
            <span>Mishka yoki barmoq bilan surib varaqlash</span>
        </span>
        <span class="text-mist/40">•</span>
        <span class="flex items-center gap-1.5">
            <kbd class="px-2 py-0.5 rounded bg-ink-800 border border-ink-border text-paper font-mono text-[11px]">←</kbd>
            <kbd class="px-2 py-0.5 rounded bg-ink-800 border border-ink-border text-paper font-mono text-[11px]">→</kbd>
            <span>Klaviatura o'qlari</span>
        </span>
        <span class="text-mist/40">•</span>
        <span class="flex items-center gap-1.5">
            <kbd class="px-2 py-0.5 rounded bg-ink-800 border border-ink-border text-paper font-mono text-[11px]">+</kbd>
            <kbd class="px-2 py-0.5 rounded bg-ink-800 border border-ink-border text-paper font-mono text-[11px]">-</kbd>
            <span>Kattalashtirish (Zoom)</span>
        </span>
        <span class="text-mist/40">•</span>
        <span>Sahifani bosish yoki 2 marta tez bosib yaqinlashtirish</span>
    </div>

    <!-- Kitobxonlar jonli suhbati paneli (Kitobning pastida) -->
    <div x-show="showChatDrawer" x-cloak
         class="rounded-card border border-amber-500/30 bg-ink-950/95 backdrop-blur-md shadow-2xl p-3 sm:p-5 space-y-3">
        <div class="flex items-center justify-between border-b border-ink-border pb-3">
            <div class="flex items-center gap-2">
                <span class="text-xl">💬</span>
                <div>
                    <h3 class="text-sm font-bold font-serif text-paper">Kitobxonlar jonli suhbati va muhokamasi</h3>
                    <p class="text-[11px] text-mist font-mono">Mutolaa jarayonida kitobxonlar bilan gaplashing va izohlar qoldiring</p>
                </div>
            </div>
            <button type="button" @click="showChatDrawer = false" class="text-mist hover:text-paper font-mono px-2.5 py-1 text-xs bg-ink-900 border border-ink-border rounded-xl transition-colors">
                Yopish ✕
            </button>
        </div>
        <div>
            @livewire('book.book-discussion-chat', ['book' => $book, 'isDrawerMode' => true], key('flipbook-chat-'.$book->id))
        </div>
    </div>

    <!-- PDF Annotatsiyalar va Bet belgilari (Kitobning pastida) -->
    <x-reader.pdf-annotations :book="$book" :annotations="$pdfAnnotations" />

    <!-- Reading Tracker & 5-minute AFK Inactivity Modal -->
    @include('components.reading-tracker', [
        'bookId' => $book->id,
        'chapterId' => null,
        'pageType' => 'flipbook'
    ])

    <!-- Ambient Background Music Player -->
    <x-ambient-music-player :book="$book" />
    <!-- Matn rejimi paneli (jonli shrift sozlamalari) -->
    <div id="fbt-panel" class="fbt-panel" data-theme="dark" role="dialog" aria-modal="true" aria-label="Matn rejimi">
        <div class="fbt-bar">
            <!-- Shrift o'lchami -->
            <div class="fbt-group" title="Shrift o'lchami (klaviaturada: + / -)">
                <button type="button" class="fbt-btn" onclick="fbtSizeDown()" aria-label="Shriftni kichraytirish">A−</button>
                <button type="button" class="fbt-label" id="fbt-size-label" onclick="fbtSizeReset()" style="background:none;border:0;cursor:pointer" title="Asl o'lchamga qaytarish">19px</button>
                <button type="button" class="fbt-btn" onclick="fbtSizeUp()" aria-label="Shriftni kattalashtirish" style="font-size:1rem">A+</button>
            </div>
            <input type="range" id="fbt-size-range" class="fbt-range" min="13" max="40" step="1" value="19" aria-label="Shrift o'lchami">

            <!-- Shrift turi (Maxsus ko'rinadigan dropdown) -->
            <div class="fbt-dd" id="fbt-font-dd">
                <button type="button" class="fbt-dd-trigger" onclick="fbtToggleDd('fbt-font-dd')" aria-haspopup="listbox">
                    <span class="fbt-dd-prefix">🔤</span>
                    <span id="fbt-font-label">Merriweather</span>
                    <span class="fbt-dd-caret">▾</span>
                </button>
                <div class="fbt-dd-menu" role="listbox" aria-label="Shrift turi">
                    <button type="button" class="fbt-dd-item" data-fbt-font="merriweather" style="font-family:'Merriweather',Georgia,serif">
                        <span>Merriweather (Klassik)</span>
                        <span class="fbt-dd-check">✓</span>
                    </button>
                    <button type="button" class="fbt-dd-item" data-fbt-font="spectral" style="font-family:'Spectral',Georgia,serif">
                        <span>Spectral (Adabiy)</span>
                        <span class="fbt-dd-check">✓</span>
                    </button>
                    <button type="button" class="fbt-dd-item" data-fbt-font="sans" style="font-family:'DM Sans',system-ui,sans-serif">
                        <span>DM Sans (Zamonaviy)</span>
                        <span class="fbt-dd-check">✓</span>
                    </button>
                    <button type="button" class="fbt-dd-item" data-fbt-font="mono" style="font-family:'DM Mono',monospace">
                        <span>DM Mono (Texnik)</span>
                        <span class="fbt-dd-check">✓</span>
                    </button>
                </div>
            </div>

            <!-- Qator oralig'i (Maxsus ko'rinadigan dropdown) -->
            <div class="fbt-dd" id="fbt-line-dd">
                <button type="button" class="fbt-dd-trigger" onclick="fbtToggleDd('fbt-line-dd')" aria-haspopup="listbox">
                    <span class="fbt-dd-prefix">↕</span>
                    <span id="fbt-line-label">Qator: qulay</span>
                    <span class="fbt-dd-caret">▾</span>
                </button>
                <div class="fbt-dd-menu" role="listbox" aria-label="Qator oralig'i">
                    <button type="button" class="fbt-dd-item" data-fbt-line="1.4">
                        <span>Qator: zich (1.4)</span>
                        <span class="fbt-dd-check">✓</span>
                    </button>
                    <button type="button" class="fbt-dd-item" data-fbt-line="1.6">
                        <span>Qator: o'rtacha (1.6)</span>
                        <span class="fbt-dd-check">✓</span>
                    </button>
                    <button type="button" class="fbt-dd-item" data-fbt-line="1.8">
                        <span>Qator: qulay (1.8)</span>
                        <span class="fbt-dd-check">✓</span>
                    </button>
                    <button type="button" class="fbt-dd-item" data-fbt-line="2.1">
                        <span>Qator: keng (2.1)</span>
                        <span class="fbt-dd-check">✓</span>
                    </button>
                </div>
            </div>

            <!-- Fon mavzusi -->
            <div class="fbt-group">
                <button type="button" class="fbt-btn" data-fbt-theme="light">☀ Yorug'</button>
                <button type="button" class="fbt-btn" data-fbt-theme="sepia">🍵 Sepia</button>
                <button type="button" class="fbt-btn" data-fbt-theme="dark">🌙 Tun</button>
            </div>

            <!-- Sahifaga tez o'tish (Top bar ichida ham ko'rinadi) -->
            <div class="fbt-group">
                <button type="button" class="fbt-btn" id="fbt-prev-top" onclick="fbtPrev()" title="Oldingi sahifa">←</button>
                <div class="fbt-dd" id="fbt-page-dd">
                    <button type="button" class="fbt-dd-trigger fbt-dd-trigger--compact" onclick="fbtToggleDd('fbt-page-dd')" title="Sahifani tanlash">
                        <span id="fbt-page-indicator" class="fbt-label" style="min-width:4.2rem">1 / 1</span>
                        <span class="fbt-dd-caret">▾</span>
                    </button>
                    <div class="fbt-dd-menu fbt-dd-menu--pages" id="fbt-page-menu" role="listbox" aria-label="Sahifani tanlash"></div>
                </div>
                <button type="button" class="fbt-btn" id="fbt-next-top" onclick="fbtNext()" title="Keyingi sahifa">→</button>
            </div>

            <span class="fbt-spacer"></span>
            <button type="button" class="fbt-btn fbt-close" onclick="fbtClose()" title="Asl ko'rinishga qaytish (Esc)">✕ Asl ko'rinish</button>
        </div>

        <div id="fbt-scroll" class="fbt-scroll">
            <div id="fbt-body" class="fbt-body"></div>
        </div>

        <div class="fbt-foot">
            <button type="button" id="fbt-prev" class="fbt-btn" onclick="fbtPrev()">← Oldingi sahifa</button>
            <span id="fbt-page-indicator-foot" class="fbt-label" style="min-width:7rem">Sahifa 1 / 1</span>
            <button type="button" id="fbt-next" class="fbt-btn" onclick="fbtNext()">Keyingi sahifa →</button>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<!-- PDF.js CDN -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<!-- Nodlik/StPageFlip 3D Engine -->
<script src="{{ asset('assets/flipbook/page-flip.browser.js') }}?v={{ filemtime(public_path('assets/flipbook/page-flip.browser.js')) }}"></script>
<!-- Flipbook Core Engine -->
<script src="{{ asset('assets/flipbook/flipbook.js') }}?v={{ filemtime(public_path('assets/flipbook/flipbook.js')) }}"></script>
<!-- Matn rejimi (jonli shrift sozlamalari) -->
<script src="{{ asset('assets/flipbook/flipbook-text.js') }}?v={{ filemtime(public_path('assets/flipbook/flipbook-text.js')) }}"></script>
@endpush
