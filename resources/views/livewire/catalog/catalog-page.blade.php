<div class="max-w-7xl mx-auto space-y-8 pb-16">

    <!-- Header & Search/Sort Row -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white font-manrope">Kitoblar kutubxonasi</h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                Jami <strong class="text-amber-500 font-bold">{{ $books->total() }}</strong> ta kitob topildi
            </p>
        </div>

        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5">
            <!-- Search Input -->
            <div class="relative w-full sm:w-64">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </span>
                <input type="text" wire:model.debounce.300ms="search" placeholder="Nom yoki muallif..."
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
                    <option value="week_desc">⚡ Eng yangi haftalik</option>
                    <option value="week_asc">📅 Eskiroq haftalik</option>
                    <option value="popular">🔥 Eng ko'p o'qilganlar</option>
                    <option value="title_asc">🔤 Alifbo bo'yicha (A-Z)</option>
                    <option value="chapters_desc">📚 Boblar soni bo'yicha</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Filter Bar Card -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-3xl p-4 sm:p-5 shadow-soft space-y-4">
        
        <!-- Format Tabs (Pills) -->
        <div class="flex flex-wrap items-center gap-1.5">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider mr-2 hidden sm:inline-block">Format:</span>
            
            <button type="button" wire:click="setFormat('all')"
                class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 {{ ($format === 'all' && $readingStatus === 'all') ? 'bg-amber-500 text-slate-950 shadow-md shadow-amber-500/25' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
                <span>Barchasi</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ ($format === 'all' && $readingStatus === 'all') ? 'bg-slate-950/20 text-slate-950' : 'bg-slate-200 dark:bg-slate-700 text-slate-500' }}">
                    {{ $totalBooksCount }}
                </span>
            </button>

            <button type="button" wire:click="setFormat('pdf')"
                class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 {{ $format === 'pdf' ? 'bg-amber-500 text-slate-950 shadow-md shadow-amber-500/25' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
                <span>📖 Varaqlab o'qish (PDF)</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $format === 'pdf' ? 'bg-slate-950/20 text-slate-950' : 'bg-slate-200 dark:bg-slate-700 text-slate-500' }}">
                    {{ $pdfBooksCount }}
                </span>
            </button>

            <button type="button" wire:click="setFormat('audio')"
                class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 {{ $format === 'audio' ? 'bg-amber-500 text-slate-950 shadow-md shadow-amber-500/25' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
                <span>🎧 Audio</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $format === 'audio' ? 'bg-slate-950/20 text-slate-950' : 'bg-slate-200 dark:bg-slate-700 text-slate-500' }}">
                    {{ $audioBooksCount }}
                </span>
            </button>

            <button type="button" wire:click="setFormat('video')"
                class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 {{ $format === 'video' ? 'bg-amber-500 text-slate-950 shadow-md shadow-amber-500/25' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
                <span>🎥 Video sharh</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $format === 'video' ? 'bg-slate-950/20 text-slate-950' : 'bg-slate-200 dark:bg-slate-700 text-slate-500' }}">
                    {{ $videoBooksCount }}
                </span>
            </button>

            <button type="button" wire:click="setFormat('quiz')"
                class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 {{ $format === 'quiz' ? 'bg-amber-500 text-slate-950 shadow-md shadow-amber-500/25' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
                <span>🧠 Testli kitoblar</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $format === 'quiz' ? 'bg-slate-950/20 text-slate-950' : 'bg-slate-200 dark:bg-slate-700 text-slate-500' }}">
                    {{ $quizBooksCount }}
                </span>
            </button>

            @auth
                <div class="h-5 w-px bg-slate-200 dark:bg-slate-700 mx-1 hidden sm:block"></div>

                <button type="button" wire:click="setReadingStatus('reading')"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 {{ $readingStatus === 'reading' ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
                    <span>⏳ O'qiyotganlarim</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $readingStatus === 'reading' ? 'bg-indigo-700 text-white' : 'bg-slate-200 dark:bg-slate-700 text-slate-500' }}">
                        {{ $readingCount }}
                    </span>
                </button>

                <button type="button" wire:click="setReadingStatus('finished')"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 {{ $readingStatus === 'finished' ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/30' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
                    <span>✅ Tugatganlarim</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $readingStatus === 'finished' ? 'bg-emerald-700 text-white' : 'bg-slate-200 dark:bg-slate-700 text-slate-500' }}">
                        {{ $finishedCount }}
                    </span>
                </button>
            @endauth
        </div>

        <!-- Genres Filter Pills Row -->
        <div class="pt-3 border-t border-slate-100 dark:border-slate-800/80 flex flex-wrap items-center justify-between gap-2.5">
            <div class="flex flex-wrap items-center gap-1.5 overflow-x-auto pb-1 sm:pb-0">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider mr-2 hidden sm:inline-block">Janr:</span>
                
                <button type="button" wire:click="setGenre('')"
                    class="px-3 py-1 rounded-xl text-xs font-medium transition-all {{ $genre === '' ? 'bg-slate-900 dark:bg-white text-white dark:text-slate-900 font-bold' : 'text-slate-500 hover:text-slate-900 dark:hover:text-white bg-slate-50 dark:bg-slate-800/60' }}">
                    Barcha janrlar
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
                    <span>Filtrlarni tozalash</span>
                    <span>✕</span>
                </button>
            @endif
        </div>
    </div>

    <!-- Books Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @forelse ($books as $book)
            <div class="group bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-soft hover:shadow-soft-lg transition-all duration-300 overflow-hidden flex flex-col justify-between" wire:key="book-{{ $book->id }}">
                <div>
                    <!-- Cover Container -->
                    <div class="relative h-64 bg-slate-900 overflow-hidden">
                        <img src="{{ $book->cover_url }}" alt="{{ $book->title }}"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                             onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($book->title) }}&size=512&background=1e1b4b&color=fff&bold=true'">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-transparent to-transparent opacity-80"></div>
                        
                        <!-- Week Badge -->
                        <span class="absolute top-3 left-3 px-2.5 py-1 bg-amber-500 text-slate-950 text-[10px] font-black rounded-lg uppercase tracking-wider shadow">
                            {{ $book->week_number }}-Hafta
                        </span>

                        <!-- Genre Badge -->
                        <span class="absolute bottom-3 left-3 px-2.5 py-1 bg-white/20 backdrop-blur-md text-white text-[10px] font-semibold rounded-lg">
                            {{ $book->genre }}
                        </span>

                        <!-- Format Micro-Badges -->
                        <div class="absolute top-3 right-3 flex flex-col gap-1 items-end">
                            @if($book->pdf_path)
                                <span class="px-2 py-0.5 rounded-md bg-amber-500/90 text-slate-950 text-[9px] font-black shadow-sm" title="Varaqlab o'qish (PDF) mavjud">
                                    📖 PDF
                                </span>
                            @endif
                            @if($book->audios_count > 0)
                                <span class="px-2 py-0.5 rounded-md bg-emerald-500/90 text-white text-[9px] font-bold shadow-sm" title="Audio mavjud">
                                    🎧 Audio
                                </span>
                            @endif
                            @if($book->videos_count > 0)
                                <span class="px-2 py-0.5 rounded-md bg-rose-500/90 text-white text-[9px] font-bold shadow-sm" title="Video dars mavjud">
                                    🎥 Video
                                </span>
                            @endif
                            @if($book->quizzes_count > 0)
                                <span class="px-2 py-0.5 rounded-md bg-indigo-500/90 text-white text-[9px] font-bold shadow-sm" title="Test mavjud">
                                    🧠 Test
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Details -->
                    <div class="p-5 space-y-2">
                        <h3 class="text-base font-bold text-slate-900 dark:text-white line-clamp-1 group-hover:text-amber-500 dark:group-hover:text-amber-400 transition-colors">
                            {{ $book->title }}
                        </h3>
                        <p class="text-xs text-slate-400 font-medium">Muallif: <span class="text-slate-600 dark:text-slate-300 font-semibold">{{ $book->author }}</span></p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 line-clamp-2 leading-relaxed">
                            {{ $book->description }}
                        </p>
                    </div>
                </div>

                <!-- Footer Action -->
                <div class="p-5 pt-0 border-t border-slate-100 dark:border-slate-800/60 mt-3 flex items-center justify-between">
                    <span class="text-[11px] text-slate-400">{{ $book->chapters_count }} ta bob</span>
                    
                    <div class="flex items-center gap-1.5">
                        @if($book->pdf_path)
                            <a href="{{ route('books.flipbook', $book->id) }}"
                               class="px-2.5 py-1.5 bg-amber-500/10 hover:bg-amber-500 text-amber-500 hover:text-slate-950 font-bold text-xs rounded-xl transition-all"
                               title="Varaqlab o'qish">
                                📖
                            </a>
                        @endif
                        <a href="{{ route('books.show', $book->slug) }}"
                           class="px-4 py-2 bg-indigo-50 hover:bg-indigo-600 dark:bg-indigo-950/60 dark:hover:bg-indigo-600 text-indigo-600 dark:text-indigo-400 hover:text-white dark:hover:text-white font-bold text-xs rounded-xl transition-all">
                            Batafsil →
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-16 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-8 space-y-3">
                @if ($hasActiveFilters)
                    <span class="text-4xl block">🔍</span>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Tanlangan filtrlar bo'yicha kitob topilmadi</h3>
                    <p class="text-xs text-slate-400 max-w-sm mx-auto">Qidiruv so'zini, janr yoki formatni o'zgartirib ko'ring.</p>
                    <button type="button" wire:click="resetFilters" class="px-5 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs rounded-xl shadow transition-all">
                        Barcha kitoblarni ko'rsatish
                    </button>
                @else
                    <span class="text-4xl block">📚</span>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Hozircha kitoblar mavjud emas</h3>
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
