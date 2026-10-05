@if(!$event)
    {{-- Efir tugadi (host tomonidan o'chirilgan) --}}
    <div class="h-full flex flex-col items-center justify-center bg-ink-900 border border-ink-border rounded-panel shadow-soft text-center p-8 space-y-4"
        x-data="{ countdown: 10 }"
        x-init="(() => { const t = setInterval(() => { countdown--; if (countdown <= 0) { clearInterval(t); window.location.href = @js(route('live.index')); } }, 1000); })()">
        <div class="w-14 h-14 rounded-btn bg-ink-800 border border-ink-border flex items-center justify-center text-amber-400">
            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polygon points="10 8 16 12 10 16 10 8"/></svg>
        </div>
        <div>
            <h3 class="text-base font-bold text-paper font-serif">Efir tugadi</h3>
            <p class="text-xs text-mist font-mono mt-1 max-w-xs">Ustoz efirni yakunladi. Boshqa jonli efirlarni kuzatib boring.</p>
        </div>
        <a href="{{ route('live.index') }}"
            class="ks-btn-primary text-xs py-2 px-4 font-mono">
            Boshqa jonli efirlarni ko'rish →
        </a>
        <p class="text-[10px] text-mist font-mono">Bosh sahifaga o'tish: <span x-text="countdown">10</span> soniya</p>
    </div>
@else
<div class="h-full flex flex-col bg-ink-900 border border-ink-border rounded-panel shadow-soft overflow-hidden relative"
    wire:poll.2s
    x-data="{
        pinMenuId: null,
        confirmDeleteId: null,
    }"
    x-init="(() => {
        const c = document.getElementById('liveChatScroll');
        if (!c) return;
        let stick = true;
        c.addEventListener('scroll', () => { stick = c.scrollHeight - c.scrollTop - c.clientHeight < 120; });
        if (window.Livewire) {
            Livewire.hook('message.processed', () => {
                if (stick && document.body.contains(c)) {
                    requestAnimationFrame(() => { c.scrollTop = c.scrollHeight; });
                }
            });
        }
        window.addEventListener('live-chat-scroll-bottom', () => {
            if (document.body.contains(c)) {
                stick = true;
                requestAnimationFrame(() => { c.scrollTop = c.scrollHeight; });
            }
        });
        c.scrollTop = c.scrollHeight;
    })()">

    <!-- Chat Header -->
    <div class="p-3.5 border-b border-ink-border flex items-center justify-between bg-ink-950/80 backdrop-blur-md shrink-0">
        <div class="flex items-center gap-2">
            <div class="w-7 h-7 rounded-btn bg-amber-500/10 border border-amber-500/25 text-amber-400 flex items-center justify-center">
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            </div>
            <div>
                <span class="text-xs sm:text-sm font-bold text-paper font-serif block leading-tight">Jonli Muloqot</span>
                <span class="text-[10px] text-emerald-400 font-mono font-semibold flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span> Jonli yangilanadi
                </span>
            </div>
        </div>
        <div class="flex items-center gap-2.5">
            <span class="text-xs text-mist font-mono">{{ $messages->count() }} ta xabar</span>
        </div>
    </div>

    <!-- PINNED xabarlar -->
    @if($pinned->isNotEmpty())
        <div class="px-3.5 pt-3 shrink-0 space-y-2">
            @foreach($pinned as $pin)
                <div class="relative p-2.5 rounded-panel bg-amber-500/10 border border-amber-500/30 shadow-sm" wire:key="pin-{{ $pin->id }}">
                    <div class="flex items-start gap-2">
                        <svg class="w-3.5 h-3.5 text-amber-400 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="17" x2="12" y2="22"/><path d="M5 17h14v-1.76a2 2 0 0 0-1.11-1.79l-1.78-.9A2 2 0 0 1 15 10.76V6h1a2 2 0 0 0 0-4H8a2 2 0 0 0 0 4h1v4.76a2 2 0 0 1-1.11 1.79l-1.78.9A2 2 0 0 0 5 15.24Z"/></svg>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-1.5 mb-0.5 flex-wrap font-mono text-[10px]">
                                <span class="font-bold text-amber-400">{{ $pin->user?->name ?? 'Foydalanuvchi' }}</span>
                                @if(isset($topFiveIds[$pin->user_id]))
                                    <x-ui.rank-badge :rank="$topFiveIds[$pin->user_id]" size="xs" />
                                @endif
                                @if(($pin->user?->role ?? '') === 'admin')
                                    <span class="px-1.5 py-0.2 bg-[#C1392B]/15 border border-rose-500/30 text-rose-300 font-bold rounded-pill uppercase">Admin</span>
                                @elseif(($pin->user?->role ?? '') === 'teacher')
                                    <span class="px-1.5 py-0.2 bg-amber-500/10 border border-amber-500/25 text-amber-400 font-bold rounded-pill uppercase">Ustoz</span>
                                @endif
                                <span class="text-mist">{{ $pin->created_at->timezone('Asia/Tashkent')->format('H:i') }}</span>
                                @if($isHost)
                                    <button type="button" wire:click="togglePin({{ $pin->id }})" wire:loading.attr="disabled" wire:target="togglePin"
                                        class="ml-auto opacity-75 hover:opacity-100 text-amber-400 text-[10px] font-bold px-1.5 py-0.5 rounded hover:bg-amber-500/20 transition-all">
                                        Pin bekor
                                    </button>
                                @endif
                            </div>
                            <p class="text-xs text-paper leading-relaxed break-words font-sans">{{ $pin->question }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Messages Container -->
    <div id="liveChatScroll" class="flex-1 p-3.5 overflow-y-auto overflow-x-hidden space-y-2.5">
        @if($messages->isEmpty())
            <div class="h-full flex flex-col items-center justify-center text-center p-6 text-mist space-y-2">
                <div class="w-8 h-8 rounded-btn bg-ink-800 border border-ink-border flex items-center justify-center text-amber-400">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                </div>
                <p class="text-xs font-mono">Hozircha xabarlar yo'q. Birinchi bo'lib fikringizni bildiring!</p>
            </div>
        @else
            @foreach($messages as $msg)
                @php
                    $isMe       = auth()->check() && auth()->id() === $msg->user_id;
                    $uRole      = $msg->user?->role ?? '';
                    $isH        = ($uRole === 'teacher' || $uRole === 'admin') || ($msg->user && method_exists($msg->user, 'hasRole') && $msg->user->hasRole('teacher'));
                    $senderRank = $msg->user_id ? ($topFiveIds[$msg->user_id] ?? null) : null;
                    $isTopFive  = ($senderRank !== null && $senderRank >= 1 && $senderRank <= 5);
                @endphp
                <div class="flex items-start gap-2 group {{ $isMe ? 'flex-row-reverse' : '' }}" wire:key="lmsg-{{ $msg->id }}">
                    <x-ui.avatar :user="$msg->user" size="sm" shape="rounded-btn" :rank="$senderRank" link />
                    <div class="flex flex-col {{ $isMe ? 'items-end' : 'items-start' }} max-w-[80%] sm:max-w-md">
                        <div class="flex items-center gap-1.5 mb-1 {{ $isMe ? 'flex-row-reverse' : '' }} flex-wrap">
                            @if($msg->user && !$isMe)
                                <a href="{{ route('profile.show', $msg->user->username) }}" class="text-xs font-medium font-sans {{ $isH ? 'text-amber-400' : 'text-paper' }} hover:text-amber-400 hover:underline transition-colors">
                                    {{ $msg->user->name }}
                                </a>
                            @else
                                <span class="text-xs font-medium font-sans {{ $isH ? 'text-amber-400' : 'text-paper' }}">{{ $isMe ? 'Siz' : ($msg->user?->name ?? 'Foydalanuvchi') }}</span>
                            @endif
                            @if($isTopFive)
                                <x-ui.rank-badge :rank="$senderRank" size="xs" />
                            @endif
                            @if($msg->user_id === $event->host_user_id)
                                <span class="px-1.5 py-0.2 bg-amber-500/15 border border-amber-500/30 text-amber-400 font-mono text-[9px] rounded-pill uppercase">Ustoz</span>
                            @elseif($uRole === 'admin')
                                <span class="px-1.5 py-0.2 bg-[#C1392B]/15 border border-rose-500/30 text-rose-300 font-mono text-[9px] rounded-pill uppercase">Admin</span>
                            @elseif($uRole === 'teacher')
                                <span class="px-1.5 py-0.2 bg-amber-500/10 border border-amber-500/25 text-amber-400 font-mono text-[9px] rounded-pill uppercase">Ustoz</span>
                            @endif
                            <span class="text-[10px] text-mist font-mono">{{ $msg->created_at->timezone('Asia/Tashkent')->format('H:i') }}</span>
                            @if($msg->is_answered)
                                <span class="text-[10px] font-mono text-emerald-400">✓ javob berildi</span>
                            @endif

                            {{-- Like tugmasi (toggle): bosilganda +1, qayta bosilganda -1. Hisob barchada real vaqtda kopyiladi --}}
                            @if(auth()->check())
                                <button type="button"
                                    wire:click="toggleLike({{ $msg->id }})"
                                    wire:loading.attr="disabled" wire:target="toggleLike({{ $msg->id }})"
                                    wire:key="like-btn-{{ $msg->id }}"
                                    title="Like bosish"
                                    class="flex items-center gap-0.5 p-0.5 rounded transition-all {{ in_array($msg->id, $this->myLikedIds, true) ? 'text-rose-400' : 'text-mist opacity-0 group-hover:opacity-100 focus:opacity-100 hover:text-rose-400' }}">
                                    <svg class="w-3 h-3 {{ in_array($msg->id, $this->myLikedIds, true) ? 'fill-current' : '' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                                    </svg>
                                    @if($msg->likes_count > 0)
                                        <span class="text-[10px] font-mono font-bold">{{ $msg->likes_count }}</span>
                                    @endif
                                </button>
                            @else
                                <span class="flex items-center gap-0.5 text-mist" title="Like bosish uchun tizimga kiring">
                                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                                    </svg>
                                    @if($msg->likes_count > 0)
                                        <span class="text-[10px] font-mono">{{ $msg->likes_count }}</span>
                                    @endif
                                </span>
                            @endif

                            @if($isHost)
                                <div class="flex items-center gap-0.5 {{ $isMe ? 'flex-row-reverse' : '' }}">
                                    <button type="button" wire:click="togglePin({{ $msg->id }})" wire:loading.attr="disabled" wire:target="togglePin"
                                        title="Pin qilish"
                                        class="opacity-100 sm:opacity-0 sm:group-hover:opacity-100 focus:opacity-100 transition-opacity p-0.5 rounded text-mist hover:text-amber-400">
                                        <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="17" x2="12" y2="22"/><path d="M5 17h14v-1.76a2 2 0 0 0-1.11-1.79l-1.78-.9A2 2 0 0 1 15 10.76V6h1a2 2 0 0 0 0-4H8a2 2 0 0 0 0 4h1v4.76a2 2 0 0 1-1.11 1.79l-1.78.9A2 2 0 0 0 5 15.24Z"/></svg>
                                    </button>
                                    <button type="button" wire:click="markAnswered({{ $msg->id }})" wire:loading.attr="disabled" wire:target="markAnswered"
                                        title="Javob berildi"
                                        class="opacity-100 sm:opacity-0 sm:group-hover:opacity-100 focus:opacity-100 transition-opacity p-0.5 rounded text-mist hover:text-emerald-400">
                                        ✓
                                    </button>
                                </div>
                            @endif
                        </div>

                        {{-- Host (efir egasi) xabari --}}
                        @if($msg->user_id === $event->host_user_id)
                            <div class="px-3.5 py-2 rounded-panel text-xs sm:text-sm leading-relaxed shadow-sm break-words bg-amber-400 text-ink-950 font-medium rounded-tr-none">
                                {{ $msg->question }}
                            </div>
                        {{-- Me --}}
                        @elseif($isMe)
                            <div class="px-3.5 py-2 rounded-panel text-xs sm:text-sm leading-relaxed shadow-sm break-words bg-ink-800 border border-ink-border text-paper rounded-tr-none">
                                {{ $msg->question }}
                            </div>
                        @else
                            @php
                                $tierBubble = match(true) {
                                    $senderRank === 1 => 'border-[#F59E0B]/50 bg-gradient-to-r from-[#F59E0B]/10 to-ink-950/90 shadow-[0_0_12px_rgba(245,158,11,0.12)]',
                                    $senderRank === 2 => 'border-[#E2E8F0]/40 bg-gradient-to-r from-[#E2E8F0]/8 to-ink-950/90 shadow-[0_0_10px_rgba(226,232,240,0.08)]',
                                    $senderRank === 3 => 'border-[#D97706]/40 bg-gradient-to-r from-[#D97706]/8 to-ink-950/90 shadow-[0_0_10px_rgba(217,119,6,0.08)]',
                                    $senderRank === 4 || $senderRank === 5 => 'border-[#6366F1]/35 bg-gradient-to-r from-[#6366F1]/8 to-ink-950/90 shadow-[0_0_8px_rgba(99,102,241,0.08)]',
                                    default => 'bg-ink-950/80 border-ink-border',
                                };
                            @endphp
                            <div class="px-3.5 py-2 rounded-panel text-xs sm:text-sm leading-relaxed shadow-sm break-words border {{ $tierBubble }} text-paper rounded-tl-none">
                                {{ $msg->question }}
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        @endif
    </div>

    <!-- Permission notice strip -->
    <div class="px-3.5 py-1.5 bg-ink-950/80 border-t border-ink-border text-[11px] font-mono text-mist flex items-center justify-between shrink-0">
        <span>
            @if(in_array($event->permission_mode, ['view_only', 'voice_only']))
                🔒 Ma'ruza rejimi (savol berish yopiq)
            @else
                💬 Faqat yozma chat faol
            @endif
        </span>
    </div>

    <!-- Input Bar -->
    <div class="p-3 bg-ink-900 border-t border-ink-border shrink-0" x-data="{ count: 0 }">
        @if($this->canWrite)
            <form wire:submit.prevent="sendMessage" class="flex items-center gap-2">
                <div class="relative flex-1">
                    <input type="text" wire:model.defer="message" x-on:input="count = $event.target.value.length"
                        placeholder="Fikringiz yoki savolingiz..." maxlength="300"
                        class="w-full pl-3.5 pr-14 py-2 bg-ink-950/80 border border-ink-border rounded-btn text-xs font-sans text-paper placeholder-mist focus:border-amber-400 focus:outline-none">
                    <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-[10px] font-mono select-none text-mist"
                        :class="{ 'text-amber-400 font-bold': count >= 240 }">
                        <span x-text="count">0</span>/300
                    </div>
                </div>
                <button type="submit" wire:loading.attr="disabled" wire:target="sendMessage"
                    class="px-3 sm:px-3.5 py-2 bg-[#C1392B] hover:bg-[#a63024] disabled:opacity-50 text-paper font-mono font-bold text-xs rounded-btn transition-all shrink-0 flex items-center gap-1.5">
                    <span class="hidden sm:inline" wire:loading.remove wire:target="sendMessage">Yuborish</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    <span wire:loading wire:target="sendMessage">...</span>
                </button>
            </form>
        @elseif(!auth()->check() && !in_array($event->permission_mode, ['view_only', 'voice_only']))
            <div class="text-center p-2">
                <a href="{{ route('login') }}" class="text-xs font-mono text-amber-400 hover:underline">
                    Savol berish yoki chatda yozish uchun kiring →
                </a>
            </div>
        @else
            <div class="p-2.5 rounded-btn bg-ink-950 border border-ink-border text-center text-xs font-mono text-mist">
                Ushbu efir faqat ma'ruza rejimida. Savol yozish cheklangan.
            </div>
        @endif
    </div>
</div>
@endif
