@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex flex-col sm:flex-row items-center justify-between gap-4 p-4 sm:p-5 rounded-3xl bg-ink-900/80 dark:bg-slate-900/80 border border-white/10 dark:border-slate-800 shadow-card-depth backdrop-blur-xl">
        <!-- Results Counter -->
        <div class="text-xs text-slate-400 font-medium text-center sm:text-left">
            <span>Jami <strong class="text-white font-bold">{{ $paginator->total() }}</strong> ta natijadan <strong class="text-amber-400 font-bold">{{ $paginator->firstItem() }} - {{ $paginator->lastItem() }}</strong> ko'rsatilmoqda</span>
        </div>

        <!-- Pagination Controls -->
        <div class="flex items-center gap-1.5 flex-wrap justify-center">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <span class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-500 bg-white/[0.02] border border-white/5 cursor-not-allowed select-none flex items-center gap-1.5">
                    <span>←</span>
                    <span class="hidden sm:inline">Oldingi</span>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-300 hover:text-white bg-white/5 hover:bg-white/10 border border-white/10 active:scale-95 transition-all flex items-center gap-1.5">
                    <span>←</span>
                    <span class="hidden sm:inline">Oldingi</span>
                </a>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <span class="px-2 py-1 text-xs text-slate-500 font-bold select-none">{{ $element }}</span>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="px-3.5 py-2 rounded-xl text-xs font-black bg-gradient-to-r from-amber-500 to-amber-600 text-slate-950 shadow-md shadow-amber-500/25 ring-2 ring-amber-400/30 select-none">
                                {{ $page }}
                            </span>
                        @else
                            <a href="{{ $url }}" class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-400 hover:text-white hover:bg-white/5 border border-transparent hover:border-white/10 active:scale-95 transition-all select-none">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-300 hover:text-white bg-white/5 hover:bg-white/10 border border-white/10 active:scale-95 transition-all flex items-center gap-1.5">
                    <span class="hidden sm:inline">Keyingi</span>
                    <span>→</span>
                </a>
            @else
                <span class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-500 bg-white/[0.02] border border-white/5 cursor-not-allowed select-none flex items-center gap-1.5">
                    <span class="hidden sm:inline">Keyingi</span>
                    <span>→</span>
                </span>
            @endif
        </div>
    </nav>
@endif
