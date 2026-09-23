<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" 
      x-data="readerApp()" 
      :class="{ 'dark': theme === 'dark', 'reader-sepia': theme === 'sepia' }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $chapter->title }} - {{ $book->title }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Merriweather:ital,wght@0,300;0,400;0,700;1,300&display=swap" rel="stylesheet">

    @if(file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = {
                darkMode: 'class',
                theme: {
                    extend: {
                        colors: {
                            primary: { 600: '#4f46e5' },
                            accent: { 500: '#f59e0b' }
                        }
                    }
                }
            }
        </script>
        <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @endif
    <style>
        .reader-sepia { background-color: #fbf0d9 !important; color: #433422 !important; }
        .reader-sepia .reader-card { background-color: #f4e6c8 !important; border-color: #ebd6ae !important; }
        .reader-sepia .reader-text { color: #3b2c1b !important; }
    </style>
</head>
<body class="bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100 min-h-screen flex flex-col font-sans transition-colors duration-200">

    <!-- Fixed Reader Top Bar -->
    <header class="sticky top-0 z-40 bg-white/90 dark:bg-slate-900/90 backdrop-blur-md border-b border-slate-200 dark:border-slate-800 px-4 py-3 reader-card">
        <div class="max-w-5xl mx-auto flex items-center justify-between">
            <!-- Left: Back link & chapter info -->
            <div class="flex items-center gap-3">
                <a href="{{ route('books.show', $book->slug) }}" class="p-2 rounded-xl text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                <div>
                    <h2 class="text-xs font-bold text-slate-900 dark:text-white truncate max-w-xs sm:max-w-md">{{ $chapter->title }}</h2>
                    <span class="text-[11px] text-slate-400">В«{{ $book->title }}В»</span>
                </div>
            </div>

            <!-- Active Reading Timer Pill -->
            <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 text-xs font-bold"
                 :class="{ 'opacity-50': isIdle }">
                <span class="w-2 h-2 rounded-full" :class="isIdle ? 'bg-amber-400' : 'bg-emerald-500 animate-pulse'"></span>
                <span x-text="formatTime(activeSeconds)">00:00</span>
                <span x-show="isIdle" class="text-[10px] text-amber-500">(pauza)</span>
            </div>

            <!-- Right Controls: Font size, Serif/Sans, Theme -->
            <div class="flex items-center gap-2">
                <!-- Font Family Toggle -->
                <button @click="toggleFont()" class="px-2.5 py-1.5 rounded-xl text-xs font-bold border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                    <span x-text="fontFamily === 'serif' ? 'Serif' : 'Sans'"></span>
                </button>

                <!-- Font Size Controls -->
                <div class="flex items-center border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden">
                    <button @click="decreaseFont()" class="px-2.5 py-1 text-xs font-bold hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">A-</button>
                    <button @click="increaseFont()" class="px-2.5 py-1 text-xs font-bold hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">A+</button>
                </div>

                <!-- Theme Toggle (Light, Dark, Sepia) -->
                <div class="flex items-center gap-1 border border-slate-200 dark:border-slate-700 rounded-xl p-0.5">
                    <button @click="setTheme('light')" class="w-6 h-6 rounded-lg bg-white border text-xs flex items-center justify-center text-slate-800" title="Yorug' rejim">вЂпёЏ</button>
                    <button @click="setTheme('sepia')" class="w-6 h-6 rounded-lg bg-[#fbf0d9] border text-xs flex items-center justify-center text-amber-900" title="Sepiya (ko'zga qulay)">рџ“–</button>
                    <button @click="setTheme('dark')" class="w-6 h-6 rounded-lg bg-slate-900 border text-xs flex items-center justify-center text-slate-100" title="Tungi rejim">рџЊ™</button>
                </div>
            </div>
        </div>

        <!-- Reading Progress bar at bottom of topbar -->
        <div class="absolute bottom-0 inset-x-0 h-1 bg-slate-200 dark:bg-slate-800">
            <div class="bg-indigo-600 h-1 transition-all duration-200" :style="`width: ${scrollPercent}%`"></div>
        </div>
    </header>

    <!-- Reading Content Area -->
    <main class="flex-1 max-w-3xl mx-auto px-6 py-12 w-full">
        <!-- Chapter Header -->
        <div class="mb-10 text-center border-b border-slate-200/60 dark:border-slate-800/60 pb-8">
            <span class="text-xs font-bold uppercase tracking-widest text-indigo-600 dark:text-indigo-400 block mb-2">
                {{ $chapter->chapter_number }}-Bob
            </span>
            <h1 class="text-3xl sm:text-4xl font-black font-manrope tracking-tight reader-text">{{ $chapter->title }}</h1>
        </div>

        <!-- Main Chapter Text -->
        <article class="reader-content leading-relaxed transition-all reader-text"
                 :class="{ 'reader-serif': fontFamily === 'serif', 'reader-sans': fontFamily === 'sans' }"
                 :style="`font-size: ${fontSize}px; line-height: ${lineHeight};`">
            {!! nl2br(e($chapter->content)) !!}
        </article>

        <!-- Chapter Navigation Footer -->
        <div class="mt-16 pt-8 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between">
            <a href="{{ route('books.show', $book->slug) }}" class="text-xs text-slate-400 hover:text-slate-600">в†ђ Boblar ro'yxatiga qaytish</a>
            <button @click="saveProgress(100)" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs rounded-xl shadow-md transition-all">
                Bobni yakunlash вњ“
            </button>
        </div>
    </main>

    <script>
        function readerApp() {
            return {
                theme: localStorage.getItem('reader_theme') || 'dark',
                fontFamily: localStorage.getItem('reader_font') || 'serif',
                fontSize: parseInt(localStorage.getItem('reader_size')) || 18,
                lineHeight: '1.8',
                scrollPercent: 0,
                activeSeconds: 0,
                isIdle: false,
                idleTimer: null,
                heartbeatInterval: null,

                init() {
                    // Track scroll
                    window.addEventListener('scroll', () => {
                        const winScroll = document.documentElement.scrollTop;
                        const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
                        this.scrollPercent = Math.min(100, Math.round((winScroll / height) * 100)) || 0;
                        this.resetIdleTimer();
                    });

                    // Track activity (mouse, key, touch)
                    ['mousemove', 'keydown', 'touchstart', 'scroll'].forEach(evt => {
                        window.addEventListener(evt, () => this.resetIdleTimer(), { passive: true });
                    });

                    // Timer interval - counts active seconds
                    setInterval(() => {
                        if (!this.isIdle) {
                            this.activeSeconds++;
                        }
                    }, 1000);

                    // Heartbeat to server every 60 seconds of active reading
                    this.heartbeatInterval = setInterval(() => {
                        if (this.activeSeconds >= 30) {
                            this.sendHeartbeat();
                        }
                    }, 60000);

                    this.resetIdleTimer();
                },

                resetIdleTimer() {
                    this.isIdle = false;
                    clearTimeout(this.idleTimer);
                    // 60 soniyadan ortiq harakatsizlikda timer pauza bo'ladi
                    this.idleTimer = setTimeout(() => {
                        this.isIdle = true;
                    }, 60000);
                },

                formatTime(seconds) {
                    const m = Math.floor(seconds / 60).toString().padStart(2, '0');
                    const s = (seconds % 60).toString().padStart(2, '0');
                    return `${m}:${s}`;
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

                sendHeartbeat() {
                    const mins = this.activeSeconds / 60;
                    if (mins < 0.5) return;

                    fetch('/api/reading/heartbeat', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            book_id: {{ $book->id }},
                            chapter_id: {{ $chapter->id }},
                            minutes: mins
                        })
                    }).then(res => res.json()).then(data => {
                        if (data.success) {
                            this.activeSeconds = 0; // Reset active session counter after sync
                        }
                    }).catch(e => console.error('Heartbeat sync error', e));
                },

                saveProgress(percent) {
                    this.sendHeartbeat();
                    fetch('/api/reading/progress', {
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
                    });
                }
            }
        }
    </script>
</body>
</html>

