@extends('admin.layouts.app')

@section('title', $user->name . ' — Foydalanuvchi ma\'lumotlari')
@section('breadcrumb', 'Foydalanuvchini tahrirlash')

@section('content')

<div class="max-w-7xl mx-auto space-y-6">

    {{-- Header: Back link & Title bar --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-slate-800">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.users.index') }}" 
               class="p-2.5 rounded-xl bg-slate-900 border border-slate-800 text-slate-400 hover:text-white hover:border-slate-700 transition-colors shadow-sm"
               title="Foydalanuvchilar ro'yxatiga qaytish">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <div class="flex items-center gap-2.5 flex-wrap">
                    <h1 class="text-xl sm:text-2xl font-bold font-serif text-white tracking-tight">{{ $user->name }}</h1>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-slate-800 text-slate-400 border border-slate-700">
                        ID: #{{ $user->id }}
                    </span>
                    @if($user->isBanned())
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-500/20 text-red-400 border border-red-500/30 flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-red-400 animate-pulse"></span>
                            Bloklangan
                        </span>
                    @else
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                            Faol
                        </span>
                    @endif
                </div>
                <p class="text-xs text-slate-400 font-mono mt-1">
                    {{ $user->email }} • {{ '@' . ($user->username ?? 'foydalanuvchi') }} • A'zo bo'lgan: {{ $user->created_at->format('d.m.Y') }}
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.users.index') }}" 
               class="px-4 py-2 text-xs font-medium text-slate-300 bg-slate-900 hover:bg-slate-800 border border-slate-800 rounded-xl transition-all shadow-sm flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                </svg>
                Ro'yxatga qaytish
            </a>
        </div>
    </div>

    {{-- Top 4 KPI Metric Cards (Reyting, O'qish, Streak & Kitoblar, Tangalar & Balans) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        {{-- Card 1: Leaderboard Reytingi --}}
        <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4.5 relative overflow-hidden group hover:border-amber-500/40 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider font-mono">Umumiy Reyting</span>
                <div class="w-9 h-9 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-400 shadow-sm">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M5 3h14a1 1 0 0 1 1 1v2a5 5 0 0 1-5 5h-1a6.002 6.002 0 0 1-5 5.918V19h3a1 1 0 1 1 0 2H7a1 1 0 1 1 0-2h3v-2.082A6.002 6.002 0 0 1 5 11H4a5 5 0 0 1-5-5V4a1 1 0 0 1 1-1h5zm-1 3H3a3 3 0 0 0 3 3h1V6H4zm16 0h-3v3h1a3 3 0 0 0 3-3v0z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3">
                <div class="flex items-baseline gap-2">
                    <span class="text-2xl sm:text-3xl font-extrabold text-amber-400 font-mono tracking-tight">
                        #{{ $leaderboardRank }}
                    </span>
                    <span class="text-xs text-slate-500 font-medium font-mono">
                        / {{ $totalUsersCount }} nafar
                    </span>
                </div>
                <div class="mt-2 flex items-center justify-between text-xs pt-2 border-t border-slate-800/80">
                    <span class="text-slate-400">Jami ball:</span>
                    <span class="font-bold text-amber-400 font-mono">{{ number_format($user->total_points) }} ball</span>
                </div>
            </div>
        </div>

        {{-- Card 2: Mutolaa Vaqti & O'rtacha --}}
        <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4.5 relative overflow-hidden group hover:border-indigo-500/40 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider font-mono">Mutolaa Vaqti</span>
                <div class="w-9 h-9 rounded-xl bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center text-indigo-400 shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3">
                <div class="flex items-baseline gap-1.5">
                    @php
                        $hrs = floor($totalReadingMinutes / 60);
                        $mins = $totalReadingMinutes % 60;
                    @endphp
                    <span class="text-2xl sm:text-3xl font-extrabold text-white font-mono tracking-tight">
                        {{ $hrs > 0 ? $hrs . 's ' : '' }}{{ $mins }}d
                    </span>
                    <span class="text-xs text-slate-500 font-medium">jami mutolaa</span>
                </div>
                <div class="mt-2 flex items-center justify-between text-xs pt-2 border-t border-slate-800/80">
                    <span class="text-slate-400">Kunlik o'rtacha:</span>
                    <span class="font-bold text-indigo-400 font-mono">{{ $avgDailyMinutes }} daqiqa/kun</span>
                </div>
            </div>
        </div>

        {{-- Card 3: Kitoblar & Ketma-ketlik (Streak) --}}
        <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4.5 relative overflow-hidden group hover:border-emerald-500/40 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider font-mono">Kitoblar & Streak</span>
                <div class="w-9 h-9 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400 shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3">
                <div class="flex items-baseline gap-2">
                    <span class="text-2xl sm:text-3xl font-extrabold text-emerald-400 font-mono tracking-tight">
                        {{ $currentStreak }} kun
                    </span>
                    <span class="text-xs text-slate-500 font-medium">streak</span>
                </div>
                <div class="mt-2 flex items-center justify-between text-xs pt-2 border-t border-slate-800/80">
                    <span class="text-slate-400">Kitoblar holati:</span>
                    <span class="font-bold text-emerald-400 font-mono">{{ $booksFinishedCount }} tugatilgan / {{ $booksInProgressCount }} o'qilmoqda</span>
                </div>
            </div>
        </div>

        {{-- Card 4: Tangalar & Balans --}}
        <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4.5 relative overflow-hidden group hover:border-yellow-500/40 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider font-mono">Tangalar & Inventar</span>
                <div class="w-9 h-9 rounded-xl bg-yellow-500/10 border border-yellow-500/20 flex items-center justify-center text-yellow-400 shadow-sm">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="9"/>
                        <circle cx="12" cy="12" r="4.5"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3">
                <div class="flex items-baseline gap-2">
                    <span class="text-2xl sm:text-3xl font-extrabold text-yellow-400 font-mono tracking-tight">
                        {{ number_format($user->coin_balance) }}
                    </span>
                    <span class="text-xs text-slate-500 font-medium">tanga</span>
                </div>
                <div class="mt-2 flex items-center justify-between text-xs pt-2 border-t border-slate-800/80">
                    <span class="text-slate-400">Do'kon buyumlari:</span>
                    <span class="font-bold text-amber-400 font-mono">{{ $userItems->count() }} ta egalikda</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Layout: Left Admin Sidebar (1/3) + Right 360° Data Tabs (2/3) --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        {{-- ==================== LEFT COLUMN: Admin Controls (4 cols) ==================== --}}
        <div class="lg:col-span-4 space-y-5">

            {{-- 1. User Profile & Visuals Card --}}
            <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-lg">
                @php
                    $equippedFrame = $equipment->get('avatar_frame') ?? $equipment->get('frame');
                    $equippedTheme = $equipment->get('profile_theme') ?? $equipment->get('theme');
                    $equippedTitle = $equipment->get('title');
                    $nameColor = $user->equipped_name_color;
                @endphp

                {{-- Banner with theme color preview if equipped --}}
                <div class="h-24 bg-gradient-to-br from-indigo-700 via-purple-700 to-slate-900 relative">
                    <div class="absolute inset-0 opacity-25">
                        <div class="absolute top-2 right-2 w-20 h-20 rounded-full bg-white/20 blur-xl"></div>
                        <div class="absolute bottom-1 left-3 w-12 h-12 rounded-full bg-amber-400/20 blur-md"></div>
                    </div>
                    @if($equippedTheme)
                        <div class="absolute top-2 right-2.5 px-2.5 py-0.5 rounded-md text-[10px] font-mono font-bold bg-black/60 backdrop-blur-md text-amber-300 border border-amber-500/30">
                            🎨 {{ $equippedTheme->shopItem?->name_text ?? 'Mavzu' }}
                        </div>
                    @endif
                </div>

                {{-- Avatar + Identity --}}
                <div class="relative z-10 flex flex-col items-center px-5 pb-5 -mt-10">
                    <div class="relative">
                        @if ($user->avatar)
                            <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}"
                                 class="w-20 h-20 rounded-2xl object-cover shadow-2xl shadow-black/60 border-4 {{ $equippedFrame ? 'border-amber-400 ring-2 ring-amber-400/50' : 'border-slate-900' }}">
                        @else
                            <div class="w-20 h-20 rounded-2xl bg-slate-800 border-4 {{ $equippedFrame ? 'border-amber-400 ring-2 ring-amber-400/50' : 'border-slate-900' }} flex items-center justify-center text-3xl font-bold text-amber-400 shadow-2xl shadow-black/60">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                        @endif

                        @if($equippedFrame)
                            <div class="absolute -bottom-2 -right-1 px-1.5 py-0.5 rounded bg-amber-500 text-[9px] font-bold text-slate-950 uppercase tracking-wider shadow">
                                Ramka
                            </div>
                        @endif
                    </div>

                    <h3 class="mt-3 text-base font-bold text-center" style="{{ $nameColor ? 'color: ' . $nameColor . ';' : 'color: #ffffff;' }}">
                        {{ $user->name }}
                    </h3>

                    @if($equippedTitle)
                        <span class="mt-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-amber-500/10 text-amber-300 border border-amber-500/25 flex items-center gap-1">
                            <span class="text-amber-400 text-xs">✦</span>
                            {{ $equippedTitle->shopItem?->name_text ?? 'Unvon' }}
                        </span>
                    @endif

                    <p class="text-xs text-slate-500 mt-1 text-center font-mono">{{ $user->email }}</p>

                    {{-- Current Role Pill --}}
                    @php
                        $currentRole = $user->roles->first()?->name ?? 'student';
                        $roleConfig = [
                            'admin'     => ['label' => 'Admin',             'class' => 'bg-red-500/15 text-red-400 border-red-500/25'],
                            'teacher'   => ['label' => "O'qituvchi",        'class' => 'bg-blue-500/15 text-blue-400 border-blue-500/25'],
                            'student'   => ['label' => "O'quvchi",          'class' => 'bg-emerald-500/15 text-emerald-400 border-emerald-500/25'],
                            'author'    => ['label' => "Muallif",           'class' => 'bg-amber-500/15 text-amber-400 border-amber-500/25'],
                            'moderator' => ['label' => "Moderator",         'class' => 'bg-violet-500/15 text-violet-400 border-violet-500/25'],
                            'reader'    => ['label' => "O'quvchi (reader)", 'class' => 'bg-slate-500/15 text-slate-400 border-slate-500/25'],
                        ];
                        $rc = $roleConfig[$currentRole] ?? ['label' => $currentRole, 'class' => 'bg-slate-500/15 text-slate-400 border-slate-500/25'];
                    @endphp
                    <div class="mt-2.5">
                        <span class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-semibold border {{ $rc['class'] }}">
                            Rol: {{ $rc['label'] }}
                        </span>
                    </div>

                    {{-- Mini stats row --}}
                    <div class="w-full mt-4 pt-3.5 border-t border-slate-800 grid grid-cols-3 gap-2 text-center">
                        <div class="p-2 rounded-xl bg-slate-800/40 border border-slate-800">
                            <p class="text-sm font-bold text-white font-mono">{{ number_format($user->total_points) }}</p>
                            <p class="text-[10px] text-slate-400 mt-0.5">Jami ball</p>
                        </div>
                        <div class="p-2 rounded-xl bg-slate-800/40 border border-slate-800">
                            <p class="text-sm font-bold text-amber-400 font-mono">{{ number_format($user->coin_balance) }}</p>
                            <p class="text-[10px] text-slate-400 mt-0.5">Tanga</p>
                        </div>
                        <div class="p-2 rounded-xl bg-slate-800/40 border border-slate-800">
                            <p class="text-sm font-bold text-indigo-400 font-mono">{{ $readingDaysCount }} kun</p>
                            <p class="text-[10px] text-slate-400 mt-0.5">Faol kun</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2. Edit User Form (Role, Points, Coins) --}}
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-lg">
                <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-800">
                    <h2 class="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Rol va Balansni o'zgartirish
                    </h2>
                </div>

                <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    {{-- Role Selection --}}
                    <div>
                        <label for="role" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5">
                            Rol <span class="text-red-400">*</span>
                        </label>
                        <div class="relative">
                            <select name="role" id="role"
                                    class="w-full appearance-none pl-3.5 pr-10 py-2.5 bg-slate-800 border border-slate-700 rounded-xl text-xs sm:text-sm text-slate-200 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all cursor-pointer">
                                @foreach($roles as $role)
                                    @php
                                        $isSelected = $user->roles->first()?->name === $role->name;
                                    @endphp
                                    <option value="{{ $role->name }}" {{ $isSelected ? 'selected' : '' }}>
                                        {{ ['admin' => 'Admin', 'teacher' => "O'qituvchi", 'student' => "O'quvchi", 'author' => "Muallif", 'reader' => "O'quvchi (reader)", 'moderator' => 'Moderator'][$role->name] ?? $role->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-slate-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </div>
                        </div>
                        @error('role')
                            <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Points Input --}}
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="points" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">
                                Ball Balansi
                            </label>
                            <span class="text-[11px] text-amber-400 font-mono font-semibold">Hozirgi: {{ number_format($user->total_points) }}</span>
                        </div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-3 flex items-center justify-center pointer-events-none text-amber-400">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                </svg>
                            </div>
                            <input type="number" name="points" id="points" value="{{ $user->total_points }}" min="0"
                                   class="w-full pl-9 pr-3.5 py-2.5 bg-slate-800 border border-slate-700 rounded-xl text-xs sm:text-sm text-slate-200 focus:outline-none focus:border-indigo-500 font-mono transition-all">
                        </div>
                        <p class="mt-1 text-[10px] text-slate-500">Yangi qiymat kiritilsa, farq avtomatik tranzaksiya tarixiga qo'shiladi.</p>
                    </div>

                    {{-- Coins Input --}}
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="coins" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">
                                Tanga Balansi
                            </label>
                            <span class="text-[11px] text-yellow-400 font-mono font-semibold">Hozirgi: {{ number_format($user->coin_balance) }}</span>
                        </div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-3 flex items-center justify-center pointer-events-none text-yellow-400">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="9"/>
                                    <circle cx="12" cy="12" r="4.5"/>
                                </svg>
                            </div>
                            <input type="number" name="coins" id="coins" value="{{ $user->coin_balance }}" min="0"
                                   class="w-full pl-9 pr-3.5 py-2.5 bg-slate-800 border border-slate-700 rounded-xl text-xs sm:text-sm text-slate-200 focus:outline-none focus:border-amber-500 font-mono transition-all">
                        </div>
                        <p class="mt-1 text-[10px] text-slate-500">Tanga o'zgarishi ham kirim/chiqim tranzaksiyasi sifatida qayd etiladi.</p>
                    </div>

                    <button type="submit"
                            class="w-full flex items-center justify-center gap-2 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold rounded-xl text-xs transition-all shadow-md shadow-indigo-900/30">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        O'zgarishlarni saqlash
                    </button>
                </form>
            </div>

            {{-- 3. Ban Management Card --}}
            <div class="bg-slate-900 border {{ $user->isBanned() ? 'border-amber-500/40 bg-amber-950/10' : 'border-slate-800' }} rounded-2xl p-4.5 shadow-lg">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-semibold {{ $user->isBanned() ? 'text-amber-400' : 'text-slate-400' }} uppercase tracking-wider flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                        </svg>
                        Bloklash holati
                    </p>
                    @if($user->isBanned())
                        <span class="px-2 py-0.5 text-[11px] font-bold rounded-md bg-red-500/20 text-red-400 border border-red-500/30">
                            Bloklangan
                        </span>
                    @else
                        <span class="px-2 py-0.5 text-[11px] font-medium rounded-md bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                            Faol
                        </span>
                    @endif
                </div>

                @if($user->isBanned())
                    <div class="space-y-2 text-xs mb-3">
                        <div class="bg-slate-800/80 rounded-xl p-3 border border-slate-700/50">
                            <p class="text-slate-400 text-[11px]">Qolgan muddat:</p>
                            <p class="text-amber-300 font-bold mt-0.5 text-xs">{{ $user->ban_remaining }}</p>
                            @if($user->ban_reason)
                                <p class="text-slate-400 text-[11px] mt-2">Sabab:</p>
                                <p class="text-slate-200 italic mt-0.5 text-[11px]">"{{ $user->ban_reason }}"</p>
                            @endif
                        </div>
                    </div>
                    <form method="POST" action="{{ route('admin.users.unban', $user) }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center justify-center gap-2 py-2.5 bg-emerald-600/20 border border-emerald-500/30 text-emerald-400 hover:bg-emerald-600/30 rounded-xl text-xs font-semibold transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/>
                            </svg>
                            Banni bekor qilish
                        </button>
                    </form>
                @elseif($user->id === auth()->id())
                    <p class="text-xs text-slate-500 py-1">O'zingizni bloklay olmaysiz.</p>
                @else
                    <div x-data="{ open: false }">
                        <button type="button" @click="open = !open"
                                class="w-full flex items-center justify-center gap-2 py-2.5 bg-amber-500/10 border border-amber-500/20 text-amber-400 hover:bg-amber-500/20 rounded-xl text-xs font-semibold transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <span x-text="open ? 'Bekor qilish' : 'Foydalanuvchini bloklash (Ban)'"></span>
                        </button>

                        <div x-show="open" x-cloak class="mt-3 space-y-3 pt-3 border-t border-slate-800">
                            <form method="POST" action="{{ route('admin.users.ban', $user) }}" class="space-y-3">
                                @csrf
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-400 mb-1">Muddat</label>
                                    <select name="duration" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-2.5 py-2 text-xs text-white focus:outline-none focus:border-amber-500">
                                        <option value="1_hour">1 soat</option>
                                        <option value="1_day" selected>1 kun</option>
                                        <option value="1_week">1 hafta</option>
                                        <option value="1_month">1 oy</option>
                                        <option value="permanent">Doimiy (butun umrga)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-400 mb-1">Sabab</label>
                                    <textarea name="reason" rows="2" placeholder="Masalan: qoidabuzarlik yoki haqorat..." class="w-full bg-slate-800 border border-slate-700 rounded-lg p-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-amber-500"></textarea>
                                </div>
                                <button type="submit" class="w-full py-2 bg-amber-600 hover:bg-amber-500 text-white rounded-lg text-xs font-bold transition-all shadow-md shadow-amber-900/40">
                                    Bloklashni tasdiqlash
                                </button>
                            </form>
                        </div>
                    </div>
                @endif
            </div>

            {{-- 4. Danger Zone (Delete user) --}}
            <div class="bg-slate-900 border border-red-500/20 rounded-2xl p-4.5 shadow-lg">
                <p class="text-xs font-semibold text-red-400 uppercase tracking-wider mb-2.5 flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Xavfli zona
                </p>
                <div x-data="{ confirm: false }">
                    <button x-show="!confirm" @click="confirm = true" type="button"
                            class="w-full flex items-center justify-center gap-2 py-2.5 bg-red-500/10 border border-red-500/20 text-red-400 hover:bg-red-500/20 rounded-xl text-xs font-medium transition-all">
                        Foydalanuvchini tizimdan o'chirish
                    </button>
                    <div x-show="confirm" class="space-y-2" style="display:none;">
                        <p class="text-xs text-red-400 text-center font-medium">Bu amalni bekor qilib bo'lmaydi. Ishonchingiz komilmi?</p>
                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full py-2 bg-red-600 hover:bg-red-500 text-white rounded-xl text-xs font-semibold transition-colors shadow">
                                Ha, butunlay o'chirilsin
                            </button>
                        </form>
                        <button @click="confirm = false" type="button" class="w-full py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl text-xs font-medium transition-colors border border-slate-700">
                            Bekor qilish
                        </button>
                    </div>
                </div>
            </div>

        </div>

        {{-- ==================== RIGHT COLUMN: 360° Data Tabs (8 cols) ==================== --}}
        <div class="lg:col-span-8 space-y-4" x-data="{ activeTab: 'shop' }">

            {{-- Tab Navigation Pills --}}
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-2 flex items-center gap-1.5 overflow-x-auto shadow-md">
                <button type="button" @click="activeTab = 'shop'"
                        :class="activeTab === 'shop' ? 'bg-amber-500/15 text-amber-300 border-amber-500/30' : 'text-slate-400 hover:text-white border-transparent'"
                        class="px-3.5 py-2 rounded-xl text-xs font-semibold border transition-all flex items-center gap-2 whitespace-nowrap">
                    <span>🛍️</span>
                    <span>Do'kon & Jihozlar</span>
                    <span class="px-1.5 py-0.5 rounded-full text-[10px] bg-slate-800 text-slate-300 font-mono">{{ $userItems->count() }}</span>
                </button>

                <button type="button" @click="activeTab = 'reading'"
                        :class="activeTab === 'reading' ? 'bg-indigo-500/15 text-indigo-300 border-indigo-500/30' : 'text-slate-400 hover:text-white border-transparent'"
                        class="px-3.5 py-2 rounded-xl text-xs font-semibold border transition-all flex items-center gap-2 whitespace-nowrap">
                    <span>📚</span>
                    <span>Mutolaa & Kitoblar</span>
                    <span class="px-1.5 py-0.5 rounded-full text-[10px] bg-slate-800 text-slate-300 font-mono">{{ $readingProgress->count() }}</span>
                </button>

                <button type="button" @click="activeTab = 'quizzes'"
                        :class="activeTab === 'quizzes' ? 'bg-emerald-500/15 text-emerald-300 border-emerald-500/30' : 'text-slate-400 hover:text-white border-transparent'"
                        class="px-3.5 py-2 rounded-xl text-xs font-semibold border transition-all flex items-center gap-2 whitespace-nowrap">
                    <span>🧠</span>
                    <span>Viktorinalar</span>
                    <span class="px-1.5 py-0.5 rounded-full text-[10px] bg-slate-800 text-slate-300 font-mono">{{ $quizTotalCount }}</span>
                </button>

                <button type="button" @click="activeTab = 'transactions'"
                        :class="activeTab === 'transactions' ? 'bg-yellow-500/15 text-yellow-300 border-yellow-500/30' : 'text-slate-400 hover:text-white border-transparent'"
                        class="px-3.5 py-2 rounded-xl text-xs font-semibold border transition-all flex items-center gap-2 whitespace-nowrap">
                    <span>💰</span>
                    <span>Moliya & Tranzaksiyalar</span>
                </button>

                <button type="button" @click="activeTab = 'activity'"
                        :class="activeTab === 'activity' ? 'bg-purple-500/15 text-purple-300 border-purple-500/30' : 'text-slate-400 hover:text-white border-transparent'"
                        class="px-3.5 py-2 rounded-xl text-xs font-semibold border transition-all flex items-center gap-2 whitespace-nowrap">
                    <span>⚡</span>
                    <span>Boshqa Faolliklar</span>
                </button>
            </div>

            {{-- ==================== TAB 1: DO'KON & JİHOZLAR ==================== --}}
            <div x-show="activeTab === 'shop'" class="space-y-5">

                {{-- A. Currently Equipped Visuals (Faol jihozlar) --}}
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-lg">
                    <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-800">
                        <div>
                            <h3 class="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
                                <span class="text-amber-400">✨</span>
                                Hozir kiyilgan vizuallar (Faol Jihozlar)
                            </h3>
                            <p class="text-xs text-slate-400 mt-0.5">Foydalanuvchi hozirda o'z profilida faollashtirgan buyumlar</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                        {{-- Slot 1: Avatar Ramkasi --}}
                        @php $eqFrame = $equipment->get('avatar_frame') ?? $equipment->get('frame'); @endphp
                        <div class="p-3.5 rounded-xl bg-slate-800/40 border {{ $eqFrame ? 'border-amber-500/30 bg-amber-500/5' : 'border-slate-800' }}">
                            <div class="flex items-center justify-between text-xs text-slate-400 mb-1.5">
                                <span class="font-semibold">Avatar Ramkasi</span>
                                <span class="text-[10px] font-mono uppercase {{ $eqFrame ? 'text-amber-400' : 'text-slate-600' }}">
                                    {{ $eqFrame ? ($eqFrame->shopItem?->rarity ?? 'Faol') : "Yo'q" }}
                                </span>
                            </div>
                            @if($eqFrame)
                                <p class="text-sm font-bold text-white flex items-center gap-1.5">
                                    <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span>
                                    {{ $eqFrame->shopItem?->name_text ?? 'Noma\'lum ramka' }}
                                </p>
                            @else
                                <p class="text-xs text-slate-500 italic">Standart ramka</p>
                            @endif
                        </div>

                        {{-- Slot 2: Profil Mavzusi --}}
                        @php $eqTheme = $equipment->get('profile_theme') ?? $equipment->get('theme'); @endphp
                        <div class="p-3.5 rounded-xl bg-slate-800/40 border {{ $eqTheme ? 'border-purple-500/30 bg-purple-500/5' : 'border-slate-800' }}">
                            <div class="flex items-center justify-between text-xs text-slate-400 mb-1.5">
                                <span class="font-semibold">Profil Mavzusi</span>
                                <span class="text-[10px] font-mono uppercase {{ $eqTheme ? 'text-purple-400' : 'text-slate-600' }}">
                                    {{ $eqTheme ? ($eqTheme->shopItem?->rarity ?? 'Faol') : "Yo'q" }}
                                </span>
                            </div>
                            @if($eqTheme)
                                <p class="text-sm font-bold text-white flex items-center gap-1.5">
                                    <span class="w-2.5 h-2.5 rounded-full bg-purple-400"></span>
                                    {{ $eqTheme->shopItem?->name_text ?? 'Noma\'lum mavzu' }}
                                </p>
                            @else
                                <p class="text-xs text-slate-500 italic">Standart mavzu</p>
                            @endif
                        </div>

                        {{-- Slot 3: Maxsus Unvon (Title) --}}
                        @php $eqTitle = $equipment->get('title'); @endphp
                        <div class="p-3.5 rounded-xl bg-slate-800/40 border {{ $eqTitle ? 'border-blue-500/30 bg-blue-500/5' : 'border-slate-800' }}">
                            <div class="flex items-center justify-between text-xs text-slate-400 mb-1.5">
                                <span class="font-semibold">Maxsus Unvon</span>
                                <span class="text-[10px] font-mono uppercase {{ $eqTitle ? 'text-blue-400' : 'text-slate-600' }}">
                                    {{ $eqTitle ? ($eqTitle->shopItem?->rarity ?? 'Faol') : "Yo'q" }}
                                </span>
                            </div>
                            @if($eqTitle)
                                <p class="text-sm font-bold text-blue-300 flex items-center gap-1.5">
                                    <span>✦</span>
                                    {{ $eqTitle->shopItem?->name_text ?? 'Noma\'lum unvon' }}
                                </p>
                            @else
                                <p class="text-xs text-slate-500 italic">Unvon tanlanmagan</p>
                            @endif
                        </div>

                        {{-- Slot 4: Ism Rangi (Name Color) --}}
                        @php $eqNameColor = $equipment->get('name_color'); @endphp
                        <div class="p-3.5 rounded-xl bg-slate-800/40 border {{ $eqNameColor ? 'border-emerald-500/30 bg-emerald-500/5' : 'border-slate-800' }}">
                            <div class="flex items-center justify-between text-xs text-slate-400 mb-1.5">
                                <span class="font-semibold">Ism Rangi</span>
                                <span class="text-[10px] font-mono uppercase {{ $eqNameColor ? 'text-emerald-400' : 'text-slate-600' }}">
                                    {{ $eqNameColor ? 'Faol' : "Yo'q" }}
                                </span>
                            </div>
                            @if($eqNameColor)
                                <div class="flex items-center gap-2">
                                    <span class="w-3.5 h-3.5 rounded-full border border-white/30" style="background-color: {{ $user->equipped_name_color ?? '#fff' }}"></span>
                                    <span class="text-sm font-bold" style="color: {{ $user->equipped_name_color ?? '#fff' }}">
                                        {{ $eqNameColor->shopItem?->name_text ?? 'Maxsus rang' }}
                                    </span>
                                </div>
                            @else
                                <p class="text-xs text-slate-500 italic">Standart oq rang</p>
                            @endif
                        </div>

                        {{-- Slot 5: Chat Pufagi (Bubble) --}}
                        @php $eqBubble = $equipment->get('chat_bubble'); @endphp
                        <div class="p-3.5 rounded-xl bg-slate-800/40 border {{ $eqBubble ? 'border-cyan-500/30 bg-cyan-500/5' : 'border-slate-800' }}">
                            <div class="flex items-center justify-between text-xs text-slate-400 mb-1.5">
                                <span class="font-semibold">Chat Pufagi</span>
                                <span class="text-[10px] font-mono uppercase {{ $eqBubble ? 'text-cyan-400' : 'text-slate-600' }}">
                                    {{ $eqBubble ? ($eqBubble->shopItem?->rarity ?? 'Faol') : "Yo'q" }}
                                </span>
                            </div>
                            @if($eqBubble)
                                <p class="text-sm font-bold text-white flex items-center gap-1.5">
                                    <span class="w-2.5 h-2.5 rounded-full bg-cyan-400"></span>
                                    {{ $eqBubble->shopItem?->name_text ?? 'Maxsus pufak' }}
                                </p>
                            @else
                                <p class="text-xs text-slate-500 italic">Standart pufak</p>
                            @endif
                        </div>

                        {{-- Slot 6: Kitobxondagi Aksent Rangi --}}
                        @php $eqAccent = $equipment->get('accent') ?? $equipment->get('reader_palette'); @endphp
                        <div class="p-3.5 rounded-xl bg-slate-800/40 border {{ $eqAccent ? 'border-pink-500/30 bg-pink-500/5' : 'border-slate-800' }}">
                            <div class="flex items-center justify-between text-xs text-slate-400 mb-1.5">
                                <span class="font-semibold">Mutolaa Aksenti</span>
                                <span class="text-[10px] font-mono uppercase {{ $eqAccent ? 'text-pink-400' : 'text-slate-600' }}">
                                    {{ $eqAccent ? 'Faol' : "Yo'q" }}
                                </span>
                            </div>
                            @if($eqAccent)
                                <p class="text-sm font-bold text-white flex items-center gap-1.5">
                                    <span class="w-2.5 h-2.5 rounded-full bg-pink-400"></span>
                                    {{ $eqAccent->shopItem?->name_text ?? 'Maxsus aksent' }}
                                </p>
                            @else
                                <p class="text-xs text-slate-500 italic">Standart aksent</p>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- B. All Owned Items (Foydalanuvchi Inventari) --}}
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-lg">
                    <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-800">
                        <div>
                            <h3 class="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
                                <span class="text-amber-400">📦</span>
                                Egalik qilingan barcha buyumlar (Inventar)
                            </h3>
                            <p class="text-xs text-slate-400 mt-0.5">Foydalanuvchi hisobidagi do'kon buyumlari</p>
                        </div>
                        <span class="px-2.5 py-1 rounded-lg text-xs font-mono font-bold bg-slate-800 text-amber-400 border border-slate-700">
                            Jami: {{ $userItems->count() }} ta
                        </span>
                    </div>

                    @if($userItems->isEmpty())
                        <div class="text-center py-8 px-4 rounded-xl border border-dashed border-slate-800">
                            <p class="text-2xl mb-1.5">🛍️</p>
                            <p class="text-xs text-slate-400 font-medium">Foydalanuvchida hali hech qanday do'kon buyumi mavjud emas.</p>
                        </div>
                    @else
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @foreach($userItems as $uItem)
                                @php
                                    $item = $uItem->shopItem;
                                    $rarity = $item?->rarity ?? 'common';
                                    $rarityClasses = [
                                        'common'    => 'border-slate-700 bg-slate-800/40 text-slate-300',
                                        'rare'      => 'border-blue-500/30 bg-blue-500/5 text-blue-300',
                                        'epic'      => 'border-purple-500/30 bg-purple-500/5 text-purple-300',
                                        'legendary' => 'border-amber-500/30 bg-amber-500/5 text-amber-300',
                                    ];
                                    $rClass = $rarityClasses[$rarity] ?? $rarityClasses['common'];
                                @endphp
                                <div class="p-3.5 rounded-xl border {{ $rClass }} flex flex-col justify-between transition-all hover:border-slate-600">
                                    <div class="flex items-start justify-between gap-2">
                                        <div>
                                            <p class="text-sm font-bold text-white">
                                                {{ $item?->name_text ?? 'Buyum #' . $uItem->shop_item_id }}
                                            </p>
                                            <p class="text-[11px] text-slate-400 font-mono mt-0.5">
                                                Toifa: <span class="text-slate-300 uppercase">{{ $item?->category ?? 'Noma\'lum' }}</span>
                                                @if($item?->slot)
                                                    • Slot: <span class="text-slate-300">{{ $item->slot }}</span>
                                                @endif
                                            </p>
                                        </div>
                                        <div class="text-right">
                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-mono font-bold bg-slate-800 border border-slate-700 text-amber-400">
                                                x{{ $uItem->quantity }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="mt-3 pt-2.5 border-t border-slate-800/60 flex items-center justify-between text-[11px] text-slate-400">
                                        <span>Olingan: {{ $uItem->acquired_at ? $uItem->acquired_at->format('d.m.Y H:i') : '-' }}</span>
                                        @if($uItem->expires_at)
                                            <span class="{{ $uItem->isExpired() ? 'text-red-400 font-bold' : 'text-slate-300 font-mono' }}">
                                                {{ $uItem->isExpired() ? 'Tugagan' : $uItem->expires_at->format('d.m.Y') . ' gacha' }}
                                            </span>
                                        @else
                                            <span class="text-emerald-400 font-medium">Doimiy</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- C. Recent Purchases Table (Xaridlar tarixi) --}}
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-lg">
                    <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-800">
                        <div>
                            <h3 class="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
                                <span class="text-yellow-400">🪙</span>
                                So'nggi xaridlar tarixi (Do'kon cheklari)
                            </h3>
                            <p class="text-xs text-slate-400 mt-0.5">Foydalanuvchining do'kondan amalga oshirgan xaridlari</p>
                        </div>
                        <span class="text-xs text-slate-500 font-mono">So'nggi 20 ta</span>
                    </div>

                    @if($shopPurchases->isEmpty())
                        <div class="text-center py-6 px-4 rounded-xl border border-dashed border-slate-800 text-xs text-slate-500">
                            Xaridlar tarixi bo'sh.
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead>
                                    <tr class="border-b border-slate-800 text-slate-400 font-mono uppercase text-[11px]">
                                        <th class="pb-2 font-semibold">Buyum</th>
                                        <th class="pb-2 font-semibold">Miqdor</th>
                                        <th class="pb-2 font-semibold">To'langan Tanga</th>
                                        <th class="pb-2 font-semibold">Token / Chek</th>
                                        <th class="pb-2 font-semibold text-right">Vaqt</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-800/60 font-mono">
                                    @foreach($shopPurchases as $purchase)
                                        <tr class="hover:bg-slate-800/30 transition-colors">
                                            <td class="py-2.5 font-sans font-semibold text-white">
                                                {{ $purchase->shopItem?->name_text ?? 'O\'chirilgan buyum #' . $purchase->shop_item_id }}
                                            </td>
                                            <td class="py-2.5 text-slate-300">
                                                x{{ $purchase->quantity }}
                                            </td>
                                            <td class="py-2.5 text-yellow-400 font-bold">
                                                {{ number_format($purchase->price_paid) }} tanga
                                            </td>
                                            <td class="py-2.5 text-slate-400 text-[11px]" title="{{ $purchase->purchase_token }}">
                                                {{ $purchase->purchase_token ?: ('#' . $purchase->id) }}
                                            </td>
                                            <td class="py-2.5 text-slate-400 text-right">
                                                {{ $purchase->created_at ? $purchase->created_at->format('d.m.Y H:i') : '-' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

            </div>

            {{-- ==================== TAB 2: MUTOLAA & KITOBLAR ==================== --}}
            <div x-show="activeTab === 'reading'" class="space-y-5" style="display: none;">

                {{-- Reading Progress List --}}
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-lg">
                    <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-800">
                        <div>
                            <h3 class="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
                                <span class="text-indigo-400">📖</span>
                                Mutolaa qilinayotgan va tugatilgan kitoblar
                            </h3>
                            <p class="text-xs text-slate-400 mt-0.5">O'qilgan sahifalar, foiz va oxirgi faollik</p>
                        </div>
                        <span class="px-2.5 py-1 rounded-lg text-xs font-mono font-bold bg-slate-800 text-indigo-300 border border-slate-700">
                            Jami: {{ $readingProgress->count() }} ta kitob
                        </span>
                    </div>

                    @if($readingProgress->isEmpty())
                        <div class="text-center py-8 px-4 rounded-xl border border-dashed border-slate-800 text-xs text-slate-400">
                            Foydalanuvchi hali biron bir kitob mutolaasini boshlamagan.
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach($readingProgress as $prog)
                                @php
                                    $book = $prog->book;
                                    $percent = (float) $prog->percent_complete;
                                    $isFinished = $percent >= 100;
                                @endphp
                                <div class="p-4 rounded-xl bg-slate-800/40 border border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:border-slate-700 transition-colors">
                                    <div class="flex items-center gap-3">
                                        @if($book && $book->cover_image)
                                            <img src="{{ asset('storage/' . $book->cover_image) }}" alt="{{ $book->title }}"
                                                 class="w-12 h-16 rounded-lg object-cover border border-slate-700 flex-shrink-0 shadow">
                                        @else
                                            <div class="w-12 h-16 rounded-lg bg-slate-800 border border-slate-700 flex items-center justify-center text-slate-500 font-bold text-lg flex-shrink-0">
                                                📖
                                            </div>
                                        @endif
                                        <div>
                                            <h4 class="text-sm font-bold text-white">
                                                {{ $book?->title ?? 'Kitob #' . $prog->book_id }}
                                            </h4>
                                            <p class="text-xs text-slate-400 mt-0.5">
                                                Muallif: <span class="text-slate-300">{{ $book?->author ?? 'Noma\'lum' }}</span>
                                            </p>
                                            <p class="text-[11px] text-slate-500 font-mono mt-1">
                                                Oxirgi mutolaa: {{ $prog->updated_at ? $prog->updated_at->diffForHumans() : '-' }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="sm:text-right sm:w-48 flex-shrink-0">
                                        <div class="flex items-center justify-between sm:justify-end gap-2 mb-1.5">
                                            @if($isFinished)
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/15 text-emerald-400 border border-emerald-500/25">
                                                    ✓ Tugatilgan
                                                </span>
                                            @else
                                                <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-indigo-500/15 text-indigo-400 border border-indigo-500/25">
                                                    Mutolaada
                                                </span>
                                            @endif
                                            <span class="text-xs font-mono font-bold {{ $isFinished ? 'text-emerald-400' : 'text-indigo-400' }}">
                                                {{ number_format($percent, 1) }}%
                                            </span>
                                        </div>
                                        <div class="w-full bg-slate-700/60 rounded-full h-2 overflow-hidden">
                                            <div class="{{ $isFinished ? 'bg-emerald-500' : 'bg-indigo-500' }} h-2 rounded-full transition-all"
                                                 style="width: {{ min(100, $percent) }}%"></div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Book Comments --}}
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-lg">
                    <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-800">
                        <div>
                            <h3 class="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
                                <span class="text-blue-400">💬</span>
                                Kitoblarga yozilgan sharh va izohlar
                            </h3>
                            <p class="text-xs text-slate-400 mt-0.5">Foydalanuvchi qoldirgan mulohazalar</p>
                        </div>
                        <span class="text-xs text-slate-500 font-mono">So'nggi {{ $bookComments->count() }} ta</span>
                    </div>

                    @if($bookComments->isEmpty())
                        <div class="text-center py-6 px-4 rounded-xl border border-dashed border-slate-800 text-xs text-slate-500">
                            Foydalanuvchi hali kitoblarga izoh yozmagan.
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach($bookComments as $comment)
                                <div class="p-3.5 rounded-xl bg-slate-800/40 border border-slate-800 hover:border-slate-700 transition-colors">
                                    <div class="flex items-center justify-between gap-2 text-xs mb-1.5">
                                        <span class="font-bold text-amber-400">
                                            📖 {{ $comment->book?->title ?? 'Kitob' }}
                                        </span>
                                        <span class="text-[11px] text-slate-500 font-mono">
                                            {{ $comment->created_at->format('d.m.Y H:i') }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-slate-200 italic bg-slate-800/60 p-2.5 rounded-lg border border-slate-700/50">
                                        "{{ $comment->comment }}"
                                    </p>
                                    <div class="mt-2 flex items-center gap-3 text-[11px] text-slate-400">
                                        @if($comment->rating)
                                            <span class="text-amber-400 font-semibold">Baho: {{ $comment->rating }} ★</span>
                                        @endif
                                        <span>❤️ {{ $comment->likes_count ?? 0 }} layk</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

            </div>

            {{-- ==================== TAB 3: VIKTORINALAR ==================== --}}
            <div x-show="activeTab === 'quizzes'" class="space-y-5" style="display: none;">

                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-lg">
                    <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-800">
                        <div>
                            <h3 class="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
                                <span class="text-emerald-400">🧠</span>
                                Viktorina va test natijalari
                            </h3>
                            <p class="text-xs text-slate-400 mt-0.5">Topshirilgan testlar, to'plangan ballar va o'tish ko'rsatkichi</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-1 rounded-lg text-xs font-mono font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                O'tgan: {{ $quizPassedCount }} / {{ $quizTotalCount }}
                            </span>
                        </div>
                    </div>

                    @if($quizAttempts->isEmpty())
                        <div class="text-center py-8 px-4 rounded-xl border border-dashed border-slate-800 text-xs text-slate-400">
                            Foydalanuvchi hali birorta ham viktorina topshirmagan.
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead>
                                    <tr class="border-b border-slate-800 text-slate-400 font-mono uppercase text-[11px]">
                                        <th class="pb-2 font-semibold">Test / Kitob</th>
                                        <th class="pb-2 font-semibold">Ball</th>
                                        <th class="pb-2 font-semibold">Natija Foizi</th>
                                        <th class="pb-2 font-semibold">Holat</th>
                                        <th class="pb-2 font-semibold text-right">Topshirilgan Sana</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-800/60 font-mono">
                                    @foreach($quizAttempts as $attempt)
                                        @php
                                            $percent = (float) $attempt->percent;
                                            $isPassed = $percent >= 70;
                                        @endphp
                                        <tr class="hover:bg-slate-800/30 transition-colors">
                                            <td class="py-2.5 font-sans">
                                                <p class="font-bold text-white">
                                                    {{ $attempt->quiz?->title ?? 'Mustaqil Test #' . $attempt->quiz_id }}
                                                </p>
                                                <p class="text-[11px] text-slate-400">
                                                    Kitob: {{ $attempt->quiz?->book?->title ?? 'Umumiy' }}
                                                </p>
                                            </td>
                                            <td class="py-2.5 text-slate-200 font-bold">
                                                {{ $attempt->score }} / {{ $attempt->max_score }}
                                            </td>
                                            <td class="py-2.5">
                                                <span class="font-bold {{ $isPassed ? 'text-emerald-400' : 'text-red-400' }}">
                                                    {{ number_format($percent, 1) }}%
                                                </span>
                                            </td>
                                            <td class="py-2.5">
                                                @if($isPassed)
                                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                                                        ✓ Muvaffaqiyatli
                                                    </span>
                                                @else
                                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-red-500/20 text-red-400 border border-red-500/30">
                                                        ✗ O'tmadi
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="py-2.5 text-slate-400 text-right">
                                                {{ $attempt->completed_at ? $attempt->completed_at->format('d.m.Y H:i') : ($attempt->created_at ? $attempt->created_at->format('d.m.Y H:i') : '-') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

            </div>

            {{-- ==================== TAB 4: MOLIYA & TRANZAKSIYALAR ==================== --}}
            <div x-show="activeTab === 'transactions'" class="space-y-5" style="display: none;">

                {{-- Coin Transactions --}}
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-lg">
                    <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-800">
                        <div>
                            <h3 class="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
                                <span class="text-yellow-400">🪙</span>
                                Tanga tranzaksiyalari (Kirim va Chiqim)
                            </h3>
                            <p class="text-xs text-slate-400 mt-0.5">Foydalanuvchining so'nggi 20 ta tanga harakati</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 rounded text-[11px] font-mono bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                +{{ number_format($totalEarnedCoins) }} kirim
                            </span>
                            <span class="px-2 py-0.5 rounded text-[11px] font-mono bg-red-500/10 text-red-400 border border-red-500/20">
                                -{{ number_format($totalSpentCoins) }} chiqim
                            </span>
                        </div>
                    </div>

                    @if($coinTransactions->isEmpty())
                        <div class="text-center py-6 px-4 rounded-xl border border-dashed border-slate-800 text-xs text-slate-500">
                            Tanga tranzaksiyalari mavjud emas.
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead>
                                    <tr class="border-b border-slate-800 text-slate-400 font-mono uppercase text-[11px]">
                                        <th class="pb-2 font-semibold">Miqdor</th>
                                        <th class="pb-2 font-semibold">Manba</th>
                                        <th class="pb-2 font-semibold">Tavsif / Izoh</th>
                                        <th class="pb-2 font-semibold">Qoldiq</th>
                                        <th class="pb-2 font-semibold text-right">Sana</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-800/60 font-mono">
                                    @foreach($coinTransactions as $tx)
                                        <tr class="hover:bg-slate-800/30 transition-colors">
                                            <td class="py-2.5 font-bold {{ $tx->coins > 0 ? 'text-emerald-400' : 'text-red-400' }}">
                                                {{ $tx->coins > 0 ? '+' : '' }}{{ $tx->coins }}
                                            </td>
                                            <td class="py-2.5 text-slate-300">
                                                <span class="px-2 py-0.5 rounded bg-slate-800 text-[10px] text-slate-300 border border-slate-700">
                                                    {{ $tx->source }}
                                                </span>
                                            </td>
                                            <td class="py-2.5 font-sans text-slate-300 max-w-xs truncate">
                                                {{ $tx->description ?: '-' }}
                                            </td>
                                            <td class="py-2.5 text-yellow-400">
                                                {{ $tx->balance_after !== null ? number_format($tx->balance_after) : '-' }}
                                            </td>
                                            <td class="py-2.5 text-slate-400 text-right">
                                                {{ $tx->created_at->format('d.m.Y H:i') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                {{-- Point Transactions --}}
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-lg">
                    <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-800">
                        <div>
                            <h3 class="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
                                <span class="text-amber-400">⭐</span>
                                Ball tranzaksiyalari (Kirim va Sarf)
                            </h3>
                            <p class="text-xs text-slate-400 mt-0.5">Mutolaa, testlar va faolliklar orqali to'plangan ballar</p>
                        </div>
                        <span class="text-xs text-slate-500 font-mono">So'nggi 20 ta</span>
                    </div>

                    @if($pointTransactions->isEmpty())
                        <div class="text-center py-6 px-4 rounded-xl border border-dashed border-slate-800 text-xs text-slate-500">
                            Ball tranzaksiyalari mavjud emas.
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead>
                                    <tr class="border-b border-slate-800 text-slate-400 font-mono uppercase text-[11px]">
                                        <th class="pb-2 font-semibold">Miqdor</th>
                                        <th class="pb-2 font-semibold">Manba</th>
                                        <th class="pb-2 font-semibold">Tavsif / Izoh</th>
                                        <th class="pb-2 font-semibold text-right">Sana</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-800/60 font-mono">
                                    @foreach($pointTransactions as $ptx)
                                        <tr class="hover:bg-slate-800/30 transition-colors">
                                            <td class="py-2.5 font-bold {{ $ptx->points > 0 ? 'text-amber-400' : 'text-red-400' }}">
                                                {{ $ptx->points > 0 ? '+' : '' }}{{ $ptx->points }}
                                            </td>
                                            <td class="py-2.5 text-slate-300">
                                                <span class="px-2 py-0.5 rounded bg-slate-800 text-[10px] text-slate-300 border border-slate-700">
                                                    {{ $ptx->source }}
                                                </span>
                                            </td>
                                            <td class="py-2.5 font-sans text-slate-300 max-w-sm truncate">
                                                {{ $ptx->description ?: '-' }}
                                            </td>
                                            <td class="py-2.5 text-slate-400 text-right">
                                                {{ $ptx->created_at->format('d.m.Y H:i') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

            </div>

            {{-- ==================== TAB 5: BOSHQA FAOLLIKLAR ==================== --}}
            <div x-show="activeTab === 'activity'" class="space-y-5" style="display: none;">

                {{-- Quick Summary Badges --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="p-3.5 rounded-xl bg-slate-900 border border-slate-800 text-center">
                        <p class="text-xl font-bold text-white font-mono">{{ $userNotesCount }}</p>
                        <p class="text-xs text-slate-400 mt-1">📝 Shaxsiy qaydlar</p>
                    </div>
                    <div class="p-3.5 rounded-xl bg-slate-900 border border-slate-800 text-center">
                        <p class="text-xl font-bold text-white font-mono">{{ $userBookmarksCount }}</p>
                        <p class="text-xs text-slate-400 mt-1">🔖 Xatcho'plar</p>
                    </div>
                    <div class="p-3.5 rounded-xl bg-slate-900 border border-slate-800 text-center">
                        <p class="text-xl font-bold text-white font-mono">{{ $userQuotesCount }}</p>
                        <p class="text-xs text-slate-400 mt-1">💬 Saqlangan iqtiboslar</p>
                    </div>
                </div>

                {{-- AI Book Assistant Questions --}}
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-lg">
                    <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-800">
                        <div>
                            <h3 class="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
                                <span class="text-purple-400">🤖</span>
                                AI Kitobxonga berilgan savollar
                            </h3>
                            <p class="text-xs text-slate-400 mt-0.5">Foydalanuvchining kitoblar bo'yicha sun'iy intellektga yuborgan so'rovlari</p>
                        </div>
                        <span class="text-xs text-slate-500 font-mono">So'nggi {{ $aiQuestions->count() }} ta</span>
                    </div>

                    @if($aiQuestions->isEmpty())
                        <div class="text-center py-6 px-4 rounded-xl border border-dashed border-slate-800 text-xs text-slate-500">
                            Foydalanuvchi hali AI kitobxonga murojaat qilmagan.
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach($aiQuestions as $aiQ)
                                <div class="p-3.5 rounded-xl bg-slate-800/40 border border-slate-800 hover:border-slate-700 transition-colors">
                                    <div class="flex items-center justify-between gap-2 text-xs mb-1.5">
                                        <span class="font-bold text-purple-300">
                                            📖 {{ $aiQ->book?->title ?? 'Umumiy' }}
                                        </span>
                                        <span class="text-[11px] text-slate-500 font-mono">
                                            {{ $aiQ->created_at->format('d.m.Y H:i') }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-slate-200">
                                        <span class="font-semibold text-slate-400">Savol:</span> "{{ $aiQ->message }}"
                                    </p>
                                    @if($aiQ->model || $aiQ->tokens_used)
                                        <div class="mt-2 text-[10px] text-slate-500 font-mono flex items-center gap-3">
                                            <span>Model: {{ $aiQ->model ?: 'standart' }}</span>
                                            <span>Tokenlar: {{ $aiQ->tokens_used }}</span>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Live Stream Questions --}}
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-lg">
                    <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-800">
                        <div>
                            <h3 class="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
                                <span class="text-red-400">🔴</span>
                                Jonli efirdagi savollar
                            </h3>
                            <p class="text-xs text-slate-400 mt-0.5">Jonli darslar va translatsiyalarda berilgan savollar</p>
                        </div>
                        <span class="text-xs text-slate-500 font-mono">So'nggi {{ $liveQuestions->count() }} ta</span>
                    </div>

                    @if($liveQuestions->isEmpty())
                        <div class="text-center py-6 px-4 rounded-xl border border-dashed border-slate-800 text-xs text-slate-500">
                            Jonli efirlarda berilgan savollar mavjud emas.
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach($liveQuestions as $lq)
                                <div class="p-3.5 rounded-xl bg-slate-800/40 border border-slate-800 hover:border-slate-700 transition-colors">
                                    <div class="flex items-center justify-between gap-2 text-xs mb-1.5">
                                        <span class="font-bold text-white">
                                            {{ $lq->liveEvent?->title ?? 'Jonli Efir #' . $lq->live_event_id }}
                                        </span>
                                        <div class="flex items-center gap-2">
                                            @if($lq->is_priority)
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30 flex items-center gap-1">
                                                    <svg class="w-3 h-3 text-amber-400" viewBox="0 0 20 20" fill="currentColor">
                                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                    </svg>
                                                    Ustuvor Savol
                                                </span>
                                            @endif
                                            <span class="text-[11px] text-slate-500 font-mono">
                                                {{ $lq->created_at->format('d.m.Y H:i') }}
                                            </span>
                                        </div>
                                    </div>
                                    <p class="text-xs text-slate-200">
                                        "{{ $lq->question }}"
                                    </p>
                                    <div class="mt-2 flex items-center justify-between text-[11px] text-slate-400">
                                        <span>❤️ {{ $lq->likes_count ?? 0 }} layk</span>
                                        <span>
                                            @if($lq->is_answered)
                                                <span class="text-emerald-400 font-bold">✓ Javob berildi</span>
                                            @else
                                                <span class="text-slate-500">Javob kutilmoqda</span>
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

            </div>

        </div>

    </div>

</div>

@endsection
