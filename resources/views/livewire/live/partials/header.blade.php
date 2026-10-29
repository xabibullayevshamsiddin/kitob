    <!-- ── 1. TOP HEADER & STATUS BAR ── -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-ink-900 border border-ink-border p-4 sm:p-5 rounded-panel shadow-soft">
        <div class="flex items-center gap-3">
            <a href="{{ $event->group_id ? route('groups.show', $event->group_id) : route('live.index') }}" class="p-2 rounded-btn bg-ink-950/80 border border-ink-border text-mist hover:text-paper hover:bg-ink-800 transition-colors" title="{{ $event->group_id ? 'Guruhga qaytish' : 'Orqaga' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h1 class="text-base sm:text-xl font-bold font-serif text-paper truncate max-w-xl">{{ $event->title }}</h1>
                    @if($event->group)
                        <a href="{{ route('groups.show', $event->group) }}" class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-pill bg-amber-500/15 border border-amber-500/30 text-amber-400 hover:text-amber-300 text-[11px] font-mono font-medium transition-colors">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 20h5v-2a3 3 0 0 0-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span>{{ $event->group->name }}</span>
                        </a>
                    @endif
                    @if($event->is_live)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-pill bg-[#C1392B]/15 border border-rose-500/30 text-rose-300 text-[11px] font-mono font-bold uppercase tracking-wider">
                            <span class="w-1.5 h-1.5 rounded-full bg-rose-400 animate-pulse"></span>
                            <span>{{ __('site.live.filter_live') }}</span>
                        </span>
                    @else
                        <span class="px-2 py-0.5 rounded-pill bg-ink-800 border border-ink-border text-mist text-[11px] font-mono uppercase">
                            {{ ucfirst($event->status) }}
                        </span>
                    @endif
                </div>
                <p class="text-xs font-mono text-mist mt-0.5 flex flex-wrap items-center gap-2">
                    @if($event->hostUser)
                        <span>{{ __('site.live.host') }}: <strong class="text-paper">{{ $event->hostUser->name }}</strong></span>
                    @endif
                    @if($event->book)
                        <span>• {{ $event->book->title }}</span>
                    @endif
                    <span>• {{ $event->scheduled_at?->timezone('Asia/Tashkent')->format('d M, H:i') }}</span>
                </p>
            </div>
        </div>

        <!-- Right: Current Permission Pill & Host Quick End -->
        <div class="flex items-center gap-2.5 shrink-0">
            <!-- Permission indicator badge -->
            <div class="px-3 py-1.5 rounded-btn bg-ink-950/80 border border-ink-border text-mist font-mono text-xs font-medium flex items-center gap-1.5">
                @if(in_array($event->permission_mode, ['view_only', 'voice_only']))
                    <span>{{ __('site.live.perm_filter_view') }}</span>
                @else
                    <span>{{ __('site.live.perm_filter_chat') }}</span>
                @endif
            </div>

            @if($isHost || $canManage)
                @if($event->status === 'live')
                    <button wire:click="endLiveStream" wire:confirm="{{ __('site.live.end_confirm', ['title' => '']) }}"
                        @click="try { localStorage.removeItem('kitobxon_live_state'); } catch(e) {}"
                        class="px-3.5 py-1.5 bg-[#C1392B] hover:bg-[#a63024] text-paper font-mono font-bold text-xs uppercase tracking-wider rounded-btn transition-all">
                        {{ __('site.live.end') }}
                    </button>
                @else
                    <button wire:click="restartLiveStream"
                        class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-paper font-mono font-bold text-xs uppercase tracking-wider rounded-btn transition-all">
                        ▶ {{ __('site.live.restart') }}
                    </button>
                @endif
            @endif
        </div>
    </div>


