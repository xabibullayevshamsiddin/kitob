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

    <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-x-5 gap-y-8">
        @forelse ($books as $book)
            <x-ui.book-card :book="$book" />
        @empty
            <div class="col-span-full py-20 text-center">
                <p class="text-sm" style="color:#8B9BAD; font-family:'DM Mono',monospace;">Kitoblar mavjud emas</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
