<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" 
      x-data="readerApp()" 
      :class="{ 'dark': theme === 'dark', 'reader-sepia': theme === 'sepia', 'reader-light': theme === 'light' }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $chapter->title }} — {{ $book->title }} | Kitobxon</title>

    @include('partials.design-system')
    @if(file_exists(public_path('build/manifest.json')))
        @vite(['resources/js/app.js'])
    @else
        <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @endif
    <style>
        input[type="number"] {
            -moz-appearance: textfield;
            appearance: textfield;
        }
        input[type="number"]::-webkit-outer-spin-button,
        input[type="number"]::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        /* 3 Editorial Reading Themes */
        .reader-light { background-color: #FAF7F2 !important; color: #1A1D24 !important; }
        .reader-light .reader-topbar { background-color: rgba(250, 247, 242, 0.95) !important; border-color: #E5DFD5 !important; }
        .reader-light .reader-text { color: #1A1D24 !important; }
        .reader-light .reader-ctrl { background-color: #F0EAE1 !important; border-color: #E5DFD5 !important; color: #1A1D24 !important; }

        .reader-sepia { background-color: #FBF0D9 !important; color: #433422 !important; }
        .reader-sepia .reader-topbar { background-color: rgba(251, 240, 217, 0.95) !important; border-color: #EBD6AE !important; }
        .reader-sepia .reader-text { color: #3B2C1B !important; }
        .reader-sepia .reader-ctrl { background-color: #F4E6C8 !important; border-color: #EBD6AE !important; color: #433422 !important; }

        .dark { background-color: #07090E !important; color: #F0EDE6 !important; }
        .dark .reader-topbar { background-color: rgba(7, 9, 14, 0.95) !important; border-color: #1F293D !important; }
        .dark .reader-text { color: #F0EDE6 !important; }
        .dark .reader-ctrl { background-color: #0F141F !important; border-color: #1F293D !important; color: #F0EDE6 !important; }

        .reader-serif { font-family: 'Spectral', Georgia, serif !important; }
        .reader-sans { font-family: 'DM Sans', system-ui, sans-serif !important; }
    </style>
</head>
<body class="bg-ink-950 text-paper min-h-screen flex flex-col font-sans transition-colors duration-200">

    <!-- Fixed Reader Top Bar -->
    <header class="sticky top-0 z-40 backdrop-blur-md border-b px-2 sm:px-4 py-2.5 sm:py-3 reader-topbar">
        <div class="max-w-4xl mx-auto flex items-center justify-between gap-1.5 sm:gap-4">
            <!-- Left: Back link & chapter info -->
            <div class="flex items-center gap-1.5 sm:gap-3 min-w-0">
                <a href="{{ route('books.show', $book->slug) }}" class="p-1 sm:p-1.5 rounded-btn text-mist hover:text-paper transition-colors shrink-0" title="Kitob sahifasiga qaytish">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                <div class="min-w-0">
                    <h2 class="text-xs font-bold font-serif text-paper truncate max-w-[90px] sm:max-w-xs">{{ $chapter->title }}</h2>
                    <span class="text-[10px] sm:text-[11px] font-mono text-mist truncate hidden sm:block">«{{ $book->title }}»</span>
                </div>
            </div>

            <!-- Active Reading Timer Pill -->
            <div class="flex items-center gap-1 sm:gap-2 px-2 py-0.5 sm:px-3 sm:py-1 rounded-badge bg-ink-900 border border-ink-border text-amber-400 font-mono text-[11px] sm:text-xs shrink-0"
                 :class="{ 'opacity-50': isIdle }">
                <span class="w-1.5 h-1.5 sm:w-2 sm:h-2 rounded-full" :class="isIdle ? 'bg-amber-500' : 'bg-emerald-400 animate-pulse'"></span>
                <span x-text="formatTime(activeSeconds)">00:00</span>
                <span x-show="isIdle" class="text-[9px] sm:text-[10px] text-amber-400 font-sans hidden sm:inline">(pauza)</span>
            </div>

            <!-- Right Controls: Font size, Serif/Sans, Theme, Music -->
            <div class="flex items-center gap-1 sm:gap-2 shrink-0">
                <!-- Ambient Music Quick Toggle in Topbar -->
                <button type="button" 
                        @click="window.dispatchEvent(new CustomEvent('open-ambient-music'))" 
                        class="px-2 py-0.5 sm:px-2.5 sm:py-1 rounded-btn text-xs font-mono font-bold border reader-ctrl hover:border-amber-400/50 hover:text-amber-400 transition-colors flex items-center gap-1"
                        title="Fon musiqasi pleyeri (M klavishi)">
                    <span>🎵</span>
                    <span class="hidden md:inline">Musiqa</span>
                </button>

                <!-- Font Family Toggle -->
                <button @click="toggleFont()" class="hidden sm:inline-flex px-2 py-0.5 sm:px-2.5 sm:py-1 rounded-btn text-xs font-mono font-bold border reader-ctrl transition-colors">
                    <span x-text="fontFamily === 'serif' ? 'Serif' : 'Sans'"></span>
                </button>

                <!-- Font Size Controls -->
                <div class="flex items-center border reader-ctrl rounded-btn overflow-hidden">
                    <button @click="decreaseFont()" class="px-1.5 sm:px-2 py-0.5 text-xs font-mono font-bold hover:bg-black/10 transition-colors">A-</button>
                    <button @click="increaseFont()" class="px-1.5 sm:px-2 py-0.5 text-xs font-mono font-bold hover:bg-black/10 transition-colors">A+</button>
                </div>

                <!-- Theme Toggle (Light, Sepia, Dark) -->
                <div class="flex items-center gap-0.5 sm:gap-1 border reader-ctrl rounded-btn p-0.5">
                    <button @click="setTheme('light')" 
                            :class="{ 'ring-1 ring-amber-500': theme === 'light' }"
                            class="w-5 h-5 sm:w-6 sm:h-6 rounded-badge bg-[#FAF7F2] text-xs flex items-center justify-center text-slate-800" title="Yorug' rejim">
                        <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
                    </button>
                    <button @click="setTheme('sepia')" 
                            :class="{ 'ring-1 ring-amber-500': theme === 'sepia' }"
                            class="w-5 h-5 sm:w-6 sm:h-6 rounded-badge bg-[#FBF0D9] text-xs flex items-center justify-center text-amber-900" title="Sepiya (ko'zga qulay)">
                        <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                    </button>
                    <button @click="setTheme('dark')" 
                            :class="{ 'ring-1 ring-amber-500': theme === 'dark' }"
                            class="w-5 h-5 sm:w-6 sm:h-6 rounded-badge bg-[#07090E] text-xs flex items-center justify-center text-amber-400" title="Tungi rejim">
                        <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Amber Reading Progress bar -->
        <div class="absolute bottom-0 inset-x-0 h-0.5 bg-black/10 dark:bg-white/10">
            <div class="bg-amber-500 h-0.5 transition-all duration-150" :style="`width: ${scrollPercent}%`"></div>
        </div>
    </header>

    <!-- Reading Content Area -->
    <main class="flex-1 max-w-[66ch] mx-auto px-4 py-8 sm:px-6 sm:py-12 w-full">
        <!-- Chapter Header -->
        <div class="mb-10 text-center border-b pb-8" style="border-color: rgba(139,155,173,0.2);">
            <span class="text-xs font-mono uppercase tracking-widest text-amber-500 block mb-2 font-bold">
                {{ $chapter->chapter_number }}-Bob
            </span>
            <h1 class="text-3xl sm:text-4xl font-bold font-serif tracking-tight reader-text leading-tight">{{ $chapter->title }}</h1>
        </div>

        <!-- Main Chapter Text -->
        <article class="reader-content leading-[1.75] transition-all reader-text text-justify"
                 :class="{ 'reader-serif': fontFamily === 'serif', 'reader-sans': fontFamily === 'sans' }"
                 :style="`font-size: ${fontSize}px;`">
            {!! nl2br(e($chapter->content)) !!}
        </article>

        <!-- Chapter Navigation Footer -->
        <div class="mt-16 pt-8 border-t flex items-center justify-between" style="border-color: rgba(139,155,173,0.2);">
            <a href="{{ route('books.show', $book->slug) }}" class="ks-btn-ghost py-1.5 px-3 text-xs inline-flex items-center gap-1.5 font-mono">
                ← Boblar ro'yxatiga qaytish
            </a>
            <button @click="saveProgress(100)" class="ks-btn-primary py-2 px-4 text-xs font-semibold inline-flex items-center gap-1.5">
                <span>Bobni yakunlash</span>
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            </button>
        </div>
    </main>

    <script>
        function readerApp() {
            return {
                theme: localStorage.getItem('reader_theme') || 'dark',
                fontFamily: localStorage.getItem('reader_font') || 'serif',
                fontSize: parseInt(localStorage.getItem('reader_size')) || 19,
                scrollPercent: 0,
                activeSeconds: 0,
                isIdle: false,
                idleTimer: null,
                heartbeatInterval: null,

                init() {
                    window.addEventListener('scroll', () => {
                        const winScroll = document.documentElement.scrollTop;
                        const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
                        this.scrollPercent = Math.min(100, Math.round((winScroll / height) * 100)) || 0;
                    }, { passive: true });
                },

                toggleFont() {
                    this.fontFamily = this.fontFamily === 'serif' ? 'sans' : 'serif';
                    localStorage.setItem('reader_font', this.fontFamily);
                },

                increaseFont() {
                    if (this.fontSize < 28) {
                        this.fontSize += 2;
                        localStorage.setItem('reader_size', this.fontSize);
                    }
                },

                decreaseFont() {
                    if (this.fontSize > 14) {
                        this.fontSize -= 2;
                        localStorage.setItem('reader_size', this.fontSize);
                    }
                },

                setTheme(t) {
                    this.theme = t;
                    localStorage.setItem('reader_theme', t);
                },

                formatTime(sec) {
                    const m = Math.floor(sec / 60).toString().padStart(2, '0');
                    const s = (sec % 60).toString().padStart(2, '0');
                    return `${m}:${s}`;
                },

                saveProgress(percent) {
                    fetch('{{ route('reading.progress') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            book_id: {{ $book->id }},
                            chapter_id: {{ $chapter->id }},
                            last_position: window.scrollY,
                            percent_complete: percent
                        })
                    }).then(() => {
                        window.location.href = "{{ route('books.show', $book->slug) }}";
                    }).catch(() => {
                        window.location.href = "{{ route('books.show', $book->slug) }}";
                    });
                }
            }
        }
    </script>

    <!-- Ambient Background Music Player -->
    <x-ambient-music-player :book="$book" />

    <!-- Reading Tracker & 5-minute AFK Inactivity Modal -->
    @include('components.reading-tracker', [
        'bookId' => $book->id,
        'chapterId' => $chapter->id,
        'pageType' => 'chapter'
    ])
</body>
</html>
