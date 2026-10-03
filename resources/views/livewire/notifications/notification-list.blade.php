<div class="max-w-4xl mx-auto space-y-5 pb-16">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold font-serif text-paper">Bildirishnomalar</h1>
            <p class="text-xs font-mono text-mist mt-1">
                @if ($unreadCount > 0)
                    <span class="text-amber-400 font-bold">{{ $unreadCount }} ta o'qilmagan</span> bildirishnoma
                @else
                    Barcha bildirishnomalar o'qilgan
                @endif
            </p>
        </div>
        @if ($unreadCount > 0)
            <button wire:click="markAllRead" class="text-xs font-mono text-amber-400 hover:text-amber-300 transition-colors">
                Barchasini o'qilgan qilish
            </button>
        @endif
    </div>

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
            <a href="{{ route('books.catalog') }}" class="ks-btn-primary text-xs py-1.5 px-3 shrink-0">O'qishni boshlash</a>
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
            <div @if($link) wire:click="openNotification('{{ $n['id'] }}', '{{ $link }}')" @endif
                class="p-4 sm:p-5 flex items-start gap-3.5 hover:bg-ink-800/40 transition-colors {{ $n['read_at'] ? '' : 'bg-amber-500/[0.04]' }} {{ $link ? 'cursor-pointer' : '' }}" wire:key="notif-{{ $n['id'] }}">
                <span class="w-8 h-8 rounded-btn bg-ink-800 border border-ink-border text-amber-400 flex items-center justify-center shrink-0 text-sm">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                </span>
                <div class="flex-1 min-w-0">
                    <h4 class="text-xs sm:text-sm font-semibold text-paper font-sans">{{ $n['title'] }} @if(!$n['read_at'])<span class="inline-block w-1.5 h-1.5 rounded-full bg-amber-400 align-middle ml-1"></span>@endif</h4>
                    <p class="text-xs text-mist font-sans mt-0.5">{{ $n['body'] }}</p>
                    <span class="text-[10px] text-mist font-mono block mt-1">{{ $n['time'] }}</span>
                </div>
                @if (!$n['read_at'])
                    <button wire:click.stop="markRead('{{ $n['id'] }}')" title="O'qilgan deb belgilash"
                        class="w-2 h-2 rounded-full bg-amber-400 shrink-0 mt-2 hover:scale-150 transition-transform"></button>
                @endif
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
