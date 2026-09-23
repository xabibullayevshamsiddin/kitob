@extends('layouts.app')

@section('title', $book->title)

@section('content')
<div class="max-w-6xl mx-auto space-y-8 pb-16" x-data="{ tab: 'chapters' }">

    <!-- Hero Book Banner -->
    <div class="p-6 sm:p-10 rounded-3xl bg-gradient-to-br from-indigo-950 via-slate-900 to-slate-900 border border-indigo-900/40 text-white relative overflow-hidden shadow-xl">
        <div class="absolute -right-20 -top-20 w-96 h-96 bg-indigo-500/15 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col sm:flex-row gap-8 items-center sm:items-start">
            <div class="w-44 h-64 rounded-2xl overflow-hidden shadow-2xl shrink-0 ring-2 ring-white/10">
                <img src="{{ $book->cover_url }}" alt="{{ $book->title }}" class="w-full h-full object-cover">
            </div>

            <div class="flex-1 space-y-4 text-center sm:text-left">
                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2">
                    <span class="px-3 py-1 bg-amber-500 text-slate-950 text-xs font-black rounded-lg uppercase">
                        {{ $book->week_number }}-Hafta kitobi
                    </span>
                    <span class="px-3 py-1 bg-indigo-500/20 text-indigo-300 text-xs font-semibold rounded-lg">
                        {{ $book->genre }}
                    </span>
                </div>

                <h1 class="text-2xl sm:text-4xl font-black font-manrope tracking-tight leading-tight">{{ $book->title }}</h1>
                <p class="text-sm text-indigo-200">Muallif: <strong class="text-white font-bold">{{ $book->author }}</strong></p>
                <p class="text-xs sm:text-sm text-slate-300 leading-relaxed max-w-2xl">{{ $book->description }}</p>

                <!-- Quick Action Buttons -->
                <div class="pt-2 flex flex-wrap items-center justify-center sm:justify-start gap-3">
                    @if($firstChapter = $book->chapters()->orderBy('chapter_number')->first())
                        <a href="{{ route('reader.show', ['book' => $book->id, 'chapter' => $firstChapter->id]) }}" 
                           class="px-6 py-2.5 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs rounded-xl shadow-md transition-all flex items-center gap-2">
                            <span>📖 Mutolaa qilish</span>
                        </a>
                    @endif

                    <a href="{{ route('audio.show', $book->id) }}" 
                       class="px-5 py-2.5 bg-white/10 hover:bg-white/20 text-white font-semibold text-xs rounded-xl backdrop-blur-md transition-all flex items-center gap-2">
                        <span>🎧 Audio</span>
                    </a>

                    <a href="{{ route('videos.index', $book->id) }}" 
                       class="px-5 py-2.5 bg-white/10 hover:bg-white/20 text-white font-semibold text-xs rounded-xl backdrop-blur-md transition-all flex items-center gap-2">
                        <span>🎬 Videolar</span>
                    </a>

                    <a href="{{ route('quiz.show', $book->id) }}" 
                       class="px-5 py-2.5 bg-white/10 hover:bg-white/20 text-white font-semibold text-xs rounded-xl backdrop-blur-md transition-all flex items-center gap-2">
                        <span>🧠 Test topshirish</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <div class="flex items-center gap-2 border-b border-slate-200 dark:border-slate-800 pb-2 overflow-x-auto">
        <button @click="tab = 'chapters'" :class="{ 'bg-indigo-600 text-white shadow-md': tab === 'chapters', 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800': tab !== 'chapters' }"
                class="px-4 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap">
            Boblar ro'yxati ({{ $book->chapters()->count() }})
        </button>
        <button @click="tab = 'overview'" :class="{ 'bg-indigo-600 text-white shadow-md': tab === 'overview', 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800': tab !== 'overview' }"
                class="px-4 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap">
            Kitob haqida umumiy
        </button>
    </div>

    <!-- Tab 1: Chapters -->
    <div x-show="tab === 'chapters'" class="space-y-3">
        @forelse($book->chapters()->orderBy('chapter_number')->get() as $chapter)
            <a href="{{ route('reader.show', ['book' => $book->id, 'chapter' => $chapter->id]) }}" 
               class="group p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-soft hover:shadow-soft-lg flex items-center justify-between transition-all">
                <div class="flex items-center gap-4 min-w-0">
                    <span class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 font-black text-sm flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                        {{ $chapter->chapter_number }}
                    </span>
                    <div class="min-w-0">
                        <h4 class="text-sm font-bold text-slate-800 dark:text-white truncate group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                            {{ $chapter->title }}
                        </h4>
                        <span class="text-xs text-slate-400">Taxminiy o'qish vaqti: ~{{ $chapter->duration_minutes ?? 15 }} daqiqa</span>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-xs text-indigo-600 dark:text-indigo-400 font-bold group-hover:translate-x-1 transition-transform">O'qish →</span>
                </div>
            </a>
        @empty
            <p class="text-xs text-slate-400 py-8 text-center">Ushbu kitobga hali boblar yuklanmagan.</p>
        @endforelse
    </div>

    <!-- Tab 2: Overview -->
    <div x-show="tab === 'overview'" class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-soft space-y-4">
        <h3 class="text-base font-bold text-slate-900 dark:text-white">To'liq tavsif va mazmuni</h3>
        <div class="prose dark:prose-invert text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
            {{ $book->description }}
        </div>
    </div>

</div>
@endsection
