<div class="max-w-5xl mx-auto space-y-6 pb-16">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-paper font-serif">{{ __('site.live.title') }}</h1>
            <p class="text-xs sm:text-sm text-mist font-sans mt-1">{{ __('site.live.subtitle') }}</p>
        </div>

        @if (auth()->check() && auth()->user()->isAdminOrTeacher())
            <div class="flex items-center gap-2 shrink-0">
                <button wire:click="openStudioModal"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-btn bg-[#C1392B] hover:bg-[#a63024] text-paper font-mono font-bold text-xs uppercase tracking-wider shadow-sm transition-all">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-paper opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-paper"></span>
                    </span>
                    <span>{{ __('site.live.start_btn') }}</span>
                </button>

                <button wire:click="endAllLiveStreams" wire:confirm="{{ __('site.live.end_all_confirm') }}"
                    class="inline-flex items-center gap-1.5 px-3 py-2.5 rounded-btn bg-ink-900 hover:bg-[#C1392B]/20 border border-ink-border hover:border-rose-500/40 text-mist hover:text-rose-300 font-mono text-xs uppercase tracking-wider transition-all"
                    title="{{ __('site.live.end_all_title') }}">
                    {{ __('site.live.end_all') }}
                </button>
            </div>
        @endif
    </div>

    @if (session()->has('success'))
        <div x-init="window.toast({ type: 'success', message: @js(session('success')), title: 'Muvaffaqiyatli!' })"></div>
    @endif
    @if (session()->has('error'))
        <div x-init="window.toast({ type: 'error', message: @js(session('error')), title: 'Xatolik yuz berdi' })"></div>
    @endif

    <!-- ── EFIRNI SOZLASH VA BOSHLASH MODALI ── -->
    @if($showStudioModal)
        <div class="fixed inset-0 z-[9999] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-ink-950/85 backdrop-blur-md transition-opacity" wire:click="closeStudioModal"></div>

            <div class="flex min-h-full items-center justify-center p-3 sm:p-6 text-center">
                <div class="relative w-full max-w-xl transform rounded-panel bg-ink-900 border border-ink-border shadow-2xl text-left my-8 overflow-hidden z-10"
                     @click.stop>

                    <!-- Modal Header -->
                    <div class="flex items-center justify-between border-b border-ink-border p-4 sm:p-5 bg-ink-950/60">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-btn bg-[#C1392B]/15 border border-rose-500/30 text-rose-300 flex items-center justify-center text-sm shrink-0">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3Z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2"/><line x1="12" y1="19" x2="12" y2="22"/></svg>
                            </div>
                            <div>
                                <h2 class="text-sm sm:text-base font-bold text-paper font-serif">{{ __('site.live.setup_title') }}</h2>
                                <p class="text-xs text-mist font-mono">{{ __('site.live.setup_sub') }}</p>
                            </div>
                        </div>
                        <button type="button" wire:click="closeStudioModal" class="p-1.5 rounded-btn text-mist hover:text-paper hover:bg-ink-800 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <!-- Form Body -->
                    <form wire:submit.prevent="startLiveStream" class="p-4 sm:p-5 space-y-3.5 max-h-[calc(85vh-130px)] overflow-y-auto">
                        <!-- Title -->
                        <div>
                            <label class="block text-xs font-mono uppercase tracking-wider text-mist mb-1">
                                {{ __('site.live.topic_label') }} <span class="text-rose-400">*</span>
                            </label>
                            <input type="text" wire:model.defer="newTitle" placeholder="{{ __('site.live.topic_placeholder') }}"
                                class="w-full px-3.5 py-2 bg-ink-950/80 border border-ink-border rounded-btn text-xs text-paper placeholder-mist focus:border-amber-400 focus:outline-none">
                            @error('newTitle') <p class="text-xs font-mono text-rose-300 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Book Select -->
                        <div>
                            <label class="block text-xs font-mono uppercase tracking-wider text-mist mb-1">
                                {{ __('site.live.book_label') }}
                            </label>
                            <select wire:model.defer="newBookId"
                                class="w-full px-3.5 py-2 bg-ink-950/80 border border-ink-border rounded-btn text-xs text-paper focus:border-amber-400 focus:outline-none font-sans">
                                <option value="">{{ __('site.live.book_none') }}</option>
                                @foreach($books as $b)
                                    <option value="{{ $b->id }}">{{ $b->title }} ({{ $b->author }})</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Description -->
                        <div>
                            <label class="block text-xs font-mono uppercase tracking-wider text-mist mb-1">
                                {{ __('site.live.desc_label') }}
                            </label>
                            <textarea wire:model.defer="newDescription" rows="2" placeholder="{{ __('site.live.desc_placeholder') }}"
                                class="w-full px-3.5 py-2 bg-ink-950/80 border border-ink-border rounded-btn text-xs text-paper placeholder-mist focus:border-amber-400 focus:outline-none resize-none font-sans"></textarea>
                        </div>

                        <!-- Permission Mode Selector -->
                        <div class="space-y-2 pt-1">
                            <label class="block text-xs font-mono uppercase tracking-wider text-mist">
                                {{ __('site.live.perm_label') }}
                            </label>
                            <p class="text-xs text-mist font-mono mb-2">{{ __('site.live.perm_sub') }}</p>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 font-mono">
                                <!-- Option 1: Both -->
                                <label class="relative flex items-start gap-2.5 p-2.5 rounded-btn border cursor-pointer transition-all {{ $newPermissionMode === 'both' ? 'bg-amber-500/10 border-amber-400/50' : 'bg-ink-950/80 border-ink-border' }}">
                                    <input type="radio" wire:model="newPermissionMode" value="both" class="mt-0.5 text-amber-500 focus:ring-0">
                                    <div class="text-xs">
                                        <span class="font-bold text-paper block">{{ __('site.live.perm_both') }}</span>
                                        <span class="text-mist text-[10px] block mt-0.5">{{ __('site.live.perm_both_sub') }}</span>
                                    </div>
                                </label>

                                <!-- Option 2: Chat only -->
                                <label class="relative flex items-start gap-2.5 p-2.5 rounded-btn border cursor-pointer transition-all {{ $newPermissionMode === 'chat_only' ? 'bg-amber-500/10 border-amber-400/50' : 'bg-ink-950/80 border-ink-border' }}">
                                    <input type="radio" wire:model="newPermissionMode" value="chat_only" class="mt-0.5 text-amber-500 focus:ring-0">
                                    <div class="text-xs">
                                        <span class="font-bold text-paper block">{{ __('site.live.perm_chat') }}</span>
                                        <span class="text-mist text-[10px] block mt-0.5">{{ __('site.live.perm_chat_sub') }}</span>
                                    </div>
                                </label>

                                <!-- Option 3: Voice only -->
                                <label class="relative flex items-start gap-2.5 p-2.5 rounded-btn border cursor-pointer transition-all {{ $newPermissionMode === 'voice_only' ? 'bg-amber-500/10 border-amber-400/50' : 'bg-ink-950/80 border-ink-border' }}">
                                    <input type="radio" wire:model="newPermissionMode" value="voice_only" class="mt-0.5 text-amber-500 focus:ring-0">
                                    <div class="text-xs">
                                        <span class="font-bold text-paper block">{{ __('site.live.perm_voice') }}</span>
                                        <span class="text-mist text-[10px] block mt-0.5">{{ __('site.live.perm_voice_sub') }}</span>
                                    </div>
                                </label>

                                <!-- Option 4: View only -->
                                <label class="relative flex items-start gap-2.5 p-2.5 rounded-btn border cursor-pointer transition-all {{ $newPermissionMode === 'view_only' ? 'bg-amber-500/10 border-amber-400/50' : 'bg-ink-950/80 border-ink-border' }}">
                                    <input type="radio" wire:model="newPermissionMode" value="view_only" class="mt-0.5 text-amber-500 focus:ring-0">
                                    <div class="text-xs">
                                        <span class="font-bold text-paper block">{{ __('site.live.perm_view') }}</span>
                                        <span class="text-mist text-[10px] block mt-0.5">{{ __('site.live.perm_view_sub') }}</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Modal Actions Footer -->
                        <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-ink-border">
                            <button type="button" wire:click="closeStudioModal"
                                class="px-3.5 py-1.5 rounded-btn border border-ink-border text-xs font-mono text-mist hover:text-paper transition-colors">
                                {{ __('site.live.cancel') }}
                            </button>
                            <button type="submit"
                                class="px-4 py-1.5 rounded-btn bg-[#C1392B] hover:bg-[#a63024] text-paper font-mono font-bold text-xs uppercase tracking-wider transition-all">
                                {{ __('site.live.start_now') }}
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    @endif

    <!-- ── EFIRLAR FILTERLARI VA QIDIRUV ── -->
    <div class="ks-panel bg-ink-900 border border-ink-border p-4 sm:p-5 space-y-3.5">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
            <!-- Status Tabs -->
            <div class="flex items-center gap-1.5 p-1 bg-ink-950/80 border border-ink-border rounded-btn overflow-x-auto">
                <button type="button" wire:click="setStatusFilter('all')"
                    class="px-3 py-1.5 rounded-btn text-xs font-mono font-medium transition-all flex items-center gap-1.5 whitespace-nowrap {{ $statusFilter === 'all' ? 'bg-ink-800 text-amber-400 border border-ink-border shadow-sm' : 'text-mist hover:text-paper' }}">
                    <span>{{ __('site.live.filter_all') }}</span>
                    <span class="px-1.5 py-0.2 rounded-pill text-[10px] {{ $statusFilter === 'all' ? 'bg-ink-900 text-amber-400' : 'bg-ink-800 text-mist' }}">
                        {{ $totalActiveCount }}
                    </span>
                </button>
                <button type="button" wire:click="setStatusFilter('live')"
                    class="px-3 py-1.5 rounded-btn text-xs font-mono font-medium transition-all flex items-center gap-1.5 whitespace-nowrap {{ $statusFilter === 'live' ? 'bg-[#C1392B]/15 text-rose-300 border border-rose-500/30' : 'text-mist hover:text-rose-300' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-rose-400 animate-pulse"></span>
                    <span>{{ __('site.live.filter_live') }}</span>
                    <span class="px-1.5 py-0.2 rounded-pill text-[10px] bg-rose-500/20 text-rose-300">
                        {{ $liveCount }}
                    </span>
                </button>
                <button type="button" wire:click="setStatusFilter('scheduled')"
                    class="px-3 py-1.5 rounded-btn text-xs font-mono font-medium transition-all flex items-center gap-1.5 whitespace-nowrap {{ $statusFilter === 'scheduled' ? 'bg-amber-500/10 text-amber-400 border border-amber-500/25' : 'text-mist hover:text-amber-400' }}">
                    <span>{{ __('site.live.filter_scheduled') }}</span>
                    <span class="px-1.5 py-0.2 rounded-pill text-[10px] bg-amber-500/20 text-amber-400">
                        {{ $scheduledCount }}
                    </span>
                </button>
            </div>

            <!-- Qidiruv qatori -->
            <div class="relative flex-1 md:max-w-xs">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-mist">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </span>
                <input type="text" wire:model.debounce.300ms="search" placeholder="{{ __('site.live.search_placeholder') }}"
                    class="w-full pl-8 pr-7 py-1.5 bg-ink-950/80 border border-ink-border rounded-btn text-xs font-sans text-paper placeholder-mist focus:border-amber-400 focus:outline-none transition-all">
                @if($search)
                    <button type="button" wire:click="$set('search', '')" class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-mist hover:text-paper font-mono text-xs">
                        ✕
                    </button>
                @endif
            </div>
        </div>

        <!-- 2-qator: Qo'shimcha filtrlar -->
        <div class="flex flex-wrap items-center justify-between gap-3 pt-2.5 border-t border-ink-border text-xs font-mono">
            <div class="flex flex-wrap items-center gap-3">
                <div class="flex items-center gap-1.5">
                    <span class="text-mist">{{ __('site.live.book_filter') }}</span>
                    <select wire:model="bookFilter"
                        class="px-2.5 py-1 bg-ink-950/80 border border-ink-border rounded-btn text-xs text-paper focus:border-amber-400 focus:outline-none">
                        <option value="">{{ __('site.live.book_filter_all') }}</option>
                        @foreach($books as $b)
                            <option value="{{ $b->id }}">{{ $b->title }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center gap-1.5">
                    <span class="text-mist">{{ __('site.live.perm_filter') }}</span>
                    <select wire:model="permissionFilter"
                        class="px-2.5 py-1 bg-ink-950/80 border border-ink-border rounded-btn text-xs text-paper focus:border-amber-400 focus:outline-none">
                        <option value="all">{{ __('site.live.perm_filter_all') }}</option>
                        <option value="both">{{ __('site.live.perm_filter_both') }}</option>
                        <option value="chat_only">{{ __('site.live.perm_filter_chat') }}</option>
                        <option value="voice_only">{{ __('site.live.perm_filter_voice') }}</option>
                        <option value="view_only">{{ __('site.live.perm_filter_view') }}</option>
                    </select>
                </div>
            </div>

            @if($hasActiveFilters)
                <button type="button" wire:click="resetFilters"
                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-btn bg-ink-800 hover:bg-ink-700/60 border border-ink-border text-amber-400 text-xs transition-all">
                    <span>{{ __('site.live.clear_filters') }}</span>
                    <span>✕</span>
                </button>
            @endif
        </div>
    </div>

    <!-- ── EFIRLAR RO'YXATI ── -->
    @if ($upcoming->isEmpty())
        <div class="p-10 rounded-panel bg-ink-900 border border-ink-border text-center space-y-3">
            @if($hasActiveFilters)
                <div class="w-10 h-10 mx-auto rounded-btn bg-ink-800 border border-ink-border flex items-center justify-center text-amber-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <h2 class="text-sm font-bold text-paper font-serif">{{ __('site.live.no_results_t') }}</h2>
                <p class="text-xs text-mist font-mono max-w-sm mx-auto">{{ __('site.live.no_results_s') }}</p>
                <button type="button" wire:click="resetFilters"
                    class="ks-btn-ghost text-xs py-1.5 px-3.5 font-mono">
                    {{ __('site.live.show_all') }}
                </button>
            @else
                <div class="w-10 h-10 mx-auto rounded-btn bg-ink-800 border border-ink-border flex items-center justify-center text-amber-400">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polygon points="10 8 16 12 10 16 10 8"/></svg>
                </div>
                <h2 class="text-sm font-bold text-paper font-serif">{{ __('site.live.empty_title') }}</h2>
                <p class="text-xs text-mist font-sans max-w-sm mx-auto">{{ __('site.live.empty_sub') }}</p>
            @endif
        </div>
    @else
        @foreach ($upcoming as $event)
            <div class="p-6 sm:p-8 rounded-panel bg-ink-900 border {{ $event->is_live ? 'border-[#C1392B]/50' : 'border-ink-border' }} text-paper shadow-soft relative overflow-hidden space-y-4" wire:key="event-{{ $event->id }}">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-pill {{ $event->is_live ? 'bg-[#C1392B]/15 border border-rose-500/30 text-rose-300' : 'bg-ink-800 border border-ink-border text-mist' }} text-[11px] font-mono font-bold uppercase tracking-wider">
                        <span class="w-1.5 h-1.5 rounded-full {{ $event->is_live ? 'bg-rose-400 animate-pulse' : 'bg-mist' }}"></span>
                        <span>{{ $event->is_live ? __('site.live.now_live') : __('site.live.next_up', ['time' => $event->scheduled_at?->timezone('Asia/Tashkent')->format('d M, H:i')]) }}</span>
                    </div>

                    <!-- Permission mode badge -->
                    <span class="text-[10px] font-mono px-2 py-0.5 rounded-pill bg-ink-800 border border-ink-border text-mist">
                        @if($event->permission_mode === 'chat_only')
                            {{ __('site.live.perm_filter_chat') }}
                        @elseif($event->permission_mode === 'voice_only')
                            {{ __('site.live.perm_filter_voice') }}
                        @elseif($event->permission_mode === 'view_only')
                            {{ __('site.live.perm_filter_view') }}
                        @else
                            {{ __('site.live.perm_filter_both') }}
                        @endif
                    </span>
                </div>

                <h2 class="text-xl sm:text-2xl font-bold font-serif text-paper leading-snug">{{ $event->title }}</h2>

                @if ($event->description)
                    <p class="text-xs sm:text-sm text-mist max-w-xl leading-relaxed font-sans">{{ $event->description }}</p>
                @endif

                <div class="flex flex-wrap items-center gap-4 text-xs font-mono text-mist">
                    @if ($event->book)
                        <span class="text-amber-400 font-medium flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20 M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                            <span>{{ $event->book->title }}</span>
                        </span>
                    @endif
                    @if ($event->hostUser)
                        <span>{{ __('site.live.host') }}: <strong class="text-paper">{{ $event->hostUser->name }}</strong></span>
                    @endif
                </div>

                <div class="pt-2 flex flex-wrap items-center gap-2.5">
                    <a href="{{ route('live.show', $event) }}"
                       class="inline-flex items-center gap-1.5 px-4 py-2 bg-[#C1392B] hover:bg-[#a63024] text-paper font-mono font-bold text-xs uppercase tracking-wider rounded-btn transition-all">
                        <span>{{ __('site.live.enter_hall') }}</span>
                        <span>→</span>
                    </a>

                    @if (auth()->check() && auth()->user()->isAdminOrTeacher())
                        <button wire:click="endEvent({{ $event->id }})" wire:confirm="{{ __('site.live.end_confirm', ['title' => $event->title]) }}"
                            class="inline-flex items-center gap-1.5 px-3 py-2 bg-ink-800 hover:bg-[#C1392B]/20 border border-ink-border text-mist hover:text-rose-300 font-mono text-xs rounded-btn transition-all">
                            {{ __('site.live.end') }}
                        </button>
                    @endif
                </div>
            </div>
        @endforeach

        @if ($upcoming->hasPages())
            <div class="pt-2 font-mono text-xs">
                {{ $upcoming->links('vendor.pagination.taste-livewire') }}
            </div>
        @endif

        <!-- Question submission box -->
        <div class="p-5 rounded-panel bg-ink-900 border border-ink-border space-y-3.5">
            <h3 class="text-xs font-mono uppercase tracking-wider text-mist">{{ __('site.live.ask_title') }}</h3>
            <form wire:submit.prevent="submitQuestion" class="flex flex-col sm:flex-row gap-2">
                <input type="text" wire:model.defer="question" placeholder="{{ __('site.live.ask_placeholder') }}" maxlength="300"
                    class="flex-1 px-3.5 py-2 bg-ink-950/80 border border-ink-border rounded-btn text-xs text-paper placeholder-mist focus:border-amber-400 focus:outline-none">
                <button type="submit" wire:loading.attr="disabled"
                    class="ks-btn-primary text-xs py-2 px-4 disabled:opacity-50 shrink-0">
                    {{ __('site.live.send') }}
                </button>
            </form>
            @error('question') <p class="text-rose-300 font-mono text-xs">{{ $message }}</p> @enderror

            @if ($myQuestions->isNotEmpty())
                <div class="pt-3 border-t border-ink-border space-y-2">
                    <span class="text-[10px] font-mono uppercase tracking-wider text-mist">{{ __('site.live.my_questions') }}</span>
                    @foreach ($myQuestions as $q)
                        <div class="flex items-center justify-between text-xs" wire:key="q-{{ $q->id }}">
                            <span class="text-mist truncate max-w-md font-sans">{{ $q->question }}</span>
                            <span class="{{ $q->is_answered ? 'text-emerald-400' : ($q->is_selected ? 'text-amber-400' : 'text-mist') }} shrink-0 font-mono text-[11px]">
                                {{ $q->is_answered ? __('site.live.q_answered') : ($q->is_selected ? __('site.live.q_selected') : __('site.live.q_pending')) }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endif

</div>
