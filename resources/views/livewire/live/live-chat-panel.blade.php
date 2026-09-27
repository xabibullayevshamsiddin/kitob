@if(!$event)
    {{-- Efir tugadi (host tomonidan o'chirilgan) — xatosiz, yoqimli yakuniy holat --}}
    <div class="h-full flex flex-col items-center justify-center bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-3xl shadow-soft text-center p-8 space-y-4"
        x-data="{ countdown: 10 }"
        x-init="(() => { const t = setInterval(() => { countdown--; if (countdown <= 0) { clearInterval(t); window.location.href = @js(route('live.index')); } }, 1000); })()">
        <div class="w-16 h-16 rounded-3xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-3xl">📺</div>
        <div>
            <h3 class="text-base font-bold text-slate-900 dark:text-white">Efir tugadi</h3>
            <p class="text-xs text-slate-400 mt-1.5 max-w-xs">Ustoz efirni yakunladi. Rahmat! Boshqa jonli efillarni kuzatib boring.</p>
        </div>
        <a href="{{ route('live.index') }}"
            class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-md transition-all active:scale-95">
        📺 Boshqa jonli efillarni ko'rish →
        </a>
        <p class="text-[11px] text-slate-400 font-mono">Bosh sahifaga o'tish: <span x-text="countdown">10</span> soniya</p>
    </div>
@else
<div class="h-full flex flex-col bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-3xl shadow-soft overflow-hidden relative"
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
    <div class="p-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-900/50 backdrop-blur-md shrink-0">
        <div class="flex items-center gap-2.5">
            <div class="w-9 h-9 rounded-2xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-lg">💬</div>
            <div>
                <span class="text-sm font-bold text-slate-900 dark:text-white block leading-tight">Jonli Muloqot</span>
                <span class="text-[11px] text-emerald-500 font-semibold flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Jonli yangilanadi
                </span>
            </div>
        </div>
        <span class="text-xs text-slate-400 font-mono">{{ $messages->count() }} ta xabar</span>
    </div>

    <!-- PINNED (mustahkamlangan) xabarlar -->
    @if($pinned->isNotEmpty())
        <div class="px-4 pt-3 shrink-0 space-y-2">
            @foreach($pinned as $pin)
                <div class="relative p-3 rounded-2xl bg-gradient-to-r from-amber-500/15 via-amber-400/10 to-transparent border border-amber-500/40 shadow-sm animate-slide-up" wire:key="pin-{{ $pin->id }}">
                    <div class="flex items-start gap-2.5">
                        <span class="text-base leading-none mt-0.5">📌</span>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-1.5 mb-1 flex-wrap">
                                <span class="text-[11px] font-bold text-amber-600 dark:text-amber-400">{{ $pin->user?->name ?? 'Foydalanuvchi' }}</span>
                                @if(($pin->user?->role ?? '') === 'admin')
                                    <span class="px-1.5 py-0.5 bg-amber-500/15 text-amber-600 dark:text-amber-400 font-bold text-[9px] rounded uppercase">Admin</span>
                                @elseif(($pin->user?->role ?? '') === 'teacher')
                                    <span class="px-1.5 py-0.5 bg-sky-500/15 text-sky-600 dark:text-sky-400 font-bold text-[9px] rounded uppercase">Ustoz</span>
                                @endif
                                <span class="text-[10px] text-amber-500/70 font-mono">{{ $pin->created_at->timezone('Asia/Tashkent')->format('H:i') }}</span>
                                @if($isHost)
                                    <button type="button" wire:click="togglePin({{ $pin->id }})" wire:loading.attr="disabled" wire:target="togglePin"
                                        class="ml-auto opacity-60 hover:opacity-100 text-amber-600 dark:text-amber-400 text-[10px] font-bold px-1.5 py-0.5 rounded-md hover:bg-amber-500/15 transition-all">
                                        📌 Pin bekor
                                    </button>
                                @endif
                            </div>
                            <p class="text-xs text-amber-900 dark:text-amber-200 leading-relaxed break-words [word-break:break-word]">{{ $pin->question }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Messages Container -->
    <div id="liveChatScroll" class="flex-1 p-4 overflow-y-auto overflow-x-hidden space-y-3">
        @if($messages->isEmpty())
            <div class="h-full flex flex-col items-center justify-center text-center p-6 text-slate-400 space-y-2">
                <span class="text-3xl">💭</span>
                <p class="text-xs">Hozircha xabarlar yo'q. Birinchi bo'lib fikringizni bildiring!</p>
            </div>
        @else
            @foreach($messages as $msg)
                @php
                    $isMe   = auth()->check() && auth()->id() === $msg->user_id;
                    $uRole  = $msg->user?->role ?? '';
                    $isH    = ($uRole === 'teacher' || $uRole === 'admin') || ($msg->user && method_exists($msg->user, 'hasRole') && $msg->user->hasRole('teacher'));
                @endphp
                <div class="flex items-start gap-2.5 group {{ $isMe ? 'flex-row-reverse' : '' }} animate-slide-up" wire:key="lmsg-{{ $msg->id }}">
                    <img src="{{ $msg->user?->avatar_url ?? 'https://ui-avatars.com/api/?name=User&background=4f46e5&color=fff' }}"
                        class="w-8 h-8 rounded-xl object-cover shrink-0 ring-1 ring-slate-200 dark:ring-slate-700 mt-0.5"
                        alt="{{ $msg->user?->name ?? 'Foydalanuvchi' }}">
                    <div class="flex flex-col {{ $isMe ? 'items-end' : 'items-start' }} max-w-[80%] sm:max-w-md">
                        <div class="flex items-center gap-1.5 mb-1 {{ $isMe ? 'flex-row-reverse' : '' }} flex-wrap">
                            <span class="text-xs font-bold {{ $isH ? 'text-sky-600 dark:text-sky-400' : 'text-slate-900 dark:text-white' }}">{{ $isMe ? 'Siz' : ($msg->user?->name ?? 'Foydalanuvchi') }}</span>
                            @if($msg->user_id === $event->host_user_id)
                                <span class="px-1.5 py-0.5 bg-rose-500/15 text-rose-500 font-bold text-[9px] rounded uppercase">🎙 Ustoz</span>
                            @elseif($uRole === 'admin')
                                <span class="px-1.5 py-0.5 bg-amber-500/15 text-amber-600 dark:text-amber-400 font-bold text-[9px] rounded uppercase">Admin</span>
                            @elseif($uRole === 'teacher')
                                <span class="px-1.5 py-0.5 bg-sky-500/15 text-sky-600 dark:text-sky-400 font-bold text-[9px] rounded uppercase">Ustoz</span>
                            @endif
                            <span class="text-[10px] text-slate-400 font-mono">{{ $msg->created_at->timezone('Asia/Tashkent')->format('H:i') }}</span>
                            @if($msg->is_answered)
                                <span class="text-[10px] font-bold text-emerald-500">✓ javob berildi</span>
                            @endif

                            @if($isHost)
                                <div class="flex items-center gap-0.5 {{ $isMe ? 'flex-row-reverse' : '' }}">
                                    <button type="button" wire:click="togglePin({{ $msg->id }})" wire:loading.attr="disabled" wire:target="togglePin"
                                        title="Muhim savol sifatida pin qilish"
                                        class="opacity-0 group-hover:opacity-100 focus:opacity-100 transition-opacity p-1 rounded {{ $msg->is_selected ? 'text-amber-500' : 'text-slate-400 hover:text-amber-500' }} hover:bg-amber-500/15">
                                        {{ $msg->is_selected ? '📌' : '📍' }}
                                    </button>
                                    <button type="button" wire:click="markAnswered({{ $msg->id }})" wire:loading.attr="disabled" wire:target="markAnswered"
                                        title="Javob berildi"
                                        class="opacity-0 group-hover:opacity-100 focus:opacity-100 transition-opacity p-1 rounded {{ $msg->is_answered ? 'text-emerald-500' : 'text-slate-400 hover:text-emerald-500' }} hover:bg-emerald-500/15">
                                        ✓
                                    </button>
                                </div>
                            @endif
                        </div>

                        {{-- Host (efir egasi) xabari — oltin / boshqalardan farqli --}}
                        @if($msg->user_id === $event->host_user_id)
                            <div class="px-4 py-2.5 rounded-2xl text-xs sm:text-sm leading-relaxed shadow-sm break-words [word-break:break-word] bg-gradient-to-br from-amber-400 to-amber-500 text-slate-900 font-medium rounded-tr-none shadow-amber-500/25">
                                {{ $msg->question }}
                            </div>
                        {{-- Me (o'zimniki) --}}
                        @elseif($isMe)
                            <div class="px-4 py-2.5 rounded-2xl text-xs sm:text-sm leading-relaxed shadow-sm break-words [word-break:break-word] bg-indigo-600 text-white rounded-tr-none shadow-indigo-600/20">
                                {{ $msg->question }}
                            </div>
                        @else
                            <div class="px-4 py-2.5 rounded-2xl text-xs sm:text-sm leading-relaxed shadow-sm break-words [word-break:break-word] bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 rounded-tl-none">
                                {{ $msg->question }}
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        @endif
    </div>

    <!-- Permission notice strip -->
    <div class="px-4 py-2 bg-slate-50 dark:bg-slate-800/60 border-t border-slate-100 dark:border-slate-800 text-[11px] text-slate-500 dark:text-slate-400 flex items-center justify-between shrink-0">
        <span>
            @if($event->permission_mode === 'chat_only')
                💬 Faqat yozma chat faol
            @elseif($event->permission_mode === 'voice_only')
                🎙️ Faqat ovozli savollar qabul qilinadi
            @elseif($event->permission_mode === 'view_only')
                🔒 Ma'ruza rejimi (savol berish yopiq)
            @else
                ✨ Chat va mikrofon ruxsat etilgan
            @endif
        </span>
    </div>

    <!-- Input Bar -->
    <div class="p-3 bg-white dark:bg-slate-900 shrink-0" x-data="{ count: 0 }">
        @if($this->canWrite)
            <form wire:submit.prevent="sendMessage" class="flex items-center gap-2">
                <div class="relative flex-1">
                    <input type="text" wire:model.defer="message" x-on:input="count = $event.target.value.length"
                        placeholder="Fikringiz yoki savolingiz..." maxlength="300"
                        class="w-full pl-4 pr-14 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-rose-500 focus:outline-none">
                    <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-[10px] font-mono select-none"
                        :class="{ 'text-slate-400': count < 240, 'text-rose-500 font-bold': count >= 240 }">
                        <span x-text="count">0</span>/300
                    </div>
                </div>
                <button type="submit" wire:loading.attr="disabled" wire:target="sendMessage"
                    class="px-4 py-2.5 bg-rose-600 hover:bg-rose-500 disabled:opacity-50 text-white font-bold text-xs rounded-xl shadow-md transition-all active:scale-95 shrink-0 flex items-center gap-1.5">
                    <span wire:loading.remove wire:target="sendMessage">Yuborish</span>
                    <span wire:loading wire:target="sendMessage">...</span>
                    <svg wire:loading.remove wire:target="sendMessage" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </button>
            </form>
        @elseif($this->canRequestVoice)
            <div class="p-2.5 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-center">
                <p class="text-xs text-amber-600 dark:text-amber-400 font-semibold mb-2">🎙️ Faqat ovozli savollar qabul qilinadi.</p>
                <button type="button" wire:click="requestVoiceSpeech"
                    class="px-4 py-2 bg-amber-500 hover:bg-amber-400 text-ink-950 font-bold text-xs uppercase tracking-wider rounded-xl shadow-md transition-all active:scale-95">
                    ✋ Navbatga turish (Ovozli savol)
                </button>
            </div>
        @elseif(!auth()->check() && in_array($event->permission_mode, ['both', 'chat_only']))
            <div class="text-center p-2">
                <a href="{{ route('login') }}" class="text-xs font-bold text-rose-500 hover:underline">
                    Savol berish yoki chatda yozish uchun kiring →
                </a>
            </div>
        @else
            <div class="p-3 rounded-2xl bg-slate-100 dark:bg-slate-800 text-center text-xs text-slate-400">
                🔒 Ushbu efir faqat ma'ruza rejimida. Savol yozish cheklangan.
            </div>
        @endif
    </div>

    @if(session('success'))
        <div class="absolute top-16 left-1/2 -translate-x-1/2 z-20 px-4 py-2 rounded-xl bg-emerald-500 text-white text-xs font-bold shadow-lg animate-slide-up">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="absolute top-16 left-1/2 -translate-x-1/2 z-20 px-4 py-2 rounded-xl bg-rose-500 text-white text-xs font-bold shadow-lg animate-slide-up">
            {{ session('error') }}
        </div>
    @endif
</div>
@endif
