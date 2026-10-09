@extends('teacher.layouts.app')
@section('title', $quiz->title . ' — Test tafsilotlari')

@section('content')
<div class="mx-auto max-w-6xl space-y-5">
    <header class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="min-w-0">
            <a href="{{ route('teacher.quizzes.index') }}" class="inline-flex min-h-10 items-center gap-1 text-sm font-semibold text-amber-400 hover:text-amber-300 focus:outline-none focus:ring-2 focus:ring-amber-400">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6"/></svg>
                Barcha testlar
            </a>
            <p class="mt-2 font-mono text-[11px] uppercase tracking-widest text-amber-400">Test tafsilotlari</p>
            <h1 class="mt-1 break-words font-serif text-2xl font-bold text-paper">{{ $quiz->title }}</h1>
            <p class="mt-1 text-sm text-mist">{{ $quiz->book?->title ?? 'Mustaqil test' }} <span class="px-1 text-slate-600">·</span> {{ ucfirst($quiz->difficulty) }} daraja</p>
        </div>
        <form action="{{ route('teacher.quizzes.destroy', $quiz->id) }}" method="POST" onsubmit="return confirm('Ushbu test va uning savollari butunlay o‘chiriladi. Davam ettiraymi?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-btn border border-rose-500/25 bg-rose-500/10 px-4 py-2 text-sm font-semibold text-rose-300 transition hover:bg-rose-500/20 focus:outline-none focus:ring-2 focus:ring-rose-300">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16m-10 4v6m4-6v6M5 7l1 14h12l1-14M9 7V4h6v3"/></svg>
                Testni o‘chirish
            </button>
        </form>
    </header>

    <section class="grid grid-cols-2 gap-3 sm:grid-cols-4" aria-label="Test ko‘rsatkichlari">
        <div class="ks-panel p-4"><p class="text-xs text-mist">Savollar</p><p class="mt-1 font-mono text-xl font-bold text-paper">{{ $quiz->questions->count() }} ta</p></div>
        <div class="ks-panel p-4"><p class="text-xs text-mist">Jami ball</p><p class="mt-1 font-mono text-xl font-bold text-amber-400">{{ $quiz->questions->sum('points') }}</p></div>
        <div class="ks-panel p-4"><p class="text-xs text-mist">Vaqt limiti</p><p class="mt-1 font-mono text-xl font-bold text-paper">{{ $quiz->time_limit_minutes ? $quiz->time_limit_minutes . ' daq.' : 'Cheksiz' }}</p></div>
        <div class="ks-panel p-4"><p class="text-xs text-mist">Urinishlar</p><p class="mt-1 font-mono text-xl font-bold text-emerald-300">{{ $attempts->total() }}</p></div>
    </section>

    @if($quiz->description)
        <section class="ks-panel p-4 sm:p-5">
            <h2 class="text-xs font-bold uppercase tracking-wider text-mist">Test tavsifi</h2>
            <p class="mt-2 whitespace-pre-line break-words text-sm leading-relaxed text-paper">{{ $quiz->description }}</p>
        </section>
    @endif

    @if(session('success'))
        <div role="status" class="rounded-panel border border-emerald-500/25 bg-emerald-500/10 p-3 text-sm text-emerald-300">{{ session('success') }}</div>
    @endif

    <nav aria-label="Test bo‘limlari" class="flex gap-2 overflow-x-auto border-b border-ink-border pb-2">
        <a href="{{ route('teacher.quizzes.show', ['quiz' => $quiz, 'section' => 'results']) }}" @class(['inline-flex min-h-11 shrink-0 items-center rounded-btn border px-4 text-xs font-bold transition', 'border-amber-500/30 bg-amber-500/10 text-amber-300' => $section === 'results', 'border-ink-border bg-ink-900 text-mist hover:text-paper' => $section !== 'results']) @if($section === 'results') aria-current="page" @endif>
            Natijalar <span class="ml-2 font-mono">{{ $attempts->total() }}</span>
        </a>
        <a href="{{ route('teacher.quizzes.show', ['quiz' => $quiz, 'section' => 'questions']) }}" @class(['inline-flex min-h-11 shrink-0 items-center rounded-btn border px-4 text-xs font-bold transition', 'border-amber-500/30 bg-amber-500/10 text-amber-300' => $section === 'questions', 'border-ink-border bg-ink-900 text-mist hover:text-paper' => $section !== 'questions']) @if($section === 'questions') aria-current="page" @endif>
            Savollar <span class="ml-2 font-mono">{{ $quiz->questions->count() }}</span>
        </a>
        <a href="{{ route('teacher.quizzes.edit', $quiz) }}" class="inline-flex min-h-11 shrink-0 items-center rounded-btn border border-ink-border bg-ink-900 px-4 text-xs font-bold text-mist transition hover:border-amber-500/30 hover:text-amber-300">Tahrirlash</a>
    </nav>

    @if($section === 'results')
    <section class="ks-panel overflow-hidden" aria-labelledby="quiz-attempts-heading">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-ink-border px-4 py-3 sm:px-5">
            <div>
                <h2 id="quiz-attempts-heading" class="font-serif text-lg font-bold text-paper">Topshirilgan ishlar</h2>
                <p class="mt-0.5 text-xs text-mist">Har bir o‘quvchining natijasi va javob varag‘ini oching.</p>
            </div>
            <span class="rounded-badge border border-ink-border bg-ink-800 px-2.5 py-1 font-mono text-xs text-paper">{{ $attempts->total() }} ta urinish</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[44rem] text-left">
                <thead class="bg-ink-950/60 font-mono text-[11px] uppercase tracking-wider text-mist">
                    <tr>
                        <th class="px-4 py-2.5 font-semibold">O‘quvchi</th>
                        <th class="px-3 py-2.5 font-semibold">Topshirgan vaqt</th>
                        <th class="px-3 py-2.5 font-semibold">Natija</th>
                        <th class="px-3 py-2.5 font-semibold">Holat</th>
                        <th class="px-4 py-2.5 text-right font-semibold">Amallar</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink-border">
                    @forelse($attempts as $attempt)
                        <tr class="transition-colors hover:bg-ink-800/40">
                            <td class="px-4 py-3">
                                <p class="text-xs font-semibold text-paper">{{ $attempt->user?->name ?? 'Foydalanuvchi o‘chirilgan' }}</p>
                                @if($attempt->user?->email)<p class="mt-0.5 text-[11px] text-mist">{{ $attempt->user->email }}</p>@endif
                            </td>
                            <td class="whitespace-nowrap px-3 py-3 font-mono text-xs text-mist">{{ $attempt->completed_at?->format('d.m.Y H:i') ?? '—' }}</td>
                            <td class="whitespace-nowrap px-3 py-3 text-xs font-semibold text-paper">
                                @if($attempt->review_status === 'pending') Kutilmoqda
                                @else {{ $attempt->score }} / {{ $attempt->max_score }} ball <span class="ml-1 text-mist">({{ number_format((float) $attempt->percent, 0) }}%)</span>
                                @endif
                            </td>
                            <td class="px-3 py-3">
                                @if($attempt->review_status === 'pending')<span class="rounded-badge border border-amber-500/25 bg-amber-500/10 px-2 py-1 text-[10px] font-bold text-amber-300">Tekshirilmagan</span>
                                @elseif($attempt->review_status === 'reviewed')<span class="rounded-badge border border-emerald-500/25 bg-emerald-500/10 px-2 py-1 text-[10px] font-bold text-emerald-300">Tekshirildi</span>
                                @else<span class="rounded-badge border border-ink-border bg-ink-800 px-2 py-1 text-[10px] font-bold text-mist">Avtomatik baholandi</span>@endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('teacher.quiz-reviews.show', $attempt) }}" class="inline-flex min-h-10 items-center justify-center rounded-btn border border-ink-border bg-ink-800 px-3 text-xs font-semibold text-paper transition hover:border-amber-500/40 hover:text-amber-300 focus:outline-none focus:ring-2 focus:ring-amber-400">
                                    {{ $attempt->review_status === 'pending' ? 'Tekshirish' : 'Javoblarni ko‘rish' }} <span aria-hidden="true" class="ml-1">→</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-10 text-center text-sm text-mist">Hali hech kim bu testni topshirmagan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($attempts->hasPages())<div class="border-t border-ink-border p-3">{{ $attempts->links() }}</div>@endif
    </section>
    @endif

    @if($section === 'questions')
    <section class="space-y-3" aria-labelledby="quiz-questions-heading">
        <div>
            <h2 id="quiz-questions-heading" class="font-serif text-lg font-bold text-paper">Test savollari</h2>
            <p class="mt-0.5 text-xs text-mist">To‘g‘ri javoblar va savol sozlamalari faqat ustozga ko‘rinadi.</p>
        </div>
        @foreach($quiz->questions as $index => $q)
            <article class="ks-panel space-y-4 p-4 sm:p-5">
                <div class="flex items-start justify-between gap-3 border-b border-ink-border pb-3">
                    <h3 class="flex items-start gap-2 text-sm font-semibold leading-6 text-paper">
                        <span class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-btn border border-amber-500/25 bg-amber-500/10 font-mono text-xs text-amber-300">{{ $index + 1 }}</span>
                        {{ $q->question_text }}
                    </h3>
                    <span class="shrink-0 rounded-badge border border-amber-500/25 bg-amber-500/10 px-2 py-1 font-mono text-xs font-bold text-amber-300">{{ $q->points }} ball</span>
                </div>

                @if($q->image_path)
                    <img src="{{ asset('storage/' . $q->image_path) }}" alt="Savol rasmi" class="max-h-80 max-w-full object-contain {{ match($q->image_shape ?? 'rectangle') { 'square' => 'h-56 w-56 rounded-md', 'circle' => 'h-56 w-56 rounded-full', 'rounded' => 'rounded-xl', default => 'rounded-md' } }}">
                @endif

                @if($q->type === 'text')
                    <div class="rounded-md border border-amber-500/25 bg-amber-500/5 p-3 text-sm">
                        <p class="text-xs font-bold uppercase tracking-wide text-amber-300">Yozma javob · ustoz tekshiradi</p>
                        <p class="mt-2 whitespace-pre-wrap text-paper">{{ $q->expected_answer ?: 'Namunaviy javob yoki mezon kiritilmagan.' }}</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                        @foreach($q->options as $oIdx => $opt)
                            <div @class([
                                'flex items-center justify-between gap-3 rounded-card border p-3 text-sm',
                                'border-emerald-500/30 bg-emerald-500/10 text-emerald-200' => $opt->is_correct,
                                'border-ink-border bg-ink-950/60 text-mist' => !$opt->is_correct,
                            ])>
                                <span class="flex min-w-0 items-start gap-2"><span class="font-mono text-xs text-mist">{{ ['A', 'B', 'C', 'D'][$oIdx] ?? ($oIdx + 1) }}.</span><span>{{ $opt->option_text }}</span></span>
                                @if($opt->is_correct)
                                    <span class="shrink-0 rounded-badge bg-emerald-500/15 px-2 py-1 text-[10px] font-bold text-emerald-300">To‘g‘ri javob</span>
                                @else
                                    <span class="shrink-0 rounded-badge bg-ink-800 px-2 py-1 text-[10px] font-semibold text-mist">Noto‘g‘ri variant</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif

                @if($q->explanation)
                    <div class="rounded-md border border-ink-border bg-ink-950/60 p-3 text-xs leading-relaxed text-mist"><strong class="text-paper">O‘quvchiga ko‘rinadigan izoh:</strong> {{ $q->explanation }}</div>
                @endif
            </article>
        @endforeach
    </section>
    @endif
</div>
@endsection
