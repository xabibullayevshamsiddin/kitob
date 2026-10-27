<div class="space-y-4">
    @if($activeRoom)
        <!-- Active Buddy Read Room -->
        <div class="space-y-4">
            <!-- Room Top Bar -->
            <div class="flex items-center justify-between pb-3 border-b border-ink-border">
                <div class="flex items-center gap-2">
                    <button type="button" wire:click="selectBuddyRead(null)" class="text-xs font-mono text-mist hover:text-paper px-2 py-1 rounded bg-ink-900 border border-ink-border">
                        ← Barcha xonalar
                    </button>
                    <div>
                        <h4 class="text-sm font-bold text-paper font-serif">{{ $activeRoom->title }}</h4>
                        <p class="text-[11px] text-amber-500 font-mono">👥 {{ $activeRoom->members->count() }}/{{ $activeRoom->max_members }} ishtirokchi</p>
                    </div>
                </div>

                @if(!$myMember && auth()->check())
                    <button type="button" wire:click="joinBuddyRead({{ $activeRoom->id }})" class="ks-btn-primary text-xs py-1.5 px-3">
                        + Qo'shilish
                    </button>
                @endif
            </div>

            <!-- Members Progress Tracker (StoryGraph style) -->
            <div class="p-3 rounded-card bg-ink-900 border border-ink-border space-y-2">
                <span class="text-[11px] font-mono text-mist uppercase tracking-wider block">Kitobxonlar mutolaa tezligi:</span>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                    @foreach($activeRoom->members as $member)
                        <div class="flex items-center gap-2 p-2 rounded bg-ink-950 border border-ink-border text-xs">
                            <img src="{{ $member->user?->avatar_url ?? asset('images/default-avatar.png') }}" class="w-6 h-6 rounded-full object-cover border border-amber-500/40" alt="{{ $member->user?->name }}">
                            <div class="min-w-0">
                                <span class="font-semibold text-paper truncate block text-[11px]">{{ $member->user?->name }}</span>
                                <span class="text-amber-500 font-mono text-[10px]">{{ $member->current_page }}-betda</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Spoilers-Protected Messages Thread -->
            <div class="space-y-2.5 max-h-[350px] overflow-y-auto p-2 rounded-card bg-ink-950 border border-ink-border">
                @forelse($activeRoom->messages as $msg)
                    @php
                        $myPage = $myMember?->current_page ?? 1;
                        $isAhead = $msg->page_number > $myPage;
                        $isRevealed = isset($revealedSpoilers[$msg->id]) || $msg->user_id === auth()->id();
                        $hideSpoiler = ($isAhead || $msg->is_spoiler) && !$isRevealed;
                    @endphp

                    <div class="p-2.5 rounded-card bg-ink-900/70 border border-ink-border space-y-1.5">
                        <div class="flex items-center justify-between text-[11px] font-mono">
                            <div class="flex items-center gap-1.5">
                                <span class="font-bold text-paper">{{ $msg->user?->name }}</span>
                                <span class="px-1.5 py-0.2 rounded bg-amber-500/20 text-amber-400 text-[10px]">{{ $msg->page_number }}-bet</span>
                            </div>
                            <span class="text-mist text-[10px]">{{ $msg->created_at->diffForHumans() }}</span>
                        </div>

                        @if($hideSpoiler)
                            <div class="p-3 rounded bg-amber-950/30 border border-amber-500/40 text-center space-y-1.5">
                                <div class="text-xs text-amber-400 font-bold flex items-center justify-center gap-1.5">
                                    <span>⚠️</span>
                                    <span>Spoyler himoyasi (Ushbu fikr {{ $msg->page_number }}-betga oid)</span>
                                </div>
                                <p class="text-[11px] text-mist">Siz hozir {{ $myPage }}-betdasiz. Syujetni oldindan bilib olmasligingiz uchun xabar yashirildi.</p>
                                <button type="button" wire:click="revealSpoiler({{ $msg->id }})" class="px-2.5 py-1 rounded bg-amber-500 text-slate-950 font-bold text-[11px] hover:bg-amber-400 transition-colors">
                                    Baribir o'qish (Ochish)
                                </button>
                            </div>
                        @else
                            <p class="text-xs text-paper font-sans leading-relaxed">{{ $msg->message }}</p>
                        @endif
                    </div>
                @empty
                    <div class="text-center py-8 text-xs text-mist font-mono">
                        Hozircha muhokamalar yo'q. Birinchi bo'lib fikr qoldiring! 📖
                    </div>
                @endforelse
            </div>

            <!-- Send Message Box (faqat a'zolar uchun) -->
            @if($myMember)
                <form wire:submit.prevent="sendMessage" class="p-3 rounded-card bg-ink-900 border border-ink-border space-y-2">
                    <div class="flex items-center gap-2">
                        <input type="text" wire:model.defer="newMessage" placeholder="Ushbu sahifa haqida fikringiz..." class="ks-input text-xs flex-1">
                        <div class="flex items-center gap-1 bg-ink-950 border border-ink-border rounded-btn px-2 py-1 shrink-0">
                            <span class="text-[11px] text-mist font-mono">Bet:</span>
                            <input type="number" wire:model.defer="messagePage" min="1" class="w-12 text-center text-xs font-mono font-bold bg-transparent text-amber-400 outline-none">
                        </div>
                        <button type="submit" class="ks-btn-primary text-xs py-1.5 px-3 shrink-0">
                            Yuborish
                        </button>
                    </div>
                    <label class="flex items-center gap-2 text-[11px] text-mist font-mono cursor-pointer">
                        <input type="checkbox" wire:model.defer="isSpoiler" class="rounded bg-ink-950 border-ink-border text-amber-500">
                        <span>Spoyler deb belgilash (Syujet siri saqlanadi)</span>
                    </label>
                </form>
            @elseif(auth()->check())
                <div class="text-center p-3 rounded bg-ink-900 border border-ink-border text-xs text-mist font-mono">
                    Muhokamada qatnashish uchun xonaga qo'shiling.
                </div>
            @endif
        </div>
    @else
        <!-- Rooms List & Create New Room -->
        <div class="space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-ink-border">
                <div>
                    <h4 class="text-sm font-bold text-paper font-serif">«{{ $book->title }}» bo'yicha Birgalikda Mutolaa</h4>
                    <p class="text-xs text-mist font-mono">Do'stlaringiz bilan birga o'qing va boblar bo'yicha muhokama qiling</p>
                </div>
            </div>

            <!-- Create New Buddy Read Form -->
            @auth
                <div class="p-3.5 rounded-card bg-ink-900 border border-ink-border space-y-2.5">
                    <span class="text-xs font-bold text-amber-400 font-mono block">+ Yangi mutolaa xonasi ochish</span>
                    <form wire:submit.prevent="createBuddyRead" class="flex gap-2">
                        <input type="text" wire:model.defer="newTitle" placeholder="Masalan: 3-kurs talabalari / Toshkent kitobxonlari" class="ks-input text-xs flex-1">
                        <button type="submit" class="ks-btn-primary text-xs py-1.5 px-3 shrink-0">
                            Ochish
                        </button>
                    </form>
                    @error('newTitle') <span class="text-[10px] text-rose-400 font-mono">{{ $message }}</span> @enderror
                </div>
            @else
                <div class="p-3 rounded bg-ink-900 border border-ink-border text-xs text-center text-mist font-mono">
                    Birgalikda mutolaa xonasi ochish uchun <a href="{{ route('login') }}" class="text-amber-400 underline">tizimga kiring</a>.
                </div>
            @endauth

            <!-- Existing Rooms -->
            <div class="space-y-2">
                <span class="text-[11px] font-mono text-mist uppercase tracking-wider block">Mavjud guruhlar:</span>
                @forelse($allRooms as $room)
                    <div class="p-3 rounded-card bg-ink-900/60 border border-ink-border flex items-center justify-between hover:bg-ink-800/40 transition-colors">
                        <div>
                            <h5 class="text-xs font-bold text-paper">{{ $room->title }}</h5>
                            <span class="text-[11px] text-mist font-mono">Muallif: {{ $room->creator?->name }} • {{ $room->members_count }}/{{ $room->max_members }} a'zo</span>
                        </div>
                        <button type="button" wire:click="selectBuddyRead({{ $room->id }})" class="px-3 py-1 rounded bg-ink-800 hover:bg-ink-700 text-amber-400 text-xs font-mono font-bold border border-ink-border transition-colors">
                            Kirish →
                        </button>
                    </div>
                @empty
                    <div class="text-center py-6 text-xs text-mist font-mono p-4 rounded bg-ink-900/40 border border-ink-border">
                        Hozircha birgalikda mutolaa xonalari yo'q. Birinchi xonani siz oching!
                    </div>
                @endforelse
            </div>
        </div>
    @endif
</div>
