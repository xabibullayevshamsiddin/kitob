<div wire:poll.4s class="flex flex-col h-full bg-ink-900 border border-ink-border rounded-card overflow-hidden shadow-2xl" id="book-discussion-chat-root">
    
    <!-- ── Header ── -->
    <div class="p-3.5 sm:p-4 bg-ink-950/90 border-b border-ink-border flex items-center justify-between gap-3 shrink-0 backdrop-blur-sm">
        <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-full bg-amber-500/15 border border-amber-500/30 text-amber-400 flex items-center justify-center text-sm font-bold">
                💬
            </div>
            <div>
                <h3 class="text-xs sm:text-sm font-bold font-serif text-paper flex items-center gap-1.5">
                    <span>Kitobxonlar suhbati va izohlari</span>
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse" title="Jonli muloqot faol"></span>
                </h3>
                <p class="text-[10px] sm:text-[11px] text-mist font-mono">
                    {{ $totalCommentsCount }} ta fikr • Jonli suhbat
                </p>
            </div>
        </div>

        <!-- Filter Page Pill -->
        <div class="flex items-center gap-1 text-[11px] font-mono">
            <button type="button" 
                    wire:click="$set('filterPage', 'all')"
                    class="px-2 py-0.5 rounded-badge transition-colors {{ $filterPage === 'all' ? 'bg-amber-500/20 text-amber-300 font-bold border border-amber-500/30' : 'text-mist hover:text-paper bg-ink-900' }}">
                Barchasi
            </button>
            @if(!empty($selectedPage))
                <button type="button" 
                        wire:click="$set('filterPage', '{{ $selectedPage }}')"
                        class="px-2 py-0.5 rounded-badge transition-colors {{ $filterPage == $selectedPage ? 'bg-amber-500 text-ink-950 font-bold' : 'text-mist hover:text-paper bg-ink-900' }}"
                        title="{{ $selectedPage }}-betdagi izohlarni saralash">
                    📄 {{ $selectedPage }}-bet
                </button>
            @endif
        </div>
    </div>

    <!-- ── Messages / Comments Feed ── -->
    <div class="flex-1 p-3.5 sm:p-5 overflow-y-auto space-y-4 max-h-[550px] min-h-[280px]" id="book-chat-messages-container">
        @forelse($comments as $comment)
            <div class="p-3.5 sm:p-4 rounded-xl bg-ink-950/60 border border-ink-border/80 space-y-2.5 transition-all hover:border-amber-500/30"
                 id="chat-comment-{{ $comment->id }}">
                
                <!-- Comment Header -->
                <div class="flex items-start justify-between gap-2">
                    <div class="flex items-center gap-2.5">
                        <img src="{{ $comment->user->avatar_url }}" 
                             alt="{{ $comment->user->name }}" 
                             class="w-7 h-7 sm:w-8 sm:h-8 rounded-full object-cover border border-ink-border shrink-0">
                        <div>
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <span class="text-xs font-bold text-paper">{{ $comment->user->name }}</span>
                                @if($comment->user->isAdmin())
                                    <span class="px-1.5 py-0.2 rounded-badge bg-rose-500/15 border border-rose-500/30 text-rose-400 text-[9px] font-mono font-bold">Admin</span>
                                @elseif($comment->user->isTeacher())
                                    <span class="px-1.5 py-0.2 rounded-badge bg-purple-500/15 border border-purple-500/30 text-purple-400 text-[9px] font-mono font-bold">Ustoz</span>
                                @endif
                                
                                @if($comment->page_number)
                                    <span class="px-1.5 py-0.2 rounded-badge bg-amber-500/10 border border-amber-500/25 text-amber-300 text-[10px] font-mono font-semibold">
                                        📄 {{ $comment->page_number }}-bet
                                    </span>
                                @endif
                            </div>
                            <div class="flex items-center gap-2 text-[10px] text-mist font-mono mt-0.5">
                                <span>{{ $comment->created_at->diffForHumans() }}</span>
                                @if($comment->rating)
                                    <span>•</span>
                                    <span class="text-amber-400 font-bold">
                                        @for($i = 1; $i <= 5; $i++)
                                            <span class="{{ $i <= $comment->rating ? 'text-amber-400' : 'text-slate-600' }}">★</span>
                                        @endfor
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($comment->canDelete(auth()->user()))
                        <button type="button" 
                                wire:click="deleteComment({{ $comment->id }})" 
                                wire:confirm="Bu izohni o'chirishni xohlaysizmi?"
                                class="text-mist hover:text-rose-400 p-1 text-xs transition-colors shrink-0" 
                                title="O'chirish">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                        </button>
                    @endif
                </div>

                <!-- Comment Message Text -->
                <div class="text-xs sm:text-sm text-mist leading-relaxed font-sans whitespace-pre-line pl-9 sm:pl-10">
                    {{ $comment->comment }}
                </div>

                <!-- Action Bar: Like & Reply -->
                <div class="flex items-center gap-4 pl-9 sm:pl-10 text-[11px] text-mist">
                    <button type="button" 
                            wire:click="toggleLike({{ $comment->id }})"
                            class="inline-flex items-center gap-1 hover:text-rose-400 transition-colors group">
                        <svg class="w-3.5 h-3.5 transition-transform group-active:scale-125 {{ $comment->isLikedBy(auth()->user()) ? 'text-rose-500 fill-rose-500' : 'text-mist' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                        </svg>
                        <span class="font-mono">{{ $comment->likes_count }}</span>
                    </button>

                    @auth
                        <button type="button" 
                                wire:click="setReplyTo({{ $comment->id }})"
                                class="hover:text-amber-400 flex items-center gap-1 transition-colors">
                            <span>💬</span>
                            <span>Javob yozish</span>
                        </button>
                    @endauth
                </div>

                <!-- Threaded Replies (Ichki javoblar) -->
                @if($comment->replies->isNotEmpty())
                    <div class="pl-8 sm:pl-10 space-y-2 pt-1 border-l-2 border-ink-border/60 ml-4 sm:ml-5">
                        @foreach($comment->replies as $reply)
                            <div class="p-2.5 rounded-lg bg-ink-900/90 border border-ink-border/50 space-y-1.5" id="chat-comment-{{ $reply->id }}">
                                <div class="flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-2">
                                        <img src="{{ $reply->user->avatar_url }}" alt="{{ $reply->user->name }}" class="w-5 h-5 rounded-full object-cover border border-ink-border">
                                        <span class="text-[11px] font-bold text-paper">{{ $reply->user->name }}</span>
                                        <span class="text-[9px] text-mist font-mono">{{ $reply->created_at->diffForHumans() }}</span>
                                    </div>
                                    @if($reply->canDelete(auth()->user()))
                                        <button type="button" 
                                                wire:click="deleteComment({{ $reply->id }})"
                                                wire:confirm="O'chirilsinmi?"
                                                class="text-mist hover:text-rose-400 p-0.5 text-xs">×</button>
                                    @endif
                                </div>
                                <p class="text-xs text-mist leading-relaxed whitespace-pre-line">{{ $reply->comment }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif

            </div>
        @empty
            <div class="py-12 text-center space-y-2">
                <span class="text-3xl block">💬</span>
                <p class="text-sm font-bold font-serif text-paper">Ushbu kitobda hali suhbat boshlanmagan.</p>
                <p class="text-xs text-mist max-w-sm mx-auto">
                    Kitob haqida birinchi bo'lib o'z fikringizni yozing yoki kitobxonlar bilan muhokamani boshlang!
                </p>
            </div>
        @endforelse
    </div>

    <!-- ── Bottom Input & Reply Box ── -->
    <div class="p-3 sm:p-4 bg-ink-950 border-t border-ink-border shrink-0 space-y-2.5">
        @auth
            @if(auth()->user()->isBanned())
                <div class="p-3 rounded-xl bg-rose-950/30 border border-rose-500/35 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-start sm:items-center gap-3 min-w-0">
                        <div class="w-9 h-9 rounded-lg bg-rose-500/15 border border-rose-500/30 text-rose-300 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2" stroke-width="2"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        </div>
                        <div class="space-y-0.5 min-w-0">
                            <p class="text-xs font-bold text-rose-300 font-mono flex flex-wrap items-center gap-1.5">
                                <span>🔒 Izoh yozish qulflangan (Siz bloklangansiz!)</span>
                                <span class="px-1.5 py-0.5 rounded bg-rose-500/20 border border-rose-500/30 text-[10px] text-rose-200">
                                    {{ auth()->user()->ban_remaining }}
                                </span>
                            </p>
                            <p class="text-[11px] text-mist font-sans">
                                <span class="text-rose-200/90 font-mono">Sabab: {{ auth()->user()->ban_reason ?? 'Qoidabuzarlik' }}.</span>
                                ⚖️ Noto'g'ri ban berilgan bo'lsa, mana bu joyga yozib qoldiring:
                            </p>
                        </div>
                    </div>
                    <a href="{{ route('contact', ['appeal' => 1, 'user_id' => auth()->id()]) }}"
                       class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 rounded-lg bg-rose-500/20 hover:bg-rose-500/30 border border-rose-500/40 text-rose-200 font-bold text-xs shrink-0 transition-colors">
                        <span>📩 Aloqa bo'limi</span>
                        <span>→</span>
                    </a>
                </div>
            @else
            <!-- Reply Banner Indicator -->
            @if($replyToId)
                <div class="flex items-center justify-between px-3 py-1.5 rounded-btn bg-amber-500/10 border border-amber-500/25 text-xs text-amber-300">
                    <div class="flex items-center gap-1.5 truncate">
                        <span>↩ Javob berilmoqda:</span>
                        <strong class="text-paper">{{ $replyToUserName }}</strong>
                        <span class="text-mist text-[11px] italic truncate">"{{ $replyToSnippet }}"</span>
                    </div>
                    <button type="button" wire:click="cancelReply" class="text-mist hover:text-paper font-mono px-1">×</button>
                </div>
            @endif

            <!-- Composer form -->
            <form wire:submit.prevent="sendMessage" class="space-y-2"
                  x-data="{
                      isMobile: window.innerWidth < 640,
                      init() {
                          window.addEventListener('resize', () => {
                              this.isMobile = window.innerWidth < 640;
                          }, { passive: true });
                      }
                  }">
                <div class="flex items-center justify-between gap-2 text-xs">
                    <!-- Page Tag Selector -->
                    <div class="flex items-center gap-1.5">
                        <span class="text-[11px] text-mist font-mono">Qaysi bet haqida:</span>
                        <select wire:model="selectedPage" class="bg-ink-900 border border-ink-border text-paper rounded-btn text-xs px-2 py-0.5 focus:border-amber-400 outline-none">
                            <option value="">Umumiy kitob haqida</option>
                            @php
                                $maxPages = max(100, $book ? ($book->pages()->max('page_number') ?? 100) : 100);
                            @endphp
                            @for($p = 1; $p <= $maxPages; $p++)
                                <option value="{{ $p }}">{{ $p }}-bet</option>
                            @endfor
                        </select>
                    </div>

                    <!-- Optional 5-Star Rating (if not replying) -->
                    @if(!$replyToId)
                        <div class="hidden sm:flex items-center gap-1">
                            <span class="text-[11px] text-mist font-mono">Baho:</span>
                            <div class="flex items-center">
                                @for($s = 1; $s <= 5; $s++)
                                    <button type="button" 
                                            wire:click="$set('rating', {{ $rating === $s ? 'null' : $s }})" 
                                            class="text-sm px-0.5 transition-transform hover:scale-125 focus:outline-none {{ ($rating && $rating >= $s) ? 'text-amber-400' : 'text-slate-600' }}"
                                            title="{{ $s }} yulduz">
                                        ★
                                    </button>
                                @endfor
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Textarea + Send button row -->
                <div class="relative flex items-end gap-2">
                    <textarea wire:model.defer="message" 
                              rows="2" 
                              required 
                              minlength="1"
                              maxlength="2000"
                              placeholder="{{ $replyToId ? $replyToUserName . 'ga javob...' : 'Fikringizni yozing...' }}"
                              :placeholder="isMobile 
                                  ? '{{ $replyToId ? $replyToUserName . 'ga javob...' : 'Fikringizni yozing...' }}' 
                                  : '{{ $replyToId ? $replyToUserName . 'ga javob yozish...' : 'Fikr yoki savolingizni yozing...' }}'"
                              class="ks-input flex-1 text-xs sm:text-sm resize-none pr-10 min-h-[50px]"></textarea>

                    <button type="submit" 
                            wire:loading.attr="disabled"
                            class="ks-btn-primary px-4 py-2.5 h-[50px] text-xs font-bold inline-flex items-center gap-1.5 shrink-0 shadow-lg shadow-amber-500/20 active:scale-95">
                        <span wire:loading.remove>Yuborish ➔</span>
                        <span wire:loading class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10" stroke-width="4" stroke="currentColor" class="opacity-25"/><path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" class="opacity-75"/></svg>
                        </span>
                    </button>
                </div>
            </form>
            @endif
        @else
            <div class="p-3 text-center space-y-1.5">
                <p class="text-xs text-mist">Kitobxonlar suhbatiga qo'shilish va izoh yozish uchun tizimga kiring.</p>
                <a href="{{ route('login') }}" class="ks-btn-primary text-xs py-1.5 px-3.5 inline-block">
                    Tizimga kirish ➔
                </a>
            </div>
        @endauth
    </div>

</div>

@push('scripts')
<script>
    document.addEventListener('livewire:load', function () {
        window.addEventListener('book-chat-scroll-bottom', () => {
            const container = document.getElementById('book-chat-messages-container');
            if (container) {
                setTimeout(() => {
                    container.scrollTop = container.scrollHeight;
                }, 100);
            }
        });

        // Listen for flipbook page changes and notify Livewire
        window.addEventListener('flipbook:pagechange', (e) => {
            if (e.detail && e.detail.page && window.Livewire) {
                Livewire.emit('book:page-changed', e.detail.page);
            }
        });
    });
</script>
@endpush
