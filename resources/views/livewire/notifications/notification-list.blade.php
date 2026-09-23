<div class="max-w-4xl mx-auto space-y-6 pb-16">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white font-manrope">Bildirishnomalar</h1>
            <p class="text-xs text-slate-400 mt-1">
                @if ($unreadCount > 0)
                    <span class="text-indigo-600 dark:text-indigo-400 font-bold">{{ $unreadCount }} ta o'qilmagan</span> bildirishnoma
                @else
                    Barcha bildirishnomalar o'qilgan
                @endif
            </p>
        </div>
        @if ($unreadCount > 0)
            <button wire:click="markAllRead" class="text-xs text-indigo-600 dark:text-indigo-400 font-bold hover:underline">
                Barchasini o'qilgan qilish
            </button>
        @endif
    </div>

    <!-- Smart Streak Alert (real-time data) -->
    @if ($streakAlert)
        <div class="p-5 rounded-3xl bg-amber-500/10 border border-amber-500/20 flex items-start gap-4">
            <span class="w-10 h-10 rounded-2xl bg-amber-500/20 text-amber-500 flex items-center justify-center text-xl shrink-0">🔥</span>
            <div class="flex-1">
                <h4 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white">{{ $streakAlert['title'] }}</h4>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $streakAlert['body'] }}</p>
            </div>
            <a href="{{ route('books.catalog') }}" class="px-4 py-2 bg-amber-500 text-slate-950 font-bold text-xs rounded-xl shadow-md shrink-0">O'qishni boshlash</a>
        </div>
    @endif

    <!-- Upcoming Live Event -->
    @if ($upcomingLive)
        <a href="{{ route('live.show', $upcomingLive->id) }}" class="block p-5 rounded-3xl bg-rose-500/10 border border-rose-500/20 flex items-start gap-4 hover:border-rose-500/40 transition-colors">
            <span class="w-10 h-10 rounded-2xl bg-rose-500/20 text-rose-500 flex items-center justify-center text-xl shrink-0">🔴</span>
            <div class="flex-1">
                <h4 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white">
                    {{ $upcomingLive->status === 'live' ? 'Hozir efirda!' : 'Jonli efir: ' . $upcomingLive->title }}
                </h4>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $upcomingLive->scheduled_at->timezone('Asia/Tashkent')->format('d M, H:i') }}</p>
            </div>
            <span class="text-xs font-bold text-rose-500 shrink-0 self-center">{{ $upcomingLive->status === 'live' ? 'Qo\'shilish →' : 'Tafsilotlar →' }}</span>
        </a>
    @endif

    <!-- Notification items list -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-soft divide-y divide-slate-100 dark:divide-slate-800">
        @forelse ($notifications as $n)
            <div class="p-5 flex items-start gap-4 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors {{ $n['read_at'] ? '' : 'bg-indigo-50/30 dark:bg-indigo-950/10' }}" wire:key="notif-{{ $n['id'] }}">
                <span class="w-10 h-10 rounded-2xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xl shrink-0">
                    {{ $n['icon'] }}
                </span>
                <div class="flex-1 min-w-0">
                    <h4 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white">{{ $n['title'] }}</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $n['body'] }}</p>
                    <span class="text-[10px] text-slate-400 block mt-1">{{ $n['time'] }}</span>
                </div>
                @if (!$n['read_at'])
                    <button wire:click="markRead('{{ $n['id'] }}')" title="O'qilgan deb belgilash"
                        class="w-2 h-2 rounded-full bg-indigo-600 shrink-0 mt-2 hover:scale-150 transition-transform"></button>
                @endif
            </div>
        @empty
            <div class="p-12 text-center text-sm text-slate-400">
                🔔 Hali bildirishnomalar yo'q. Test topshirganingizda va nishon olganingizda shu yerda ko'rasiz.
            </div>
        @endforelse
    </div>
</div>
