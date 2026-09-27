<div class="max-w-6xl mx-auto space-y-8 pb-16">

    <!-- Profile Header Card -->
    <div class="p-6 sm:p-10 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-soft relative overflow-hidden">
        <!-- Ambient Glow -->
        <div class="absolute -right-20 -top-20 w-80 h-80 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col sm:flex-row items-center sm:items-start gap-6 text-center sm:text-left">
            <!-- Avatar -->
            <div class="relative shrink-0">
                <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}"
                    class="w-24 h-24 sm:w-28 sm:h-28 rounded-3xl object-cover ring-4 ring-amber-500/20 shadow-xl">
                @if($stats['current_streak'] > 0)
                    <div class="absolute -bottom-2 -right-2 px-2.5 py-1 bg-amber-500 text-slate-950 font-black text-[11px] rounded-xl shadow-md flex items-center gap-1">
                        <span>🔥</span> {{ $stats['current_streak'] }}
                    </div>
                @endif
            </div>

            <!-- Profile Info -->
            <div class="flex-1 space-y-3 min-w-0">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <div class="flex items-center justify-center sm:justify-start gap-2.5 flex-wrap">
                            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white font-manrope">{{ $user->name }}</h1>
                            @if($user->role === 'admin' || $user->hasRole('admin'))
                                <span class="px-2.5 py-0.5 rounded-full bg-rose-500/10 border border-rose-500/20 text-rose-400 text-[10px] font-bold uppercase tracking-wider">Admin</span>
                            @elseif($user->role === 'teacher' || $user->hasRole('teacher'))
                                <span class="px-2.5 py-0.5 rounded-full bg-blue-500/10 border border-blue-500/20 text-blue-400 text-[10px] font-bold uppercase tracking-wider">Ustoz</span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full bg-amber-500/10 border border-amber-500/20 text-amber-400 text-[10px] font-bold uppercase tracking-wider">Kitobxon</span>
                            @endif
                        </div>
                        <p class="text-sm text-slate-400 font-medium mt-0.5">{{ '@' . $user->username }}</p>
                    </div>

                    <!-- Action Button -->
                    <div>
                        @if ($isOwner)
                            <a href="{{ route('settings') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-semibold text-xs rounded-xl transition-all inline-flex items-center gap-1.5">
                                <span>⚙️</span> Sozlamalar
                            </a>
                        @else
                            <button wire:click="toggleFollow" class="px-5 py-2.5 rounded-xl font-bold text-xs transition-all {{ $isFollowing ? 'bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300' : 'bg-indigo-600 hover:bg-indigo-500 text-white shadow-md shadow-indigo-600/25' }}">
                                {{ $isFollowing ? '✓ Obuna bo\'lingan' : '+ Obuna bo\'lish' }}
                            </button>
                        @endif
                    </div>
                </div>

                @if ($user->bio)
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 max-w-2xl leading-relaxed">
                        {{ $user->bio }}
                    </p>
                @endif

                <!-- Meta Pills -->
                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2 pt-1">
                    @if($user->profile && $user->profile->reading_place)
                        <span class="px-3 py-1 rounded-xl bg-slate-100 dark:bg-slate-800/80 text-slate-600 dark:text-slate-300 text-xs font-medium flex items-center gap-1.5">
                            <span>📍</span> Mutolaa joyi: <strong class="text-slate-900 dark:text-white">{{ ucfirst($user->profile->reading_place) }}</strong>
                        </span>
                    @endif

                    @if($user->profile && $user->profile->reading_goal)
                        <span class="px-3 py-1 rounded-xl bg-slate-100 dark:bg-slate-800/80 text-slate-600 dark:text-slate-300 text-xs font-medium flex items-center gap-1.5">
                            <span>🎯</span> Maqsad: <strong class="text-slate-900 dark:text-white">{{ ucfirst($user->profile->reading_goal) }}</strong>
                        </span>
                    @endif

                    <span class="px-3 py-1 rounded-xl bg-slate-100 dark:bg-slate-800/80 text-slate-600 dark:text-slate-300 text-xs font-medium flex items-center gap-1.5">
                        <span>📅</span> A'zo bo'lgan: {{ $user->created_at->format('d.m.Y') }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Quick Stats Grid in Header -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-8 pt-8 border-t border-slate-200/80 dark:border-slate-800/80">
            <div class="text-center sm:text-left p-3 rounded-2xl bg-amber-500/5 border border-amber-500/10">
                <span class="text-[11px] text-slate-400 font-bold uppercase tracking-wider block mb-1">🔥 Ketma-ketlik</span>
                <span class="text-2xl font-black text-amber-500">{{ $stats['current_streak'] }} <small class="text-xs text-slate-400 font-normal">kun</small></span>
            </div>
            <div class="text-center sm:text-left p-3 rounded-2xl bg-indigo-500/5 border border-indigo-500/10">
                <span class="text-[11px] text-slate-400 font-bold uppercase tracking-wider block mb-1">⭐️ To'plangan ball</span>
                <span class="text-2xl font-black text-indigo-600 dark:text-indigo-400">{{ number_format($stats['total_points']) }}</span>
            </div>
            <div class="text-center sm:text-left p-3 rounded-2xl bg-amber-400/5 border border-amber-400/10">
                <span class="text-[11px] text-slate-400 font-bold uppercase tracking-wider block mb-1">🪙 Tanga balansi</span>
                <span class="text-2xl font-black text-amber-400">{{ number_format($stats['coin_balance']) }}</span>
            </div>
            <div class="text-center sm:text-left p-3 rounded-2xl bg-emerald-500/5 border border-emerald-500/10">
                <span class="text-[11px] text-slate-400 font-bold uppercase tracking-wider block mb-1">⏱️ O'qilgan vaqt</span>
                <span class="text-2xl font-black text-emerald-500">{{ number_format($stats['total_minutes']) }} <small class="text-xs text-slate-400 font-normal">daq</small></span>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex items-center gap-2 border-b border-slate-200 dark:border-slate-800 pb-2 overflow-x-auto">
        <button wire:click="setTab('overview')" class="px-4 py-2 rounded-xl text-xs font-bold transition-all shrink-0 {{ $activeTab === 'overview' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/20' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
            Umumiy ko'rinish
        </button>
        <button wire:click="setTab('books')" class="px-4 py-2 rounded-xl text-xs font-bold transition-all shrink-0 {{ $activeTab === 'books' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/20' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
            Kitoblar ({{ count($readingProgresses) }})
        </button>
        <button wire:click="setTab('badges')" class="px-4 py-2 rounded-xl text-xs font-bold transition-all shrink-0 {{ $activeTab === 'badges' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/20' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
            Yutuqlar ({{ count($badges) }})
        </button>
        <button wire:click="setTab('notes')" class="px-4 py-2 rounded-xl text-xs font-bold transition-all shrink-0 {{ $activeTab === 'notes' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/20' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
            Qaydlar ({{ count($notes) }})
        </button>
    </div>

    <!-- Tab 1: Overview & Heatmap -->
    @if ($activeTab === 'overview')
        <div class="space-y-6">
            <!-- Activity Heatmap Card -->
            <div class="p-6 sm:p-8 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-soft space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <span>📊</span> So'nggi 60 kunlik mutolaa faolligi (Heatmap)
                        </h3>
                        <p class="text-xs text-slate-400 mt-0.5">Har bir blok kunlik mutolaa daqiqalarini ifodalaydi</p>
                    </div>
                    <span class="text-xs font-mono text-slate-400">Asia/Tashkent</span>
                </div>

                <div class="overflow-x-auto pb-2 pt-2">
                    <div class="flex gap-1.5 min-w-max">
                        @for ($i = 59; $i >= 0; $i--)
                            @php
                                $date = now()->subDays($i)->toDateString();
                                $minutes = $activities[$date] ?? 0;
                                $colorClass = 'bg-slate-100 dark:bg-slate-800';
                                if ($minutes > 0 && $minutes <= 15) $colorClass = 'bg-emerald-400/50 dark:bg-emerald-700/60';
                                elseif ($minutes > 15 && $minutes <= 30) $colorClass = 'bg-emerald-500 dark:bg-emerald-600';
                                elseif ($minutes > 30) $colorClass = 'bg-emerald-400 dark:bg-emerald-400 shadow-sm shadow-emerald-400/40';
                            @endphp
                            <div class="w-3.5 h-12 rounded-sm {{ $colorClass }} transition-all hover:scale-110 cursor-pointer" 
                                 title="{{ $date }}: {{ $minutes }} daqiqa o'qilgan"></div>
                        @endfor
                    </div>
                </div>

                <!-- Legend -->
                <div class="flex items-center justify-between text-[11px] text-slate-400 pt-2 border-t border-slate-100 dark:border-slate-800">
                    <span>Kam</span>
                    <div class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded-sm bg-slate-100 dark:bg-slate-800"></span>
                        <span class="w-3 h-3 rounded-sm bg-emerald-400/50 dark:bg-emerald-700/60"></span>
                        <span class="w-3 h-3 rounded-sm bg-emerald-500 dark:bg-emerald-600"></span>
                        <span class="w-3 h-3 rounded-sm bg-emerald-400 dark:bg-emerald-400"></span>
                    </div>
                    <span>Ko'p (30+ daq)</span>
                </div>
            </div>

            <!-- Real Activity Grid Cards -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-soft space-y-1">
                    <span class="text-xs text-slate-400 font-medium block">📚 O'qilayotgan kitoblar</span>
                    <span class="text-xl font-bold text-slate-900 dark:text-white">{{ $stats['books_reading'] }} ta</span>
                </div>
                <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-soft space-y-1">
                    <span class="text-xs text-slate-400 font-medium block">🎓 Yechilgan testlar</span>
                    <span class="text-xl font-bold text-indigo-600 dark:text-indigo-400">{{ $stats['quizzes_passed'] }} ta</span>
                </div>
                <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-soft space-y-1">
                    <span class="text-xs text-slate-400 font-medium block">👥 Kitobxon guruhlari</span>
                    <span class="text-xl font-bold text-amber-500">{{ $stats['groups_count'] }} ta</span>
                </div>
                <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-soft space-y-1">
                    <span class="text-xs text-slate-400 font-medium block">💬 Chatdagi xabarlar</span>
                    <span class="text-xl font-bold text-emerald-500">{{ $stats['messages_count'] }} ta</span>
                </div>
            </div>
        </div>
    @endif

    <!-- Tab 2: Books Progress -->
    @if ($activeTab === 'books')
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse ($readingProgresses as $progress)
                @if($progress->book)
                    <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-soft flex gap-4 hover:border-slate-300 dark:hover:border-slate-700 transition-all">
                        <img src="{{ $progress->book->cover_url }}" class="w-16 h-24 rounded-xl object-cover shrink-0 shadow-md">
                        <div class="flex-1 min-w-0 flex flex-col justify-between">
                            <div>
                                <h4 class="text-sm font-bold text-slate-900 dark:text-white truncate">{{ $progress->book->title }}</h4>
                                <p class="text-xs text-slate-400 truncate">{{ $progress->book->author }}</p>
                            </div>
                            <div class="space-y-1.5">
                                <div class="flex justify-between text-[11px] text-slate-400 font-semibold">
                                    <span>Mutolaa</span>
                                    <span class="text-indigo-600 dark:text-indigo-400 font-bold">{{ number_format($progress->percent_complete) }}%</span>
                                </div>
                                <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-1.5 overflow-hidden">
                                    <div class="bg-indigo-600 h-1.5 rounded-full" style="width: {{ $progress->percent_complete }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @empty
                <div class="col-span-full py-12 text-center p-8 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                    <span class="text-3xl block mb-2">📖</span>
                    <p class="text-xs text-slate-400">Hozircha kitob mutolaasi boshlanmagan.</p>
                </div>
            @endforelse
        </div>
    @endif

    <!-- Tab 3: Badges -->
    @if ($activeTab === 'badges')
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            @forelse ($badges as $badge)
                <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-soft text-center space-y-2 hover:border-amber-400/40 transition-all">
                    <div class="w-14 h-14 rounded-2xl bg-amber-500/10 text-amber-500 mx-auto flex items-center justify-center text-3xl shadow-sm">
                        {{ $badge->icon ?? '🎖️' }}
                    </div>
                    <h4 class="text-xs font-bold text-slate-900 dark:text-white">{{ $badge->name }}</h4>
                    <p class="text-[11px] text-slate-400 leading-snug">{{ $badge->description }}</p>
                    @if($badge->pivot && $badge->pivot->earned_at)
                        <span class="inline-block text-[10px] text-amber-500/80 font-mono font-semibold pt-1">
                            ✓ {{ \Carbon\Carbon::parse($badge->pivot->earned_at)->format('d.m.Y') }}
                        </span>
                    @endif
                </div>
            @empty
                <div class="col-span-full py-12 text-center p-8 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                    <span class="text-3xl block mb-2">🎖️</span>
                    <p class="text-xs text-slate-400">Hali yutuqlar qo'lga kiritilmagan.</p>
                </div>
            @endforelse
        </div>
    @endif

    <!-- Tab 4: Notes (Qaydlar) -->
    @if ($activeTab === 'notes')
        <div class="space-y-4">
            @forelse ($notes as $note)
                <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-soft space-y-3">
                    <div class="flex items-center justify-between text-xs">
                        <span class="font-bold text-indigo-600 dark:text-indigo-400">📖 {{ $note->book?->title ?? 'Kitob' }}</span>
                        <span class="text-slate-400 text-[11px]">{{ $note->created_at->format('d.m.Y H:i') }}</span>
                    </div>
                    @if($note->selected_text)
                        <blockquote class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border-l-4 border-amber-400 text-xs italic text-slate-700 dark:text-slate-300">
                            «{{ $note->selected_text }}»
                        </blockquote>
                    @endif
                    <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                        {{ $note->note_text }}
                    </p>
                </div>
            @empty
                <div class="py-12 text-center p-8 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                    <span class="text-3xl block mb-2">✍️</span>
                    <p class="text-xs text-slate-400">Hozircha saqlangan qaydlar mavjud emas.</p>
                </div>
            @endforelse
        </div>
    @endif

</div>
