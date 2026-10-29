@extends('admin.layouts.app')

@section('title', 'Aloqa va shikoyatlar')
@section('breadcrumb', 'Shikoyatlar')

@section('content')

<div class="space-y-4"
     x-data="{
         banModalOpen: false,
         targetUserId: null,
         targetUserName: '',
         banActionUrl: '',
         tgModalOpen: false,
         tgTargetUserName: '',
         tgActionUrl: '',
         showModalOpen: false,
         activeReport: {},
         openBan(userId, userName) {
             this.targetUserId = userId;
             this.targetUserName = userName;
             this.banActionUrl = '{{ url('/admin/users') }}/' + userId + '/ban';
             this.banModalOpen = true;
         },
         openTgReply(userId, userName) {
             this.tgTargetUserName = userName;
             this.tgActionUrl = '{{ url('/admin/users') }}/' + userId + '/telegram-message';
             this.tgModalOpen = true;
         },
         openShow(data) {
             this.activeReport = data;
             this.showModalOpen = true;
         }
     }">
    {{-- Page header --}}
    <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div class="flex items-start gap-3">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-card border border-amber-500/20 bg-amber-500/10 text-amber-400">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V4s-1 1-4 1-5-2-8-2-4 1-4 1z"/><path d="M4 22V15"/></svg>
            </span>
            <div>
                <h1 class="font-display text-xl font-semibold text-paper sm:text-2xl">Aloqa va shikoyatlar</h1>
                <p class="mt-1 max-w-2xl text-sm text-mist">Foydalanuvchilar yuborgan xabarlar, ban apellyatsiyalari va shikoyatlarni ko'rib chiqing.</p>
            </div>
        </div>
        <div class="grid grid-cols-3 gap-2 sm:flex sm:gap-3">
            <div class="rounded-panel border border-ink-border bg-ink-900 px-3.5 py-2.5">
                <p class="text-[10px] font-semibold uppercase tracking-wide text-mist">Jami murojaat</p>
                <p class="mt-0.5 font-mono text-lg font-bold text-paper">{{ $reports->total() }}</p>
            </div>
            <div class="rounded-panel border border-rose-500/30 bg-rose-500/10 px-3.5 py-2.5">
                <p class="text-[10px] font-semibold uppercase tracking-wide text-rose-300">🚫 Ban olganlardan</p>
                <p class="mt-0.5 font-mono text-lg font-bold text-rose-400">{{ $bannedAppealsCount ?? 0 }}</p>
            </div>
            <div class="rounded-panel border border-amber-500/20 bg-amber-500/5 px-3.5 py-2.5">
                <p class="text-[10px] font-semibold uppercase tracking-wide text-amber-300">Kutilmoqda</p>
                <p class="mt-0.5 font-mono text-lg font-bold text-amber-400">{{ \App\Models\Report::pending()->count() }}</p>
            </div>
        </div>
    </header>

    {{-- Filter Tabs --}}
    @php $activeFilter = $filter ?? request('filter', 'all'); @endphp
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('admin.reports.index', ['filter' => 'all']) }}"
           class="inline-flex items-center gap-1.5 rounded-card border px-3.5 py-2 text-xs font-semibold transition-colors {{ $activeFilter === 'all' ? 'border-amber-500/40 bg-amber-500/15 text-amber-300' : 'border-ink-border bg-ink-900 text-mist hover:text-paper' }}">
            <span>Barchasi</span>
        </a>
        <a href="{{ route('admin.reports.index', ['filter' => 'banned']) }}"
           class="inline-flex items-center gap-2 rounded-card border px-3.5 py-2 text-xs font-bold transition-colors {{ $activeFilter === 'banned' ? 'border-rose-500 bg-rose-500/20 text-rose-200 shadow-sm shadow-rose-500/20' : 'border-rose-500/30 bg-rose-500/10 text-rose-300 hover:bg-rose-500/20' }}">
            <span>🚫 Ban olgan foydalanuvchilardan (Apellyatsiya)</span>
            <span class="rounded-badge bg-rose-500 px-1.5 py-0.5 font-mono text-[10px] font-bold text-white">{{ $bannedAppealsCount ?? 0 }}</span>
        </a>
        <a href="{{ route('admin.reports.index', ['filter' => 'reports']) }}"
           class="inline-flex items-center gap-1.5 rounded-card border px-3.5 py-2 text-xs font-semibold transition-colors {{ $activeFilter === 'reports' ? 'border-amber-500/40 bg-amber-500/15 text-amber-300' : 'border-ink-border bg-ink-900 text-mist hover:text-paper' }}">
            <span>🚩 Qoidabuzarlik shikoyatlari</span>
        </a>
        <a href="{{ route('admin.reports.index', ['filter' => 'contact']) }}"
           class="inline-flex items-center gap-1.5 rounded-card border px-3.5 py-2 text-xs font-semibold transition-colors {{ $activeFilter === 'contact' ? 'border-amber-500/40 bg-amber-500/15 text-amber-300' : 'border-ink-border bg-ink-900 text-mist hover:text-paper' }}">
            <span>✉️ Oddiy aloqa xabarlari</span>
        </a>
    </div>

