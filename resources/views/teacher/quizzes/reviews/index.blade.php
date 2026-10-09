@extends(auth()->user()->isAdmin() ? 'admin.layouts.app' : 'teacher.layouts.app')
@section('title', 'Yozma javoblarni tekshirish')

@section('content')
<div class="mx-auto max-w-5xl space-y-5">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="font-mono text-xs uppercase tracking-widest text-amber-400">Test nazorati</p>
            <div class="mt-1 flex flex-wrap items-center gap-3">
                <h1 class="font-serif text-2xl font-bold text-paper">Yozma javoblar</h1>
                <span class="rounded-pill border border-amber-500/25 bg-amber-500/10 px-2.5 py-1 text-xs font-bold text-amber-300">{{ $attempts->total() }} ta kutilmoqda</span>
            </div>
            <p class="mt-1 text-sm text-mist">O‘quvchilar topshirgan yozma javoblarni ko‘rib, ball qo‘ying.</p>
        </div>
        <a href="{{ auth()->user()->isAdmin() ? route('admin.quizzes.index') : route('teacher.quizzes.index') }}" class="ks-btn-ghost inline-flex min-h-11 items-center justify-center px-4 py-2 text-xs font-bold">Testlarga qaytish</a>
    </div>

    @if(session('success'))
        <div role="status" class="rounded-panel border border-emerald-500/25 bg-emerald-500/10 p-3 text-sm text-emerald-300">{{ session('success') }}</div>
    @endif

    <div class="ks-panel overflow-hidden divide-y divide-ink-border">
        @forelse($attempts as $attempt)
            @php
                $answerPreview = collect($attempt->answers ?? [])->first(fn ($answer) => ($answer['type'] ?? null) === 'text' && filled($answer['written_answer'] ?? null));
            @endphp
            <a href="{{ auth()->user()->isAdmin() ? route('admin.quiz-reviews.show', $attempt) : route('teacher.quiz-reviews.show', $attempt) }}" class="group flex min-h-24 flex-col gap-4 p-4 transition hover:bg-ink-800/60 focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-amber-400 sm:flex-row sm:items-center sm:justify-between sm:px-5">
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                        <span class="font-semibold text-paper">{{ $attempt->user->name }}</span>
                        <span class="text-mist">·</span>
                        <span class="font-medium text-paper">{{ $attempt->quiz->title }}</span>
                    </div>
                    <p class="mt-1 truncate text-xs font-mono text-mist">
                        {{ $attempt->quiz->book?->title ?? 'Kitob biriktirilmagan' }}
                        <span class="px-1 text-ink-600">·</span>
                        {{ $attempt->completed_at?->format('d.m.Y H:i') ?? 'Vaqt ko‘rsatilmagan' }}
                        <span class="px-1 text-ink-600">·</span>
                        {{ $attempt->quiz->questions->where('type', 'text')->count() }} ta yozma savol
                    </p>
                    <p class="mt-2 line-clamp-2 break-words text-sm leading-5 text-mist">
                        <span class="font-semibold text-paper">Javob:</span>
                        {{ $answerPreview ? \Illuminate\Support\Str::limit(trim($answerPreview['written_answer']), 180) : 'Yozma javob matni topilmadi — topshiriqni ochib tekshiring.' }}
                    </p>
                </div>
                <span class="ks-btn-ghost inline-flex min-h-11 shrink-0 items-center justify-center self-start px-4 text-xs font-bold text-amber-300 group-hover:border-amber-500/30 group-hover:bg-amber-500/10 sm:self-center">Tekshirish <span aria-hidden="true" class="ml-2">→</span></span>
            </a>
        @empty
            <div class="p-10 text-center">
                <p class="font-semibold text-paper">Tekshirilmagan yozma javob yo‘q</p>
                <p class="mt-1 text-sm text-mist">O‘quvchilar yozma test topshirganda, kim topshirgani va javoblari shu yerda ko‘rinadi.</p>
            </div>
        @endforelse
    </div>
    @if($attempts->hasPages())
        <div class="font-mono text-xs">{{ $attempts->links() }}</div>
    @endif
</div>
@endsection
