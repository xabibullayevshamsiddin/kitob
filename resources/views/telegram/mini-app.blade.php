<!DOCTYPE html>
<html lang="uz" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Davra_books — Telegram Mini App</title>

    <!-- Telegram WebApp JS SDK -->
    <script src="https://telegram.org/js/telegram-web-app.js"></script>

    @include('partials.design-system')

    <style>
        :root {
            --tg-theme-bg-color: #0F141F;
            --tg-theme-text-color: #F0EDE6;
            --tg-theme-hint-color: #94A3B8;
            --tg-theme-link-color: #24A1DE;
            --tg-theme-button-color: #24A1DE;
            --tg-theme-button-text-color: #FFFFFF;
            --tg-theme-secondary-bg-color: #171F2E;
        }

        body {
            background-color: var(--tg-theme-bg-color, #0F141F);
            color: var(--tg-theme-text-color, #F0EDE6);
            -webkit-tap-highlight-color: transparent;
            user-select: none;
        }

        /* iOS safe area */
        .safe-bottom {
            padding-bottom: max(1rem, env(safe-area-inset-bottom));
        }

        .hide-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .hide-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col font-sans ks-grain pb-20">

    <!-- Top App Bar / User Pill -->
    <header class="sticky top-0 z-30 bg-[#0F141F]/90 backdrop-blur-md border-b border-slate-800/80 px-4 py-3 flex items-center justify-between">
        <div class="flex items-center gap-2.5">
            <div id="user-avatar-wrap" class="w-9 h-9 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-400 font-bold overflow-hidden shadow-inner">
                <span id="user-avatar-text">{{ $user ? strtoupper(substr($user->name, 0, 1)) : 'K' }}</span>
                <img id="user-avatar-img" src="{{ $user?->avatar_url }}" class="w-full h-full object-cover {{ $user?->avatar ? '' : 'hidden' }}" alt="">
            </div>
            <div>
                <h2 id="user-name" class="text-xs font-bold text-white leading-tight">
                    {{ $user ? $user->name : 'Kitobxon' }}
                </h2>
                <div class="flex items-center gap-2 mt-0.5">
                    <span id="user-streak" class="text-[10px] text-amber-400 font-mono flex items-center gap-0.5">
                        🔥 {{ $user?->streak?->current_streak ?? 0 }} streak
                    </span>
                    <span class="text-[10px] text-slate-500">•</span>
                    <span id="user-coins" class="text-[10px] text-amber-300 font-mono flex items-center gap-0.5">
                        🪙 {{ number_format($user?->coin_balance ?? 0) }}
                    </span>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <button onclick="triggerHaptic(); openPlatformUrl('{{ route('shop.index') }}')" class="px-2.5 py-1.5 rounded-xl bg-slate-800/80 border border-slate-700 text-[11px] font-bold text-amber-400 flex items-center gap-1 active:scale-95 transition-transform">
                <span>🛍</span>
                <span>Do'kon</span>
            </button>
        </div>
    </header>

    <!-- Main Content Container -->
    <main class="flex-1 px-4 py-4 space-y-5">

        <!-- Loading / Guest Status Toast (Auto-login) -->
        <div id="auth-status-bar" class="hidden p-3 rounded-2xl bg-sky-500/10 border border-sky-500/20 text-sky-400 text-xs flex items-center gap-2">
            <svg class="w-4 h-4 animate-spin shrink-0" viewBox="0 0 24 24" fill="none">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
            </svg>
            <span id="auth-status-text">Telegram orqali ulanmoqda...</span>
        </div>

        <!-- Weekly Featured Book Hero Card -->
        @if($featuredBook)
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-indigo-950/70 via-slate-900 to-slate-950 border border-indigo-500/20 p-5 shadow-lg">
            <div class="absolute -right-8 -top-8 w-32 h-32 rounded-full bg-indigo-500/10 blur-2xl pointer-events-none"></div>

            <div class="flex items-start gap-4">
                <!-- Book Cover -->
                <div class="w-24 h-32 shrink-0 rounded-2xl bg-slate-800 border border-slate-700/60 overflow-hidden shadow-md relative">
                    @if($featuredBook->cover_image)
                        <img src="{{ asset('storage/' . $featuredBook->cover_image) }}" alt="{{ $featuredBook->title }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex flex-col items-center justify-center p-2 text-center bg-slate-800 text-slate-400">
                            <span class="text-2xl mb-1">📖</span>
                            <span class="text-[9px] line-clamp-2 leading-tight font-serif">{{ $featuredBook->title }}</span>
                        </div>
                    @endif
                    <span class="absolute top-1.5 left-1.5 px-1.5 py-0.5 rounded-md bg-amber-500/90 text-[8px] font-black text-slate-950 uppercase tracking-wider">
                        Hafta
                    </span>
                </div>

                <!-- Book Meta -->
                <div class="flex-1 space-y-1.5">
                    <span class="text-[10px] font-mono text-indigo-400 uppercase tracking-wider">{{ $featuredBook->genre ?? 'Mutolaa' }}</span>
                    <h3 class="text-sm font-bold text-white leading-snug line-clamp-2 font-serif">{{ $featuredBook->title }}</h3>
                    <p class="text-xs text-slate-400 font-medium">{{ $featuredBook->author }}</p>
                    <p class="text-[11px] text-slate-400 line-clamp-2 leading-relaxed pt-1">
                        {{ $featuredBook->description }}
                    </p>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="mt-4 pt-3 border-t border-slate-800/80 flex items-center gap-2">
                <button onclick="triggerHaptic(); openPlatformUrl('{{ route('books.show', $featuredBook->slug) }}')"
                   class="flex-1 py-2.5 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-500 active:scale-98 text-white font-bold text-xs flex items-center justify-center gap-2 shadow-md shadow-indigo-600/30 transition-all">
                    <span>📖 Mutolaa qilish</span>
                </button>
                <button onclick="triggerHaptic(); openPlatformUrl('{{ route('books.show', $featuredBook->slug) }}')"
                   class="py-2.5 px-3 rounded-xl bg-slate-800/90 hover:bg-slate-700 active:scale-98 text-slate-300 font-bold text-xs border border-slate-700/80 transition-all">
                    <span>Tafsilot</span>
                </button>
            </div>
        </div>
        @endif

        <!-- Quick Stats Bento Grid -->
        <div class="grid grid-cols-2 gap-3">
            <!-- Streak Card -->
            <div onclick="triggerHaptic(); openPlatformUrl('{{ auth()->check() ? route('profile.show', auth()->user()->username) : route('settings') }}')"
                 class="p-4 rounded-2xl bg-slate-900/80 border border-slate-800/80 active:scale-98 transition-transform cursor-pointer">
                <div class="flex items-center justify-between mb-2">
                    <span class="w-8 h-8 rounded-xl bg-orange-500/10 text-orange-400 flex items-center justify-center text-sm">🔥</span>
                    <span class="text-[10px] text-slate-500 font-mono">Bugun</span>
                </div>
                <div class="text-lg font-black text-white font-mono" id="bento-streak">
                    {{ $user?->streak?->current_streak ?? 0 }} kun
                </div>
                <p class="text-[11px] text-slate-400 mt-0.5">Ketma-ket mutolaa</p>
            </div>

            <!-- Coins & Rewards Card -->
            <div onclick="triggerHaptic(); openPlatformUrl('{{ route('shop.index') }}')"
                 class="p-4 rounded-2xl bg-slate-900/80 border border-slate-800/80 active:scale-98 transition-transform cursor-pointer">
                <div class="flex items-center justify-between mb-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center text-sm">🪙</span>
                    <span class="text-[10px] text-amber-500 font-mono">{{ $shopItemsCount }} ta sovg'a</span>
                </div>
                <div class="text-lg font-black text-amber-300 font-mono" id="bento-coins">
                    {{ number_format($user?->coin_balance ?? 0) }}
                </div>
                <p class="text-[11px] text-slate-400 mt-0.5">Oltin tangalar</p>
            </div>
        </div>

        <!-- Daily Quote Card -->
        @if($dailyQuote)
        <div class="p-4 rounded-2xl bg-amber-500/5 border border-amber-500/15 relative">
            <span class="text-amber-500 font-serif text-2xl absolute top-2 right-3 opacity-30">“</span>
            <span class="text-[10px] font-mono uppercase tracking-wider text-amber-400 block mb-1">✨ Kun hikmati</span>
            <p class="text-xs text-slate-200 font-serif italic leading-relaxed">
                «{{ $dailyQuote->quote_text }}»
            </p>
            <div class="mt-2 text-right">
                <span class="text-[10px] font-mono text-slate-400">— {{ $dailyQuote->book?->author ?? 'Kitobxon' }}</span>
            </div>
        </div>
        @endif

        <!-- Recent Books Catalog Section -->
        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <h3 class="text-xs font-bold text-white uppercase tracking-wider">Sara kitoblar</h3>
                <button onclick="triggerHaptic(); openPlatformUrl('{{ route('books.catalog') }}')" class="text-xs text-[#24A1DE] font-semibold">
                    Barchasi →
                </button>
            </div>

            <div class="grid grid-cols-3 gap-2.5">
                @foreach($recentBooks as $book)
                <div onclick="triggerHaptic(); openPlatformUrl('{{ route('books.show', $book->slug) }}')"
                     class="flex flex-col group cursor-pointer active:scale-95 transition-transform">
                    <div class="aspect-[3/4] w-full rounded-xl bg-slate-800 border border-slate-700/60 overflow-hidden shadow-sm relative mb-1.5">
                        @if($book->cover_image)
                            <img src="{{ asset('storage/' . $book->cover_image) }}" alt="{{ $book->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform">
                        @else
                            <div class="w-full h-full flex items-center justify-center bg-slate-800 text-slate-500">
                                📖
                            </div>
                        @endif
                    </div>
                    <h4 class="text-[11px] font-bold text-white truncate font-serif">{{ $book->title }}</h4>
                    <p class="text-[10px] text-slate-400 truncate">{{ $book->author }}</p>
                </div>
                @endforeach
            </div>
        </div>

    </main>

    <!-- Bottom Navigation Bar (App-native look) -->
    <nav class="fixed bottom-0 inset-x-0 z-40 bg-[#0F141F]/95 backdrop-blur-md border-t border-slate-800 px-3 py-2 flex items-center justify-around safe-bottom">
        <button onclick="triggerHaptic(); openPlatformUrl('{{ route('home') }}')" class="flex flex-col items-center gap-1 text-[#24A1DE] px-3 py-1">
            <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>
            <span class="text-[10px] font-bold">Asosiy</span>
        </button>

        <button onclick="triggerHaptic(); openPlatformUrl('{{ route('books.catalog') }}')" class="flex flex-col items-center gap-1 text-slate-400 hover:text-white px-3 py-1">
            <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M18 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zM6 4h5v8l-2.5-1.5L6 12V4z"/></svg>
            <span class="text-[10px] font-medium">Kitoblar</span>
        </button>

        <button onclick="triggerHaptic(); openPlatformUrl('{{ route('shop.index') }}')" class="flex flex-col items-center gap-1 text-slate-400 hover:text-white px-3 py-1">
            <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M19 6h-2c0-2.76-2.24-5-5-5S7 3.24 7 6H5c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2zm-7-3c1.66 0 3 1.34 3 3H9c0-1.66 1.34-3 3-3zm7 17H5V8h14v12z"/></svg>
            <span class="text-[10px] font-medium">Do'kon</span>
        </button>

        <button onclick="triggerHaptic(); openPlatformUrl('{{ auth()->check() ? route('profile.show', auth()->user()->username) : route('settings') }}')" class="flex flex-col items-center gap-1 text-slate-400 hover:text-white px-3 py-1">
            <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
            <span class="text-[10px] font-medium">Profil</span>
        </button>
    </nav>

    <!-- Telegram WebApp Core Logic -->
    <script>
        const tg = window.Telegram?.WebApp;

        // 1. Telegram WebApp tayyorgarligi va kengaytirish
        if (tg) {
            tg.ready();
            tg.expand();

            // Telegram ranglarini qo'llash
            if (tg.themeParams) {
                if (tg.themeParams.bg_color) {
                    document.documentElement.style.setProperty('--tg-theme-bg-color', tg.themeParams.bg_color);
                }
                if (tg.themeParams.text_color) {
                    document.documentElement.style.setProperty('--tg-theme-text-color', tg.themeParams.text_color);
                }
            }
        }

        // 2. Tebranish (Haptic feedback)
        function triggerHaptic(type = 'light') {
            try {
                if (tg?.HapticFeedback) {
                    tg.HapticFeedback.impactOccurred(type);
                }
            } catch(e) {}
        }

        // 3. Sahifaga o'tish (Telegram ichida yoki tashqarisida)
        function openPlatformUrl(url) {
            window.location.href = url;
        }

        // 4. initData orqali avtomatik tizimga kiritish (Seamless Login)
        (async function checkTelegramAuth() {
            const isGuest = {{ auth()->check() ? 'false' : 'true' }};

            if (isGuest && tg && tg.initData && tg.initData.length > 0) {
                const statusBar = document.getElementById('auth-status-bar');
                const statusText = document.getElementById('auth-status-text');

                if (statusBar) statusBar.classList.remove('hidden');

                try {
                    const response = await fetch('{{ route("tg-app.auth") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            init_data: tg.initData
                        })
                    });

                    const data = await response.json();

                    if (data.success && data.user) {
                        triggerHaptic('medium');
                        if (statusText) statusText.textContent = "Ulandi! Xush kelibsiz, " + data.user.name;

                        // UI yangilash
                        document.getElementById('user-name').textContent = data.user.name;
                        document.getElementById('user-coins').textContent = "🪙 " + data.user.coin_balance.toLocaleString();
                        document.getElementById('user-streak').textContent = "🔥 " + data.user.streak + " streak";
                        document.getElementById('bento-streak').textContent = data.user.streak + " kun";
                        document.getElementById('bento-coins').textContent = data.user.coin_balance.toLocaleString();

                        if (data.user.avatar_url) {
                            const img = document.getElementById('user-avatar-img');
                            img.src = data.user.avatar_url;
                            img.classList.remove('hidden');
                            document.getElementById('user-avatar-text').classList.add('hidden');
                        }

                        setTimeout(() => {
                            if (statusBar) statusBar.classList.add('hidden');
                        }, 1800);
                    } else {
                        if (statusBar) statusBar.classList.add('hidden');
                    }
                } catch(e) {
                    if (statusBar) statusBar.classList.add('hidden');
                }
            }
        })();
    </script>
</body>
</html>
