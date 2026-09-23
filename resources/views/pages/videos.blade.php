@extends('layouts.app')

@section('title', 'Video darslar - ' . $book->title)

@section('content')
<div class="max-w-5xl mx-auto space-y-8 pb-16">
    <div class="flex items-center gap-3">
        <a href="{{ route('books.show', $book->slug) }}" class="p-2 rounded-xl text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800">
            ← Orqaga
        </a>
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white">Video sharhlar va tahlillar</h1>
            <p class="text-xs text-slate-400">«{{ $book->title }}» bo'yicha 20-30 daqiqalik overview va boblar videosi</p>
        </div>
    </div>

    <!-- Main Overview Video Player Box -->
    <div class="rounded-3xl bg-slate-900 overflow-hidden shadow-2xl border border-slate-800 relative aspect-video flex items-center justify-center group">
        <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/40 to-transparent"></div>
        <img src="{{ $book->cover_url }}" class="absolute inset-0 w-full h-full object-cover opacity-25 filter blur-sm">
        
        <div class="relative z-10 text-center space-y-4 p-6">
            <button class="w-16 h-16 sm:w-20 sm:h-20 rounded-full bg-rose-600 hover:bg-rose-500 text-white flex items-center justify-center text-2xl font-black shadow-xl shadow-rose-600/40 transform group-hover:scale-110 transition-all mx-auto">
                ▶
            </button>
            <h3 class="text-lg sm:text-xl font-bold text-white max-w-md mx-auto">{{ $book->title }} — To'liq Video Sharh</h3>
            <span class="px-3 py-1 bg-white/20 backdrop-blur-md rounded-full text-xs text-white">Davomiyligi: ~25 daqiqa</span>
        </div>
    </div>
</div>
@endsection
