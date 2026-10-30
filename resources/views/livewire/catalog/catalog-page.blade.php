<div class="max-w-7xl mx-auto space-y-8 pb-16">

    <!-- Header & Search/Sort Row -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 border-b border-ink-border pb-6">
        <div>
            <span class="ks-eyebrow">{{ __('site.nav.books') }}</span>
            <h1 class="font-display text-3xl sm:text-4xl font-bold text-paper mt-1">{{ __('site.catalog.title') }}</h1>
            <p class="font-mono text-xs text-mist mt-1">
                {{ __('site.catalog.total_found', ['count' => $books->total()]) }}
            </p>
        </div>

        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5">
            <!-- Search Input -->
            <div class="relative w-full sm:w-72">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-mist">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </span>
                <input type="text" wire:model.debounce.300ms="search" placeholder="{{ __('site.catalog.search_ph') }}"
                    class="ks-input pl-9 pr-8 text-xs">
                @if($search)
                    <button type="button" wire:click="$set('search', '')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-mist hover:text-paper cursor-pointer">
                        ✕
                    </button>
                @endif
            </div>

            <!-- Sort By Select -->
            <div class="flex items-center gap-1.5 shrink-0">
                <select wire:model="sortBy" class="ks-input text-xs w-full sm:w-auto font-mono">
                    <option value="week_desc">{{ __('site.catalog.sort_new') }}</option>
                    <option value="week_asc">{{ __('site.catalog.sort_old') }}</option>
                    <option value="popular">{{ __('site.catalog.sort_popular') }}</option>
                    <option value="title_asc">{{ __('site.catalog.sort_alpha') }}</option>
                    <option value="chapters_desc">{{ __('site.catalog.sort_chapters') }}</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Filter Bar Card (Directly above books) -->
    <div class="ks-panel p-4 sm:p-5 space-y-4">
        
        <!-- Format Tabs (Pills) -->
        <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar py-0.5 -mx-1 px-1 sm:mx-0 sm:px-0 flex-nowrap sm:flex-wrap">
            <span class="ks-eyebrow mr-2 hidden sm:inline-block shrink-0">{{ __('site.catalog.format') }}</span>
            
            <button type="button" wire:click="setFormat('all')"
                class="ks-filter-pill px-3 py-1.5 rounded-btn text-xs font-medium flex items-center gap-1.5 cursor-pointer shrink-0 {{ ($format === 'all' && $readingStatus === 'all') ? 'bg-amber-500 text-slate-950 font-bold shadow-sm' : 'bg-ink-800 text-paper hover:bg-ink-700 border border-ink-border' }}">
                <span>{{ __('site.catalog.all') }}</span>
                <span class="px-1.5 py-0.2 rounded-badge font-mono text-[10px] {{ ($format === 'all' && $readingStatus === 'all') ? 'bg-slate-950/15 text-slate-950 font-bold' : 'bg-ink-700 text-mist' }}">
                    {{ $totalBooksCount }}
                </span>
            </button>

            <button type="button" wire:click="setFormat('pdf')"
                class="ks-filter-pill px-3 py-1.5 rounded-btn text-xs font-medium flex items-center gap-1.5 cursor-pointer shrink-0 {{ $format === 'pdf' ? 'bg-amber-500 text-slate-950 font-bold shadow-sm' : 'bg-ink-800 text-paper hover:bg-ink-700 border border-ink-border' }}">
                <span>{{ __('site.catalog.pdf') }}</span>
                <span class="px-1.5 py-0.2 rounded-badge font-mono text-[10px] {{ $format === 'pdf' ? 'bg-slate-950/15 text-slate-950 font-bold' : 'bg-ink-700 text-mist' }}">
                    {{ $pdfBooksCount }}
                </span>
            </button>

            <button type="button" wire:click="setFormat('audio')"
                class="ks-filter-pill px-3 py-1.5 rounded-btn text-xs font-medium flex items-center gap-1.5 cursor-pointer shrink-0 {{ $format === 'audio' ? 'bg-amber-500 text-slate-950 font-bold shadow-sm' : 'bg-ink-800 text-paper hover:bg-ink-700 border border-ink-border' }}">
                <span>{{ __('site.books.audio') }}</span>
                <span class="px-1.5 py-0.2 rounded-badge font-mono text-[10px] {{ $format === 'audio' ? 'bg-slate-950/15 text-slate-950 font-bold' : 'bg-ink-700 text-mist' }}">
                    {{ $audioBooksCount }}
                </span>
            </button>

            <button type="button" wire:click="setFormat('video')"
                class="ks-filter-pill px-3 py-1.5 rounded-btn text-xs font-medium flex items-center gap-1.5 cursor-pointer shrink-0 {{ $format === 'video' ? 'bg-amber-500 text-slate-950 font-bold shadow-sm' : 'bg-ink-800 text-paper hover:bg-ink-700 border border-ink-border' }}">
                <span>{{ __('site.catalog.video_review') }}</span>
                <span class="px-1.5 py-0.2 rounded-badge font-mono text-[10px] {{ $format === 'video' ? 'bg-slate-950/15 text-slate-950 font-bold' : 'bg-ink-700 text-mist' }}">
                    {{ $videoBooksCount }}
                </span>
            </button>

            <button type="button" wire:click="setFormat('quiz')"
                class="ks-filter-pill px-3 py-1.5 rounded-btn text-xs font-medium flex items-center gap-1.5 cursor-pointer shrink-0 {{ $format === 'quiz' ? 'bg-amber-500 text-slate-950 font-bold shadow-sm' : 'bg-ink-800 text-paper hover:bg-ink-700 border border-ink-border' }}">
                <span>{{ __('site.catalog.quiz_books') }}</span>
                <span class="px-1.5 py-0.2 rounded-badge font-mono text-[10px] {{ $format === 'quiz' ? 'bg-slate-950/15 text-slate-950 font-bold' : 'bg-ink-700 text-mist' }}">
                    {{ $quizBooksCount }}
                </span>
            </button>

            @auth
                <div class="h-5 w-px bg-ink-border mx-1 shrink-0"></div>

                <button type="button" wire:click="setReadingStatus('reading')"
                    class="ks-filter-pill px-3 py-1.5 rounded-btn text-xs font-medium flex items-center gap-1.5 cursor-pointer shrink-0 {{ $readingStatus === 'reading' ? 'bg-amber-500 text-slate-950 font-bold shadow-sm' : 'bg-ink-800 text-paper hover:bg-ink-700 border border-ink-border' }}">
                    <span>{{ __('site.catalog.im_reading') }}</span>
                    <span class="px-1.5 py-0.2 rounded-badge font-mono text-[10px] {{ $readingStatus === 'reading' ? 'bg-slate-950/15 text-slate-950 font-bold' : 'bg-ink-700 text-mist' }}">
                        {{ $readingCount }}
                    </span>
                </button>

                <button type="button" wire:click="setReadingStatus('finished')"
                    class="ks-filter-pill px-3 py-1.5 rounded-btn text-xs font-medium flex items-center gap-1.5 cursor-pointer shrink-0 {{ $readingStatus === 'finished' ? 'bg-emerald-500 text-slate-950 font-bold shadow-sm' : 'bg-ink-800 text-paper hover:bg-ink-700 border border-ink-border' }}">
                    <span>{{ __('site.catalog.finished') }}</span>
                    <span class="px-1.5 py-0.2 rounded-badge font-mono text-[10px] {{ $readingStatus === 'finished' ? 'bg-slate-950/15 text-slate-950 font-bold' : 'bg-ink-700 text-mist' }}">
                        {{ $finishedCount }}
                    </span>
                </button>

                <button type="button" wire:click="setReadingStatus('saved')"
                    class="ks-filter-pill px-3 py-1.5 rounded-btn text-xs font-medium flex items-center gap-1.5 cursor-pointer shrink-0 {{ $readingStatus === 'saved' ? 'bg-amber-500 text-slate-950 font-bold shadow-sm' : 'bg-ink-800 text-paper hover:bg-ink-700 border border-ink-border' }}"
                    title="{{ __('site.saved_books.filter_title') }}">
                    <svg class="w-3.5 h-3.5 {{ $readingStatus === 'saved' ? 'text-slate-950' : 'text-amber-400' }}" viewBox="0 0 24 24" fill="{{ $readingStatus === 'saved' ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
                    <span>{{ __('site.catalog.saved') }}</span>
                    <span class="px-1.5 py-0.2 rounded-badge font-mono text-[10px] {{ $readingStatus === 'saved' ? 'bg-slate-950/15 text-slate-950 font-bold' : 'bg-ink-700 text-mist' }}">
                        {{ $savedBooksCount }}
                    </span>
                </button>
            @endauth
        </div>

        <!-- Genres Filter Pills Row -->
        <div class="pt-3 border-t border-ink-border flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-2.5">
            <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar pb-1 sm:pb-0 -mx-1 px-1 sm:mx-0 sm:px-0 flex-nowrap sm:flex-wrap">
                <span class="ks-eyebrow mr-2 hidden sm:inline-block shrink-0">{{ __('site.catalog.genre') }}</span>
                
                <button type="button" wire:click="setGenre('')"
                    class="ks-filter-pill px-3 py-1.5 rounded-btn text-xs font-medium cursor-pointer shrink-0 {{ $genre === '' ? 'bg-amber-500 text-slate-950 font-bold shadow-sm' : 'bg-ink-800 text-paper hover:bg-ink-700 border border-ink-border' }}">
                    {{ __('site.catalog.all_genres') }}
                </button>

                @foreach ($genres as $g)
                    <button type="button" wire:click="setGenre('{{ $g }}')"
                        class="ks-filter-pill px-3 py-1.5 rounded-btn text-xs font-medium cursor-pointer shrink-0 {{ $genre === $g ? 'bg-amber-500 text-slate-950 font-bold shadow-sm' : 'bg-ink-800 text-paper hover:bg-ink-700 border border-ink-border' }}">
                        {{ $g }}
                    </button>
                @endforeach
            </div>

            @if($hasActiveFilters)
                <button type="button" wire:click="resetFilters"
                    class="ks-filter-pill inline-flex items-center justify-center gap-1 px-3 py-1 rounded-btn bg-rose-500/10 hover:bg-rose-500/20 text-rose-300 text-xs font-semibold border border-rose-500/30 transition-colors cursor-pointer shrink-0">
                    <span>{{ __('site.catalog.clear_filters') }}</span>
                    <span>✕</span>
                </button>
            @endif
        </div>

        <!-- Mood Filter Row (Kayfiyat bo'yicha) -->
        <div class="pt-3 border-t border-ink-border">
            <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar -mx-1 px-1 sm:mx-0 sm:px-0 flex-nowrap sm:flex-wrap">
                <span class="ks-eyebrow mr-2 hidden sm:inline-block shrink-0">{{ __('site.catalog.mood') }}</span>

                <button type="button" wire:click="setMood('')"
                    class="ks-filter-pill px-3 py-1.5 rounded-btn text-xs font-medium flex items-center gap-1.5 cursor-pointer shrink-0 {{ $mood === '' ? 'bg-violet-500 text-white font-bold shadow-sm' : 'bg-ink-800 text-paper hover:bg-ink-700 border border-ink-border' }}">
                    {{ __('site.catalog.mood_all') }}
                </button>

                <button type="button" wire:click="setMood('tezkor')"
                    class="ks-filter-pill px-3 py-1.5 rounded-btn text-xs font-medium flex items-center gap-1.5 cursor-pointer shrink-0 {{ $mood === 'tezkor' ? 'bg-violet-500 text-white font-bold shadow-sm' : 'bg-ink-800 text-paper hover:bg-ink-700 border border-ink-border' }}">
                    ⚡ {{ __('site.catalog.mood_fast') }}
                </button>

                <button type="button" wire:click="setMood('chuqur')"
                    class="ks-filter-pill px-3 py-1.5 rounded-btn text-xs font-medium flex items-center gap-1.5 cursor-pointer shrink-0 {{ $mood === 'chuqur' ? 'bg-violet-500 text-white font-bold shadow-sm' : 'bg-ink-800 text-paper hover:bg-ink-700 border border-ink-border' }}">
                    🧠 {{ __('site.catalog.mood_deep') }}
                </button>

                <button type="button" wire:click="setMood('ilhomlantiruvchi')"
                    class="ks-filter-pill px-3 py-1.5 rounded-btn text-xs font-medium flex items-center gap-1.5 cursor-pointer shrink-0 {{ $mood === 'ilhomlantiruvchi' ? 'bg-violet-500 text-white font-bold shadow-sm' : 'bg-ink-800 text-paper hover:bg-ink-700 border border-ink-border' }}">
                    🔥 {{ __('site.catalog.mood_inspiring') }}
                </button>

                <button type="button" wire:click="setMood('dam_oluvchi')"
                    class="ks-filter-pill px-3 py-1.5 rounded-btn text-xs font-medium flex items-center gap-1.5 cursor-pointer shrink-0 {{ $mood === 'dam_oluvchi' ? 'bg-violet-500 text-white font-bold shadow-sm' : 'bg-ink-800 text-paper hover:bg-ink-700 border border-ink-border' }}">
                    🌿 {{ __('site.catalog.mood_relaxing') }}
                </button>
            </div>
        </div>
    </div>

    @if($showRecommendations && $recommendations->isNotEmpty())
        <section class="space-y-4" aria-labelledby="catalog-recommendations-title">
            <div class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <span class="ks-eyebrow">{{ __('site.catalog.based_on_reading') }}</span>
                    <h2 id="catalog-recommendations-title" class="mt-1 font-display text-xl font-bold text-paper sm:text-2xl">{{ __('site.catalog.recommended_for_you') }}</h2>
                </div>
                <p class="text-xs text-mist">{{ __('site.catalog.based_on_genres') }}</p>
            </div>
            <div class="grid grid-cols-2 gap-3 sm:gap-x-5 sm:gap-y-6 md:grid-cols-3 lg:grid-cols-4">
                @foreach($recommendations as $recommendedBook)
                    <x-ui.book-card :book="$recommendedBook" wire:key="recommended-book-{{ $recommendedBook->id }}" />
                @endforeach
            </div>
        </section>
    @endif

    <!-- Books Grid with Signature 3D Opening Animation (MASTER §5.2) -->
    <div class="space-y-4">
        @if($showRecommendations && $recommendations->isNotEmpty())
            <div class="border-t border-ink-border pt-4">
                <h2 class="font-display text-xl font-bold text-paper sm:text-2xl">{{ __('site.catalog.all_books') }}</h2>
            </div>
        @endif

        {{-- Scoped Livewire Loading Indicator (No layout shift, soft dimming) --}}
        <div wire:loading.flex wire:target="setFormat, setReadingStatus, setGenre, setMood, search, sortBy, resetFilters"
             class="items-center justify-center py-2 text-xs font-mono text-cyan-600 dark:text-cyan-400 gap-2">
            <svg class="w-4 h-4 animate-spin text-cyan-500 shrink-0" viewBox="0 0 24 24" fill="none">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
            </svg>
            <span>{{ __('site.common.loading') ?? 'Yuklanmoqda...' }}</span>
        </div>

        <div data-catalog-grid class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-x-5 sm:gap-y-10 transition-opacity duration-200">
            @forelse ($books as $book)
                <x-ui.book-card :book="$book" wire:key="book-{{ $book->id }}" />
            @empty
                <div class="col-span-full text-center py-20 ks-panel p-8 space-y-3">
                    <div class="w-12 h-12 rounded-full bg-ink-800 border border-ink-border text-mist flex items-center justify-center mx-auto">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    </div>
                    @if ($readingStatus === 'saved')
                        <h3 class="font-display text-lg font-bold text-paper">{{ __('site.saved_books.empty_title') }}</h3>
                        <p class="font-sans text-xs text-mist max-w-sm mx-auto">{{ __('site.saved_books.empty_hint') }}</p>
                        <button type="button" wire:click="resetFilters" class="ks-btn-ghost text-xs mt-2">
                            {{ __('site.catalog.show_all') }}
                        </button>
                    @elseif ($hasActiveFilters)
                        <h3 class="font-display text-lg font-bold text-paper">{{ __('site.catalog.no_results_t') }}</h3>
                        <p class="font-sans text-xs text-mist max-w-sm mx-auto">{{ __('site.catalog.no_results_s') }}</p>
                        <button type="button" wire:click="resetFilters" class="ks-btn-ghost text-xs mt-2">
                            {{ __('site.catalog.show_all') }}
                        </button>
                    @else
                        <h3 class="font-display text-lg font-bold text-paper">{{ __('site.catalog.empty') }}</h3>
                    @endif
                </div>
            @endforelse
        </div>
    </div>

    @if ($books->hasPages())
        <div class="pt-6">
            {{ $books->links('vendor.pagination.taste-livewire') }}
        </div>
    @endif
</div>
