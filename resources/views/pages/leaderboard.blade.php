@extends('layouts.app')

@section('title', 'Peshqadamlar reytingi')

@section('content')
<div class="max-w-5xl mx-auto space-y-8 pb-16" x-data="{ period: 'all_time' }">
    <div class="text-center max-w-xl mx-auto space-y-2">
        <span class="text-xs font-bold text-amber-500 uppercase tracking-widest flex items-center justify-center gap-1">
            <span>🏆</span> Kitobxonlar Liderboardi
        </span>
        <h1 class="text-3xl font-black text-slate-900 dark:text-white font-manrope">Peshqadamlar Jadvali</h1>
        <p class="text-xs text-slate-500 dark:text-slate-400">Har bir o'qilgan daqiqa va topshirilgan test sizni cho'qqiga yetaklaydi!</p>
    </div>

    <!-- Top 3 Podium Cards -->
    @php
        $top3 = \App\Models\User::orderBy('total_points', 'desc')->take(3)->get();
    @endphp

    <div class="grid grid-cols-3 gap-3 sm:gap-6 pt-6 items-end max-w-2xl mx-auto">
        <!-- 2nd Place Silver -->
        @if(isset($top3[1]))
            <div class="p-4 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-soft text-center space-y-2">
                <span class="text-2xl block">🥈</span>
                <img src="{{ $top3[1]->avatar_url }}" class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl mx-auto object-cover ring-2 ring-slate-300">
                <h3 class="text-xs sm:text-sm font-bold truncate text-slate-900 dark:text-white">{{ $top3[1]->name }}</h3>
                <span class="px-2.5 py-1 bg-slate-100 dark:bg-slate-800 rounded-lg text-xs font-black text-slate-700 dark:text-slate-300 block">
                    {{ number_format($top3[1]->total_points) }} ball
                </span>
            </div>
        @endif

        <!-- 1st Place Gold -->
        @if(isset($top3[0]))
            <div class="p-5 rounded-3xl bg-gradient-to-b from-amber-500/15 via-white dark:via-slate-900 to-white dark:to-slate-900 border-2 border-amber-500 shadow-glow-accent text-center space-y-3 transform -translate-y-4">
                <span class="text-3xl block animate-bounce-sm">👑 🥇</span>
                <img src="{{ $top3[0]->avatar_url }}" class="w-16 h-16 sm:w-20 sm:h-20 rounded-3xl mx-auto object-cover ring-4 ring-amber-500 shadow-lg">
                <h3 class="text-sm sm:text-base font-black truncate text-slate-900 dark:text-white">{{ $top3[0]->name }}</h3>
                <span class="px-3 py-1 bg-amber-500 text-slate-950 rounded-xl text-xs font-black block shadow-md">
                    {{ number_format($top3[0]->total_points) }} ball
                </span>
            </div>
        @endif

        <!-- 3rd Place Bronze -->
        @if(isset($top3[2]))
            <div class="p-4 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-soft text-center space-y-2">
                <span class="text-2xl block">🥉</span>
                <img src="{{ $top3[2]->avatar_url }}" class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl mx-auto object-cover ring-2 ring-amber-700">
                <h3 class="text-xs sm:text-sm font-bold truncate text-slate-900 dark:text-white">{{ $top3[2]->name }}</h3>
                <span class="px-2.5 py-1 bg-slate-100 dark:bg-slate-800 rounded-lg text-xs font-black text-slate-700 dark:text-slate-300 block">
                    {{ number_format($top3[2]->total_points) }} ball
                </span>
            </div>
        @endif
    </div>

    <!-- Leaderboard Table -->
    @php
        $allUsers = \App\Models\User::orderBy('total_points', 'desc')->take(30)->get();
    @endphp

    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-soft overflow-hidden">
        <div class="p-4 sm:p-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Barcha ishtirokchilar</h3>
            <span class="text-xs text-slate-400">Jonli reyting</span>
        </div>

        <div class="divide-y divide-slate-100 dark:divide-slate-800/60">
            @foreach ($allUsers as $index => $u)
                <div class="p-4 sm:px-6 flex items-center justify-between hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors {{ auth()->id() === $u->id ? 'bg-indigo-50/50 dark:bg-indigo-950/30' : '' }}">
                    <div class="flex items-center gap-4 min-w-0">
                        <span class="w-6 text-center text-xs font-black {{ $index < 3 ? 'text-amber-500 font-extrabold text-sm' : 'text-slate-400' }}">
                            {{ $index + 1 }}
                        </span>
                        <a href="{{ route('profile.show', $u->username) }}" class="flex items-center gap-3 min-w-0">
                            <img src="{{ $u->avatar_url }}" class="w-10 h-10 rounded-xl object-cover shrink-0">
                            <div class="min-w-0">
                                <span class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white truncate block">{{ $u->name }}</span>
                                <span class="text-[11px] text-slate-400 truncate block">{{ '@' . $u->username }}</span>
                            </div>
                        </a>
                    </div>

                    <div class="flex items-center gap-6">
                        @if($u->current_streak > 0)
                            <div class="hidden sm:flex items-center gap-1 text-xs font-bold text-amber-500">
                                <span>🔥</span> {{ $u->current_streak }} kun
                            </div>
                        @endif

                        <div class="text-right">
                            <span class="text-xs sm:text-sm font-black text-indigo-600 dark:text-indigo-400 block">{{ number_format($u->total_points) }}</span>
                            <span class="text-[10px] text-slate-400 block">ball</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
