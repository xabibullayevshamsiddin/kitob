@extends('admin.layouts.app')

@section('title', $user->name . ' — Foydalanuvchi ma\'lumotlari')
@section('breadcrumb', 'Foydalanuvchi profili')

@section('content')

<div class="max-w-7xl mx-auto space-y-6 pb-32" x-data="{ tgModalOpen: false }">

    {{-- Top Bar: Back Link & Quick Actions --}}
    <div class="flex items-center justify-between gap-4 flex-wrap">
        <a href="{{ route('admin.users.index') }}" 
           class="inline-flex items-center gap-2 text-xs font-mono uppercase tracking-wider text-mist hover:text-paper transition-colors py-2 px-3 rounded-lg bg-ink-900 border border-ink-border hover:bg-ink-800">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span>Foydalanuvchilar ro'yxatiga qaytish</span>
        </a>

        <div class="flex items-center gap-2 flex-wrap">
            <button type="button"
                    @click="tgModalOpen = true"
                    class="px-3 py-1.5 rounded-lg bg-sky-500/15 hover:bg-sky-500/25 border border-sky-500/30 text-sky-300 text-xs font-mono font-bold inline-flex items-center gap-1.5 transition-colors">
                <span>✈️</span>
                <span>Botdan shaxsiy xabar / e'lon yuborish</span>
            </button>
            <span class="text-xs font-mono text-mist hidden sm:inline">Foydalanuvchi ID:</span>
            <span class="px-2.5 py-1 rounded-badge text-xs font-mono font-bold bg-ink-800 text-amber-400 border border-ink-border">
                #{{ $user->id }}
            </span>
        </div>
    </div>

    {{-- User Identity Hero Card --}}
    <div class="bg-ink-900 border border-ink-border rounded-panel p-6 shadow-sm relative overflow-hidden">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6 relative z-10">
            
            {{-- Left: Avatar + Details --}}
            <div class="flex items-center gap-5">
                @php
                    $equippedFrame = $equipment->get('avatar_frame') ?? $equipment->get('frame');
                    $equippedTheme = $equipment->get('profile_theme') ?? $equipment->get('theme');
                    $equippedTitle = $equipment->get('title');
                    $nameColor = $user->equipped_name_color;
                @endphp

                <div class="relative shrink-0">
                    @if ($user->avatar)
                        <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}"
                             class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl object-cover shadow-md border-2 {{ $equippedFrame ? 'border-amber-400 ring-2 ring-amber-400/40' : 'border-ink-border' }}">
                    @else
                        <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-ink-800 border-2 {{ $equippedFrame ? 'border-amber-400 ring-2 ring-amber-400/40' : 'border-ink-border' }} flex items-center justify-center text-2xl sm:text-3xl font-bold text-amber-400 shadow-md">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                    @endif

                    @if($equippedFrame)
                        <div class="absolute -bottom-1 -right-1 px-1.5 py-0.5 rounded bg-amber-500 text-[9px] font-bold text-ink-950 uppercase tracking-wider shadow">
                            Ramka
                        </div>
                    @endif
                </div>

                <div class="space-y-1">
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <h1 class="text-xl sm:text-2xl font-bold font-serif tracking-tight" style="{{ $nameColor ? 'color: ' . $nameColor . ';' : 'color: var(--paper, #F0EDE6);' }}">
                            {{ $user->name }}
                        </h1>

                        @if($user->isBanned())
                            <span class="px-2.5 py-0.5 rounded-badge text-[11px] font-bold bg-rose-500/15 text-rose-400 border border-rose-500/25 flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-400 animate-pulse"></span>
                                Bloklangan
                            </span>
                        @else
                            <span class="px-2.5 py-0.5 rounded-badge text-[11px] font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                Faol
                            </span>
                        @endif
                    </div>

                    <div class="flex items-center gap-3 text-xs text-mist font-mono flex-wrap">
                        @if($user->phone)
                            <a href="tel:{{ $user->phone }}" class="text-amber-400 hover:underline inline-flex items-center gap-1 font-semibold">
                                <span>📞</span>
                                <span>{{ \App\Support\PhoneNumber::format($user->phone) }}</span>
                            </a>
                        @endif
                        @if($user->isTelegramLinked())
                            <button type="button" @click="tgModalOpen = true" class="text-sky-400 hover:underline inline-flex items-center gap-1 font-semibold" title="Telegram Bot (@{{ config('telegram.bot_username') }}) orqali xabar yuborish">
                                <span>🤖</span>
                                <span>{{ $user->telegram_username ? '@' . $user->telegram_username : 'TG #' . $user->telegram_id }} (Botdan yozish)</span>
                            </button>
                        @endif
                        @if($user->email)
                            <span class="text-mist">{{ $user->email }}</span>
                        @endif
                        <span>•</span>
                        <span>{{ '@' . ($user->username ?? 'foydalanuvchi') }}</span>
                        <span>•</span>
                        <span>A'zo: {{ $user->created_at->format('d.m.Y') }}</span>
                    </div>

                    <div class="flex items-center gap-2 pt-1 flex-wrap">
                        @php
                            $currentRole = $user->roles->first()?->name ?? 'student';
                            $roleConfig = [
                                'admin'     => ['label' => 'Admin',             'class' => 'bg-red-500/15 text-red-400 border-red-500/25'],
                                'teacher'   => ['label' => "O'qituvchi",        'class' => 'bg-blue-500/15 text-blue-400 border-blue-500/25'],
                                'student'   => ['label' => "O'quvchi",          'class' => 'bg-emerald-500/15 text-emerald-400 border-emerald-500/20'],
                                'author'    => ['label' => "Muallif",           'class' => 'bg-amber-500/15 text-amber-400 border-amber-500/25'],
                                'moderator' => ['label' => "Moderator",         'class' => 'bg-violet-500/15 text-violet-400 border-violet-500/25'],
                                'reader'    => ['label' => "O'quvchi (reader)", 'class' => 'bg-ink-800 text-mist border-ink-border'],
                            ];
                            $rc = $roleConfig[$currentRole] ?? ['label' => $currentRole, 'class' => 'bg-ink-800 text-mist border-ink-border'];
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-badge text-xs font-semibold border {{ $rc['class'] }}">
                            Rol: {{ $rc['label'] }}
                        </span>

                        @if($equippedTitle)
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-badge text-xs font-semibold bg-amber-500/10 text-amber-300 border border-amber-500/25">
                                <span class="text-amber-400">✦</span>
                                {{ $equippedTitle->shopItem?->name_text ?? 'Unvon' }}
                            </span>
                        @endif

                        @if($equippedTheme)
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-badge text-xs font-mono bg-purple-500/10 text-purple-300 border border-purple-500/25">
                                🎨 {{ $equippedTheme->shopItem?->name_text ?? 'Mavzu' }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Right: Quick Stats Pills --}}
            <div class="flex items-center gap-3 shrink-0 pt-4 sm:pt-0 border-t sm:border-t-0 border-ink-border">
                <div class="px-4 py-3 rounded-xl bg-ink-800 border border-ink-border text-center min-w-[90px]">
                    <p class="text-base font-bold font-mono text-paper">{{ number_format($user->total_points) }}</p>
                    <p class="text-[10px] text-mist font-mono uppercase tracking-wider mt-0.5">Ball</p>
                </div>
                <div class="px-4 py-3 rounded-xl bg-ink-800 border border-ink-border text-center min-w-[90px]">
                    <p class="text-base font-bold font-mono text-amber-400">{{ number_format($user->coin_balance) }}</p>
                    <p class="text-[10px] text-mist font-mono uppercase tracking-wider mt-0.5">Tanga</p>
                </div>
                <div class="px-4 py-3 rounded-xl bg-ink-800 border border-ink-border text-center min-w-[90px]">
                    <p class="text-base font-bold font-mono text-emerald-400">{{ $currentStreak }} kun</p>
                    <p class="text-[10px] text-mist font-mono uppercase tracking-wider mt-0.5">Streak</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Top 4 KPI Metric Cards (Generous padding, clean spacing) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        
        {{-- Card 1: Leaderboard Reytingi --}}
        <div class="bg-ink-900 border border-ink-border rounded-panel p-5 shadow-sm flex flex-col justify-between hover:border-amber-500/30 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-xs font-mono uppercase tracking-wider text-mist">Umumiy Reyting</span>
                <div class="w-8 h-8 rounded-lg bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-400">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M5 3h14a1 1 0 0 1 1 1v2a5 5 0 0 1-5 5h-1a6.002 6.002 0 0 1-5 5.918V19h3a1 1 0 1 1 0 2H7a1 1 0 1 1 0-2h3v-2.082A6.002 6.002 0 0 1 5 11H4a5 5 0 0 1-5-5V4a1 1 0 0 1 1-1h5zm-1 3H3a3 3 0 0 0 3 3h1V6H4zm16 0h-3v3h1a3 3 0 0 0 3-3v0z"/>
                    </svg>
                </div>
            </div>
            <div class="my-3">
                <div class="flex items-baseline gap-2">
                    <span class="text-3xl font-bold font-mono text-amber-400">
                        #{{ $leaderboardRank }}
                    </span>
                    <span class="text-xs font-mono text-mist">
                        / {{ $totalUsersCount }} nafar ichida
                    </span>
                </div>
            </div>
            <div class="pt-3 border-t border-ink-border flex items-center justify-between text-xs">
                <span class="text-mist">Jami ball:</span>
                <span class="font-mono font-bold text-amber-400">{{ number_format($user->total_points) }} ball</span>
            </div>
        </div>

        {{-- Card 2: Mutolaa Vaqti & O'rtacha --}}
        <div class="bg-ink-900 border border-ink-border rounded-panel p-5 shadow-sm flex flex-col justify-between hover:border-indigo-500/30 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-xs font-mono uppercase tracking-wider text-mist">Mutolaa Vaqti</span>
                <div class="w-8 h-8 rounded-lg bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center text-indigo-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="my-3">
                @php
                    $hrs = floor($totalReadingMinutes / 60);
                    $mins = $totalReadingMinutes % 60;
                @endphp
                <div class="flex items-baseline gap-2">
                    <span class="text-3xl font-bold font-mono text-paper">
                        {{ $hrs > 0 ? $hrs . 's ' : '' }}{{ $mins }}d
                    </span>
                    <span class="text-xs text-mist">jami mutolaa</span>
                </div>
            </div>
            <div class="pt-3 border-t border-ink-border flex items-center justify-between text-xs">
                <span class="text-mist">Kunlik o'rtacha:</span>
                <span class="font-mono font-bold text-indigo-400">{{ $avgDailyMinutes }} daqiqa/kun</span>
            </div>
        </div>

        {{-- Card 3: Kitoblar & Streak --}}
        <div class="bg-ink-900 border border-ink-border rounded-panel p-5 shadow-sm flex flex-col justify-between hover:border-emerald-500/30 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-xs font-mono uppercase tracking-wider text-mist">Streak & Kitoblar</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/>
                    </svg>
                </div>
            </div>
            <div class="my-3">
                <div class="flex items-baseline gap-2">
                    <span class="text-3xl font-bold font-mono text-emerald-400">
                        {{ $currentStreak }} kun
                    </span>
                    <span class="text-xs text-mist">streak</span>
                </div>
            </div>
            <div class="pt-3 border-t border-ink-border flex items-center justify-between text-xs">
                <span class="text-mist">Kitoblar:</span>
                <span class="font-mono font-bold text-emerald-400">{{ $booksFinishedCount }} tugatilgan • {{ $booksInProgressCount }} o'qilmoqda</span>
            </div>
        </div>

        {{-- Card 4: Tangalar & Balans --}}
        <div class="bg-ink-900 border border-ink-border rounded-panel p-5 shadow-sm flex flex-col justify-between hover:border-yellow-500/30 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-xs font-mono uppercase tracking-wider text-mist">Tangalar & Inventar</span>
                <div class="w-8 h-8 rounded-lg bg-yellow-500/10 border border-yellow-500/20 flex items-center justify-center text-yellow-400">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="9"/>
                        <circle cx="12" cy="12" r="4.5"/>
                    </svg>
                </div>
            </div>
            <div class="my-3">
                <div class="flex items-baseline gap-2">
                    <span class="text-3xl font-bold font-mono text-yellow-400">
                        {{ number_format($user->coin_balance) }}
                    </span>
                    <span class="text-xs text-mist">tanga</span>
                </div>
            </div>
            <div class="pt-3 border-t border-ink-border flex items-center justify-between text-xs">
                <span class="text-mist">Do'kon buyumlari:</span>
                <span class="font-mono font-bold text-amber-400">{{ $userItems->count() }} ta egalikda</span>
            </div>
        </div>
    </div>

    {{-- Main 2-Column Layout (Left: Admin controls, Right: 360° Data Tabs) --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        {{-- ==================== LEFT COLUMN: Admin Controls (lg:col-span-4) ==================== --}}
        <div class="lg:col-span-4 space-y-5">

            {{-- 1. Rol va Balansni tahrirlash formasi --}}
            <div class="bg-ink-900 border border-ink-border rounded-panel p-6 shadow-sm">
                <div class="pb-3 mb-4 border-b border-ink-border">
                    <h2 class="text-sm font-bold text-paper uppercase tracking-wider font-mono flex items-center gap-2">
                        <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Rol va Balansni boshqarish
                    </h2>
                    <p class="text-xs text-mist mt-1">Foydalanuvchi rolini va hisobidagi ball/tangalarni o'zgartirish</p>
                </div>

                <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    {{-- Role Selection --}}
                    <div>
                        <label for="role" class="block text-xs font-semibold text-mist uppercase tracking-wider mb-2 font-mono">
                            Rol <span class="text-rose-400">*</span>
                        </label>
                        <div class="relative">
                            <select name="role" id="role"
                                    class="w-full appearance-none pl-3.5 pr-10 py-2.5 bg-ink-800 border border-ink-border rounded-xl text-xs sm:text-sm text-paper focus:outline-none focus:border-amber-500 transition-all cursor-pointer">
                                @foreach($roles as $role)
                                    @php
                                        $isSelected = $user->roles->first()?->name === $role->name;
                                    @endphp
                                    <option value="{{ $role->name }}" {{ $isSelected ? 'selected' : '' }}>
                                        {{ ['admin' => 'Admin', 'teacher' => "O'qituvchi", 'student' => "O'quvchi", 'author' => "Muallif", 'reader' => "O'quvchi (reader)", 'moderator' => 'Moderator'][$role->name] ?? $role->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-mist">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </div>
                        </div>
                        @error('role')
                            <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Points Input --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label for="points" class="block text-xs font-semibold text-mist uppercase tracking-wider font-mono">
                                Ball Balansi
                            </label>
                            <span class="text-xs text-amber-400 font-mono font-bold">Hozirgi: {{ number_format($user->total_points) }}</span>
                        </div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-3 flex items-center justify-center pointer-events-none text-amber-400">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                </svg>
                            </div>
                            <input type="number" name="points" id="points" value="{{ $user->total_points }}" min="0"
                                   class="w-full pl-9 pr-3.5 py-2.5 bg-ink-800 border border-ink-border rounded-xl text-sm text-paper focus:outline-none focus:border-amber-500 font-mono transition-all">
                        </div>
                        <p class="mt-1 text-[11px] text-mist">Yangi qiymat kiritilsa, farq avtomatik tranzaksiya tarixiga qayd etiladi.</p>
                    </div>

                    {{-- Coins Input --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label for="coins" class="block text-xs font-semibold text-mist uppercase tracking-wider font-mono">
                                Tanga Balansi
                            </label>
                            <span class="text-xs text-yellow-400 font-mono font-bold">Hozirgi: {{ number_format($user->coin_balance) }}</span>
                        </div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-3 flex items-center justify-center pointer-events-none text-yellow-400">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="9"/>
                                    <circle cx="12" cy="12" r="4.5"/>
                                </svg>
                            </div>
                            <input type="number" name="coins" id="coins" value="{{ $user->coin_balance }}" min="0"
                                   class="w-full pl-9 pr-3.5 py-2.5 bg-ink-800 border border-ink-border rounded-xl text-sm text-paper focus:outline-none focus:border-amber-500 font-mono transition-all">
                        </div>
                        <p class="mt-1 text-[11px] text-mist">Tanga o'zgarishi ham operatsiya tarixi jurnaliga tushadi.</p>
                    </div>

                    <div class="pt-2">
                        <button type="submit"
                                class="w-full flex items-center justify-center gap-2 py-3 bg-amber-500 hover:bg-amber-400 text-ink-950 font-bold rounded-xl text-xs transition-all shadow-md">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            O'zgarishlarni saqlash
                        </button>
                    </div>
                </form>
            </div>

            {{-- 2. Ban Management Card --}}
            <div class="bg-ink-900 border {{ $user->isBanned() ? 'border-amber-500/40 bg-amber-950/10' : 'border-ink-border' }} rounded-panel p-6 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-bold {{ $user->isBanned() ? 'text-amber-400' : 'text-mist' }} uppercase tracking-wider font-mono flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                        </svg>
                        Bloklash holati
                    </p>
                    @if($user->isBanned())
                        <span class="px-2 py-0.5 text-[11px] font-bold rounded-badge bg-rose-500/20 text-rose-400 border border-rose-500/30">
                            Bloklangan
                        </span>
                    @else
                        <span class="px-2 py-0.5 text-[11px] font-medium rounded-badge bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                            Faol
                        </span>
                    @endif
                </div>

                @if($user->isBanned())
                    <div class="space-y-2 text-xs mb-4">
                        <div class="bg-ink-800 rounded-xl p-3 border border-ink-border">
                            <p class="text-mist text-[11px]">Qolgan muddat:</p>
                            <p class="text-amber-400 font-bold font-mono mt-0.5 text-xs">{{ $user->ban_remaining }}</p>
                            @if($user->ban_reason)
                                <p class="text-mist text-[11px] mt-2">Sabab:</p>
                                <p class="text-paper italic mt-0.5 text-[11px]">"{{ $user->ban_reason }}"</p>
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
                    <p class="text-xs text-mist py-1">O'zingizni bloklay olmaysiz.</p>
                @else
                    <div x-data="{ open: false }">
                        <button type="button" @click="open = !open"
                                class="w-full flex items-center justify-center gap-2 py-2.5 bg-amber-500/10 border border-amber-500/20 text-amber-400 hover:bg-amber-500/20 rounded-xl text-xs font-semibold transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <span x-text="open ? 'Bekor qilish' : 'Foydalanuvchini bloklash (Ban)'"></span>
                        </button>

                        <div x-show="open" x-cloak class="mt-3 space-y-3 pt-3 border-t border-ink-border">
                            <form method="POST" action="{{ route('admin.users.ban', $user) }}" class="space-y-3">
                                @csrf
                                <div>
                                    <label class="block text-[11px] font-semibold text-mist mb-1">Muddat</label>
                                    <select name="duration" class="w-full bg-ink-800 border border-ink-border rounded-lg px-2.5 py-2 text-xs text-paper focus:outline-none focus:border-amber-500">
                                        <option value="1_hour">1 soat</option>
                                        <option value="1_day" selected>1 kun</option>
                                        <option value="1_week">1 hafta</option>
                                        <option value="1_month">1 oy</option>
                                        <option value="permanent">Doimiy (butun umrga)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-mist mb-1">Sabab</label>
                                    <textarea name="reason" rows="2" placeholder="Masalan: qoidabuzarlik yoki haqorat..." class="w-full bg-ink-800 border border-ink-border rounded-lg p-2 text-xs text-paper placeholder-mist/60 focus:outline-none focus:border-amber-500"></textarea>
                                </div>
                                <button type="submit" class="w-full py-2 bg-amber-600 hover:bg-amber-500 text-white rounded-lg text-xs font-bold transition-all shadow-md shadow-amber-900/40">
                                    Bloklashni tasdiqlash
                                </button>
                            </form>
                        </div>
                    </div>
                @endif
            </div>

            {{-- 3. Danger Zone (Delete user) --}}
            <div class="bg-ink-900 border border-rose-500/20 rounded-panel p-5 shadow-sm">
                <p class="text-xs font-semibold text-rose-400 uppercase tracking-wider mb-2 flex items-center gap-1.5 font-mono">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Xavfli zona
                </p>
                <div x-data="{ confirm: false }">
                    <button x-show="!confirm" @click="confirm = true" type="button"
                            class="w-full flex items-center justify-center gap-2 py-2.5 bg-rose-500/10 border border-rose-500/20 text-rose-400 hover:bg-rose-500/20 rounded-xl text-xs font-medium transition-all">
                        Foydalanuvchini tizimdan o'chirish
                    </button>
                    <div x-show="confirm" class="space-y-2 mt-2" style="display:none;">
                        <p class="text-xs text-rose-400 text-center font-medium">Bu amalni bekor qilib bo'lmaydi. Ishonchingiz komilmi?</p>
                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full py-2 bg-rose-600 hover:bg-rose-500 text-white rounded-xl text-xs font-semibold transition-colors shadow">
                                Ha, butunlay o'chirilsin
                            </button>
                        </form>
                        <button @click="confirm = false" type="button" class="w-full py-2 bg-ink-800 hover:bg-ink-700 text-mist rounded-xl text-xs font-medium transition-colors border border-ink-border">
                            Bekor qilish
                        </button>
                    </div>
                </div>
            </div>

        </div>

        {{-- ==================== RIGHT COLUMN: 360° Data Tabs (lg:col-span-8) ==================== --}}
        <div class="lg:col-span-8 space-y-5" x-data="{ activeTab: 'shop' }">

            {{-- Tab Navigation Bar (Flex-wrap, never cut off) --}}
            <div class="bg-ink-900 border border-ink-border rounded-panel p-2 flex flex-wrap items-center gap-2 shadow-sm">
                <button type="button" @click="activeTab = 'shop'"
                        :class="activeTab === 'shop' ? 'bg-ink-800 text-amber-400 border-amber-500/30' : 'text-mist hover:text-paper border-transparent'"
                        class="px-3.5 py-2 rounded-lg text-xs font-semibold border transition-all flex items-center gap-2">
                    <span>🛍️</span>
                    <span>Do'kon & Jihozlar</span>
                    <span class="px-1.5 py-0.5 rounded-badge text-[10px] bg-ink-950 text-mist font-mono">{{ $userItems->count() }}</span>
                </button>

                <button type="button" @click="activeTab = 'reading'"
                        :class="activeTab === 'reading' ? 'bg-ink-800 text-indigo-400 border-indigo-500/30' : 'text-mist hover:text-paper border-transparent'"
                        class="px-3.5 py-2 rounded-lg text-xs font-semibold border transition-all flex items-center gap-2">
                    <span>📚</span>
                    <span>Mutolaa & Kitoblar</span>
                    <span class="px-1.5 py-0.5 rounded-badge text-[10px] bg-ink-950 text-mist font-mono">{{ $readingProgress->count() }}</span>
                </button>

                <button type="button" @click="activeTab = 'quizzes'"
                        :class="activeTab === 'quizzes' ? 'bg-ink-800 text-emerald-400 border-emerald-500/30' : 'text-mist hover:text-paper border-transparent'"
                        class="px-3.5 py-2 rounded-lg text-xs font-semibold border transition-all flex items-center gap-2">
                    <span>🧠</span>
                    <span>Viktorinalar</span>
                    <span class="px-1.5 py-0.5 rounded-badge text-[10px] bg-ink-950 text-mist font-mono">{{ $quizTotalCount }}</span>
                </button>

                <button type="button" @click="activeTab = 'transactions'"
                        :class="activeTab === 'transactions' ? 'bg-ink-800 text-yellow-400 border-yellow-500/30' : 'text-mist hover:text-paper border-transparent'"
                        class="px-3.5 py-2 rounded-lg text-xs font-semibold border transition-all flex items-center gap-2">
                    <span>💰</span>
                    <span>Moliya & Tranzaksiyalar</span>
                </button>

                <button type="button" @click="activeTab = 'activity'"
                        :class="activeTab === 'activity' ? 'bg-ink-800 text-purple-400 border-purple-500/30' : 'text-mist hover:text-paper border-transparent'"
                        class="px-3.5 py-2 rounded-lg text-xs font-semibold border transition-all flex items-center gap-2">
                    <span>⚡</span>
                    <span>Boshqa Faolliklar</span>
                </button>
            </div>

            {{-- ==================== TAB 1: DO'KON & JIHOZLAR ==================== --}}
            <div x-show="activeTab === 'shop'" class="space-y-5">

                {{-- A. Currently Equipped Visuals (Faol jihozlar) --}}
                <div class="bg-ink-900 border border-ink-border rounded-panel p-6 shadow-sm">
                    <div class="pb-3 mb-4 border-b border-ink-border">
                        <h3 class="text-sm font-bold text-paper uppercase tracking-wider font-mono flex items-center gap-2">
                            <span class="text-amber-400">✨</span>
                            Hozir kiyilgan vizuallar (Faol Jihozlar)
                        </h3>
                        <p class="text-xs text-mist mt-1">Foydalanuvchi hozirda o'z profilida faollashtirgan vizual buyumlar</p>
                    </div>

                    @php
                        $eqFrame = $equipment->get('avatar_frame') ?? $equipment->get('frame');
                        $eqTheme = $equipment->get('profile_theme') ?? $equipment->get('theme');
                        $eqTitle = $equipment->get('title');
                        $eqNameColor = $equipment->get('name_color');
                        $eqBubble = $equipment->get('chat_bubble');
                        $eqAccent = $equipment->get('accent') ?? $equipment->get('reader_palette');
                        $hasAnyEquipment = $eqFrame || $eqTheme || $eqTitle || $eqNameColor || $eqBubble || $eqAccent;
                    @endphp

                    @if(!$hasAnyEquipment)
                        <div class="p-6 rounded-xl bg-ink-800/40 border border-ink-border text-center space-y-2">
                            <p class="text-2xl">🎨</p>
                            <h4 class="text-sm font-bold text-paper">Hozircha maxsus vizual jihozlar faollashtirilmagan</h4>
                            <p class="text-xs text-mist max-w-md mx-auto">
                                Foydalanuvchi barcha standart vizual sozlamalardan (oddiy avatar, oq ism, standart mavzu) foydalanmoqda.
                            </p>
                        </div>
                    @else
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                            {{-- Slot 1: Avatar Ramkasi --}}
                            <div class="p-4 rounded-xl bg-ink-800/50 border {{ $eqFrame ? 'border-amber-500/30' : 'border-ink-border' }}">
                                <div class="flex items-center justify-between text-xs text-mist mb-1.5 font-mono">
                                    <span>Avatar Ramkasi</span>
                                    <span class="text-[10px] uppercase font-bold {{ $eqFrame ? 'text-amber-400' : 'text-mist' }}">
                                        {{ $eqFrame ? ($eqFrame->shopItem?->rarity ?? 'Faol') : "Yo'q" }}
                                    </span>
                                </div>
                                @if($eqFrame)
                                    <p class="text-sm font-bold text-paper flex items-center gap-2">
                                        <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span>
                                        {{ $eqFrame->shopItem?->name_text ?? 'Noma\'lum ramka' }}
                                    </p>
                                @else
                                    <p class="text-xs text-mist italic">Standart ramka</p>
                                @endif
                            </div>

                            {{-- Slot 2: Profil Mavzusi --}}
                            <div class="p-4 rounded-xl bg-ink-800/50 border {{ $eqTheme ? 'border-purple-500/30' : 'border-ink-border' }}">
                                <div class="flex items-center justify-between text-xs text-mist mb-1.5 font-mono">
                                    <span>Profil Mavzusi</span>
                                    <span class="text-[10px] uppercase font-bold {{ $eqTheme ? 'text-purple-400' : 'text-mist' }}">
                                        {{ $eqTheme ? ($eqTheme->shopItem?->rarity ?? 'Faol') : "Yo'q" }}
                                    </span>
                                </div>
                                @if($eqTheme)
                                    <p class="text-sm font-bold text-paper flex items-center gap-2">
                                        <span class="w-2.5 h-2.5 rounded-full bg-purple-400"></span>
                                        {{ $eqTheme->shopItem?->name_text ?? 'Noma\'lum mavzu' }}
                                    </p>
                                @else
                                    <p class="text-xs text-mist italic">Standart mavzu</p>
                                @endif
                            </div>

                            {{-- Slot 3: Maxsus Unvon --}}
                            <div class="p-4 rounded-xl bg-ink-800/50 border {{ $eqTitle ? 'border-blue-500/30' : 'border-ink-border' }}">
                                <div class="flex items-center justify-between text-xs text-mist mb-1.5 font-mono">
                                    <span>Maxsus Unvon</span>
                                    <span class="text-[10px] uppercase font-bold {{ $eqTitle ? 'text-blue-400' : 'text-mist' }}">
                                        {{ $eqTitle ? ($eqTitle->shopItem?->rarity ?? 'Faol') : "Yo'q" }}
                                    </span>
                                </div>
                                @if($eqTitle)
                                    <p class="text-sm font-bold text-blue-300 flex items-center gap-1.5">
                                        <span>✦</span>
                                        {{ $eqTitle->shopItem?->name_text ?? 'Noma\'lum unvon' }}
                                    </p>
                                @else
                                    <p class="text-xs text-mist italic">Unvon tanlanmagan</p>
                                @endif
                            </div>

                            {{-- Slot 4: Ism Rangi --}}
                            <div class="p-4 rounded-xl bg-ink-800/50 border {{ $eqNameColor ? 'border-emerald-500/30' : 'border-ink-border' }}">
                                <div class="flex items-center justify-between text-xs text-mist mb-1.5 font-mono">
                                    <span>Ism Rangi</span>
                                    <span class="text-[10px] uppercase font-bold {{ $eqNameColor ? 'text-emerald-400' : 'text-mist' }}">
                                        {{ $eqNameColor ? 'Faol' : "Yo'q" }}
                                    </span>
                                </div>
                                @if($eqNameColor)
                                    <div class="flex items-center gap-2">
                                        <span class="w-3.5 h-3.5 rounded-full border border-white/30 shrink-0" style="background-color: {{ $user->equipped_name_color ?? '#fff' }}"></span>
                                        <span class="text-sm font-bold" style="color: {{ $user->equipped_name_color ?? '#fff' }}">
                                            {{ $eqNameColor->shopItem?->name_text ?? 'Maxsus rang' }}
                                        </span>
                                    </div>
                                @else
                                    <p class="text-xs text-mist italic">Standart oq rang</p>
                                @endif
                            </div>

                            {{-- Slot 5: Chat Pufagi --}}
                            <div class="p-4 rounded-xl bg-ink-800/50 border {{ $eqBubble ? 'border-cyan-500/30' : 'border-ink-border' }}">
                                <div class="flex items-center justify-between text-xs text-mist mb-1.5 font-mono">
                                    <span>Chat Pufagi</span>
                                    <span class="text-[10px] uppercase font-bold {{ $eqBubble ? 'text-cyan-400' : 'text-mist' }}">
                                        {{ $eqBubble ? ($eqBubble->shopItem?->rarity ?? 'Faol') : "Yo'q" }}
                                    </span>
                                </div>
                                @if($eqBubble)
                                    <p class="text-sm font-bold text-paper flex items-center gap-2">
                                        <span class="w-2.5 h-2.5 rounded-full bg-cyan-400"></span>
                                        {{ $eqBubble->shopItem?->name_text ?? 'Maxsus pufak' }}
                                    </p>
                                @else
                                    <p class="text-xs text-mist italic">Standart pufak</p>
                                @endif
                            </div>

                            {{-- Slot 6: Mutolaa Aksenti --}}
                            <div class="p-4 rounded-xl bg-ink-800/50 border {{ $eqAccent ? 'border-pink-500/30' : 'border-ink-border' }}">
                                <div class="flex items-center justify-between text-xs text-mist mb-1.5 font-mono">
                                    <span>Mutolaa Aksenti</span>
                                    <span class="text-[10px] uppercase font-bold {{ $eqAccent ? 'text-pink-400' : 'text-mist' }}">
                                        {{ $eqAccent ? 'Faol' : "Yo'q" }}
                                    </span>
                                </div>
                                @if($eqAccent)
                                    <p class="text-sm font-bold text-paper flex items-center gap-2">
                                        <span class="w-2.5 h-2.5 rounded-full bg-pink-400"></span>
                                        {{ $eqAccent->shopItem?->name_text ?? 'Maxsus aksent' }}
                                    </p>
                                @else
                                    <p class="text-xs text-mist italic">Standart aksent</p>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>

                {{-- B. All Owned Items (Foydalanuvchi Inventari) --}}
                <div class="bg-ink-900 border border-ink-border rounded-panel p-6 shadow-sm">
                    <div class="flex items-center justify-between pb-3 mb-4 border-b border-ink-border">
                        <div>
                            <h3 class="text-sm font-bold text-paper uppercase tracking-wider font-mono flex items-center gap-2">
                                <span class="text-amber-400">📦</span>
                                Egalik qilingan barcha buyumlar (Inventar)
                            </h3>
                            <p class="text-xs text-mist mt-1">Foydalanuvchi hisobidagi do'kon buyumlari ro'yxati</p>
                        </div>
                        <span class="px-3 py-1 rounded-badge text-xs font-mono font-bold bg-ink-800 text-amber-400 border border-ink-border">
                            Jami: {{ $userItems->count() }} ta
                        </span>
                    </div>

                    @if($userItems->isEmpty())
                        <div class="text-center py-8 px-4 rounded-xl border border-dashed border-ink-border">
                            <p class="text-2xl mb-2">🛍️</p>
                            <p class="text-xs text-mist font-medium">Foydalanuvchida hali hech qanday do'kon buyumi mavjud emas.</p>
                        </div>
                    @else
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @foreach($userItems as $uItem)
                                @php
                                    $item = $uItem->shopItem;
                                    $rarity = $item?->rarity ?? 'common';
                                    $rarityClasses = [
                                        'common'    => 'border-ink-border bg-ink-800/40 text-mist',
                                        'rare'      => 'border-blue-500/30 bg-blue-500/5 text-blue-300',
                                        'epic'      => 'border-purple-500/30 bg-purple-500/5 text-purple-300',
                                        'legendary' => 'border-amber-500/30 bg-amber-500/5 text-amber-300',
                                    ];
                                    $rClass = $rarityClasses[$rarity] ?? $rarityClasses['common'];
                                @endphp
                                <div class="p-4 rounded-xl border {{ $rClass }} flex flex-col justify-between hover:border-mist/40 transition-all">
                                    <div class="flex items-start justify-between gap-2">
                                        <div>
                                            <p class="text-sm font-bold text-paper">
                                                {{ $item?->name_text ?? 'Buyum #' . $uItem->shop_item_id }}
                                            </p>
                                            <p class="text-xs text-mist font-mono mt-1">
                                                Toifa: <span class="text-paper uppercase">{{ $item?->category ?? 'Noma\'lum' }}</span>
                                                @if($item?->slot)
                                                    • Slot: <span class="text-paper">{{ $item->slot }}</span>
                                                @endif
                                            </p>
                                        </div>
                                        <div class="text-right">
                                            <span class="px-2.5 py-1 rounded-badge text-xs font-mono font-bold bg-ink-950 border border-ink-border text-amber-400">
                                                x{{ $uItem->quantity }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="mt-4 pt-3 border-t border-ink-border/60 flex items-center justify-between text-xs text-mist">
                                        <span>Olingan: {{ $uItem->acquired_at ? $uItem->acquired_at->format('d.m.Y H:i') : '-' }}</span>
                                        @if($uItem->expires_at)
                                            <span class="{{ $uItem->isExpired() ? 'text-rose-400 font-bold' : 'text-paper font-mono' }}">
                                                {{ $uItem->isExpired() ? 'Tugagan' : $uItem->expires_at->format('d.m.Y') . ' gacha' }}
                                            </span>
                                        @else
                                            <span class="text-emerald-400 font-semibold">Doimiy</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- C. Recent Purchases Table (Xaridlar tarixi) --}}
                <div class="bg-ink-900 border border-ink-border rounded-panel p-6 shadow-sm">
                    <div class="flex items-center justify-between pb-3 mb-4 border-b border-ink-border">
                        <div>
                            <h3 class="text-sm font-bold text-paper uppercase tracking-wider font-mono flex items-center gap-2">
                                <span class="text-yellow-400">🪙</span>
                                So'nggi xaridlar tarixi (Do'kon cheklari)
                            </h3>
                            <p class="text-xs text-mist mt-1">Foydalanuvchining do'kondan amalga oshirgan xaridlari</p>
                        </div>
                        <span class="text-xs text-mist font-mono">So'nggi 20 ta</span>
                    </div>

                    @if($shopPurchases->isEmpty())
                        <div class="text-center py-8 px-4 rounded-xl border border-dashed border-ink-border text-xs text-mist">
                            Xaridlar tarixi bo'sh.
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead>
                                    <tr class="border-b border-ink-border text-mist font-mono uppercase text-[11px]">
                                        <th class="px-4 py-3 font-semibold">Buyum</th>
                                        <th class="px-4 py-3 font-semibold">Miqdor</th>
                                        <th class="px-4 py-3 font-semibold">To'langan Tanga</th>
                                        <th class="px-4 py-3 font-semibold">Token / Chek</th>
                                        <th class="px-4 py-3 font-semibold text-right">Vaqt</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-ink-border/60 font-mono">
                                    @foreach($shopPurchases as $purchase)
                                        <tr class="hover:bg-ink-800/40 transition-colors">
                                            <td class="px-4 py-3.5 font-sans font-semibold text-paper">
                                                {{ $purchase->shopItem?->name_text ?? 'O\'chirilgan buyum #' . $purchase->shop_item_id }}
                                            </td>
                                            <td class="px-4 py-3.5 text-paper">
                                                x{{ $purchase->quantity }}
                                            </td>
                                            <td class="px-4 py-3.5 text-yellow-400 font-bold">
                                                {{ number_format($purchase->price_paid) }} tanga
                                            </td>
                                            <td class="px-4 py-3.5 text-mist text-[11px]" title="{{ $purchase->purchase_token }}">
                                                {{ $purchase->purchase_token ?: ('#' . $purchase->id) }}
                                            </td>
                                            <td class="px-4 py-3.5 text-mist text-right">
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
                <div class="bg-ink-900 border border-ink-border rounded-panel p-6 shadow-sm">
                    <div class="flex items-center justify-between pb-3 mb-4 border-b border-ink-border">
                        <div>
                            <h3 class="text-sm font-bold text-paper uppercase tracking-wider font-mono flex items-center gap-2">
                                <span class="text-indigo-400">📖</span>
                                Mutolaa qilinayotgan va tugatilgan kitoblar
                            </h3>
                            <p class="text-xs text-mist mt-1">O'qilgan sahifalar, foiz va oxirgi faollik</p>
                        </div>
                        <span class="px-3 py-1 rounded-badge text-xs font-mono font-bold bg-ink-800 text-indigo-300 border border-ink-border">
                            Jami: {{ $readingProgress->count() }} ta kitob
                        </span>
                    </div>

                    @if($readingProgress->isEmpty())
                        <div class="text-center py-8 px-4 rounded-xl border border-dashed border-ink-border text-xs text-mist">
                            Foydalanuvchi hali biron bir kitob mutolaasini boshlamagan.
                        </div>
                    @else
                        <div class="space-y-4">
                            @foreach($readingProgress as $prog)
                                @php
                                    $book = $prog->book;
                                    $percent = (float) $prog->percent_complete;
                                    $isFinished = $percent >= 100;
                                @endphp
                                <div class="p-4 rounded-xl bg-ink-800/40 border border-ink-border flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:border-mist/40 transition-colors">
                                    <div class="flex items-center gap-4">
                                        @if($book && $book->cover_image)
                                            <img src="{{ asset('storage/' . $book->cover_image) }}" alt="{{ $book->title }}"
                                                 class="w-12 h-16 rounded-lg object-cover border border-ink-border flex-shrink-0 shadow">
                                        @else
                                            <div class="w-12 h-16 rounded-lg bg-ink-800 border border-ink-border flex items-center justify-center text-mist font-bold text-lg flex-shrink-0">
                                                📖
                                            </div>
                                        @endif
                                        <div>
                                            <h4 class="text-sm font-bold text-paper">
                                                {{ $book?->title ?? 'Kitob #' . $prog->book_id }}
                                            </h4>
                                            <p class="text-xs text-mist mt-0.5">
                                                Muallif: <span class="text-paper">{{ $book?->author ?? 'Noma\'lum' }}</span>
                                            </p>
                                            <p class="text-[11px] text-mist font-mono mt-1">
                                                Oxirgi mutolaa: {{ $prog->updated_at ? $prog->updated_at->diffForHumans() : '-' }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="sm:text-right sm:w-52 flex-shrink-0">
                                        <div class="flex items-center justify-between sm:justify-end gap-2 mb-2">
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
                                        <div class="w-full bg-ink-950 rounded-full h-2 overflow-hidden border border-ink-border/50">
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
                <div class="bg-ink-900 border border-ink-border rounded-panel p-6 shadow-sm">
                    <div class="flex items-center justify-between pb-3 mb-4 border-b border-ink-border">
                        <div>
                            <h3 class="text-sm font-bold text-paper uppercase tracking-wider font-mono flex items-center gap-2">
                                <span class="text-blue-400">💬</span>
                                Kitoblarga yozilgan sharh va izohlar
                            </h3>
                            <p class="text-xs text-mist mt-1">Foydalanuvchi qoldirgan mulohazalar</p>
                        </div>
                        <span class="text-xs text-mist font-mono">So'nggi {{ $bookComments->count() }} ta</span>
                    </div>

                    @if($bookComments->isEmpty())
                        <div class="text-center py-8 px-4 rounded-xl border border-dashed border-ink-border text-xs text-mist">
                            Foydalanuvchi hali kitoblarga izoh yozmagan.
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach($bookComments as $comment)
                                <div class="p-4 rounded-xl bg-ink-800/40 border border-ink-border hover:border-mist/40 transition-colors">
                                    <div class="flex items-center justify-between gap-2 text-xs mb-2">
                                        <span class="font-bold text-amber-400">
                                            📖 {{ $comment->book?->title ?? 'Kitob' }}
                                        </span>
                                        <span class="text-[11px] text-mist font-mono">
                                            {{ $comment->created_at->format('d.m.Y H:i') }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-paper italic bg-ink-950/60 p-3 rounded-lg border border-ink-border">
                                        "{{ $comment->comment }}"
                                    </p>
                                    <div class="mt-2.5 flex items-center gap-3 text-xs text-mist">
                                        @if($comment->rating)
                                            <span class="text-amber-400 font-semibold font-mono">Baho: {{ $comment->rating }} ★</span>
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

                <div class="bg-ink-900 border border-ink-border rounded-panel p-6 shadow-sm">
                    <div class="flex items-center justify-between pb-3 mb-4 border-b border-ink-border">
                        <div>
                            <h3 class="text-sm font-bold text-paper uppercase tracking-wider font-mono flex items-center gap-2">
                                <span class="text-emerald-400">🧠</span>
                                Viktorina va test natijalari
                            </h3>
                            <p class="text-xs text-mist mt-1">Topshirilgan testlar, to'plangan ballar va o'tish ko'rsatkichi</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="px-3 py-1 rounded-badge text-xs font-mono font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                Muvaffaqiyatli: {{ $quizPassedCount }} / {{ $quizTotalCount }}
                            </span>
                        </div>
                    </div>

                    @if($quizAttempts->isEmpty())
                        <div class="text-center py-8 px-4 rounded-xl border border-dashed border-ink-border text-xs text-mist">
                            Foydalanuvchi hali birorta ham viktorina topshirmagan.
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead>
                                    <tr class="border-b border-ink-border text-mist font-mono uppercase text-[11px]">
                                        <th class="px-4 py-3 font-semibold">Test / Kitob</th>
                                        <th class="px-4 py-3 font-semibold">Ball</th>
                                        <th class="px-4 py-3 font-semibold">Natija Foizi</th>
                                        <th class="px-4 py-3 font-semibold">Holat</th>
                                        <th class="px-4 py-3 font-semibold text-right">Topshirilgan Sana</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-ink-border/60 font-mono">
                                    @foreach($quizAttempts as $attempt)
                                        @php
                                            $percent = (float) $attempt->percent;
                                            $isPassed = $percent >= 70;
                                        @endphp
                                        <tr class="hover:bg-ink-800/40 transition-colors">
                                            <td class="px-4 py-3.5 font-sans">
                                                <p class="font-bold text-paper">
                                                    {{ $attempt->quiz?->title ?? 'Mustaqil Test #' . $attempt->quiz_id }}
                                                </p>
                                                <p class="text-[11px] text-mist">
                                                    Kitob: {{ $attempt->quiz?->book?->title ?? 'Umumiy' }}
                                                </p>
                                            </td>
                                            <td class="px-4 py-3.5 text-paper font-bold">
                                                {{ $attempt->score }} / {{ $attempt->max_score }}
                                            </td>
                                            <td class="px-4 py-3.5">
                                                <span class="font-bold {{ $isPassed ? 'text-emerald-400' : 'text-rose-400' }}">
                                                    {{ number_format($percent, 1) }}%
                                                </span>
                                            </td>
                                            <td class="px-4 py-3.5">
                                                @if($isPassed)
                                                    <span class="px-2.5 py-0.5 rounded text-[10px] font-bold bg-emerald-500/15 text-emerald-400 border border-emerald-500/25">
                                                        ✓ Muvaffaqiyatli
                                                    </span>
                                                @else
                                                    <span class="px-2.5 py-0.5 rounded text-[10px] font-bold bg-rose-500/15 text-rose-400 border border-rose-500/25">
                                                        ✗ O'tmadi
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3.5 text-mist text-right">
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
                <div class="bg-ink-900 border border-ink-border rounded-panel p-6 shadow-sm">
                    <div class="flex items-center justify-between pb-3 mb-4 border-b border-ink-border">
                        <div>
                            <h3 class="text-sm font-bold text-paper uppercase tracking-wider font-mono flex items-center gap-2">
                                <span class="text-yellow-400">🪙</span>
                                Tanga tranzaksiyalari (Kirim va Chiqim)
                            </h3>
                            <p class="text-xs text-mist mt-1">Foydalanuvchining so'nggi 20 ta tanga harakati</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-1 rounded-badge text-xs font-mono bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                +{{ number_format($totalEarnedCoins) }} kirim
                            </span>
                            <span class="px-2.5 py-1 rounded-badge text-xs font-mono bg-rose-500/10 text-rose-400 border border-rose-500/20">
                                -{{ number_format($totalSpentCoins) }} chiqim
                            </span>
                        </div>
                    </div>

                    @if($coinTransactions->isEmpty())
                        <div class="text-center py-8 px-4 rounded-xl border border-dashed border-ink-border text-xs text-mist">
                            Tanga tranzaksiyalari mavjud emas.
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead>
                                    <tr class="border-b border-ink-border text-mist font-mono uppercase text-[11px]">
                                        <th class="px-4 py-3 font-semibold">Miqdor</th>
                                        <th class="px-4 py-3 font-semibold">Manba</th>
                                        <th class="px-4 py-3 font-semibold">Tavsif / Izoh</th>
                                        <th class="px-4 py-3 font-semibold">Qoldiq</th>
                                        <th class="px-4 py-3 font-semibold text-right">Sana</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-ink-border/60 font-mono">
                                    @foreach($coinTransactions as $tx)
                                        <tr class="hover:bg-ink-800/40 transition-colors">
                                            <td class="px-4 py-3.5 font-bold {{ $tx->coins > 0 ? 'text-emerald-400' : 'text-rose-400' }}">
                                                {{ $tx->coins > 0 ? '+' : '' }}{{ $tx->coins }}
                                            </td>
                                            <td class="px-4 py-3.5 text-paper">
                                                <span class="px-2 py-0.5 rounded bg-ink-800 text-[10px] text-mist border border-ink-border">
                                                    {{ $tx->source }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3.5 font-sans text-paper max-w-xs truncate">
                                                {{ $tx->description ?: '-' }}
                                            </td>
                                            <td class="px-4 py-3.5 text-yellow-400 font-bold">
                                                {{ $tx->balance_after !== null ? number_format($tx->balance_after) : '-' }}
                                            </td>
                                            <td class="px-4 py-3.5 text-mist text-right">
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
                <div class="bg-ink-900 border border-ink-border rounded-panel p-6 shadow-sm">
                    <div class="flex items-center justify-between pb-3 mb-4 border-b border-ink-border">
                        <div>
                            <h3 class="text-sm font-bold text-paper uppercase tracking-wider font-mono flex items-center gap-2">
                                <span class="text-amber-400">⭐</span>
                                Ball tranzaksiyalari (Kirim va Sarf)
                            </h3>
                            <p class="text-xs text-mist mt-1">Mutolaa, testlar va faolliklar orqali to'plangan ballar</p>
                        </div>
                        <span class="text-xs text-mist font-mono">So'nggi 20 ta</span>
                    </div>

                    @if($pointTransactions->isEmpty())
                        <div class="text-center py-8 px-4 rounded-xl border border-dashed border-ink-border text-xs text-mist">
                            Ball tranzaksiyalari mavjud emas.
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead>
                                    <tr class="border-b border-ink-border text-mist font-mono uppercase text-[11px]">
                                        <th class="px-4 py-3 font-semibold">Miqdor</th>
                                        <th class="px-4 py-3 font-semibold">Manba</th>
                                        <th class="px-4 py-3 font-semibold">Tavsif / Izoh</th>
                                        <th class="px-4 py-3 font-semibold text-right">Sana</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-ink-border/60 font-mono">
                                    @foreach($pointTransactions as $ptx)
                                        <tr class="hover:bg-ink-800/40 transition-colors">
                                            <td class="px-4 py-3.5 font-bold {{ $ptx->points > 0 ? 'text-amber-400' : 'text-rose-400' }}">
                                                {{ $ptx->points > 0 ? '+' : '' }}{{ $ptx->points }}
                                            </td>
                                            <td class="px-4 py-3.5 text-paper">
                                                <span class="px-2 py-0.5 rounded bg-ink-800 text-[10px] text-mist border border-ink-border">
                                                    {{ $ptx->source }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3.5 font-sans text-paper max-w-sm truncate">
                                                {{ $ptx->description ?: '-' }}
                                            </td>
                                            <td class="px-4 py-3.5 text-mist text-right">
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
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="p-5 rounded-panel bg-ink-900 border border-ink-border text-center">
                        <p class="text-2xl font-bold text-paper font-mono">{{ $userNotesCount }}</p>
                        <p class="text-xs text-mist mt-1">📝 Shaxsiy qaydlar</p>
                    </div>
                    <div class="p-5 rounded-panel bg-ink-900 border border-ink-border text-center">
                        <p class="text-2xl font-bold text-paper font-mono">{{ $userBookmarksCount }}</p>
                        <p class="text-xs text-mist mt-1">🔖 Xatcho'plar</p>
                    </div>
                    <div class="p-5 rounded-panel bg-ink-900 border border-ink-border text-center">
                        <p class="text-2xl font-bold text-paper font-mono">{{ $userQuotesCount }}</p>
                        <p class="text-xs text-mist mt-1">💬 Saqlangan iqtiboslar</p>
                    </div>
                </div>

                {{-- AI Book Assistant Questions --}}
                <div class="bg-ink-900 border border-ink-border rounded-panel p-6 shadow-sm">
                    <div class="flex items-center justify-between pb-3 mb-4 border-b border-ink-border">
                        <div>
                            <h3 class="text-sm font-bold text-paper uppercase tracking-wider font-mono flex items-center gap-2">
                                <span class="text-purple-400">🤖</span>
                                AI Kitobxonga berilgan savollar
                            </h3>
                            <p class="text-xs text-mist mt-1">Foydalanuvchining kitoblar bo'yicha sun'iy intellektga yuborgan so'rovlari</p>
                        </div>
                        <span class="text-xs text-mist font-mono">So'nggi {{ $aiQuestions->count() }} ta</span>
                    </div>

                    @if($aiQuestions->isEmpty())
                        <div class="text-center py-8 px-4 rounded-xl border border-dashed border-ink-border text-xs text-mist">
                            Foydalanuvchi hali AI kitobxonga murojaat qilmagan.
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach($aiQuestions as $aiQ)
                                <div class="p-4 rounded-xl bg-ink-800/40 border border-ink-border hover:border-mist/40 transition-colors">
                                    <div class="flex items-center justify-between gap-2 text-xs mb-2">
                                        <span class="font-bold text-purple-300">
                                            📖 {{ $aiQ->book?->title ?? 'Umumiy' }}
                                        </span>
                                        <span class="text-[11px] text-mist font-mono">
                                            {{ $aiQ->created_at->format('d.m.Y H:i') }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-paper bg-ink-950/60 p-3 rounded-lg border border-ink-border">
                                        <span class="font-semibold text-mist">Savol:</span> "{{ $aiQ->message }}"
                                    </p>
                                    @if($aiQ->model || $aiQ->tokens_used)
                                        <div class="mt-2 text-[10px] text-mist font-mono flex items-center gap-3">
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
                <div class="bg-ink-900 border border-ink-border rounded-panel p-6 shadow-sm">
                    <div class="flex items-center justify-between pb-3 mb-4 border-b border-ink-border">
                        <div>
                            <h3 class="text-sm font-bold text-paper uppercase tracking-wider font-mono flex items-center gap-2">
                                <span class="text-rose-400">🔴</span>
                                Jonli efirdagi savollar
                            </h3>
                            <p class="text-xs text-mist mt-1">Jonli darslar va translatsiyalarda berilgan savollar</p>
                        </div>
                        <span class="text-xs text-mist font-mono">So'nggi {{ $liveQuestions->count() }} ta</span>
                    </div>

                    @if($liveQuestions->isEmpty())
                        <div class="text-center py-8 px-4 rounded-xl border border-dashed border-ink-border text-xs text-mist">
                            Jonli efirlarda berilgan savollar mavjud emas.
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach($liveQuestions as $lq)
                                <div class="p-4 rounded-xl bg-ink-800/40 border border-ink-border hover:border-mist/40 transition-colors">
                                    <div class="flex items-center justify-between gap-2 text-xs mb-2">
                                        <span class="font-bold text-paper">
                                            {{ $lq->liveEvent?->title ?? 'Jonli Efir #' . $lq->live_event_id }}
                                        </span>
                                        <div class="flex items-center gap-2">
                                            @if($lq->is_priority)
                                                <span class="px-2.5 py-0.5 rounded text-[10px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30 flex items-center gap-1">
                                                    <svg class="w-3 h-3 text-amber-400" viewBox="0 0 20 20" fill="currentColor">
                                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                    </svg>
                                                    Ustuvor Savol
                                                </span>
                                            @endif
                                            <span class="text-[11px] text-mist font-mono">
                                                {{ $lq->created_at->format('d.m.Y H:i') }}
                                            </span>
                                        </div>
                                    </div>
                                    <p class="text-xs text-paper bg-ink-950/60 p-3 rounded-lg border border-ink-border">
                                        "{{ $lq->question }}"
                                    </p>
                                    <div class="mt-2.5 flex items-center justify-between text-xs text-mist font-mono">
                                        <span>❤️ {{ $lq->likes_count ?? 0 }} layk</span>
                                        <span>
                                            @if($lq->is_answered)
                                                <span class="text-emerald-400 font-bold">✓ Javob berildi</span>
                                            @else
                                                <span class="text-mist">Javob kutilmoqda</span>
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

    {{-- Telegram Botdan Xabar / E'lon Yuborish Modali --}}
    <div x-show="tgModalOpen" x-cloak
         class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-ink-950/80 backdrop-blur-sm"
         @keydown.escape.window="tgModalOpen = false">
        <div class="ks-panel relative w-full max-w-lg bg-ink-900 border border-ink-border rounded-panel p-5 sm:p-6 shadow-2xl text-left space-y-4"
             @click.away="tgModalOpen = false">
            <div class="flex items-center justify-between border-b border-ink-border pb-3">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-btn bg-sky-500/15 border border-sky-500/30 text-sky-400 flex items-center justify-center shrink-0">
                        <span>✈️</span>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold font-serif text-paper">Telegram Botdan Xabar / E'lon Yuborish</h3>
                        <p class="text-xs text-mist font-mono mt-0.5">
                            Qabul qiluvchi: <span class="text-amber-400 font-bold">{{ $user->name }}</span> (&#64;{{ $user->username }})
                        </p>
                    </div>
                </div>
                <button type="button" @click="tgModalOpen = false" class="text-mist hover:text-paper p-1 rounded-btn">✕</button>
            </div>

            <div class="p-2.5 rounded-btn bg-sky-500/10 border border-sky-500/25 text-[11px] font-mono text-sky-200">
                🤖 Xabar shaxsiy lichkadan emas, rasmiy <b>&#64;{{ config('telegram.bot_username', 'kitobxonS_bot') }}</b> boti nomidan yuboriladi.
                @if(!$user->isTelegramLinked())
                    <span class="block text-amber-400 mt-1">⚠️ Ushbu foydalanuvchi hali Telegram botni ulamagan — xabar uning sayt ichidagi bildirishnomalariga tushadi.</span>
                @endif
            </div>

            <form action="{{ route('admin.users.telegram-message', $user) }}" method="POST" class="space-y-3.5">
                @csrf

                <div>
                    <label class="block text-xs font-mono text-mist mb-1">Sarlavha (ixtiyoriy):</label>
                    <input type="text" name="title" placeholder="Masalan: Ma'muriyatdan xabar yoki Tabriklaymiz!"
                           class="ks-input text-xs">
                </div>

                <div>
                    <label class="block text-xs font-mono text-mist mb-1">Xabar / E'lon matni:</label>
                    <textarea name="message" rows="4" required
                              placeholder="Foydalanuvchiga yuboriladigan xabar matnini kiriting..."
                              class="ks-input text-xs"></textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                    <div>
                        <label class="block text-xs font-mono text-mist mb-1">Tugma yozuvi (ixtiyoriy):</label>
                        <input type="text" name="button_text" placeholder="Masalan: 📖 Saytga o'tish"
                               class="ks-input text-xs">
                    </div>
                    <div>
                        <label class="block text-xs font-mono text-mist mb-1">Tugma havolasi (ixtiyoriy):</label>
                        <input type="url" name="button_url" placeholder="https://..."
                               class="ks-input text-xs">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-2">
                    <button type="button" @click="tgModalOpen = false"
                            class="ks-btn-ghost py-1.5 px-3 text-xs">
                        Bekor qilish
                    </button>
                    <button type="submit"
                            class="ks-btn-primary py-1.5 px-4 text-xs font-mono inline-flex items-center gap-1.5">
                        <span>🚀 Bot orqali yuborish</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

@endsection
