<div class="space-y-8 max-w-7xl mx-auto pb-16">

    <!-- ── Live Event Alert Banner (if active) ── -->
    @if($upcomingLive)
        <div class="p-4 rounded-card bg-ink-900 border border-vermilion/30 flex flex-col sm:flex-row items-center justify-between gap-4 transition-colors">
            <div class="flex items-center gap-3">
                <span class="relative flex h-2.5 w-2.5 shrink-0">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-vermilion opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-vermilion"></span>
                </span>
                <div>
                    <span class="text-[11px] font-mono font-bold uppercase tracking-wider text-rose-300 block">
                        {{ $upcomingLive->status === 'live' ? 'Hozir jonli efirda' : 'Navbatdagi jonli efir' }}
                    </span>
                    <p class="text-sm font-serif font-bold text-paper">{{ $upcomingLive->title }}</p>
                </div>
            </div>
            <a href="{{ route('live.show', $upcomingLive->id) }}" 
               class="ks-btn-primary py-1.5 px-4 text-xs whitespace-nowrap">
                {{ $upcomingLive->status === 'live' ? 'Efirga qo\'shilish →' : 'Tafsilotlar' }}
            </a>
        </div>
    @endif

    <!-- ── Top Greeting & Streak Bento Row ── -->
    <div class="grid grid-cols-1 md:grid-cols-12 gap-5 items-stretch">
        
        <!-- Welcome & Streak Banner (Col 8) -->
        <div class="md:col-span-8 ks-panel p-6 sm:p-8 bg-ink-900 border border-ink-border flex flex-col justify-between">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <span class="ks-eyebrow">Xush kelibsiz</span>
                    @if(auth()->check())
                        <span class="text-xs font-mono text-mist">· {{ now()->format('d-M, Y') }}</span>
                    @endif
                </div>
                <h1 class="text-2xl sm:text-3xl font-bold font-serif text-paper">
                    Mutolaaga xush kelibsiz, {{ auth()->user()->name ?? 'Kitobxon' }}!
                </h1>
                <p class="text-xs sm:text-sm text-mist mt-1 max-w-xl font-sans">
                    Har kungi kichik qadam katta intellektual natijalarga olib boradi. Bugungi kitobingiz sizni kutmoqda.
                </p>
            </div>

            @auth
                <!-- Tactile Metrics Strip -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-6 pt-6 border-t border-ink-border">
                    <div class="p-3 rounded-card bg-ink-950 border border-ink-border">
                        <div class="flex items-center gap-1.5 text-amber-500 mb-1 font-mono text-[11px] font-bold uppercase">
                            <svg class="w-4 h-4 ks-flame is-lit" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2c-.5 2.5-2.5 4.5-4 6-2 2-3 4.5-3 7 0 4.4 3.6 8 8 8s8-3.6 8-8c0-3.5-2-6-4-8-.5 2-2 3.5-3 4-1-2.5 0-6.5-2-9z"/></svg>
                            <span>Streak</span>
                        </div>
                        <p class="text-xl font-bold text-amber-400 font-mono">{{ $user->current_streak }} <span class="text-[10px] text-mist font-sans font-normal">kun</span></p>
                    </div>

                    <div class="p-3 rounded-card bg-ink-950 border border-ink-border">
                        <div class="flex items-center gap-1.5 text-mist mb-1 font-mono text-[11px] font-bold uppercase">
                            <span class="text-amber-400">✦</span>
                            <span>Ballar</span>
                        </div>
                        <p class="text-xl font-bold text-paper font-mono">{{ number_format($user->total_points) }}</p>
                    </div>

                    <div class="p-3 rounded-card bg-ink-950 border border-ink-border">
                        <div class="flex items-center gap-1.5 text-mist mb-1 font-mono text-[11px] font-bold uppercase">
                            <span class="text-amber-500">🪙</span>
                            <span>Tangalar</span>
                        </div>
                        <p class="text-xl font-bold text-paper font-mono">{{ number_format($user->coin_balance) }}</p>
                    </div>

                    <div class="p-3 rounded-card bg-ink-950 border border-ink-border">
                        <div class="flex items-center gap-1.5 text-mist mb-1 font-mono text-[11px] font-bold uppercase">
                            <svg class="w-3.5 h-3.5 text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            <span>Mutolaa</span>
                        </div>
                        <p class="text-xl font-bold text-paper font-mono">{{ $user->total_reading_minutes }} <span class="text-[10px] text-mist font-sans font-normal">daq</span></p>
                    </div>
                </div>
            @endauth
        </div>

        <!-- Daily Quote Card (Col 4) -->
        <div class="md:col-span-4 ks-panel p-6 bg-ink-900 border border-ink-border flex flex-col justify-between">
            @if ($todayQuote)
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="ks-eyebrow">Kunlik hikmat</span>
                        @if($todayQuote->book)
                            <span class="text-[11px] text-mist font-mono truncate max-w-[150px]">
                                «{{ $todayQuote->book->title }}»
                            </span>
                        @endif
                    </div>

                    <blockquote class="text-sm font-serif italic text-paper leading-relaxed mb-4">
                        “{{ $todayQuote->quote_text }}”
                    </blockquote>
                </div>

                <div class="flex items-center justify-between border-t border-ink-border pt-3">
                    <span class="text-[10px] font-mono text-mist uppercase">Mutolaa sabog'i</span>
                    <div class="flex items-center gap-1">
                        <button wire:click="toggleQuoteLike({{ $todayQuote->id }})" 
                                class="p-1.5 rounded-btn text-mist hover:text-rose-300 transition-colors {{ in_array($todayQuote->id, $likedQuotes) ? 'text-rose-300' : '' }}" title="Yoqdi">
                            <svg class="w-4 h-4 {{ in_array($todayQuote->id, $likedQuotes) ? 'fill-current' : 'fill-none' }}" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                        </button>
                        <button wire:click="toggleQuoteSave({{ $todayQuote->id }})" 
                                class="p-1.5 rounded-btn text-mist hover:text-amber-400 transition-colors {{ in_array($todayQuote->id, $savedQuotes) ? 'text-amber-400' : '' }}" title="Saqlash">
                            <svg class="w-4 h-4 {{ in_array($todayQuote->id, $savedQuotes) ? 'fill-current' : 'fill-none' }}" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path></svg>
                        </button>
                    </div>
                </div>
            @else
                <div class="text-center py-6">
                    <span class="ks-eyebrow">Kitobxon</span>
                    <p class="text-xs text-mist mt-2">Bugun uchun yangi kitob mutolaasini boshlang.</p>
                </div>
            @endif
        </div>

    </div>

    <!-- ── Featured Book / Continue Reading + Leaderboard Section ── -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- Left 8 Cols: Featured Book & Progress -->
        <div class="lg:col-span-8 space-y-6">
            @if ($featuredBook)
                <div class="ks-panel p-6 sm:p-8 bg-ink-900 border border-ink-border">
                    <div class="flex items-center justify-between mb-4">
                        <span class="ks-eyebrow">{{ $userProgress ? 'Mutolaani davom ettirish' : 'Haftaning tavsiya etilgan kitobi' }}</span>
                        <span class="text-xs font-mono text-amber-400 font-bold">{{ $featuredBook->week_number }}-HAFTA</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-6 items-center">
                        <div class="sm:col-span-4 flex justify-center">
                            <div class="w-40 sm:w-44">
                                <x-ui.book-card :book="$featuredBook" :showProgress="true" :progress="$userProgress" />
                            </div>
                        </div>

                        <div class="sm:col-span-8 space-y-4 min-w-0">
                            <div>
                                <span class="text-xs font-mono text-mist uppercase">{{ $featuredBook->genre }}</span>
                                <h3 class="text-xl sm:text-2xl font-bold font-serif text-paper leading-tight mt-0.5 break-words">
                                    {{ $featuredBook->title }}
                                </h3>
                                <p class="text-xs text-mist font-mono mt-1 break-words">Muallif: <span class="text-paper">{{ $featuredBook->author }}</span></p>
                            </div>

                            <p class="text-xs sm:text-sm text-mist line-clamp-3 leading-relaxed font-sans break-words whitespace-pre-line">
                                {{ $featuredBook->description }}
                            </p>

                            @if($userProgress)
                                <div class="space-y-1.5 pt-1">
                                    <div class="flex justify-between text-xs font-mono">
                                        <span class="text-mist">O'qilgan progress:</span>
                                        <span class="font-bold text-amber-400">{{ round($userProgress->percent_complete) }}%</span>
                                    </div>
                                    <div class="w-full bg-ink-950 rounded-badge h-1.5 overflow-hidden border border-ink-border">
                                        <div class="bg-amber-500 h-1.5 transition-all duration-500" style="width: {{ $userProgress->percent_complete }}%"></div>
                                    </div>
                                </div>
                            @endif

                            <div class="flex flex-wrap items-center gap-2 pt-2">
                                <a href="{{ route('books.show', $featuredBook->slug) }}" 
                                   class="ks-btn-primary py-2 px-4 text-xs inline-flex items-center gap-2">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                                    <span>{{ $userProgress ? 'Davom ettirish' : 'O\'qishni boshlash' }}</span>
                                </a>

                                <a href="{{ route('audio.show', $featuredBook->id) }}" 
                                   class="ks-btn-ghost py-2 px-3 text-xs inline-flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 18v-6a9 9 0 0 1 18 0v6"/><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/></svg>
                                    <span>Audio</span>
                                </a>

                                <a href="{{ route('videos.index', $featuredBook->id) }}" 
                                   class="ks-btn-ghost py-2 px-3 text-xs inline-flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg>
                                    <span>Video</span>
                                </a>

                                <a href="{{ route('quiz.show', $featuredBook->id) }}" 
                                   class="ks-btn-ghost py-2 px-3 text-xs inline-flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                                    <span>Test</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Right 4 Cols: Editorial Leaderboard -->
        <div class="lg:col-span-4 space-y-6">
            <div class="ks-panel p-5 bg-ink-900 border border-ink-border">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-bold font-serif text-paper flex items-center gap-2">
                        <span class="text-amber-500">✦</span>
                        <span>Peshqadamlar</span>
                    </h3>
                    <a href="{{ route('leaderboard') }}" class="text-xs text-amber-400 font-mono hover:underline">Barchasi →</a>
                </div>

                <div class="space-y-2">
                    @forelse ($topUsers as $index => $topUser)
                        <div class="flex items-center justify-between p-2.5 rounded-card bg-ink-950 border border-ink-border/50 hover:border-ink-border transition-colors">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <span class="font-mono text-xs font-bold w-6 text-center text-amber-400">
                                    {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}
                                </span>
                                <x-ui.avatar :user="$topUser" size="xs" :rank="$index + 1" />
                                <div class="min-w-0">
                                    <p class="text-xs font-semibold text-paper truncate">{{ $topUser->name }}</p>
                                    <p class="text-[10px] text-mist font-mono truncate">{{ '@' . $topUser->username }}</p>
                                </div>
                            </div>
                            <div class="text-right shrink-0">
                                <span class="text-xs font-bold font-mono text-amber-400">{{ number_format($topUser->total_points) }}</span>
                                <span class="text-[9px] text-mist block font-mono">ball</span>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-mist text-center py-4 font-mono">Hali reyting shakllanmadi.</p>
                    @endforelse
                </div>
            </div>
        </div>

    </div>

</div>
