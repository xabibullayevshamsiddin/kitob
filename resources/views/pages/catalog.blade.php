@extends('layouts.app')

@section('title', 'Kitoblar katalogi')

@section('content')
<div class="max-w-7xl mx-auto space-y-8 pb-12">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white font-manrope">Kitoblar kutubxonasi</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Haftalik e'lon qilingan barcha kitoblar arxivi</p>
        </div>

        <!-- Quick Filters -->
        <div class="flex items-center gap-2">
            <span class="text-xs text-slate-400">Janr:</span>
            <select class="text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 font-medium">
                <option value="">Barcha janrlar</option>
                <option value="personal_dev">Shaxsiy rivojlanish</option>
                <option value="business">Biznes & Boshqaruv</option>
                <option value="science">Ilm-fan & Texnologiya</option>
                <option value="spiritual">Falsafa & Ruhiyat</option>
            </select>
        </div>
    </div>

    <!-- Books Grid -->
    @php
        $books = \App\Models\Book::orderBy('week_number', 'desc')->get();
    @endphp

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @forelse ($books as $book)
            <div class="group bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-soft hover:shadow-soft-lg transition-all duration-300 overflow-hidden flex flex-col justify-between">
                <div>
                    <!-- Cover Container -->
                    <div class="relative h-64 bg-slate-900 overflow-hidden">
                        <img src="{{ $book->cover_url }}" alt="{{ $book->title }}"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-transparent to-transparent opacity-80"></div>
                        <span class="absolute top-3 left-3 px-2.5 py-1 bg-amber-500 text-slate-950 text-[10px] font-black rounded-lg uppercase tracking-wider shadow">
                            {{ $book->week_number }}-Hafta
                        </span>
                        <span class="absolute bottom-3 left-3 px-2.5 py-1 bg-white/20 backdrop-blur-md text-white text-[10px] font-semibold rounded-lg">
                            {{ $book->genre }}
                        </span>
                    </div>

                    <!-- Details -->
                    <div class="p-5 space-y-2">
                        <h3 class="text-base font-bold text-slate-900 dark:text-white line-clamp-1 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
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
                    <span class="text-[11px] text-slate-400">{{ $book->chapters()->count() }} ta bob</span>
                    <a href="{{ route('books.show', $book->slug) }}" 
                       class="px-4 py-2 bg-indigo-50 hover:bg-indigo-600 dark:bg-indigo-950/60 dark:hover:bg-indigo-600 text-indigo-600 dark:text-indigo-400 hover:text-white dark:hover:text-white font-bold text-xs rounded-xl transition-all">
                        Batafsil →
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-4 text-center py-12 text-slate-400">
                Hozircha kitoblar mavjud emas.
            </div>
        @endforelse
    </div>
</div>
@endsection
