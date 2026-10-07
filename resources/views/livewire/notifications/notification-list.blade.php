<div x-data="{
        showCelebration: false,
        earnedPoints: 200,
        earnedCoins: 40,
        triggerCelebration(detail) {
            this.earnedPoints = detail?.points || 200;
            this.earnedCoins = detail?.coins || 40;
            this.showCelebration = true;
            setTimeout(() => { this.showCelebration = false; }, 4500);
        }
    }"
    @bonus-claimed-animation.window="triggerCelebration($event.detail)"
    class="max-w-4xl mx-auto space-y-5 pb-16 relative">

    <!-- ── Super Bonus Celebration Floating Animation ── -->
    <div x-show="showCelebration"
         x-cloak
         x-transition:enter="transition ease-out duration-500"
         x-transition:enter-start="opacity-0 scale-75 -translate-y-8"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-90 translate-y-4"
         class="fixed inset-0 z-50 pointer-events-none flex items-center justify-center p-4">
        <div class="p-6 sm:p-8 rounded-3xl bg-ink-950/95 border-2 border-amber-400/80 shadow-[0_0_80px_rgba(245,158,11,0.6)] text-center space-y-4 max-w-sm w-full backdrop-blur-2xl ring-4 ring-amber-500/20">
            <div class="relative w-20 h-20 rounded-full bg-gradient-to-tr from-amber-500 via-yellow-400 to-amber-300 mx-auto flex items-center justify-center text-4xl shadow-2xl shadow-amber-500/50">
                🏆
            </div>
            <div>
                <span class="text-[10px] font-mono uppercase tracking-widest text-amber-400 block font-bold">Kunlik Vazifa Bajarildi</span>
                <h3 class="text-xl sm:text-2xl font-bold font-serif text-paper mt-1">Super Bonus Olingan!</h3>
                <p class="text-xs text-mist font-sans mt-1">1 soatlik mutolaa uchun hisobingizga qo'shildi:</p>
            </div>
            <div class="flex items-center justify-center gap-2.5 pt-1">
                <span class="px-3.5 py-2 rounded-xl bg-amber-500/20 border border-amber-400/80 text-amber-300 font-mono font-black text-sm flex items-center gap-1.5 shadow-lg shadow-amber-500/20">
                    <span>⭐</span>
                    <span>+<span x-text="earnedPoints">200</span> BALL</span>
                </span>
                <span class="px-3.5 py-2 rounded-xl bg-yellow-500/20 border border-yellow-400/80 text-yellow-300 font-mono font-black text-sm flex items-center gap-1.5 shadow-lg shadow-yellow-500/20">
                    <span>🪙</span>
                    <span>+<span x-text="earnedCoins">40</span> TANGA</span>
                </span>
            </div>
        </div>
    </div>

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold font-serif text-paper">Bildirishnomalar</h1>
            <p class="text-xs font-mono text-mist mt-1 flex items-center gap-1.5 flex-wrap">
                @if ($unreadCount > 0)
                    <span class="text-amber-400 font-bold">{{ $unreadCount }} ta o'qilmagan</span> bildirishnoma
                @else
                    <span>Barcha bildirishnomalar o'qilgan</span>
                @endif
                <span class="text-mist/50">·</span>
                <span class="text-mist/70">Eng so'nggi 20 tasi saqlanadi</span>
            </p>
        </div>
        <div class="flex items-center gap-3">
            @if ($unreadCount > 0)
                <button wire:click="markAllRead" class="text-xs font-mono text-amber-400 hover:text-amber-300 transition-colors">
                    O'qilgan qilish
                </button>
            @endif
            @if ($notifications->total() > 0)
                <button wire:click="clearAll" 
                        onclick="return confirm('Barcha bildirishnomalarni tozalashni tasdiqlaysizmi?') || event.stopImmediatePropagation()"
                        class="text-xs font-mono text-mist hover:text-rose-400 transition-colors">
                    Tozalash
                </button>
            @endif
        </div>
    </div>

    <!-- ── Kunlik 1 Soatlik Mutolaa Vazifasi va Super Bonus Card ── -->
    @if(isset($hourlyGoal))
        @if($hourlyGoal['is_claimed'])
            {{-- Holat 1: Bugun bonus allaqachon qabul qilingan --}}
            <div class="p-4 sm:p-5 rounded-panel bg-emerald-500/10 border border-emerald-500/25 flex items-start gap-3.5 transition-all">
                <span class="w-10 h-10 rounded-btn bg-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0 text-xl">
                    ✅
                </span>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <h4 class="text-xs sm:text-sm font-bold text-paper font-serif">Bugungi Super Bonus qabul qilingan!</h4>
                        <span class="px-2 py-0.5 rounded-badge bg-emerald-500/20 border border-emerald-500/30 text-emerald-300 text-[10px] font-mono font-bold">1 soat mutolaa · +200 ball, +40 tanga</span>
                    </div>
                    <p class="text-xs text-mist font-sans mt-1">Siz bugun 1 soatdan ko'p kitob mutolaa qilib, kunlik super bonusni qabul qildingiz. Yangi vazifa ertaga ochiladi!</p>
                    <div class="mt-2.5 flex items-center gap-2">
                        <div class="flex-1 h-2 rounded-full bg-ink-800 overflow-hidden max-w-xs">
                            <div class="h-full bg-emerald-400 rounded-full" style="width: 100%;"></div>
                        </div>
                        <span class="text-[11px] font-mono text-emerald-400 font-semibold">{{ $hourlyGoal['current_minutes'] }} / 60 daqiqa (100%)</span>
                    </div>
                </div>
                <span class="px-3 py-1.5 rounded-btn bg-emerald-500/20 border border-emerald-500/30 text-emerald-300 font-mono text-xs font-bold shrink-0 self-center hidden sm:inline-flex items-center gap-1">
                    <span>✓</span>
                    <span>Olingan</span>
                </span>
            </div>
        @elseif($hourlyGoal['is_completed'])
            {{-- Holat 2: 1 soat o'qib bo'lingan, lekin hali bonus olinmagan (OLISH TUGMASI BILAN) --}}
            <div class="p-5 sm:p-6 rounded-panel bg-gradient-to-r from-amber-500/20 via-yellow-500/15 to-ink-900 border-2 border-amber-400/80 shadow-[0_0_30px_rgba(245,158,11,0.25)] flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 transition-all">
                <div class="flex items-start gap-3.5">
                    <span class="w-12 h-12 rounded-2xl bg-gradient-to-br from-amber-400 to-yellow-500 text-ink-950 flex items-center justify-center shrink-0 text-2xl shadow-lg shadow-amber-500/30 animate-pulse">
                        🏆
                    </span>
                    <div class="space-y-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h4 class="text-sm sm:text-base font-bold text-amber-300 font-serif">Vazifa Bajarildi: Super Bonus Tayyor! 🎉</h4>
                            <span class="px-2 py-0.5 rounded-badge bg-amber-400 text-ink-950 text-[10px] font-mono font-black uppercase tracking-wider">Super Mukofot</span>
                        </div>
                        <p class="text-xs text-paper/90 font-sans">
                            Siz bugun <strong class="text-amber-300 font-mono">{{ $hourlyGoal['current_minutes'] }} daqiqa</strong> mutolaa qildingiz (reja 60 daqiqa). Mukofotni hoziroq qabul qiling:
                        </p>
                        <div class="flex items-center gap-2 pt-1 font-mono text-xs text-amber-400 font-bold">
                            <span class="px-2 py-0.5 rounded bg-ink-950/80 border border-amber-400/40">+200 ball</span>
                            <span>va</span>
                            <span class="px-2 py-0.5 rounded bg-ink-950/80 border border-yellow-400/40 text-yellow-300">+40 tanga 🪙</span>
                        </div>
                    </div>
                </div>

                {{-- OLISH TUGMASI --}}
                <div class="w-full sm:w-auto shrink-0 pt-2 sm:pt-0">
                    <button wire:click="claimHourlyBonus"
                            wire:loading.attr="disabled"
                            type="button"
                            class="w-full sm:w-auto px-5 py-3 rounded-xl bg-gradient-to-r from-amber-400 via-amber-300 to-yellow-400 hover:from-amber-300 hover:to-yellow-300 active:scale-95 text-ink-950 font-mono font-black text-xs sm:text-sm shadow-xl shadow-amber-500/40 hover:shadow-amber-500/60 transition-all flex items-center justify-center gap-2 border border-white/40 cursor-pointer">
                        <span wire:loading.remove wire:target="claimHourlyBonus" class="text-base">🎁</span>
                        <span wire:loading.remove wire:target="claimHourlyBonus">Olish (+200 ball, +40 tanga)</span>
                        <span wire:loading wire:target="claimHourlyBonus" class="inline-flex items-center gap-2">
                            <svg class="animate-spin w-4 h-4 text-ink-950" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                            <span>Qabul qilinmoqda...</span>
                        </span>
                    </button>
                </div>
            </div>
        @else
            {{-- Holat 3: Mutolaa davom etmoqda (Progress bar bilan) --}}
            <div class="p-4 sm:p-5 rounded-panel bg-ink-900 border border-ink-border flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="flex items-start gap-3.5 flex-1 min-w-0">
                    <span class="w-10 h-10 rounded-btn bg-amber-500/10 border border-amber-500/20 text-amber-400 flex items-center justify-center shrink-0 text-xl">
                        ⏱️
                    </span>
                    <div class="flex-1 min-w-0 space-y-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h4 class="text-xs sm:text-sm font-bold text-paper font-serif">Kunlik Vazifa: 1 soat mutolaa qiling!</h4>
                            <span class="px-2 py-0.5 rounded-badge bg-ink-800 text-mist border border-ink-border text-[10px] font-mono">+200 ball · +40 tanga</span>
                        </div>
                        <p class="text-xs text-mist font-sans">
                            Bugun yana <strong class="text-amber-400 font-mono">{{ $hourlyGoal['remaining'] }} daqiqa</strong> mutolaa qiling va kunlik maxsus bonusga ega bo'ling!
                        </p>
                        
                        {{-- Progress bar --}}
                        <div class="pt-2 flex items-center gap-3">
                            <div class="flex-1 h-2 rounded-full bg-ink-950 border border-ink-border overflow-hidden max-w-sm">
                                <div class="h-full bg-gradient-to-r from-amber-500 to-amber-300 rounded-full transition-all duration-500"
                                     style="width: {{ $hourlyGoal['percentage'] }}%;"></div>
                            </div>
                            <span class="text-xs font-mono text-amber-400 font-bold shrink-0">
                                {{ $hourlyGoal['current_minutes'] }} / 60 daq ({{ $hourlyGoal['percentage'] }}%)
                            </span>
                        </div>
                    </div>
                </div>

                <a href="{{ route('books.catalog') }}" class="ks-btn-primary text-xs py-2 px-3.5 shrink-0 w-full sm:w-auto text-center">
                    Mutolaa qilish →
                </a>
            </div>
        @endif
    @endif

    <!-- ── Ertalabki Uyg'onish Vazifasi (05:00–07:00, O'zbekiston vaqti) ── -->
    @if(isset($earlyBird))
        @if($earlyBird['is_claimed'])
            {{-- Bugun bajarilgan --}}
            <div class="p-4 sm:p-5 rounded-panel bg-emerald-500/10 border border-emerald-500/25 flex items-start gap-3.5">
                <span class="w-10 h-10 rounded-btn bg-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0 text-xl">
                    🌅
                </span>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <h4 class="text-xs sm:text-sm font-bold text-paper font-serif">Ertalabki uyg'onish vazifasi bajarildi!</h4>
                        <span class="px-2 py-0.5 rounded-badge bg-emerald-500/20 border border-emerald-500/30 text-emerald-300 text-[10px] font-mono font-bold">+70 ball · +10 tanga</span>
                    </div>
                    <p class="text-xs text-mist font-sans mt-1">Bugun ertaldo turib vazifani bajardingiz. Yangi vazifa ertaga 05:00 da ochiladi!</p>
                </div>
                <span class="px-3 py-1.5 rounded-btn bg-emerald-500/20 border border-emerald-500/30 text-emerald-300 font-mono text-xs font-bold shrink-0 self-center hidden sm:inline-flex items-center gap-1">
                    <span>✓</span>
                    <span>Bajarildi</span>
                </span>
            </div>
        @elseif($earlyBird['window_open'])
            {{-- Oyna ochiq: hozir bajarsa bo'ladi --}}
            <div class="p-5 sm:p-6 rounded-panel bg-gradient-to-r from-sky-500/20 via-indigo-500/15 to-ink-900 border-2 border-sky-400/70 shadow-[0_0_30px_rgba(56,189,248,0.25)] flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="flex items-start gap-3.5">
                    <span class="w-12 h-12 rounded-2xl bg-gradient-to-br from-sky-400 to-indigo-500 text-ink-950 flex items-center justify-center shrink-0 text-2xl shadow-lg shadow-sky-500/30">
                        🌅
                    </span>
                    <div class="space-y-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h4 class="text-sm sm:text-base font-bold text-sky-300 font-serif">Ertalab uyg'ondingizmi? Vazifa ochiq! 🌅</h4>
                            <span class="px-2 py-0.5 rounded-badge bg-sky-400 text-ink-950 text-[10px] font-mono font-black uppercase tracking-wider">05:00–07:00</span>
                        </div>
                        <p class="text-xs text-paper/90 font-sans">
                            Hozir O'zbekiston vaqti bilan ertalabki soat — vazifani bajaring va mukofot oling:
                        </p>
                        <div class="flex items-center gap-2 pt-1 font-mono text-xs text-sky-400 font-bold">
                            <span class="px-2 py-0.5 rounded bg-ink-950/80 border border-sky-400/40">+70 ball</span>
                            <span>va</span>
                            <span class="px-2 py-0.5 rounded bg-ink-950/80 border border-indigo-400/40 text-indigo-300">+10 tanga 🪙</span>
                        </div>
                    </div>
                </div>
                <div class="w-full sm:w-auto shrink-0 pt-2 sm:pt-0">
                    <button wire:click="claimEarlyBird"
                            wire:loading.attr="disabled"
                            type="button"
                            class="w-full sm:w-auto px-5 py-3 rounded-xl bg-gradient-to-r from-sky-400 via-sky-300 to-indigo-400 hover:from-sky-300 hover:to-indigo-300 active:scale-95 text-ink-950 font-mono font-black text-xs sm:text-sm shadow-xl shadow-sky-500/40 hover:shadow-sky-500/60 transition-all flex items-center justify-center gap-2 border border-white/40 cursor-pointer">
                        <span wire:loading.remove wire:target="claimEarlyBird" class="text-base">🌅</span>
                        <span wire:loading.remove wire:target="claimEarlyBird">Bajarish (+70 ball, +10 tanga)</span>
                        <span wire:loading wire:target="claimEarlyBird" class="inline-flex items-center gap-2">
                            <svg class="animate-spin w-4 h-4 text-ink-950" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                            <span>Bajarilmoqda...</span>
                        </span>
                    </button>
                </div>
            </div>
        @else
            {{-- Oyna yopiq: ma'lumot kartasi --}}
            <div class="p-4 sm:p-5 rounded-panel bg-ink-900 border border-ink-border flex items-start gap-3.5">
                <span class="w-10 h-10 rounded-btn bg-ink-800 border border-ink-border flex items-center justify-center shrink-0 text-xl opacity-70">
                    🌅
                </span>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <h4 class="text-xs sm:text-sm font-bold text-paper font-serif">Ertalabki Vazifa: Soat 05:00–07:00 da kiring!</h4>
                        <span class="px-2 py-0.5 rounded-badge bg-ink-800 text-mist border border-ink-border text-[10px] font-mono">+70 ball · +10 tanga</span>
                    </div>
                    <p class="text-xs text-mist font-sans mt-1">
                        Har kuni ertalab <strong class="text-sky-400 font-mono">05:00–07:00</strong> oralig'ida (O'zbekiston vaqti) saytga kirib shu tugmani bossangiz, vazifa bajariladi.
                    </p>
                </div>
            </div>
        @endif
    @endif

    <!-- Smart Streak Alert -->
    @if ($streakAlert)
        <div class="p-4 sm:p-5 rounded-panel bg-amber-500/10 border border-amber-500/25 flex items-start gap-3.5">
            <span class="w-9 h-9 rounded-btn bg-amber-500/20 text-amber-400 flex items-center justify-center shrink-0">
                <span class="ks-flame is-lit inline-block scale-75">
                    <svg class="w-4 h-4 text-amber-400 fill-amber-400" viewBox="0 0 24 24"><path d="M12 2c0 4-4 6-4 10a6 6 0 0 0 12 0c0-4-4-6-4-10z"/></svg>
                </span>
            </span>
            <div class="flex-1 min-w-0">
                <h4 class="text-xs sm:text-sm font-bold text-paper font-serif">{{ $streakAlert['title'] }}</h4>
                <p class="text-xs text-mist font-sans mt-0.5">{{ $streakAlert['body'] }}</p>
            </div>
            <a href="{{ route('books.catalog') }}" class="ks-btn-ghost text-xs py-1.5 px-3 shrink-0">O'qishni boshlash</a>
        </div>
    @endif

    <!-- Upcoming Live Event -->
    @if ($upcomingLive)
        <a href="{{ route('live.show', $upcomingLive->id) }}" class="block p-4 sm:p-5 rounded-panel bg-[#C1392B]/10 border border-rose-500/25 flex items-start gap-3.5 hover:border-rose-500/40 transition-colors">
            <span class="w-9 h-9 rounded-btn bg-[#C1392B]/20 text-rose-300 flex items-center justify-center shrink-0">
                <span class="w-2 h-2 rounded-full bg-rose-400 animate-pulse"></span>
            </span>
            <div class="flex-1 min-w-0">
                <h4 class="text-xs sm:text-sm font-bold text-paper font-serif">
                    {{ $upcomingLive->status === 'live' ? 'Hozir efirda!' : 'Jonli efir: ' . $upcomingLive->title }}
                </h4>
                <p class="text-xs text-mist font-mono mt-0.5">{{ $upcomingLive->scheduled_at->timezone('Asia/Tashkent')->format('d M, H:i') }}</p>
            </div>
            <span class="text-xs font-mono font-bold text-rose-300 shrink-0 self-center">{{ $upcomingLive->status === 'live' ? 'Qo\'shilish →' : 'Tafsilotlar →' }}</span>
        </a>
    @endif

    <!-- Notification items list -->
    <div class="bg-ink-900 rounded-panel border border-ink-border divide-y divide-ink-border overflow-hidden">
        @forelse ($notifications as $n)
            @php $link = $n['link'] ?: null; @endphp
            <div @if($link && $n['type'] !== 'daily_reading_goal') wire:click="openNotification('{{ $n['id'] }}', '{{ $link }}')" @endif
                class="p-4 sm:p-5 flex items-start gap-3.5 hover:bg-ink-800/40 transition-colors {{ $n['read_at'] ? '' : 'bg-amber-500/[0.04]' }} {{ ($link && $n['type'] !== 'daily_reading_goal') ? 'cursor-pointer' : '' }}" wire:key="notif-{{ $n['id'] }}">
                <span class="w-8 h-8 rounded-btn bg-ink-800 border border-ink-border text-amber-400 flex items-center justify-center shrink-0 text-sm">
                    @if($n['icon'] && $n['icon'] !== '🔔')
                        <span>{{ $n['icon'] }}</span>
                    @else
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                    @endif
                </span>
                <div class="flex-1 min-w-0">
                    <h4 class="text-xs sm:text-sm font-semibold text-paper font-sans">
                        {{ $n['title'] }}
                        @if(!$n['read_at'])<span class="inline-block w-1.5 h-1.5 rounded-full bg-amber-400 align-middle ml-1"></span>@endif
                    </h4>
                    <p class="text-xs text-mist font-sans mt-0.5">{{ $n['body'] }}</p>
                    <span class="text-[10px] text-mist font-mono block mt-1">{{ $n['time'] }}</span>
                </div>

                {{-- Kunlik vazifa uchun maxsus inline tugma --}}
                @if ($n['type'] === 'daily_reading_goal')
                    @if (isset($hourlyGoal) && $hourlyGoal['is_claimed'])
                        <span class="px-2.5 py-1 rounded bg-emerald-500/15 border border-emerald-500/30 text-emerald-300 font-mono text-[11px] font-bold shrink-0 self-center">✓ Olingan</span>
                    @elseif (isset($hourlyGoal) && $hourlyGoal['is_completed'])
                        <button wire:click.stop="claimHourlyBonus"
                                wire:loading.attr="disabled"
                                class="px-3.5 py-1.5 rounded-btn bg-gradient-to-r from-amber-400 to-yellow-400 hover:from-amber-300 hover:to-yellow-300 active:scale-95 text-ink-950 font-mono font-bold text-xs shrink-0 self-center shadow-md flex items-center gap-1.5">
                            <span>🎁 Olish</span>
                        </button>
                    @else
                        <a href="{{ route('books.catalog') }}" class="ks-btn-ghost text-xs py-1 px-2.5 shrink-0 self-center">
                            O'qish →
                        </a>
                    @endif
                @endif

                <div class="flex items-center gap-2 shrink-0 self-center">
                    @if (!$n['read_at'])
                        <button wire:click.stop="markRead('{{ $n['id'] }}')" 
                                title="O'qilgan deb belgilash"
                                class="w-2.5 h-2.5 rounded-full bg-amber-400 hover:scale-150 transition-transform"></button>
                    @endif
                    <button wire:click.stop="deleteNotification('{{ $n['id'] }}')" 
                            title="O'chirish"
                            class="p-1.5 rounded-btn text-mist/50 hover:text-rose-400 hover:bg-rose-500/10 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </div>
            </div>
        @empty
            <div class="p-12 text-center text-xs font-mono text-mist">
                Hali bildirishnomalar yo'q. Test topshirganingizda va nishon olganingizda shu yerda ko'rasiz.
            </div>
        @endforelse
    </div>

    @if ($notifications->hasPages())
        <div class="pt-2 font-mono text-xs">
            {{ $notifications->links('vendor.pagination.taste-livewire') }}
        </div>
    @endif
</div>
