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

    <!-- Filter Bar Card -->
    <div class="ks-panel p-4 sm:p-5 space-y-4">
        
        <!-- Format Tabs (Pills) -->
        <div class="flex flex-wrap items-center gap-1.5">
            <span class="ks-eyebrow mr-2 hidden sm:inline-block">{{ __('site.catalog.format') }}</span>
            
            <button type="button" wire:click="setFormat('all')"
                class="px-3.5 py-1.5 rounded-btn text-xs font-medium transition-colors duration-base flex items-center gap-1.5 cursor-pointer {{ ($format === 'all' && $readingStatus === 'all') ? 'bg-amber-500 text-ink-950 font-bold shadow-sm' : 'bg-ink-800 text-paper hover:bg-ink-700 border border-ink-border' }}">
                <span>{{ __('site.catalog.all') }}</span>
                <span class="px-1.5 py-0.2 rounded-badge font-mono text-[10px] {{ ($format === 'all' && $readingStatus === 'all') ? 'bg-ink-950/20 text-ink-950 font-bold' : 'bg-ink-700 text-mist' }}">
                    {{ $totalBooksCount }}
                </span>
            </button>

            <button type="button" wire:click="setFormat('pdf')"
                class="px-3.5 py-1.5 rounded-btn text-xs font-medium transition-colors duration-base flex items-center gap-1.5 cursor-pointer {{ $format === 'pdf' ? 'bg-amber-500 text-ink-950 font-bold shadow-sm' : 'bg-ink-800 text-paper hover:bg-ink-700 border border-ink-border' }}">
                <span>{{ __('site.catalog.pdf') }}</span>
                <span class="px-1.5 py-0.2 rounded-badge font-mono text-[10px] {{ $format === 'pdf' ? 'bg-ink-950/20 text-ink-950 font-bold' : 'bg-ink-700 text-mist' }}">
                    {{ $pdfBooksCount }}
                </span>
            </button>

            <button type="button" wire:click="setFormat('audio')"
                class="px-3.5 py-1.5 rounded-btn text-xs font-medium transition-colors duration-base flex items-center gap-1.5 cursor-pointer {{ $format === 'audio' ? 'bg-amber-500 text-ink-950 font-bold shadow-sm' : 'bg-ink-800 text-paper hover:bg-ink-700 border border-ink-border' }}">
                <span>{{ __('site.books.audio') }}</span>
                <span class="px-1.5 py-0.2 rounded-badge font-mono text-[10px] {{ $format === 'audio' ? 'bg-ink-950/20 text-ink-950 font-bold' : 'bg-ink-700 text-mist' }}">
                    {{ $audioBooksCount }}
                </span>
            </button>

            <button type="button" wire:click="setFormat('video')"
                class="px-3.5 py-1.5 rounded-btn text-xs font-medium transition-colors duration-base flex items-center gap-1.5 cursor-pointer {{ $format === 'video' ? 'bg-amber-500 text-ink-950 font-bold shadow-sm' : 'bg-ink-800 text-paper hover:bg-ink-700 border border-ink-border' }}">
                <span>{{ __('site.catalog.video_review') }}</span>
                <span class="px-1.5 py-0.2 rounded-badge font-mono text-[10px] {{ $format === 'video' ? 'bg-ink-950/20 text-ink-950 font-bold' : 'bg-ink-700 text-mist' }}">
                    {{ $videoBooksCount }}
                </span>
            </button>

            <button type="button" wire:click="setFormat('quiz')"
                class="px-3.5 py-1.5 rounded-btn text-xs font-medium transition-colors duration-base flex items-center gap-1.5 cursor-pointer {{ $format === 'quiz' ? 'bg-amber-500 text-ink-950 font-bold shadow-sm' : 'bg-ink-800 text-paper hover:bg-ink-700 border border-ink-border' }}">
                <span>{{ __('site.catalog.quiz_books') }}</span>
                <span class="px-1.5 py-0.2 rounded-badge font-mono text-[10px] {{ $format === 'quiz' ? 'bg-ink-950/20 text-ink-950 font-bold' : 'bg-ink-700 text-mist' }}">
                    {{ $quizBooksCount }}
                </span>
            </button>

            @auth
                <div class="h-5 w-px bg-ink-border mx-1 hidden sm:block"></div>

                <button type="button" wire:click="setReadingStatus('reading')"
                    class="px-3.5 py-1.5 rounded-btn text-xs font-medium transition-colors duration-base flex items-center gap-1.5 cursor-pointer {{ $readingStatus === 'reading' ? 'bg-amber-500 text-ink-950 font-bold shadow-sm' : 'bg-ink-800 text-paper hover:bg-ink-700 border border-ink-border' }}">
                    <span>{{ __('site.catalog.im_reading') }}</span>
                    <span class="px-1.5 py-0.2 rounded-badge font-mono text-[10px] {{ $readingStatus === 'reading' ? 'bg-ink-950/20 text-ink-950 font-bold' : 'bg-ink-700 text-mist' }}">
                        {{ $readingCount }}
                    </span>
                </button>

                <button type="button" wire:click="setReadingStatus('finished')"
                    class="px-3.5 py-1.5 rounded-btn text-xs font-medium transition-colors duration-base flex items-center gap-1.5 cursor-pointer {{ $readingStatus === 'finished' ? 'bg-emerald-500 text-ink-950 font-bold shadow-sm' : 'bg-ink-800 text-paper hover:bg-ink-700 border border-ink-border' }}">
                    <span>{{ __('site.catalog.finished') }}</span>
                    <span class="px-1.5 py-0.2 rounded-badge font-mono text-[10px] {{ $readingStatus === 'finished' ? 'bg-ink-950/20 text-ink-950 font-bold' : 'bg-ink-700 text-mist' }}">
                        {{ $finishedCount }}
                    </span>
                </button>
            @endauth
        </div>

        <!-- Genres Filter Pills Row -->
        <div class="pt-3 border-t border-ink-border flex flex-wrap items-center justify-between gap-2.5">
            <div class="flex flex-wrap items-center gap-1.5 overflow-x-auto pb-1 sm:pb-0">
                <span class="ks-eyebrow mr-2 hidden sm:inline-block">{{ __('site.catalog.genre') }}</span>
                
                <button type="button" wire:click="setGenre('')"
                    class="px-3 py-1 rounded-badge font-mono text-xs font-medium transition-colors duration-base cursor-pointer {{ $genre === '' ? 'bg-paper text-ink-950 font-bold' : 'text-mist hover:text-paper bg-ink-800 border border-ink-border' }}">
                    {{ __('site.catalog.all_genres') }}
                </button>

                @foreach ($genres as $g)
                    <button type="button" wire:click="setGenre('{{ $g }}')"
                        class="px-3 py-1 rounded-badge font-mono text-xs font-medium transition-colors duration-base cursor-pointer {{ $genre === $g ? 'bg-paper text-ink-950 font-bold' : 'text-mist hover:text-paper bg-ink-800 border border-ink-border' }}">
                        {{ $g }}
                    </button>
                @endforeach
            </div>

            @if($hasActiveFilters)
                <button type="button" wire:click="resetFilters"
                    class="inline-flex items-center gap-1 px-3 py-1 rounded-btn bg-rose-500/10 hover:bg-rose-500/20 text-rose-300 text-xs font-semibold border border-rose-500/30 transition-colors cursor-pointer shrink-0">
                    <span>{{ __('site.catalog.clear_filters') }}</span>
                    <span>✕</span>
                </button>
            @endif
        </div>
    </div>

    <!-- Books Grid with Signature 3D Opening Animation (MASTER §5.2) -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-x-5 gap-y-10">
        @forelse ($books as $book)
            <x-ui.book-card :book="$book" wire:key="book-{{ $book->id }}" />
        @empty
            <div class="col-span-full text-center py-20 ks-panel p-8 space-y-3">
                <div class="w-12 h-12 rounded-full bg-ink-800 border border-ink-border text-mist flex items-center justify-center mx-auto">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
                @if ($hasActiveFilters)
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

    @if ($books->hasPages())
        <div class="pt-6">
            {{ $books->links('vendor.pagination.taste-livewire') }}
        </div>
    @endif
</div>
