<div class="max-w-7xl mx-auto space-y-8 pb-16">

    <!-- Header & Search/Sort Row -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white font-manrope">{{ __('site.catalog.title') }}</h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                {{ __('site.catalog.total_found', ['count' => $books->total()]) }}
            </p>
        </div>

        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5">
            <!-- Search Input -->
            <div class="relative w-full sm:w-64">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </span>
                <input type="text" wire:model.debounce.300ms="search" placeholder="{{ __('site.catalog.search_ph') }}"
                    class="w-full pl-9 pr-8 py-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-amber-500 focus:outline-none transition-all shadow-sm">
                @if($search)
                    <button type="button" wire:click="$set('search', '')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                        ✕
                    </button>
                @endif
            </div>

            <!-- Sort By Select -->
            <div class="flex items-center gap-1.5 shrink-0">
                <select wire:model="sortBy"
                    class="w-full sm:w-auto text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl px-3.5 py-2.5 font-medium text-slate-900 dark:text-white focus:ring-2 focus:ring-amber-500 focus:outline-none shadow-sm">
                    <option value="week_desc">⚡ {{ __('site.catalog.sort_new') }}</option>
                    <option value="week_asc">📅 {{ __('site.catalog.sort_old') }}</option>
                    <option value="popular">🔥 {{ __('site.catalog.sort_popular') }}</option>
                    <option value="title_asc">🔤 {{ __('site.catalog.sort_alpha') }}</option>
                    <option value="chapters_desc">📚 {{ __('site.catalog.sort_chapters') }}</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Filter Bar Card -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-3xl p-4 sm:p-5 shadow-soft space-y-4">
        
        <!-- Format Tabs (Pills) -->
        <div class="flex flex-wrap items-center gap-1.5">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider mr-2 hidden sm:inline-block">{{ __('site.catalog.format') }}</span>
            
            <button type="button" wire:click="setFormat('all')"
                class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 {{ ($format === 'all' && $readingStatus === 'all') ? 'bg-amber-500 text-slate-950 shadow-md shadow-amber-500/25' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
                <span>{{ __('site.catalog.all') }}</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ ($format === 'all' && $readingStatus === 'all') ? 'bg-slate-950/20 text-slate-950' : 'bg-slate-200 dark:bg-slate-700 text-slate-500' }}">
                    {{ $totalBooksCount }}
                </span>
            </button>

            <button type="button" wire:click="setFormat('pdf')"
                class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 {{ $format === 'pdf' ? 'bg-amber-500 text-slate-950 shadow-md shadow-amber-500/25' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
                <span>📖 {{ __('site.catalog.pdf') }}</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $format === 'pdf' ? 'bg-slate-950/20 text-slate-950' : 'bg-slate-200 dark:bg-slate-700 text-slate-500' }}">
                    {{ $pdfBooksCount }}
                </span>
            </button>

            <button type="button" wire:click="setFormat('audio')"
                class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 {{ $format === 'audio' ? 'bg-amber-500 text-slate-950 shadow-md shadow-amber-500/25' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
                <span>🎧 {{ __('site.books.audio') }}</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $format === 'audio' ? 'bg-slate-950/20 text-slate-950' : 'bg-slate-200 dark:bg-slate-700 text-slate-500' }}">
                    {{ $audioBooksCount }}
                </span>
            </button>

            <button type="button" wire:click="setFormat('video')"
                class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 {{ $format === 'video' ? 'bg-amber-500 text-slate-950 shadow-md shadow-amber-500/25' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
                <span>🎥 {{ __('site.catalog.video_review') }}</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $format === 'video' ? 'bg-slate-950/20 text-slate-950' : 'bg-slate-200 dark:bg-slate-700 text-slate-500' }}">
                    {{ $videoBooksCount }}
                </span>
            </button>

            <button type="button" wire:click="setFormat('quiz')"
                class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 {{ $format === 'quiz' ? 'bg-amber-500 text-slate-950 shadow-md shadow-amber-500/25' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
                <span>🧠 {{ __('site.catalog.quiz_books') }}</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $format === 'quiz' ? 'bg-slate-950/20 text-slate-950' : 'bg-slate-200 dark:bg-slate-700 text-slate-500' }}">
                    {{ $quizBooksCount }}
                </span>
            </button>

            @auth
                <div class="h-5 w-px bg-slate-200 dark:bg-slate-700 mx-1 hidden sm:block"></div>

                <button type="button" wire:click="setReadingStatus('reading')"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 {{ $readingStatus === 'reading' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
                    <span>⏳ {{ __('site.catalog.im_reading') }}</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $readingStatus === 'reading' ? 'bg-indigo-700 text-white' : 'bg-slate-200 dark:bg-slate-700 text-slate-500' }}">
                        {{ $readingCount }}
                    </span>
                </button>

                <button type="button" wire:click="setReadingStatus('finished')"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 {{ $readingStatus === 'finished' ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/30' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
                    <span>✅ {{ __('site.catalog.finished') }}</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $readingStatus === 'finished' ? 'bg-emerald-700 text-white' : 'bg-slate-200 dark:bg-slate-700 text-slate-500' }}">
                        {{ $finishedCount }}
                    </span>
                </button>
            @endauth
        </div>

        <!-- Genres Filter Pills Row -->
        <div class="pt-3 border-t border-slate-100 dark:border-slate-800/80 flex flex-wrap items-center justify-between gap-2.5">
            <div class="flex flex-wrap items-center gap-1.5 overflow-x-auto pb-1 sm:pb-0">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider mr-2 hidden sm:inline-block">{{ __('site.catalog.genre') }}</span>
                
                <button type="button" wire:click="setGenre('')"
                    class="px-3 py-1 rounded-xl text-xs font-medium transition-all {{ $genre === '' ? 'bg-slate-900 dark:bg-white text-white dark:text-slate-900 font-bold' : 'text-slate-500 hover:text-slate-900 dark:hover:text-white bg-slate-50 dark:bg-slate-800/60' }}">
                    {{ __('site.catalog.all_genres') }}
                </button>

                @foreach ($genres as $g)
                    <button type="button" wire:click="setGenre('{{ $g }}')"
                        class="px-3 py-1 rounded-xl text-xs font-medium transition-all {{ $genre === $g ? 'bg-slate-900 dark:bg-white text-white dark:text-slate-900 font-bold' : 'text-slate-500 hover:text-slate-900 dark:hover:text-white bg-slate-50 dark:bg-slate-800/60' }}">
                        {{ $g }}
                    </button>
                @endforeach
            </div>

            @if($hasActiveFilters)
                <button type="button" wire:click="resetFilters"
                    class="inline-flex items-center gap-1 px-3 py-1 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-500 text-xs font-semibold transition-all shrink-0">
                    <span>{{ __('site.catalog.clear_filters') }}</span>
                    <span>✕</span>
                </button>
            @endif
        </div>
    </div>

    {{-- Books Grid --}}
    <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-x-5 gap-y-8">
        @forelse ($books as $book)
            <x-ui.book-card :book="$book" :wireKey="$book->id" />
        @empty
            <div class="col-span-full py-20 text-center">
                @if ($hasActiveFilters)
                    <p class="text-sm" style="color:#8B9BAD; font-family:'DM Mono',monospace;">Natija topilmadi</p>
                    <button type="button" wire:click="resetFilters" 
                        class="mt-4 px-6 py-2 text-xs font-semibold" 
                        style="background:#C1392B; color:#F0EDE6; border-radius:4px; font-family:'DM Sans',sans-serif;">
                        Filtrlarni tozalash
                    </button>
                @else
                    <p class="text-sm" style="color:#8B9BAD; font-family:'DM Mono',monospace;">Kitoblar mavjud emas</p>
                @endif
            </div>
        @endforelse
    </div>

    @if ($books->hasPages())
        <div class="pt-4">
            {{ $books->links('vendor.pagination.taste-livewire') }}
        </div>
    @endif
</div>
