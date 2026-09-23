<div class="max-w-6xl mx-auto space-y-8 pb-12">

    <!-- Profile Header Card -->
    <div class="p-6 sm:p-10 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-soft relative overflow-hidden">
        <!-- Ambient Glow -->
        <div class="absolute -right-20 -top-20 w-80 h-80 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col sm:flex-row items-center sm:items-start gap-6 text-center sm:text-left">
            <!-- Avatar -->
            <div class="relative shrink-0">
                <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}"
                    class="w-24 h-24 sm:w-28 sm:h-28 rounded-3xl object-cover ring-4 ring-indigo-500/20 shadow-xl">
                @if($user->current_streak > 0)
                    <div class="absolute -bottom-2 -right-2 px-2.5 py-1 bg-amber-500 text-slate-950 font-black text-[11px] rounded-xl shadow-md flex items-center gap-1">
                        <span>🔥</span> {{ $user->current_streak }}
                    </div>
                @endif
            </div>

            <!-- Profile Info -->
            <div class="flex-1 space-y-3 min-w-0">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white font-manrope">{{ $user->name }}</h1>
                        <p class="text-sm text-slate-400 font-medium">{{ '@' . $user->username }}</p>
                    </div>

                    <!-- Action Button -->
                    <div>
                        @if ($isOwner)
                            <a href="{{ route('settings') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-semibold text-xs rounded-xl transition-all">
                                Profilni tahrirlash
                            </a>
                        @else
                            <button wire:click="toggleFollow" class="px-5 py-2.5 rounded-xl font-bold text-xs transition-all {{ $isFollowing ? 'bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300' : 'bg-indigo-600 hover:bg-indigo-500 text-white shadow-md shadow-indigo-600/25' }}">
                                {{ $isFollowing ? 'Obuna bo\'lingan' : '+ Obuna bo\'lish' }}
                            </button>
                        @endif
                    </div>
                </div>

                @if ($user->bio)
                    <p class="text-sm text-slate-600 dark:text-slate-300 max-w-2xl leading-relaxed">
                        {{ $user->bio }}
                    </p>
                @endif

                <!-- Meta Pills -->
                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2 pt-1">
                    @if($user->profile && $user->profile->reading_place)
                        <span class="px-3 py-1 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-xs font-medium flex items-center gap-1.5">
                            <span>📍</span> Joy: <strong class="text-slate-900 dark:text-white">{{ ucfirst($user->profile->reading_place) }}</strong>
                        </span>
                    @endif

                    @if($user->profile && $user->profile->reading_goal)
                        <span class="px-3 py-1 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-xs font-medium flex items-center gap-1.5">
                            <span>🎯</span> Maqsad: <strong class="text-slate-900 dark:text-white">{{ ucfirst($user->profile->reading_goal) }}</strong>
                        </span>
                    @endif

                    <span class="px-3 py-1 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-xs font-medium flex items-center gap-1.5">
                        <span>📅</span> A'zo bo'lgan: {{ $user->created_at->format('M Y') }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Quick Stats Grid in Header -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-8 pt-8 border-t border-slate-200/80 dark:border-slate-800/80">
            <div class="text-center sm:text-left">
                <span class="text-[11px] text-slate-400 font-bold uppercase tracking-wider block mb-1">Ketma-ketlik</span>
                <span class="text-2xl font-black text-amber-500">{{ $user->current_streak }} <small class="text-xs text-slate-400 font-normal">kun</small></span>
            </div>
            <div class="text-center sm:text-left">
                <span class="text-[11px] text-slate-400 font-bold uppercase tracking-wider block mb-1">To'plangan ball</span>
                <span class="text-2xl font-black text-indigo-600 dark:text-indigo-400">{{ number_format($user->total_points) }}</span>
            </div>
            <div class="text-center sm:text-left">
                <span class="text-[11px] text-slate-400 font-bold uppercase tracking-wider block mb-1">Tanga balansi</span>
                <span class="text-2xl font-black text-amber-400">{{ number_format($user->coin_balance) }}</span>
            </div>
            <div class="text-center sm:text-left">
                <span class="text-[11px] text-slate-400 font-bold uppercase tracking-wider block mb-1">O'qilgan vaqt</span>
                <span class="text-2xl font-black text-emerald-500">{{ $user->total_reading_minutes }} <small class="text-xs text-slate-400 font-normal">daq</small></span>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex items-center gap-2 border-b border-slate-200 dark:border-slate-800 pb-2">
        <button wire:click="setTab('overview')" class="px-4 py-2 rounded-xl text-xs font-bold transition-all {{ $activeTab === 'overview' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/20' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
            Umumiy ko'rinish
        </button>
        <button wire:click="setTab('books')" class="px-4 py-2 rounded-xl text-xs font-bold transition-all {{ $activeTab === 'books' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/20' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
            Kitoblar ({{ count($readingProgresses) }})
        </button>
        <button wire:click="setTab('badges')" class="px-4 py-2 rounded-xl text-xs font-bold transition-all {{ $activeTab === 'badges' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/20' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
            Yutuqlar ({{ count($badges) }})
        </button>
        <button wire:click="setTab('notes')" class="px-4 py-2 rounded-xl text-xs font-bold transition-all {{ $activeTab === 'notes' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/20' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
            Qaydlar
        </button>
    </div>

    <!-- Tab 1: Overview & Heatmap -->
    @if ($activeTab === 'overview')
        <div class="space-y-6">
            <!-- Activity Heatmap Card -->
            <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-soft">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <span>📊</span> So'nggi 60 kunlik o'qish faolligi (Heatmap)
                    </h3>
                    <span class="text-xs text-slate-400">Asia/Tashkent</span>
                </div>

                <div class="overflow-x-auto pb-2">
                    <div class="flex gap-1.5 min-w-max">
                        @for ($i = 59; $i >= 0; $i--)
                            @php
                                $date = now()->subDays($i)->toDateString();
                                $minutes = $activities[$date] ?? 0;
                                $colorClass = 'bg-slate-100 dark:bg-slate-800';
                                if ($minutes > 0 && $minutes <= 15) $colorClass = 'bg-emerald-300 dark:bg-emerald-900/60';
                                elseif ($minutes > 15 && $minutes <= 30) $colorClass = 'bg-emerald-400 dark:bg-emerald-700';
                                elseif ($minutes > 30) $colorClass = 'bg-emerald-500 dark:bg-emerald-500';
                            @endphp
                            <div class="w-3.5 h-12 rounded-sm {{ $colorClass }} transition-all hover:scale-110 cursor-pointer" 
                                 title="{{ $date }}: {{ $minutes }} daqiqa o'qilgan"></div>
                        @endfor
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Tab 2: Books Progress -->
    @if ($activeTab === 'books')
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse ($readingProgresses as $progress)
                @if($progress->book)
                    <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-soft flex gap-4">
                        <img src="{{ $progress->book->cover_url }}" class="w-16 h-24 rounded-xl object-cover shrink-0 shadow-md">
                        <div class="flex-1 min-w-0 flex flex-col justify-between">
                            <div>
                                <h4 class="text-sm font-bold text-slate-900 dark:text-white truncate">{{ $progress->book->title }}</h4>
                                <p class="text-xs text-slate-400 truncate">{{ $progress->book->author }}</p>
                            </div>
                            <div class="space-y-1">
                                <div class="flex justify-between text-[11px] text-slate-400 font-semibold">
                                    <span>Progress</span>
                                    <span class="text-indigo-600 dark:text-indigo-400">{{ number_format($progress->percent_complete) }}%</span>
                                </div>
                                <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-1.5 overflow-hidden">
                                    <div class="bg-indigo-600 h-1.5 rounded-full" style="width: {{ $progress->percent_complete }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @empty
                <p class="text-xs text-slate-400 py-8 col-span-3 text-center">Hozircha kitob o'qish boshlanmagan.</p>
            @endforelse
        </div>
    @endif

    <!-- Tab 3: Badges -->
    @if ($activeTab === 'badges')
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            @forelse ($badges as $badge)
                <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-soft text-center space-y-2">
                    <div class="w-14 h-14 rounded-2xl bg-amber-500/10 text-amber-500 mx-auto flex items-center justify-center text-3xl">
                        {{ $badge->icon ?? '🎖️' }}
                    </div>
                    <h4 class="text-xs font-bold text-slate-900 dark:text-white">{{ $badge->name }}</h4>
                    <p class="text-[11px] text-slate-400 leading-snug">{{ $badge->description }}</p>
                </div>
            @empty
                <p class="text-xs text-slate-400 py-8 col-span-4 text-center">Hali yutuqlar qo'lga kiritilmagan.</p>
            @endforelse
        </div>
    @endif

</div>
