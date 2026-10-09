@extends('admin.layouts.app')
@section('title', 'Test tafsilotlari: ' . $quiz->title)

@section('content')
<div class="max-w-4xl mx-auto space-y-5">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <span class="ks-eyebrow">Test topshiriqlari</span>
            <h1 class="text-xl sm:text-2xl font-bold font-serif text-paper mt-0.5">{{ $quiz->title }}</h1>
            <p class="text-sm text-mist">
                @if($quiz->book)
                    «{{ $quiz->book->title }}» kitobiga bog'langan test topshirig'i
                @endif
                @if($quiz->creator)
                    <span class="px-1">·</span> Yaratgan: {{ $quiz->creator->name }}
                @else
                    <span class="px-1">·</span> Yaratuvchi: noma’lum (eski test)
                @endif
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.quizzes.index') }}" class="ks-btn-ghost min-h-11 inline-flex items-center px-4 py-2 text-sm font-semibold self-start sm:self-auto">
                ← Barcha testlar
            </a>
        </div>
    </div>

    {{-- Info Card --}}
    <div class="ks-panel p-4 sm:p-5 grid grid-cols-2 sm:grid-cols-4 gap-3 text-center">
        <div class="p-3 bg-ink-950/60 rounded-card border border-ink-border">
            <span class="text-xs text-mist block mb-1">Savollar</span>
            <span class="text-xl font-bold text-paper">{{ $quiz->questions->count() }} ta</span>
        </div>
        <div class="p-3 bg-ink-950/60 rounded-card border border-ink-border">
            <span class="text-xs text-mist block mb-1">Umumiy ball</span>
            <span class="text-xl font-bold text-amber-400">{{ $quiz->questions->sum('points') }} ball</span>
        </div>
        <div class="p-3 bg-ink-950/60 rounded-card border border-ink-border">
            <span class="text-xs text-mist block mb-1">Vaqt chegarasi</span>
            <span class="text-xl font-bold text-amber-400">{{ $quiz->time_limit_minutes ? $quiz->time_limit_minutes . ' daq' : 'Cheksiz' }}</span>
        </div>
        <div class="p-3 bg-ink-950/60 rounded-card border border-ink-border">
            <span class="text-xs text-mist block mb-1">Murakkablik</span>
            <span class="text-sm font-bold uppercase text-emerald-400">{{ $quiz->difficulty }}</span>
        </div>
    </div>

    @if(session('success'))
        <div role="status" class="rounded-panel border border-emerald-500/25 bg-emerald-500/10 p-3 text-sm text-emerald-300">{{ session('success') }}</div>
    @endif

    <nav aria-label="Test bo‘limlari" class="flex gap-2 overflow-x-auto border-b border-ink-border pb-2">
        <a href="{{ route('admin.quizzes.show', ['quiz' => $quiz, 'section' => 'results']) }}" @class(['inline-flex min-h-11 shrink-0 items-center rounded-btn border px-4 text-xs font-bold transition', 'border-amber-500/30 bg-amber-500/10 text-amber-300' => $section === 'results', 'border-ink-border bg-ink-900 text-mist hover:text-paper' => $section !== 'results']) @if($section === 'results') aria-current="page" @endif>
            Natijalar <span class="ml-2 font-mono">{{ $attempts->total() }}</span>
        </a>
        <a href="{{ route('admin.quizzes.show', ['quiz' => $quiz, 'section' => 'questions']) }}" @class(['inline-flex min-h-11 shrink-0 items-center rounded-btn border px-4 text-xs font-bold transition', 'border-amber-500/30 bg-amber-500/10 text-amber-300' => $section === 'questions', 'border-ink-border bg-ink-900 text-mist hover:text-paper' => $section !== 'questions']) @if($section === 'questions') aria-current="page" @endif>
            Savollar <span class="ml-2 font-mono">{{ $quiz->questions->count() }}</span>
        </a>
        <a href="{{ route('admin.quizzes.edit', $quiz) }}" class="inline-flex min-h-11 shrink-0 items-center rounded-btn border border-ink-border bg-ink-900 px-4 text-xs font-bold text-mist transition hover:border-amber-500/30 hover:text-amber-300">Tahrirlash</a>
    </nav>

    {{-- O'quvchilar topshirgan urinishlar --}}
    @if($section === 'results')
    <section class="ks-panel overflow-hidden" aria-labelledby="quiz-attempts-heading">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-ink-border px-4 py-3 sm:px-5">
            <div>
                <h2 id="quiz-attempts-heading" class="text-base font-bold text-paper">Topshirilgan ishlar</h2>
                <p class="mt-0.5 text-xs text-mist">Har bir o‘quvchining natijasi va javob varag‘ini ko‘ring.</p>
            </div>
            <span class="rounded-badge border border-ink-border bg-ink-800 px-2.5 py-1 font-mono text-xs text-paper">{{ $attempts->total() }} ta urinish</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[44rem] text-left">
                <thead class="bg-ink-950/60 text-[11px] uppercase tracking-wider text-mist">
                    <tr>
                        <th class="px-4 py-2.5 font-semibold">O‘quvchi</th>
                        <th class="px-3 py-2.5 font-semibold">Topshirgan vaqt</th>
                        <th class="px-3 py-2.5 font-semibold">Natija</th>
                        <th class="px-3 py-2.5 font-semibold">Holat</th>
                        <th class="px-4 py-2.5 text-right font-semibold">Javob varag‘i</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink-border/60">
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
                                @if($attempt->review_status === 'pending')
                                    <span class="rounded-badge border border-amber-500/25 bg-amber-500/10 px-2 py-1 text-[10px] font-bold text-amber-300">Tekshirilmagan</span>
                                @elseif($attempt->review_status === 'reviewed')
                                    <span class="rounded-badge border border-emerald-500/25 bg-emerald-500/10 px-2 py-1 text-[10px] font-bold text-emerald-300">Tekshirildi</span>
                                @else
                                    <span class="rounded-badge border border-ink-border bg-ink-800 px-2 py-1 text-[10px] font-bold text-mist">Avtomatik baholandi</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('admin.quiz-reviews.show', $attempt) }}" class="inline-flex min-h-10 items-center justify-center rounded-btn border border-ink-border bg-ink-800 px-3 text-xs font-semibold text-paper transition hover:border-amber-500/40 hover:text-amber-300 focus:outline-none focus:ring-2 focus:ring-amber-400">
                                    {{ $attempt->review_status === 'pending' ? 'Tekshirish' : 'Javoblarni ko‘rish' }} <span aria-hidden="true" class="ml-1">→</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-9 text-center text-sm text-mist">Hali hech kim bu testni topshirmagan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($attempts->hasPages())
            <div class="border-t border-ink-border p-3">{{ $attempts->links() }}</div>
        @endif
    </section>
    @endif

    {{-- Questions List --}}
    @if($section === 'questions')
    <div class="space-y-4">
        <h2 class="text-lg font-bold font-serif text-paper">Savollar ro'yxati</h2>

        @foreach($quiz->questions as $index => $q)
            <div class="ks-panel p-4 sm:p-5 space-y-3">
                <div class="flex items-center justify-between border-b border-ink-border pb-2">
                    <span class="font-bold text-sm text-amber-400">{{ $index + 1 }}-savol <span class="text-mist font-normal">({{ $q->points }} ball)</span></span>
                </div>
                <p class="text-base font-semibold text-paper">{{ $q->question_text }}</p>

                @if($q->image_path)
                    <img src="{{ asset('storage/' . $q->image_path) }}" alt="Savol rasmi" class="max-h-80 max-w-full object-contain {{ match($q->image_shape ?? 'rectangle') { 'square' => 'h-56 w-56 rounded-md', 'circle' => 'h-56 w-56 rounded-full', 'rounded' => 'rounded-2xl', default => 'rounded-md' } }}">
                @endif

                @if($q->type === 'text')
                    <p class="inline-flex rounded-lg bg-amber-500/10 px-3 py-2 text-xs font-semibold text-amber-300">Yozma javob — ustoz/admin tomonidan tekshiriladi</p>
                @endif

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-2">
                    @foreach($q->options as $opt)
                        <div class="p-3 rounded-card border text-xs flex items-center justify-between gap-3 {{ $opt->is_correct ? 'bg-emerald-500/10 border-emerald-500/40 text-emerald-300 font-bold' : 'bg-ink-950/60 border-ink-border text-mist' }}">
                            <span>{{ $opt->option_text }}</span>
                            @if($opt->is_correct)
                                <span class="px-2 py-0.5 rounded-badge bg-emerald-500/20 text-emerald-300 text-[10px] font-bold uppercase shrink-0">To'g'ri javob</span>
                            @else
                                <span class="px-2 py-0.5 rounded-badge bg-ink-800 text-mist text-[10px] font-semibold shrink-0">Noto'g'ri variant</span>
                            @endif
                        </div>
                    @endforeach
                </div>

                @if($q->explanation)
                    <div class="p-3 rounded-card bg-amber-500/5 border border-amber-500/15 text-xs text-amber-200">
                        <strong class="font-bold">Izoh:</strong> {{ $q->explanation }}
                    </div>
                @endif
            </div>
        @endforeach
    </div>
    @endif
</div>
@endsection
