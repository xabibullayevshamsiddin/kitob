<div class="max-w-5xl mx-auto space-y-8 pb-16">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white font-manrope">{{ __('site.live.title') }}</h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">{{ __('site.live.subtitle') }}</p>
        </div>

        @if (auth()->check() && auth()->user()->isAdminOrTeacher())
            <div class="flex items-center gap-2 shrink-0">
                <button wire:click="openStudioModal"
                    class="inline-flex items-center gap-2.5 px-5 py-3 rounded-2xl bg-gradient-to-r from-rose-600 via-rose-500 to-amber-500 hover:from-rose-500 hover:to-amber-400 text-white font-bold text-xs uppercase tracking-wider shadow-lg shadow-rose-600/30 active:scale-95 transition-all group">
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-white"></span>
                    </span>
                    <span>{{ __('site.live.start_btn') }}</span>
                </button>

                <button wire:click="endAllLiveStreams" wire:confirm="{{ __('site.live.end_all_confirm') }}"
                    class="inline-flex items-center gap-2 px-4 py-3 rounded-2xl bg-white/5 hover:bg-rose-500/15 border border-slate-700 hover:border-rose-500/40 text-slate-400 hover:text-rose-300 font-bold text-xs uppercase tracking-wider transition-all"
                    title="{{ __('site.live.end_all_title') }}">
                    {{ __('site.live.end_all') }}
                </button>
            </div>
        @endif
    </div>

    @if (session()->has('success'))
        <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 text-sm font-semibold flex items-center gap-2">
            <span>✓</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if (session()->has('error'))
        <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-600 dark:text-rose-400 text-sm font-semibold flex items-center gap-2">
            <span>⚠</span>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- ── EFIRNI SOZLASH VA BOSHLASH MODALI ── -->
    @if($showStudioModal)
        <div class="fixed inset-0 z-[9999] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <!-- Fullscreen Dark Blur Backdrop covering whole screen including header -->
            <div class="fixed inset-0 bg-black/85 backdrop-blur-md transition-opacity" wire:click="closeStudioModal"></div>

            <!-- Centering container with scrolling padding -->
            <div class="flex min-h-full items-center justify-center p-3 sm:p-6 text-center">
                <div class="relative w-full max-w-2xl transform rounded-3xl bg-ink-900 border border-white/10 shadow-2xl text-left my-8 overflow-hidden z-10"
                     @click.stop>

                    <!-- Modal Header -->
                    <div class="flex items-center justify-between border-b border-white/10 p-5 sm:p-6 bg-white/[0.02]">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-rose-500 to-amber-500 flex items-center justify-center text-white text-lg shadow-md shadow-rose-500/20 shrink-0">
                                🎙️
                            </div>
                            <div>
                                <h2 class="text-base sm:text-lg font-black text-white font-manrope">{{ __('site.live.setup_title') }}</h2>
                                <p class="text-xs text-slate-400">{{ __('site.live.setup_sub') }}</p>
                            </div>
                        </div>
                        <button type="button" wire:click="closeStudioModal" class="p-2 rounded-xl text-slate-400 hover:text-white hover:bg-white/10 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <!-- Form Body with max-h and scroll if needed on short screens -->
                    <form wire:submit.prevent="startLiveStream" class="p-5 sm:p-6 space-y-4 max-h-[calc(85vh-130px)] overflow-y-auto">
                        <!-- Title -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5">
                                {{ __('site.live.topic_label') }} <span class="text-rose-400">*</span>
                            </label>
                            <input type="text" wire:model.defer="newTitle" placeholder="{{ __('site.live.topic_placeholder') }}"
                                class="w-full px-4 py-2.5 bg-ink-950 border border-white/10 rounded-xl text-sm text-white placeholder-slate-500 focus:ring-2 focus:ring-amber-500 focus:outline-none">
                            @error('newTitle') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Book Select -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5">
                                {{ __('site.live.book_label') }}
                            </label>
                            <select wire:model.defer="newBookId"
                                class="w-full px-4 py-2.5 bg-ink-950 border border-white/10 rounded-xl text-sm text-white focus:ring-2 focus:ring-amber-500 focus:outline-none">
                                <option value="">{{ __('site.live.book_none') }}</option>
                                @foreach($books as $b)
                                    <option value="{{ $b->id }}">{{ $b->title }} ({{ $b->author }})</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Description -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5">
                                {{ __('site.live.desc_label') }}
                            </label>
                            <textarea wire:model.defer="newDescription" rows="2" placeholder="{{ __('site.live.desc_placeholder') }}"
                                class="w-full px-4 py-2 bg-ink-950 border border-white/10 rounded-xl text-sm text-white placeholder-slate-500 focus:ring-2 focus:ring-amber-500 focus:outline-none"></textarea>
                        </div>

                        <!-- Permission Mode Selector -->
                        <div class="space-y-2 pt-1">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-300">
                                {{ __('site.live.perm_label') }}
                            </label>
                            <p class="text-xs text-slate-400 mb-2">{{ __('site.live.perm_sub') }}</p>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                <!-- Option 1: Both -->
                                <label class="relative flex items-start gap-3 p-3 rounded-2xl border cursor-pointer transition-all {{ $newPermissionMode === 'both' ? 'bg-amber-400/10 border-amber-400/50 ring-1 ring-amber-400/30' : 'bg-ink-950 border-white/10 hover:border-white/20' }}">
                                    <input type="radio" wire:model="newPermissionMode" value="both" class="mt-0.5 text-amber-500 focus:ring-amber-500">
                                    <div class="text-xs">
                                        <span class="font-bold text-white block">{{ __('site.live.perm_both') }}</span>
                                        <span class="text-slate-400 text-[11px] block mt-0.5">{{ __('site.live.perm_both_sub') }}</span>
                                    </div>
                                </label>

                                <!-- Option 2: Chat only -->
                                <label class="relative flex items-start gap-3 p-3 rounded-2xl border cursor-pointer transition-all {{ $newPermissionMode === 'chat_only' ? 'bg-amber-400/10 border-amber-400/50 ring-1 ring-amber-400/30' : 'bg-ink-950 border-white/10 hover:border-white/20' }}">
                                    <input type="radio" wire:model="newPermissionMode" value="chat_only" class="mt-0.5 text-amber-500 focus:ring-amber-500">
                                    <div class="text-xs">
                                        <span class="font-bold text-white block">{{ __('site.live.perm_chat') }}</span>
                                        <span class="text-slate-400 text-[11px] block mt-0.5">{{ __('site.live.perm_chat_sub') }}</span>
                                    </div>
                                </label>

                                <!-- Option 3: Voice only -->
                                <label class="relative flex items-start gap-3 p-3 rounded-2xl border cursor-pointer transition-all {{ $newPermissionMode === 'voice_only' ? 'bg-amber-400/10 border-amber-400/50 ring-1 ring-amber-400/30' : 'bg-ink-950 border-white/10 hover:border-white/20' }}">
                                    <input type="radio" wire:model="newPermissionMode" value="voice_only" class="mt-0.5 text-amber-500 focus:ring-amber-500">
                                    <div class="text-xs">
                                        <span class="font-bold text-white block">{{ __('site.live.perm_voice') }}</span>
                                        <span class="text-slate-400 text-[11px] block mt-0.5">{{ __('site.live.perm_voice_sub') }}</span>
                                    </div>
                                </label>

                                <!-- Option 4: View only -->
                                <label class="relative flex items-start gap-3 p-3 rounded-2xl border cursor-pointer transition-all {{ $newPermissionMode === 'view_only' ? 'bg-amber-400/10 border-amber-400/50 ring-1 ring-amber-400/30' : 'bg-ink-950 border-white/10 hover:border-white/20' }}">
                                    <input type="radio" wire:model="newPermissionMode" value="view_only" class="mt-0.5 text-amber-500 focus:ring-amber-500">
                                    <div class="text-xs">
                                        <span class="font-bold text-white block">{{ __('site.live.perm_view') }}</span>
                                        <span class="text-slate-400 text-[11px] block mt-0.5">{{ __('site.live.perm_view_sub') }}</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Modal Actions Footer (Pinned) -->
                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-white/10">
                            <button type="button" wire:click="closeStudioModal"
                                class="px-5 py-2.5 rounded-xl border border-white/10 text-xs font-bold text-slate-300 hover:bg-white/5 hover:text-white transition-colors">
                                {{ __('site.live.cancel') }}
                            </button>
                            <button type="submit"
                                class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-rose-600 via-rose-500 to-amber-500 hover:from-rose-500 hover:to-amber-400 text-white font-bold text-xs uppercase tracking-wider shadow-lg shadow-rose-600/30 active:scale-95 transition-all">
                                {{ __('site.live.start_now') }}
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    @endif

    <!-- ── EFIRLAR FILTERLARI VA QIDIRUV ── -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-3xl p-4 sm:p-5 shadow-soft space-y-4">
        <!-- 1-qator: Holat bo'yicha filter tugmalari (Tabs) & Qidiruv -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
            <!-- Status Tabs -->
            <div class="flex items-center gap-1.5 p-1 bg-slate-100 dark:bg-slate-800/80 rounded-2xl overflow-x-auto">
                <button type="button" wire:click="setStatusFilter('all')"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 whitespace-nowrap {{ $statusFilter === 'all' ? 'bg-white dark:bg-slate-900 text-slate-900 dark:text-white shadow-sm' : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-200' }}">
                    <span>{{ __('site.live.filter_all') }}</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $statusFilter === 'all' ? 'bg-slate-200 dark:bg-slate-800 text-slate-900 dark:text-white' : 'bg-slate-200/60 dark:bg-slate-700 text-slate-500' }}">
                        {{ $totalActiveCount }}
                    </span>
                </button>
                <button type="button" wire:click="setStatusFilter('live')"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 whitespace-nowrap {{ $statusFilter === 'live' ? 'bg-rose-600 text-white shadow-md shadow-rose-600/30' : 'text-slate-500 hover:text-rose-500 dark:hover:text-rose-400' }}">
                    <span class="w-2 h-2 rounded-full bg-rose-500 {{ $statusFilter === 'live' ? 'bg-white' : '' }} animate-ping"></span>
                    <span>{{ __('site.live.filter_live') }}</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $statusFilter === 'live' ? 'bg-rose-700 text-white' : 'bg-rose-500/10 text-rose-500' }}">
                        {{ $liveCount }}
                    </span>
                </button>
                <button type="button" wire:click="setStatusFilter('scheduled')"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 whitespace-nowrap {{ $statusFilter === 'scheduled' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'text-slate-500 hover:text-indigo-500 dark:hover:text-indigo-400' }}">
                    <span>{{ __('site.live.filter_scheduled') }}</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $statusFilter === 'scheduled' ? 'bg-indigo-700 text-white' : 'bg-indigo-500/10 text-indigo-400' }}">
                        {{ $scheduledCount }}
                    </span>
                </button>
            </div>

            <!-- Qidiruv qatori -->
            <div class="relative flex-1 md:max-w-xs">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </span>
                <input type="text" wire:model.debounce.300ms="search" placeholder="{{ __('site.live.search_placeholder') }}"
                    class="w-full pl-9 pr-8 py-2 bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/80 rounded-2xl text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-rose-500 focus:outline-none transition-all">
                @if($search)
                    <button type="button" wire:click="$set('search', '')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                        ✕
                    </button>
                @endif
            </div>
        </div>

        <!-- 2-qator: Qo'shimcha filtrlar (Kitob bo'yicha & Ruxsat rejimi) -->
        <div class="flex flex-wrap items-center justify-between gap-3 pt-2 border-t border-slate-100 dark:border-slate-800/80 text-xs">
            <div class="flex flex-wrap items-center gap-3">
                <!-- Kitob filtri -->
                <div class="flex items-center gap-1.5">
                    <span class="text-slate-400 font-medium">{{ __('site.live.book_filter') }}</span>
                    <select wire:model="bookFilter"
                        class="px-3 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-200 font-medium focus:ring-2 focus:ring-rose-500 focus:outline-none">
                        <option value="">{{ __('site.live.book_filter_all') }}</option>
                        @foreach($books as $b)
                            <option value="{{ $b->id }}">{{ $b->title }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Ruxsat rejimi filtri -->
                <div class="flex items-center gap-1.5">
                    <span class="text-slate-400 font-medium">{{ __('site.live.perm_filter') }}</span>
                    <select wire:model="permissionFilter"
                        class="px-3 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-800 dark:text-slate-200 font-medium focus:ring-2 focus:ring-rose-500 focus:outline-none">
                        <option value="all">{{ __('site.live.perm_filter_all') }}</option>
                        <option value="both">{{ __('site.live.perm_filter_both') }}</option>
                        <option value="chat_only">{{ __('site.live.perm_filter_chat') }}</option>
                        <option value="voice_only">{{ __('site.live.perm_filter_voice') }}</option>
                        <option value="view_only">{{ __('site.live.perm_filter_view') }}</option>
                    </select>
                </div>
            </div>

            <!-- Clear filters button -->
            @if($hasActiveFilters)
                <button type="button" wire:click="resetFilters"
                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-500 text-xs font-semibold transition-all">
                    <span>{{ __('site.live.clear_filters') }}</span>
                    <span>✕</span>
                </button>
            @endif
        </div>
    </div>

    <!-- ── EFIRLAR RO'YXATI ── -->
    @if ($upcoming->isEmpty())
        <div class="p-12 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-soft text-center space-y-4">
            @if($hasActiveFilters)
                <span class="text-4xl block">🔍</span>
                <h2 class="text-base font-bold text-slate-900 dark:text-white">{{ __('site.live.no_results_t') }}</h2>
                <p class="text-xs text-slate-400 max-w-sm mx-auto">{{ __('site.live.no_results_s') }}</p>
                <button type="button" wire:click="resetFilters"
                    class="mt-2 px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-xs font-bold text-slate-700 dark:text-slate-300 transition-all">
                    {{ __('site.live.show_all') }}
                </button>
            @else
                <span class="text-5xl block animate-bounce">📺</span>
                <h2 class="text-base font-bold text-slate-900 dark:text-white">{{ __('site.live.empty_title') }}</h2>
                <p class="text-xs text-slate-400 max-w-sm mx-auto">{{ __('site.live.empty_sub') }}</p>
            @endif
        </div>
    @else
        @foreach ($upcoming as $event)
            <div class="p-8 sm:p-10 rounded-3xl bg-gradient-to-br {{ $event->is_live ? 'from-rose-950 via-slate-900 to-slate-900 border-rose-900/50 ring-1 ring-rose-500/20' : 'from-indigo-950 via-slate-900 to-slate-900 border-indigo-800/40' }} border text-white shadow-2xl relative overflow-hidden space-y-5" wire:key="event-{{ $event->id }}">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full {{ $event->is_live ? 'bg-rose-500/20 text-rose-400' : 'bg-indigo-500/20 text-indigo-300' }} text-xs font-bold uppercase tracking-wider">
                        <span class="w-2 h-2 rounded-full {{ $event->is_live ? 'bg-rose-500 animate-ping' : 'bg-indigo-400' }}"></span>
                        <span>{{ $event->is_live ? __('site.live.now_live') : __('site.live.next_up', ['time' => $event->scheduled_at?->timezone('Asia/Tashkent')->format('d M, H:i')]) }}</span>
                    </div>

                    <!-- Permission mode badge -->
                    <span class="text-[11px] font-mono px-2.5 py-1 rounded-lg bg-white/5 border border-white/10 text-slate-300">
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

                <h2 class="text-2xl sm:text-4xl font-black font-manrope leading-tight">{{ $event->title }}</h2>

                @if ($event->description)
                    <p class="text-xs sm:text-sm text-slate-300 max-w-xl leading-relaxed">{{ $event->description }}</p>
                @endif

                <div class="flex flex-wrap items-center gap-4 text-xs">
                    @if ($event->book)
                        <span class="text-amber-400 font-semibold">📖 {{ $event->book->title }}</span>
                    @endif
                    @if ($event->hostUser)
                        <span class="text-slate-400">{{ __('site.live.host') }} <strong class="text-white">{{ $event->hostUser->name }}</strong></span>
                    @endif
                </div>

                <div class="pt-2 flex flex-wrap items-center gap-3">
                    <a href="{{ route('live.show', $event) }}"
                       class="inline-flex items-center gap-2 px-6 py-3.5 bg-rose-600 hover:bg-rose-500 text-white font-bold text-sm rounded-xl shadow-lg shadow-rose-600/30 transition-all hover:scale-105 active:scale-95">
                        <span>{{ __('site.live.enter_hall') }}</span>
                        <span>→</span>
                    </a>

                    @if (auth()->check() && auth()->user()->isAdminOrTeacher())
                        <button wire:click="endEvent({{ $event->id }})" wire:confirm="{{ __('site.live.end_confirm', ['title' => $event->title]) }}"
                            class="inline-flex items-center gap-2 px-4 py-3.5 bg-white/5 hover:bg-rose-500/15 border border-white/10 hover:border-rose-500/40 text-slate-300 hover:text-rose-300 font-semibold text-xs rounded-xl transition-all">
                            {{ __('site.live.end') }}
                        </button>
                    @endif
                </div>
            </div>
        @endforeach

        @if ($upcoming->hasPages())
            <div class="pt-2">
                {{ $upcoming->links('vendor.pagination.taste-livewire') }}
            </div>
        @endif

        <!-- Question submission box -->
        <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-soft space-y-4">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">{{ __('site.live.ask_title') }}</h3>
            <form wire:submit.prevent="submitQuestion" class="flex flex-col sm:flex-row gap-2">
                <input type="text" wire:model.defer="question" placeholder="{{ __('site.live.ask_placeholder') }}" maxlength="300"
                    class="flex-1 px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-rose-500 focus:outline-none">
                <button type="submit" wire:loading.attr="disabled"
                    class="px-5 py-2.5 bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs rounded-xl shadow-md transition-all disabled:opacity-50 shrink-0">
                    {{ __('site.live.send') }}
                </button>
            </form>
            @error('question') <p class="text-rose-500 text-xs">{{ $message }}</p> @enderror

            @if ($myQuestions->isNotEmpty())
                <div class="pt-3 border-t border-slate-100 dark:border-slate-800 space-y-2">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">{{ __('site.live.my_questions') }}</span>
                    @foreach ($myQuestions as $q)
                        <div class="flex items-center justify-between text-xs" wire:key="q-{{ $q->id }}">
                            <span class="text-slate-600 dark:text-slate-300 truncate max-w-md">{{ $q->question }}</span>
                            <span class="{{ $q->is_answered ? 'text-emerald-500' : ($q->is_selected ? 'text-amber-500' : 'text-slate-400') }} shrink-0 font-semibold">
                                {{ $q->is_answered ? __('site.live.q_answered') : ($q->is_selected ? __('site.live.q_selected') : __('site.live.q_pending')) }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endif

</div>
