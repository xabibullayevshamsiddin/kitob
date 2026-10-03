@extends('layouts.app')

@section('title', 'Kitoblar katalogi')

@section('content')
<div class="max-w-7xl mx-auto space-y-8 pb-16">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 border-b border-ink-border pb-6">
        <div>
            <span class="ks-eyebrow">Arxiv</span>
            <h1 class="font-display text-3xl sm:text-4xl font-bold text-paper mt-1">Kitoblar kutubxonasi</h1>
            <p class="font-mono text-xs text-mist mt-1">Haftalik e'lon qilingan barcha kitoblar arxivi</p>
        </div>
    </div>

    <!-- Books Grid with 3D Book Cards -->
    @php
        $books = \App\Models\Book::orderBy('week_number', 'desc')->get();
    @endphp

    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-x-5 gap-y-10">
        @forelse ($books as $book)
            <x-ui.book-card :book="$book" />
        @empty
            <div class="col-span-full text-center py-20 ks-panel p-8">
                <p class="font-display text-lg text-paper">Hozircha kitoblar mavjud emas.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