@if(session('success'))
    <div role="status" class="flex items-center gap-2 rounded-panel border border-emerald-500/20 bg-emerald-500/10 p-3.5 text-sm font-semibold text-emerald-300">
        <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m5 12 4 4L19 6"/></svg>
        <span>{{ session('success') }}</span>
    </div>
@endif

@if(session('error'))
    <div role="alert" class="flex items-center gap-2 rounded-panel border border-rose-500/20 bg-rose-500/10 p-3.5 text-sm font-semibold text-rose-300">
        <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 9v4m0 4h.01"/><circle cx="12" cy="12" r="10"/></svg>
        <span>{{ session('error') }}</span>
    </div>
@endif

{{-- Reports Table Card --}}
<section aria-label="Foydalanuvchi shikoyatlari" class="ks-panel overflow-hidden">
    <div class="admin-table-scroll overflow-x-auto">
        <table class="w-full min-w-[72rem] text-left">
            <thead>
                <tr class="border-b border-ink-border bg-ink-950/60">
                    <th scope="col" class="whitespace-nowrap px-3.5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-mist">Vaqt va bo'lim</th>
                    <th scope="col" class="whitespace-nowrap px-3.5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-mist">Shikoyat qilingan</th>
                    <th scope="col" class="whitespace-nowrap px-3.5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-mist">Yuboruvchi</th>
                    <th scope="col" class="whitespace-nowrap px-3.5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-mist">Xabar mazmuni</th>
                    <th scope="col" class="whitespace-nowrap px-3.5 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-mist">Holat</th>
                    <th scope="col" class="whitespace-nowrap px-3.5 py-3 text-right text-[11px] font-semibold uppercase tracking-wider text-mist">Amallar</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-ink-border/60 text-xs">
                @forelse($reports as $report)
                    @php
                        $offender = $report->reportedUser;
                        $reporter = $report->reporter;
                        $primaryUser = null;

                        // 1. Agar murojaat matnida yoki mavzuda aniq foydalanuvchi ID (#ID) ko'rsatilgan bo'lsa (apellyatsiyalar uchun):
                        if ($report->source === 'Ban Apellyatsiyasi' || str_contains($report->reason ?? '', 'apellyatsiya') || str_contains($report->message_content ?? '', 'APELLYATSIYA')) {
                            if (preg_match('/#(\d+)/', ($report->message_content ?? '') . ' ' . ($report->reason ?? ''), $mId)) {
                                $primaryUser = \App\Models\User::with('bannedBy')->find((int) $mId[1]);
                            }
                        }

                        // 2. Agar hali aniqlanmagan bo'lsa, shikoyat qilingan yoki yuboruvchidan ban olganini tanlaymiz:
                        if (!$primaryUser) {
                            if ($offender && $offender->isBanned()) {
                                $primaryUser = $offender;
                            } elseif ($reporter && $reporter->isBanned()) {
                                $primaryUser = $reporter;
                            } else {
                                $primaryUser = $offender ?? $reporter;
                            }
                        }

                        $isFromBannedUser = $report->source === 'Ban Apellyatsiyasi'
                            || ($reporter && $reporter->isBanned())
                            || ($offender && $offender->isBanned())
                            || ($primaryUser && $primaryUser->isBanned());

                        $isBanned = (bool) ($primaryUser && $primaryUser->isBanned());
                        $bannedBy = $primaryUser?->bannedBy;
                        $bannedByLabel = $primaryUser ? $primaryUser->banned_by_label : 'Tizim ma\'muriyati (Admin)';
                        $banRemaining = $primaryUser ? $primaryUser->ban_remaining : '';
                        $banDurationLabel = $primaryUser ? $primaryUser->ban_duration_label : '';
                        $banExpiresAt = $primaryUser?->banned_until
                            ? $primaryUser->banned_until->timezone('Asia/Tashkent')->format('d.m.Y H:i')
                            : 'Muddatsiz (Doimiy)';
                        $banReason = $primaryUser?->ban_reason ?: 'Ko\'rsatilmagan';
                        $unbanUrl = $primaryUser ? route('admin.users.unban', $primaryUser) : '';
                        $resolveUrl = route('admin.reports.resolve', $report);
                        $userEditUrl = $primaryUser ? route('admin.users.edit', $primaryUser) : '';

                        $reportData = [
                            'id'               => $report->id,
                            'title'            => $report->source === 'Ban Apellyatsiyasi' ? 'Ban bo\'yicha Apellyatsiya' : 'Murojaat tafsilotlari',
                            'source'           => $report->source,
                            'status'           => $report->status,
                            'reason'           => $report->reason,
                            'messageContent'   => $report->message_content,
                            'link'             => $report->link,
                            'createdAt'        => $report->created_at->timezone('Asia/Tashkent')->format('d.m.Y H:i'),
                            'isFromBannedUser' => $isFromBannedUser,
                            'userId'           => $primaryUser?->id,
                            'userName'         => $primaryUser ? $primaryUser->name : ($reporter ? $reporter->name : 'Mehmon'),
                            'userHandle'       => $primaryUser ? ('@' . $primaryUser->username . ' #' . $primaryUser->id) : 'Mehmon',
                            'userPhone'        => $primaryUser?->phone,
                            'userAvatar'       => $primaryUser ? $primaryUser->avatar_url : asset('images/default-avatar.png'),
                            'userEditUrl'      => $userEditUrl,
                            'isBanned'         => $isBanned,
                            'bannedByLabel'    => $bannedByLabel,
                            'banRemaining'     => $banRemaining,
                            'banDurationLabel' => $banDurationLabel,
                            'banExpiresAt'     => $banExpiresAt,
                            'banReason'        => $banReason,
                            'unbanUrl'         => $unbanUrl,
                            'resolveUrl'       => $resolveUrl,
                            'canReplyTg'       => (bool) ($primaryUser && $primaryUser->telegram_id && !$primaryUser->telegram_blocked_at),
                        ];
                    @endphp
                    <tr class="transition-colors {{ $isFromBannedUser ? 'bg-rose-950/30 hover:bg-rose-900/35 border-l-4 border-l-rose-500' : 'hover:bg-ink-800/40' }}">
                        {{-- Time & Source --}}
                        <td class="px-3.5 py-3 align-middle">
                            <p class="whitespace-nowrap text-xs font-semibold text-paper">{{ $report->created_at->timezone('Asia/Tashkent')->format('d.m.Y H:i') }}</p>
                            @if($isFromBannedUser)
                                <span class="mt-1 inline-flex items-center gap-1 rounded-badge border border-rose-500/50 bg-rose-500/20 px-2 py-0.5 font-mono text-[10px] font-bold text-rose-200">
                                    🚫 BAN OLGAN FOYDALANUVCHI
                                </span>
                            @endif
                            <span class="mt-1 inline-block rounded-badge border {{ $report->source === 'Ban Apellyatsiyasi' ? 'border-amber-500/40 bg-amber-500/15 text-amber-300 font-bold' : 'border-ink-border bg-ink-800 text-mist' }} px-2 py-0.5 font-mono text-[10px]">
                                {{ $report->source }}
                            </span>
                        </td>

                        {{-- Offender / Banned User info --}}
                        <td class="px-3.5 py-3 align-middle">
                            @if($primaryUser)
                                <div class="flex items-center gap-3">
                                    <img src="{{ $primaryUser->avatar_url }}" class="h-9 w-9 rounded-card border {{ $primaryUser->isBanned() ? 'border-rose-500 ring-2 ring-rose-500/30' : 'border-ink-border' }} object-cover" alt="{{ $primaryUser->name }}">
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-1.5">
                                            <a href="{{ route('admin.users.edit', $primaryUser) }}" class="truncate text-xs font-bold text-paper hover:text-amber-400 transition-colors">{{ $primaryUser->name }}</a>
                                        </div>
                                        <p class="font-mono text-[11px] text-mist">{{ '@' . $primaryUser->username }} <span class="text-mist/70">#{{ $primaryUser->id }}</span></p>
                                        @if($primaryUser->phone)
                                            <p class="font-mono text-[11px] text-amber-400/90">📞 {{ $primaryUser->phone }}</p>
                                        @endif
                                        @if($primaryUser->isBanned())
                                            <div class="mt-1 space-y-0.5">
                                                <span class="inline-block rounded-badge border border-rose-500/40 bg-rose-500/15 px-2 py-0.5 text-[10px] font-bold text-rose-300">
                                                    🚫 Bloklangan ({{ $primaryUser->ban_remaining }})
                                                </span>
                                                @if($primaryUser->ban_reason)
                                                    <p class="text-[10px] text-rose-200/80 italic truncate max-w-[14rem]" title="{{ $primaryUser->ban_reason }}">
                                                        Sabab: "{{ $primaryUser->ban_reason }}"
                                                    </p>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @else
                                <span class="font-mono text-xs text-mist">Foydalanuvchi topilmadi</span>
                            @endif
                        </td>

                        {{-- Reporter info --}}
                        <td class="px-3.5 py-3 align-middle">
                            @if($reporter)
                                <div>
                                    <p class="text-xs font-semibold text-paper">{{ $reporter->name }}</p>
                                    <p class="font-mono text-[11px] text-mist">{{ '@' . $reporter->username }}</p>
                                    @if($reporter->phone)
                                        <p class="font-mono text-[11px] text-amber-400/90">📞 {{ $reporter->phone }}</p>
                                    @endif
                                    @if($reporter->isBanned())
                                        <span class="mt-0.5 inline-block text-[10px] font-mono font-bold text-rose-400">
                                            (Ban olgan)
                                        </span>
                                    @endif
                                </div>
                            @else
                                <span class="text-xs text-mist">Mehmon / Aloqa formasi</span>
                            @endif
                        </td>

                        {{-- Message Content (Compact Preview & Show Trigger) --}}
                        <td class="max-w-xs md:max-w-sm px-3.5 py-3 align-middle">
                            @if($isFromBannedUser)
                                <div class="mb-1 flex items-center gap-1.5 text-[11px] font-bold text-rose-300">
                                    <span>⚖️ Ban Apellyatsiyasi</span>
                                </div>
                            @elseif($report->reason)
                                <p class="text-[11px] font-bold text-amber-300 truncate mb-1">
                                    {{ $report->reason }}
                                </p>
                            @endif

                            {{-- Clean, compact 2-line snippet --}}
                            <p class="text-xs text-paper/90 line-clamp-2 leading-relaxed break-words font-sans">
                                {{ Str::limit(strip_tags($report->message_content), 120) }}
                            </p>

                            <div class="mt-2 flex items-center gap-2 flex-wrap">
                                <button type="button"
                                        @click="openShow(@js($reportData))"
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-badge bg-amber-500/15 hover:bg-amber-500 text-amber-300 hover:text-slate-950 border border-amber-500/30 font-semibold text-[11px] transition-all cursor-pointer">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <span>Batafsil ko'rish</span>
                                </button>
                                @if($report->link)
                                    <a href="{{ $report->link }}" target="_blank" rel="noopener noreferrer" class="text-[11px] text-mist hover:text-amber-400 hover:underline">
                                        🔗 Havola
                                    </a>
                                @endif
                            </div>
                        </td>

                        {{-- Status --}}
                        <td class="px-3.5 py-3 align-middle">
                            @if($report->status === 'pending')
                                <span class="inline-flex items-center gap-1.5 rounded-badge border border-amber-500/25 bg-amber-500/10 px-2.5 py-1 text-xs font-semibold text-amber-300">
                                    <span class="h-1.5 w-1.5 rounded-full bg-amber-400" aria-hidden="true"></span> Kutilmoqda
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 rounded-badge border border-emerald-500/25 bg-emerald-500/10 px-2.5 py-1 text-xs font-semibold text-emerald-300">
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-400" aria-hidden="true"></span> Hal qilindi
                                </span>
                            @endif
                        </td>

                        {{-- Actions --}}
                        <td class="px-3.5 py-3 text-right align-middle">
                            <div class="flex items-center justify-end gap-1.5 flex-wrap">
                                {{-- Show / Ko'rish Button --}}
                                <button type="button"
                                        @click="openShow(@js($reportData))"
                                        class="inline-flex min-h-9 items-center gap-1 rounded-md border border-amber-500/30 bg-amber-500/10 px-2.5 py-1.5 text-xs font-semibold text-amber-300 transition-colors hover:bg-amber-500 hover:text-slate-950 cursor-pointer"
                                        title="Barcha yozganlarini ko'rish">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <span>Ko'rish</span>
                                </button>

                                {{-- Ban / Unban Button --}}
                                @if($primaryUser)
                                    @if($primaryUser->isBanned())
                                        <form action="{{ route('admin.users.unban', $primaryUser) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="inline-flex min-h-9 cursor-pointer items-center gap-1 rounded-md border border-emerald-500/35 bg-emerald-500/15 px-3 py-1.5 text-xs font-bold text-emerald-300 transition-colors hover:bg-emerald-500 hover:text-white" title="Blokdan chiqarish">
                                                <span>✅ Banni olish</span>
                                            </button>
                                        </form>
                                    @elseif(!$primaryUser->isAdmin() && $primaryUser->id !== auth()->id())
                                        <button type="button"
                                                @click="openBan({{ $primaryUser->id }}, @js($primaryUser->name))"
                                                class="inline-flex min-h-9 cursor-pointer items-center gap-1.5 rounded-md border border-rose-500/30 bg-rose-500/10 px-3 py-1.5 text-xs font-semibold text-rose-300 transition-colors hover:bg-rose-500 hover:text-white">
                                            <span>Ban berish</span>
                                        </button>
                                    @endif

                                    @if($primaryUser->telegram_id && !$primaryUser->telegram_blocked_at)
                                        <button type="button"
                                                @click="openTgReply({{ $primaryUser->id }}, @js($primaryUser->name))"
                                                class="inline-flex min-h-9 cursor-pointer items-center gap-1 rounded-md border border-sky-500/30 bg-sky-500/10 px-2.5 py-1.5 text-xs font-semibold text-sky-300 transition-colors hover:bg-sky-500 hover:text-white"
                                                title="Telegram Bot orqali javob yuborish">
                                            <span>✈️ Botdan javob</span>
                                        </button>
                                    @endif
                                @endif

                                {{-- Resolve Toggle --}}
                                @if($report->status === 'pending')
                                    <form action="{{ route('admin.reports.resolve', $report) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="inline-flex min-h-9 cursor-pointer items-center rounded-md border border-ink-border bg-ink-800 px-3 py-1.5 text-xs font-semibold text-paper transition-colors hover:bg-ink-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-400" title="Ko'rib chiqildi deb belgilash">
                                            Yopish
                                        </button>
                                    </form>
                                @endif

                                {{-- Delete --}}
                                <form action="{{ route('admin.reports.destroy', $report) }}" method="POST"
                                      data-confirm="Ushbu shikoyatni o'chirasizmi?"
                                      data-confirm-title="Shikoyatni o'chirish"
                                      data-confirm-type="danger"
                                      data-confirm-btn="Ha, o'chirish">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" aria-label="Shikoyatni o'chirish" class="inline-flex h-9 w-9 cursor-pointer items-center justify-center rounded-md border border-ink-border text-mist transition-colors hover:border-rose-500/30 hover:bg-rose-500/10 hover:text-rose-300 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-400" title="O'chirish">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-16 text-center">
                            <span class="mx-auto mb-3 flex h-11 w-11 items-center justify-center rounded-card border border-ink-border bg-ink-950 text-mist" aria-hidden="true">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V4s-1 1-4 1-5-2-8-2-4 1-4 1z"/><path d="M4 22V15"/></svg>
                            </span>
                            <p class="text-sm font-semibold text-paper">Hozircha shikoyatlar yo'q</p>
                            <p class="mt-1 text-xs text-mist">Yangi murojaatlar kelganda shu yerda ko'rinadi.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($reports->hasPages())
        <div class="border-t border-ink-border p-4 sm:p-5">
            {{ $reports->links() }}
        </div>
    @endif

    </section>

    {{-- Interactive Ban Modal --}}
    <div x-show="banModalOpen" x-cloak
         x-transition:enter="transition-opacity ease-out duration-400"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-in duration-320"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[100] flex items-center justify-center bg-black/80 p-4 backdrop-blur-md"
         @keydown.escape.window="banModalOpen = false">
        <div x-show="banModalOpen"
             x-transition:enter="ks-blur-in-enter-active"
             x-transition:enter-start="ks-blur-in-enter"
             x-transition:enter-end="ks-blur-in-enter-end"
             x-transition:leave="ks-blur-in-leave-active"
             x-transition:leave-start="ks-blur-in-leave"
             x-transition:leave-end="ks-blur-in-leave-end"
             role="dialog" aria-modal="true" aria-labelledby="ban-modal-title" class="relative w-full max-w-lg space-y-5 rounded-modal border border-ink-border bg-ink-900 p-5 text-left shadow-[0_30px_70px_rgba(0,0,0,0.95)] sm:p-6 ks-modal-card"
             @click.away="banModalOpen = false">
            <div class="flex items-center justify-between border-b border-ink-border pb-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-card border border-rose-500/20 bg-rose-500/10 font-mono text-xs font-bold text-rose-300">
                        BAN
                    </div>
                    <div>
                        <h3 id="ban-modal-title" class="text-base font-semibold text-paper">Foydalanuvchini bloklash</h3>
                        <p class="mt-0.5 text-xs text-mist">
                            Qoidabuzar: <span class="font-bold text-amber-400" x-text="targetUserName"></span>
                        </p>
                    </div>
                </div>
                <button type="button" aria-label="Oynani yopish" @click="banModalOpen = false" class="inline-flex h-9 w-9 cursor-pointer items-center justify-center rounded-md text-mist transition-colors hover:bg-ink-800 hover:text-paper focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-400">×</button>
            </div>

            <form :action="banActionUrl" method="POST" class="space-y-4">
                @csrf

                {{-- Muddat tanlash --}}
                <div class="space-y-2">
                    <label class="block text-xs font-semibold text-paper">Bloklash muddatini tanlang</label>
                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                        <label class="flex cursor-pointer items-center gap-3 rounded-card border border-ink-border bg-ink-950/70 p-3 transition-colors hover:border-rose-500/40">
                            <input type="radio" name="duration" value="1_hour" class="text-rose-600 focus:ring-rose-500">
                            <div>
                                <p class="text-xs font-semibold text-paper">1 soat</p>
                                <p class="text-[10px] text-mist">Vaqtincha ogohlantirish</p>
                            </div>
                        </label>

                        <label class="flex cursor-pointer items-center gap-3 rounded-card border border-ink-border bg-ink-950/70 p-3 transition-colors hover:border-rose-500/40">
                            <input type="radio" name="duration" value="1_day" checked class="text-rose-600 focus:ring-rose-500">
                            <div>
                                <p class="text-xs font-semibold text-paper">1 kun</p>
                                <p class="text-[10px] text-mist">24 soatga cheklash</p>
                            </div>
                        </label>

                        <label class="flex cursor-pointer items-center gap-3 rounded-card border border-ink-border bg-ink-950/70 p-3 transition-colors hover:border-rose-500/40">
                            <input type="radio" name="duration" value="1_week" class="text-rose-600 focus:ring-rose-500">
                            <div>
                                <p class="text-xs font-semibold text-paper">1 hafta</p>
                                <p class="text-[10px] text-mist">7 kunlik cheklov</p>
                            </div>
                        </label>

                        <label class="flex cursor-pointer items-center gap-3 rounded-card border border-ink-border bg-ink-950/70 p-3 transition-colors hover:border-rose-500/40">
                            <input type="radio" name="duration" value="1_month" class="text-rose-600 focus:ring-rose-500">
                            <div>
                                <p class="text-xs font-semibold text-paper">1 oy</p>
                                <p class="text-[10px] text-mist">30 kunlik cheklov</p>
                            </div>
                        </label>

                        <label class="flex cursor-pointer items-center gap-3 rounded-card border border-rose-500/30 bg-rose-500/10 p-3 transition-colors hover:border-rose-400 sm:col-span-2">
                            <input type="radio" name="duration" value="permanent" class="text-rose-600 focus:ring-rose-500">
                            <div>
                                <p class="text-xs font-semibold text-rose-300">Butun umrga (doimiy ban)</p>
                                <p class="text-[10px] text-mist">Foydalanuvchi hisobi butunlay yopiladi</p>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- Sabab --}}
                <div class="space-y-1.5">
                    <label for="ban-reason" class="block text-xs font-semibold text-paper">Ban berish sababi (ixtiyoriy)</label>
                    <input id="ban-reason" type="text" name="reason" placeholder="Masalan: Chatda haqoratli so'zlar / qoidabuzarlik"
                           class="w-full rounded-md border border-ink-border bg-ink-950 px-3.5 py-2.5 text-sm text-paper placeholder:text-mist focus:border-rose-400 focus:outline-none focus:ring-2 focus:ring-rose-400/20">
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" @click="banModalOpen = false"
                            class="inline-flex min-h-9 cursor-pointer items-center justify-center rounded-md border border-ink-border bg-ink-800 px-4 py-2 text-xs font-semibold text-paper transition-colors hover:bg-ink-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-400">
                        Bekor qilish
                    </button>
                    <button type="submit"
                            class="inline-flex min-h-9 cursor-pointer items-center justify-center rounded-md bg-rose-600 px-4 py-2 text-xs font-bold text-white transition-colors hover:bg-rose-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-rose-300">
                        Bloklashni tasdiqlash
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Interactive Telegram Bot Reply Modal --}}
    <div x-show="tgModalOpen" x-cloak
         x-transition:enter="transition-opacity ease-out duration-400"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-in duration-320"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[100] flex items-center justify-center bg-black/80 p-4 backdrop-blur-md"
         @keydown.escape.window="tgModalOpen = false">
        <div x-show="tgModalOpen"
             x-transition:enter="ks-blur-in-enter-active"
             x-transition:enter-start="ks-blur-in-enter"
             x-transition:enter-end="ks-blur-in-enter-end"
             x-transition:leave="ks-blur-in-leave-active"
             x-transition:leave-start="ks-blur-in-leave"
             x-transition:leave-end="ks-blur-in-leave-end"
             role="dialog" aria-modal="true" class="relative w-full max-w-lg space-y-5 rounded-modal border border-ink-border bg-ink-900 p-5 text-left shadow-[0_30px_70px_rgba(0,0,0,0.95)] sm:p-6 ks-modal-card"
             @click.away="tgModalOpen = false">
            <div class="flex items-center justify-between border-b border-ink-border pb-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-card border border-sky-500/20 bg-sky-500/10 font-mono text-sm font-bold text-sky-400">
                        ✈️
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-paper">Telegram Bot orqali javob yuborish</h3>
                        <p class="mt-0.5 text-xs text-mist">
                            Qabul qiluvchi: <span class="font-bold text-sky-400" x-text="tgTargetUserName"></span>
                        </p>
                    </div>
                </div>
                <button type="button" aria-label="Oynani yopish" @click="tgModalOpen = false" class="inline-flex h-9 w-9 cursor-pointer items-center justify-center rounded-md text-mist transition-colors hover:bg-ink-800 hover:text-paper">×</button>
            </div>

            <form :action="tgActionUrl" method="POST" class="space-y-4">
                @csrf
                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-paper">Sarlavha (ixtiyoriy)</label>
                    <input type="text" name="title" value="Murojaatingiz (Apellyatsiya) bo'yicha javob"
                           class="w-full rounded-md border border-ink-border bg-ink-950 px-3.5 py-2 text-sm text-paper placeholder:text-mist focus:border-sky-400 focus:outline-none">
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-paper">Xabar matni</label>
                    <textarea name="message" rows="4" required
                              placeholder="Assalomu alaykum! Apellyatsiyangiz ko'rib chiqildi..."
                              class="w-full rounded-md border border-ink-border bg-ink-950 px-3.5 py-2.5 text-sm text-paper placeholder:text-mist focus:border-sky-400 focus:outline-none"></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" @click="tgModalOpen = false"
                            class="inline-flex min-h-9 cursor-pointer items-center justify-center rounded-md border border-ink-border bg-ink-800 px-4 py-2 text-xs font-semibold text-paper transition-colors hover:bg-ink-700">
                        Bekor qilish
                    </button>
                    <button type="submit"
                            class="inline-flex min-h-9 cursor-pointer items-center justify-center rounded-md bg-sky-500 px-4 py-2 text-xs font-bold text-ink-950 transition-colors hover:bg-sky-400">
                        ✈️ Botdan yuborish
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Interactive Show Details Modal (Barcha yozganlarini ko'rish & Ban boshqaruvi) --}}
    <div x-show="showModalOpen" x-cloak
         x-transition:enter="transition-opacity ease-out duration-400"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-in duration-320"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[100] flex items-center justify-center bg-black/80 p-4 backdrop-blur-md"
         @keydown.escape.window="showModalOpen = false">
        <div x-show="showModalOpen"
             x-transition:enter="ks-blur-in-enter-active"
             x-transition:enter-start="ks-blur-in-enter"
             x-transition:enter-end="ks-blur-in-enter-end"
             x-transition:leave="ks-blur-in-leave-active"
             x-transition:leave-start="ks-blur-in-leave"
             x-transition:leave-end="ks-blur-in-leave-end"
             role="dialog" aria-modal="true" class="relative w-full max-w-2xl space-y-5 rounded-modal border border-ink-border bg-ink-900 p-5 text-left shadow-[0_30px_70px_rgba(0,0,0,0.95)] sm:p-6 ks-modal-card"
             @click.away="showModalOpen = false">
            
            {{-- Modal Header --}}
            <div class="flex items-center justify-between border-b border-ink-border pb-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-card border"
                         :class="activeReport.isFromBannedUser ? 'border-rose-500/30 bg-rose-500/15 text-rose-300' : 'border-amber-500/30 bg-amber-500/15 text-amber-400'">
                        <span class="text-base" x-text="activeReport.isFromBannedUser ? '⚖️' : '📩'"></span>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-base font-bold text-paper" x-text="activeReport.title || 'Murojaat tafsilotlari'"></h3>
                            <span class="rounded-badge px-2 py-0.5 text-[10px] font-mono font-bold"
                                  :class="activeReport.status === 'pending' ? 'bg-amber-500/15 text-amber-300 border border-amber-500/30' : 'bg-emerald-500/15 text-emerald-300 border border-emerald-500/30'"
                                  x-text="activeReport.status === 'pending' ? '⏳ Kutilmoqda' : '✅ Hal qilindi'"></span>
                        </div>
                        <p class="mt-0.5 text-xs text-mist">
                            <span x-text="activeReport.createdAt"></span> • <span class="font-semibold text-amber-400" x-text="activeReport.source"></span>
                        </p>
                    </div>
                </div>
                <button type="button" aria-label="Oynani yopish" @click="showModalOpen = false" class="inline-flex h-9 w-9 cursor-pointer items-center justify-center rounded-md text-mist transition-colors hover:bg-ink-800 hover:text-paper focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-400">✕</button>
            </div>

            {{-- Modal Content Body --}}
            <div class="space-y-4 max-h-[70vh] overflow-y-auto pr-1">

                {{-- BAN HOLATI VA TAFSILOTLARI BLOKI (Agar foydalanuvchi ban olgan bo'lsa) --}}
                <template x-if="activeReport.isBanned">
                    <div class="rounded-panel border-2 border-rose-500/40 bg-gradient-to-br from-rose-950/50 via-rose-900/30 to-ink-950 p-4 space-y-3 shadow-lg">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-rose-500/25 pb-3">
                            <div class="flex items-center gap-2.5">
                                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-rose-500/25 text-rose-300 font-bold text-base">🚫</span>
                                <div>
                                    <h4 class="text-sm font-bold text-rose-200">Foydalanuvchi hisobiga Ban o'rnatilgan</h4>
                                    <p class="text-[11px] text-rose-300/80">Cheklov muddati: <span class="font-bold text-amber-300" x-text="activeReport.banRemaining"></span></p>
                                </div>
                            </div>

                            {{-- Banni olish tugmasi --}}
                            <form :action="activeReport.unbanUrl" method="POST" class="shrink-0">
                                @csrf
                                <button type="submit"
                                        class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs rounded-btn shadow-md transition-all active:scale-95 cursor-pointer">
                                    <span>✅ Banni olish (Bandan chiqarish)</span>
                                </button>
                            </form>
                        </div>

                        {{-- Ban ma'lumotlari: Kim bergan, Qancha paytga, Nimaga --}}
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 text-xs">
                            {{-- 1. Kim bergan --}}
                            <div class="rounded-card border border-rose-500/20 bg-ink-950/80 p-3">
                                <span class="text-[10px] uppercase font-bold text-rose-300/90 block">👤 Kim bergan:</span>
                                <span class="font-bold text-paper block mt-1 truncate" x-text="activeReport.bannedByLabel"></span>
                            </div>

                            {{-- 2. Qancha paytga --}}
                            <div class="rounded-card border border-rose-500/20 bg-ink-950/80 p-3">
                                <span class="text-[10px] uppercase font-bold text-rose-300/90 block">⏳ Qancha paytga (Muddat):</span>
                                <span class="font-bold text-amber-300 block mt-1" x-text="activeReport.banDurationLabel"></span>
                                <span class="text-[10px] text-mist block mt-0.5" x-text="'Tugash vaqti: ' + activeReport.banExpiresAt"></span>
                            </div>

                            {{-- 3. Nimaga --}}
                            <div class="rounded-card border border-rose-500/20 bg-ink-950/80 p-3">
                                <span class="text-[10px] uppercase font-bold text-rose-300/90 block">⚠️ Ban sababi (Nimaga):</span>
                                <span class="font-medium text-rose-100 block mt-1 break-words italic" x-text="'«' + activeReport.banReason + '»'"></span>
                            </div>
                        </div>
                    </div>
                </template>

                {{-- Agar murojaat ban bo'yicha bo'lsa, ammo hozirda faol ban bo'lmasa --}}
                <template x-if="activeReport.isFromBannedUser && !activeReport.isBanned">
                    <div class="rounded-panel border border-emerald-500/30 bg-emerald-500/10 p-3.5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div class="flex items-center gap-2.5">
                            <span class="flex h-7 w-7 items-center justify-center rounded-full bg-emerald-500/20 text-emerald-300 font-bold text-sm">✅</span>
                            <div>
                                <h4 class="text-xs font-bold text-emerald-300">Ushbu foydalanuvchi hisobida hozirda faol ban yo'q</h4>
                                <p class="text-[11px] text-mist">Cheklov muddati tugagan yoki ma'muriyat tomonidan yechilgan (hisob faol).</p>
                            </div>
                        </div>
                        <button type="button"
                                @click="showModalOpen = false; openBan(activeReport.userId, activeReport.userName)"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-btn bg-rose-500/20 hover:bg-rose-500 text-rose-300 hover:text-white border border-rose-500/30 text-xs font-semibold transition-colors cursor-pointer shrink-0">
                            <span>🚫 Yangi ban berish</span>
                        </button>
                    </div>
                </template>

                {{-- User & Meta Information Grid --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    {{-- Yuboruvchi --}}
                    <div class="rounded-panel border border-ink-border bg-ink-950/60 p-3 space-y-1.5">
                        <span class="text-[10px] uppercase font-bold text-mist block">Yuboruvchi ma'lumotlari:</span>
                        <div class="flex items-center gap-3">
                            <img :src="activeReport.userAvatar" class="h-10 w-10 rounded-card border border-ink-border object-cover" alt="Avatar">
                            <div class="min-w-0">
                                <a :href="activeReport.userEditUrl" target="_blank" class="font-bold text-xs text-paper hover:text-amber-400 truncate block transition-colors" x-text="activeReport.userName"></a>
                                <p class="font-mono text-[11px] text-mist" x-text="activeReport.userHandle"></p>
                                <p class="font-mono text-[11px] text-amber-400" x-show="activeReport.userPhone" x-text="'📞 ' + activeReport.userPhone"></p>
                            </div>
                        </div>
                    </div>

                    {{-- Murojaat yo'nalishi / Mavzusi --}}
                    <div class="rounded-panel border border-ink-border bg-ink-950/60 p-3 space-y-1.5 flex flex-col justify-between">
                        <div>
                            <span class="text-[10px] uppercase font-bold text-mist block">Mavzu / Bo'lim:</span>
                            <p class="font-bold text-xs text-paper truncate mt-0.5" x-text="activeReport.reason || activeReport.source"></p>
                            <p class="text-[11px] text-mist mt-0.5" x-text="'Yuborilgan vaqt: ' + activeReport.createdAt"></p>
                        </div>
                        <div class="pt-1 flex items-center gap-2">
                            <a x-show="activeReport.userEditUrl" :href="activeReport.userEditUrl" target="_blank"
                               class="inline-flex items-center gap-1 text-[11px] font-semibold text-sky-400 hover:text-sky-300 hover:underline">
                                <span>Foydalanuvchini tahrirlash →</span>
                            </a>
                        </div>
                    </div>
                </div>

                {{-- HAMMA YOZGANLARI (Full Message Content) --}}
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-paper flex items-center gap-1.5">
                            <span>💬 Murojaat matni (To'liq yozilgan xabar):</span>
                        </span>
                        <span class="text-[10px] text-mist font-mono" x-text="(activeReport.messageContent || '').length + ' ta belgi'"></span>
                    </div>
                    <div class="rounded-card border border-ink-border bg-ink-950 p-4 text-xs sm:text-sm text-paper whitespace-pre-line break-words leading-relaxed select-text shadow-inner min-h-[140px] font-sans"
                         x-text="activeReport.messageContent">
                    </div>
                </div>

                {{-- Ilova qilingan havola --}}
                <template x-if="activeReport.link">
                    <div class="flex items-center gap-2 p-3 rounded-card border border-amber-500/30 bg-amber-500/10 text-xs">
                        <span class="text-amber-400 font-bold">🔗 Havola:</span>
                        <a :href="activeReport.link" target="_blank" rel="noopener noreferrer" class="font-mono text-amber-300 hover:underline break-all" x-text="activeReport.link"></a>
                    </div>
                </template>
            </div>

            {{-- Modal Footer --}}
            <div class="flex flex-wrap items-center justify-between gap-3 border-t border-ink-border pt-4">
                <div class="flex items-center gap-2 flex-wrap">
                    {{-- Hal qilindi deb belgilash --}}
                    <template x-if="activeReport.status === 'pending'">
                        <form :action="activeReport.resolveUrl" method="POST">
                            @csrf
                            <button type="submit" class="inline-flex min-h-9 items-center gap-1.5 px-3.5 py-1.5 rounded-btn bg-emerald-600/20 hover:bg-emerald-600 text-emerald-300 hover:text-white text-xs font-semibold border border-emerald-500/30 transition-colors cursor-pointer">
                                <span>✓ Hal qilindi deb belgilash</span>
                            </button>
                        </form>
                    </template>

                    {{-- Telegram Botdan javob yozish --}}
                    <template x-if="activeReport.canReplyTg">
                        <button type="button"
                                @click="showModalOpen = false; openTgReply(activeReport.userId, activeReport.userName)"
                                class="inline-flex min-h-9 items-center gap-1.5 px-3.5 py-1.5 rounded-btn bg-sky-500/15 hover:bg-sky-500 text-sky-300 hover:text-white text-xs font-semibold border border-sky-500/30 transition-colors cursor-pointer">
                            <span>✈️ Botdan javob yozish</span>
                        </button>
                    </template>
                </div>

                <button type="button" @click="showModalOpen = false"
                        class="px-4 py-2 rounded-btn bg-ink-800 hover:bg-ink-700 text-paper text-xs font-semibold border border-ink-border cursor-pointer">
                    Yopish
                </button>
            </div>
        </div>
    </div>
</div>

@endsection
