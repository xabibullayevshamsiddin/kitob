<div class="space-y-8 max-w-7xl mx-auto pb-12">

    <!-- Live Event Alert Banner (agar jonli efir bo'lsa) -->
    @if($upcomingLive)
        <div class="p-4 rounded-2xl bg-gradient-to-r from-rose-500/10 via-amber-500/10 to-indigo-500/10 border border-rose-500/20 backdrop-blur-sm flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <span class="relative flex h-3 w-3">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-rose-500"></span>
                </span>
                <div>
                    <h4 class="text-xs font-bold text-rose-500 uppercase tracking-wider">
                        {{ $upcomingLive->status === 'live' ? 'Hozir efirda!' : 'Navbatdagi jonli efir' }}
                    </h4>
                    <p class="text-sm font-semibold text-slate-800 dark:text-white">{{ $upcomingLive->title }}</p>
                </div>
            </div>
            <a href="{{ route('live.show', $upcomingLive->id) }}" class="px-4 py-2 bg-rose-600 hover:bg-rose-500 text-white font-semibold text-xs rounded-xl shadow-md shadow-rose-600/25 transition-all whitespace-nowrap">
                {{ $upcomingLive->status === 'live' ? 'Efirga qo\'shilish' : 'Tafsilotlar' }}
            </a>
        </div>
    @endif

    <!-- Hero Section: Featured Book of the Week -->
    @if ($featuredBook)
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-indigo-900 via-indigo-950 to-slate-900 border border-indigo-800/40 p-6 sm:p-10 shadow-2xl text-white">
            <!-- Background Glow -->
            <div class="absolute -right-20 -top-20 w-96 h-96 bg-indigo-500/20 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -left-20 -bottom-20 w-80 h-80 bg-amber-500/15 rounded-full blur-3xl pointer-events-none"></div>

            <div class="relative z-10 grid grid-cols-1 md:grid-cols-12 gap-8 items-center">
                <!-- Book Cover Image -->
                <div class="md:col-span-4 lg:col-span-3 flex justify-center">
                    <div class="relative group">
                        <div class="w-48 h-68 sm:w-52 sm:h-72 rounded-2xl overflow-hidden shadow-2xl ring-4 ring-white/10 transform group-hover:scale-105 transition-all duration-300">
                            <img src="{{ $featuredBook->cover_url }}" alt="{{ $featuredBook->title }}" class="w-full h-full object-cover">
                        </div>
                        <div class="absolute top-3 left-3 px-3 py-1 bg-amber-500 text-slate-900 font-extrabold text-[11px] rounded-lg shadow-md uppercase tracking-wider">
                            {{ $featuredBook->week_number }}-Hafta
                        </div>
                    </div>
                </div>

                <!-- Book Information -->
                <div class="md:col-span-8 lg:col-span-9 space-y-4 text-center md:text-left">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-500/20 text-indigo-300 text-xs font-semibold">
                        <span>📖 {{ $featuredBook->genre }}</span>
                    </div>

                    <h1 class="text-2xl sm:text-4xl font-black font-manrope tracking-tight leading-tight text-white">
                        {{ $featuredBook->title }}
                    </h1>

                    <p class="text-sm sm:text-base text-indigo-200/90 font-medium">
                        Muallif: <strong class="text-white">{{ $featuredBook->author }}</strong>
                    </p>

                    <p class="text-xs sm:text-sm text-slate-300 line-clamp-3 leading-relaxed max-w-2xl">
                        {{ $featuredBook->description }}
                    </p>

                    <!-- User Reading Progress Bar if started -->
                    @if($userProgress)
                        <div class="max-w-md pt-2">
                            <div class="flex justify-between text-xs text-indigo-200 mb-1.5 font-medium">
                                <span>O'qish progressi</span>
                                <span class="font-bold text-amber-400">{{ number_format($userProgress->percent_complete) }}%</span>
                            </div>
                            <div class="w-full bg-white/10 rounded-full h-2.5 overflow-hidden">
                                <div class="bg-gradient-to-r from-amber-400 to-amber-500 h-2.5 rounded-full transition-all duration-500" style="width: {{ $userProgress->percent_complete }}%"></div>
                            </div>
                        </div>
                    @endif

                    <!-- Action Buttons -->
                    <div class="pt-4 flex flex-wrap items-center justify-center md:justify-start gap-3">
                        <a href="{{ route('books.show', $featuredBook->slug) }}" 
                           class="px-6 py-3 bg-gradient-to-r from-amber-500 to-amber-400 hover:from-amber-400 hover:to-amber-300 text-slate-900 font-bold text-sm rounded-xl shadow-lg shadow-amber-500/25 transition-all transform active:scale-95 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                            <span>{{ $userProgress ? 'O\'qishni davom ettirish' : 'O\'qishni boshlash' }}</span>
                        </a>

                        <a href="{{ route('audio.show', $featuredBook->id) }}" 
                           class="px-5 py-3 bg-white/10 hover:bg-white/20 text-white font-semibold text-sm rounded-xl backdrop-blur-md border border-white/10 transition-all flex items-center gap-2">
                            <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span>Audio eshitish</span>
                        </a>

                        <a href="{{ route('videos.index', $featuredBook->id) }}" 
                           class="px-5 py-3 bg-white/10 hover:bg-white/20 text-white font-semibold text-sm rounded-xl backdrop-blur-md border border-white/10 transition-all flex items-center gap-2">
                            <svg class="w-4 h-4 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                            <span>Video dars</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- 2 Column Grid: Left Main Stats & Quotes, Right Leaderboard -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left 2 Cols -->
        <div class="lg:col-span-2 space-y-8">

            <!-- Quick Gamification Stats -->
            @auth
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-soft">
                        <div class="flex items-center gap-2 text-amber-500 mb-2 font-bold text-xs uppercase">
                            <span class="text-lg animate-flame">🔥</span>
                            <span>Ketma-ketlik</span>
                        </div>
                        <p class="text-2xl font-black text-slate-900 dark:text-white">{{ $user->current_streak }} <span class="text-xs text-slate-400 font-medium">kun</span></p>
                    </div>

                    <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-soft">
                        <div class="flex items-center gap-2 text-indigo-500 mb-2 font-bold text-xs uppercase">
                            <span class="text-lg">⭐️</span>
                            <span>Ballar</span>
                        </div>
                        <p class="text-2xl font-black text-slate-900 dark:text-white">{{ number_format($user->total_points) }}</p>
                    </div>

                    <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-soft">
                        <div class="flex items-center gap-2 text-amber-400 mb-2 font-bold text-xs uppercase">
                            <span class="text-lg">🪙</span>
                            <span>Tangalar</span>
                        </div>
                        <p class="text-2xl font-black text-slate-900 dark:text-white">{{ number_format($user->coin_balance) }}</p>
                    </div>

                    <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-soft">
                        <div class="flex items-center gap-2 text-emerald-500 mb-2 font-bold text-xs uppercase">
                            <span class="text-lg">⏱️</span>
                            <span>O'qish vaqti</span>
                        </div>
                        <p class="text-2xl font-black text-slate-900 dark:text-white">{{ $user->total_reading_minutes }} <span class="text-xs text-slate-400 font-medium">daq</span></p>
                    </div>
                </div>
            @endauth

            <!-- Daily Quote Card -->
            @if ($todayQuote)
                <div class="p-6 sm:p-8 rounded-3xl bg-gradient-to-br from-amber-500/10 via-amber-500/5 to-transparent border border-amber-500/20 relative overflow-hidden">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-xs font-bold text-amber-600 dark:text-amber-400 uppercase tracking-widest flex items-center gap-1.5">
                            <span>✨</span> Kunlik hikmatli so'z
                        </span>
                        @if($todayQuote->book)
                            <span class="text-xs text-slate-500 dark:text-slate-400">
                                «{{ $todayQuote->book->title }}» kitobidan
                            </span>
                        @endif
                    </div>

                    <blockquote class="text-base sm:text-lg font-serif italic text-slate-800 dark:text-slate-200 leading-relaxed mb-6">
                        “{{ $todayQuote->quote_text }}”
                    </blockquote>

                    <div class="flex items-center justify-between border-t border-amber-500/20 pt-4">
                        <span class="text-xs text-slate-400">Bugungi tavsiya</span>
                        <div class="flex items-center gap-2">
                            <button wire:click="toggleQuoteLike({{ $todayQuote->id }})" class="p-2 rounded-xl text-slate-500 hover:text-rose-500 transition-colors {{ in_array($todayQuote->id, $likedQuotes) ? 'text-rose-500' : '' }}">
                                <svg class="w-5 h-5 {{ in_array($todayQuote->id, $likedQuotes) ? 'fill-current' : 'fill-none' }}" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                            </button>
                            <button wire:click="toggleQuoteSave({{ $todayQuote->id }})" class="p-2 rounded-xl text-slate-500 hover:text-amber-500 transition-colors {{ in_array($todayQuote->id, $savedQuotes) ? 'text-amber-500' : '' }}">
                                <svg class="w-5 h-5 {{ in_array($todayQuote->id, $savedQuotes) ? 'fill-current' : 'fill-none' }}" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path></svg>
                            </button>
                        </div>
                    </div>
                </div>
            @endif

        </div>

        <!-- Right 1 Col: Leaderboard Preview -->
        <div class="space-y-6">
            <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-soft">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <span>🏆</span> Peshqadamlar
                    </h3>
                    <a href="{{ route('leaderboard') }}" class="text-xs text-indigo-600 dark:text-indigo-400 font-semibold hover:underline">Barchasi →</a>
                </div>

                <div class="space-y-3">
                    @forelse ($topUsers as $index => $topUser)
                        @php
                            $medals = [0 => '🥇', 1 => '🥈', 2 => '🥉'];
                        @endphp
                        <div class="flex items-center justify-between p-3 rounded-2xl {{ $index < 3 ? 'bg-slate-50 dark:bg-slate-800/50' : '' }}">
                            <div class="flex items-center gap-3 min-w-0">
                                <span class="text-sm font-black w-6 text-center">
                                    {{ $medals[$index] ?? ($index + 1) }}
                                </span>
                                <img src="{{ $topUser->avatar_url }}" class="w-9 h-9 rounded-full object-cover shrink-0 ring-1 ring-slate-200 dark:ring-slate-700">
                                <div class="min-w-0">
                                    <p class="text-xs font-bold text-slate-800 dark:text-white truncate">{{ $topUser->name }}</p>
                                    <p class="text-[11px] text-slate-400 truncate">@{{ $topUser->username }}</p>
                                </div>
                            </div>
                            <div class="text-right shrink-0">
                                <span class="text-xs font-black text-indigo-600 dark:text-indigo-400">{{ number_format($topUser->total_points) }}</span>
                                <span class="text-[10px] text-slate-400 block">ball</span>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 text-center py-4">Hali reyting shakllanmadi.</p>
                    @endforelse
                </div>
            </div>
        </div>

    </div>

</div>
