@extends('teacher.layouts.app')
@section('title', 'Testlar')

@section('content')
<div class="space-y-6">
    <div>
        <h2 class="text-xl font-black text-slate-800 dark:text-white">📝 Testlar</h2>
        <p class="text-sm text-slate-500">Kitoblar bo'yicha testlarni boshqaring</p>
    </div>

    @forelse($books as $book)
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-5">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-slate-800 dark:text-white">{{ $book->title }}</h3>
                    <p class="text-xs text-slate-400 mt-0.5">{{ $book->author }}</p>
                </div>
                <span class="px-3 py-1 rounded-xl text-xs font-bold bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400">
                    {{ $book->quizzes->count() }} test
                </span>
            </div>
        </div>
    @empty
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-12 text-center">
            <p class="text-4xl mb-3">📝</p>
            <p class="text-slate-500">Hali testlar mavjud emas</p>
        </div>
    @endforelse
</div>
@endsection
