<div class="space-y-8 max-w-7xl mx-auto pb-16">

    <!-- ── Live Event Alert Banner (if active) ── -->
    @if($upcomingLive)
        <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/25 flex flex-col sm:flex-row items-center justify-between gap-4 transition-all">
            <div class="flex items-center gap-3.5">
                <span class="relative flex h-3 w-3 shrink-0">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-rose-500"></span>
                </span>
                <div>
                    <span class="text-[11px] font-mono font-bold uppercase tracking-wider text-rose-400 block">
                        {{ $upcomingLive->status === 'live' ? 'Hozir jonli efirda' : 'Navbatdagi jonli efir' }}
                    </span>
                    <p class="text-sm font-semibold text-slate-100">{{ $upcomingLive->title }}</p>
                </div>
            </div>
            <a href="{{ route('live.show', $upcomingLive->id) }}" 
               class="px-4 py-2 bg-rose-600 hover:bg-rose-500 text-white font-semibold text-xs rounded-xl transition-all active:scale-[0.98] whitespace-nowrap shadow-md shadow-rose-600/20">
                {{ $upcomingLive->status === 'live' ? 'Efirga qo\'shilish →' : 'Tafsilotlar' }}
            </a>
        </div>
    @endif

    <!-- ── Hero Section: Haftalik Kitob (Restrained & High-contrast) ── -->
    @if ($featuredBook)
        <div class="relative overflow-hidden rounded-3xl bg-slate-900 border border-white/10 p-6 sm:p-10 shadow-xl">
            <!-- Subtle warm background glow -->
            <div class="absolute -right-24 -top-24 w-96 h-96 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>

            <div class="relative z-10 grid grid-cols-1 md:grid-cols-12 gap-8 items-center">
                
                <!-- Book Cover / Visual -->
                <div class="md:col-span-4 lg:col-span-3 flex justify-center">
                    <div class="relative group">
                        <div class="w-48 h-68 sm:w-52 sm:h-72 rounded-2xl overflow-hidden shadow-2xl ring-1 ring-white/15 transition-transform duration-300 group-hover:scale-[1.02]">
                            @if($featuredBook->cover_url)
                                <img src="{{ $featuredBook->cover_url }}" alt="{{ $featuredBook->title }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full bg-gradient-to-br from-indigo-950 via-slate-900 to-amber-950/40 p-6 flex flex-col justify-between text-white">
                                    <span class="text-xs font-mono text-amber-400">Kitobxon Haftasi</span>
                                    <div>
                                        <p class="text-xl font-bold leading-tight">{{ $featuredBook->title }}</p>
                                        <p class="text-xs text-slate-400 mt-1">{{ $featuredBook->author }}</p>
                                    </div>
                                    <span class="text-3xl">📖</span>
                                </div>
                            @endif
                        </div>
                        <div class="absolute top-3 left-3 px-2.5 py-1 bg-amber-500 text-slate-950 font-black text-[10px] rounded-lg shadow uppercase tracking-wider">
                            {{ $featuredBook->week_number }}-Hafta
                        </div>
                    </div>
                </div>

                <!-- Book Information -->
                <div class="md:col-span-8 lg:col-span-9 space-y-4 text-center md:text-left">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/5 border border-white/10 text-amber-400 text-xs font-mono">
                        <span>{{ $featuredBook->genre }}</span>
                    </div>

                    <h1 class="text-2xl sm:text-4xl font-extrabold tracking-tight text-white leading-tight">
                        {{ $featuredBook->title }}
                    </h1>

                    <p class="text-sm text-slate-300">
                        Muallif: <strong class="text-white font-semibold">{{ $featuredBook->author }}</strong>
                    </p>

                    <p class="text-xs sm:text-sm text-slate-400 line-clamp-3 leading-relaxed max-w-2xl">
                        {{ $featuredBook->description }}
                    </p>

                    <!-- Reading Progress Bar -->
                    @if($userProgress)
                        <div class="max-w-md pt-2">
                            <div class="flex justify-between text-xs text-slate-400 mb-1.5 font-medium">
                                <span>Mutolaa progressi</span>
                                <span class="font-bold text-amber-400 font-mono">{{ number_format($userProgress->percent_complete) }}%</span>
                            </div>
                            <div class="w-full bg-white/10 rounded-full h-2 overflow-hidden">
                                <div class="bg-amber-400 h-2 rounded-full transition-all duration-500" style="width: {{ $userProgress->percent_complete }}%"></div>
                            </div>
                        </div>
                    @endif

                    <!-- Action Buttons -->
                    <div class="pt-3 flex flex-wrap items-center justify-center md:justify-start gap-3">
                        <a href="{{ route('books.show', $featuredBook->slug) }}" 
                           class="px-6 py-3 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs uppercase tracking-wider rounded-xl transition-all active:scale-[0.98] shadow-lg shadow-amber-500/20 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                            <span>{{ $userProgress ? 'Davom ettirish' : 'O\'qishni boshlash' }}</span>
                        </a>

                        <a href="{{ route('audio.show', $featuredBook->id) }}" 
                           class="px-5 py-3 bg-white/5 hover:bg-white/10 text-white font-semibold text-xs uppercase tracking-wider rounded-xl border border-white/10 hover:border-white/20 transition-all active:scale-[0.98] flex items-center gap-2">
                            <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span>Audio tinglash</span>
                        </a>

                        <a href="{{ route('videos.index', $featuredBook->id) }}" 
                           class="px-5 py-3 bg-white/5 hover:bg-white/10 text-white font-semibold text-xs uppercase tracking-wider rounded-xl border border-white/10 hover:border-white/20 transition-all active:scale-[0.98] flex items-center gap-2">
                            <svg class="w-4 h-4 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                            <span>Video dars</span>
                        </a>
                    </div>
                </div>

            </div>
        </div>
    @endif

    <!-- ── 2 Column Grid: Left Stats & Quotes, Right Leaderboard ── -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left 2 Cols -->
        <div class="lg:col-span-2 space-y-8">

            <!-- Gamification Metrics (Clean tactile tiles) -->
            @auth
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    
                    <div class="p-5 rounded-2xl bg-slate-900 border border-white/10 hover:border-amber-400/30 transition-all">
                        <div class="flex items-center gap-2 text-amber-500 mb-2 font-mono text-xs font-bold uppercase">
                            <span>🔥</span>
                            <span>Streak</span>
                        </div>
                        <p class="text-2xl font-black text-white font-mono">{{ $user->current_streak }} <span class="text-xs text-slate-500 font-sans">kun</span></p>
                    </div>

                    <div class="p-5 rounded-2xl bg-slate-900 border border-white/10 hover:border-indigo-400/30 transition-all">
                        <div class="flex items-center gap-2 text-indigo-400 mb-2 font-mono text-xs font-bold uppercase">
                            <span>⭐️</span>
                            <span>Ballar</span>
                        </div>
                        <p class="text-2xl font-black text-white font-mono">{{ number_format($user->total_points) }}</p>
                    </div>

                    <div class="p-5 rounded-2xl bg-slate-900 border border-white/10 hover:border-amber-400/30 transition-all">
                        <div class="flex items-center gap-2 text-amber-400 mb-2 font-mono text-xs font-bold uppercase">
                            <span>🪙</span>
                            <span>Tangalar</span>
                        </div>
                        <p class="text-2xl font-black text-white font-mono">{{ number_format($user->coin_balance) }}</p>
                    </div>

                    <div class="p-5 rounded-2xl bg-slate-900 border border-white/10 hover:border-emerald-400/30 transition-all">
                        <div class="flex items-center gap-2 text-emerald-400 mb-2 font-mono text-xs font-bold uppercase">
                            <span>⏱️</span>
                            <span>Mutolaa</span>
                        </div>
                        <p class="text-2xl font-black text-white font-mono">{{ $user->total_reading_minutes }} <span class="text-xs text-slate-500 font-sans">daq</span></p>
                    </div>

                </div>
            @endauth

            <!-- Daily Quote Card (Editorial Style) -->
            @if ($todayQuote)
                <div class="p-6 sm:p-8 rounded-3xl bg-slate-900 border border-white/10 relative overflow-hidden">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-xs font-mono font-semibold text-amber-400 uppercase tracking-widest flex items-center gap-2">
                            <span>✨</span> Kunlik tavsiya
                        </span>
                        @if($todayQuote->book)
                            <span class="text-xs text-slate-400">
                                «{{ $todayQuote->book->title }}» kitobidan
                            </span>
                        @endif
                    </div>

                    <blockquote class="text-base sm:text-lg font-serif italic text-slate-200 leading-relaxed mb-6">
                        “{{ $todayQuote->quote_text }}”
                    </blockquote>

                    <div class="flex items-center justify-between border-t border-white/10 pt-4">
                        <span class="text-xs text-slate-500">Mutolaa hikmati</span>
                        <div class="flex items-center gap-2">
                            <button wire:click="toggleQuoteLike({{ $todayQuote->id }})" 
                                    class="p-2 rounded-xl text-slate-400 hover:text-rose-500 transition-colors {{ in_array($todayQuote->id, $likedQuotes) ? 'text-rose-500' : '' }}">
                                <svg class="w-5 h-5 {{ in_array($todayQuote->id, $likedQuotes) ? 'fill-current' : 'fill-none' }}" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                            </button>
                            <button wire:click="toggleQuoteSave({{ $todayQuote->id }})" 
                                    class="p-2 rounded-xl text-slate-400 hover:text-amber-400 transition-colors {{ in_array($todayQuote->id, $savedQuotes) ? 'text-amber-400' : '' }}">
                                <svg class="w-5 h-5 {{ in_array($todayQuote->id, $savedQuotes) ? 'fill-current' : 'fill-none' }}" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path></svg>
                            </button>
                        </div>
                    </div>
                </div>
            @endif

        </div>

        <!-- Right 1 Col: Leaderboard (Clean high-contrast ranks) -->
        <div class="space-y-6">
            <div class="p-6 rounded-3xl bg-slate-900 border border-white/10 shadow-sm">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-base font-bold text-white flex items-center gap-2">
                        <span>🏆</span> Peshqadamlar
                    </h3>
                    <a href="{{ route('leaderboard') }}" class="text-xs text-amber-400 font-semibold hover:underline">Barchasi →</a>
                </div>

                <div class="space-y-3">
                    @forelse ($topUsers as $index => $topUser)
                        <div class="flex items-center justify-between p-3 rounded-2xl bg-white/[0.03] hover:bg-white/[0.06] transition-colors">
                            <div class="flex items-center gap-3 min-w-0">
                                <span class="font-mono text-xs font-black w-5 text-center text-slate-400">
                                    #{{ $index + 1 }}
                                </span>
                                <img src="{{ $topUser->avatar_url }}" class="w-8 h-8 rounded-full object-cover shrink-0 ring-1 ring-white/10">
                                <div class="min-w-0">
                                    <p class="text-xs font-bold text-slate-200 truncate">{{ $topUser->name }}</p>
                                    <p class="text-[10px] text-slate-500 font-mono truncate">@{{ $topUser->username }}</p>
                                </div>
                            </div>
                            <div class="text-right shrink-0">
                                <span class="text-xs font-bold font-mono text-amber-400">{{ number_format($topUser->total_points) }}</span>
                                <span class="text-[10px] text-slate-500 block">ball</span>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-slate-500 text-center py-4">Hali reyting shakllanmadi.</p>
                    @endforelse
                </div>
            </div>
        </div>

    </div>

</div>
