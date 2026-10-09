@extends(auth()->user()->isAdmin() ? 'admin.layouts.app' : 'teacher.layouts.app')
@section('title', 'O‘quvchi javob varag‘i')

@section('content')
<div class="mx-auto max-w-4xl space-y-5">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div class="min-w-0">
            <a class="inline-flex min-h-10 items-center text-sm font-semibold text-amber-400 hover:text-amber-300 focus:outline-none focus:ring-2 focus:ring-amber-400" href="{{ auth()->user()->isAdmin() ? route('admin.quizzes.show', $attempt->quiz) : route('teacher.quizzes.show', $attempt->quiz) }}">← Testga qaytish</a>
            <h1 class="mt-1 break-words font-serif text-2xl font-bold text-paper">{{ $attempt->quiz->title }}</h1>
            <p class="mt-1 text-sm text-mist">
                O‘quvchi: <span class="font-semibold text-paper">{{ $attempt->user->name }}</span>
                @if($attempt->quiz->book) · {{ $attempt->quiz->book->title }}@endif
                · {{ $attempt->completed_at?->format('d.m.Y H:i') }}
            </p>
        </div>
        <div class="flex shrink-0 flex-wrap items-center gap-2">
            @if($attempt->review_status === 'pending')
                <span class="rounded-pill border border-amber-500/25 bg-amber-500/10 px-3 py-2 text-xs font-bold text-amber-300">Tekshirilmoqda</span>
            @elseif($attempt->review_status === 'reviewed')
                <span class="rounded-pill border border-emerald-500/25 bg-emerald-500/10 px-3 py-2 text-xs font-bold text-emerald-300">Tekshirilgan · {{ $attempt->percent }}%</span>
            @else
                <span class="rounded-pill border border-ink-border bg-ink-800 px-3 py-2 text-xs font-bold text-mist">Avtomatik natija · {{ $attempt->percent }}%</span>
            @endif
        </div>
    </div>

    @if($errors->any())
        <div role="alert" class="rounded-panel border border-rose-500/25 bg-rose-500/10 p-4 text-sm text-rose-300">{{ $errors->first() }}</div>
    @endif

    @if($attempt->review_status !== 'pending')
        <div class="ks-panel grid grid-cols-2 gap-3 p-4 sm:grid-cols-4">
            <div><p class="text-xs text-mist">Natija</p><p class="mt-1 font-bold text-paper">{{ $attempt->score }} / {{ $attempt->max_score }} ball</p></div>
            <div><p class="text-xs text-mist">Foiz</p><p class="mt-1 font-bold text-paper">{{ $attempt->percent }}%</p></div>
            <div><p class="text-xs text-mist">Tekshiruv holati</p><p class="mt-1 font-bold text-paper">{{ $attempt->review_status === 'reviewed' ? 'Ustoz/admin tekshirgan' : 'Avtomatik baholangan' }}</p></div>
            @if($attempt->reviewer)<div><p class="text-xs text-mist">Tekshirgan</p><p class="mt-1 font-bold text-paper">{{ $attempt->reviewer->name }}</p></div>@endif
        </div>
    @endif

    @if($attempt->review_status === 'pending')
        <form method="POST" action="{{ auth()->user()->isAdmin() ? route('admin.quiz-reviews.update', $attempt) : route('teacher.quiz-reviews.update', $attempt) }}" class="space-y-4">
            @csrf
    @endif

    @foreach($attempt->answers ?? [] as $index => $answer)
        @php
            $question = $attempt->quiz->questions->firstWhere('id', $answer['question_id'] ?? null);
            $questionType = $answer['type'] ?? $question?->type ?? 'single';
            $questionPoints = (int) ($answer['points'] ?? $question?->points ?? 10);
            $questionText = $answer['question_text'] ?? $question?->question_text ?? 'Savol o‘chirilgan';
        @endphp
        <section class="ks-panel space-y-3 p-4 sm:p-5">
            <div class="flex items-start justify-between gap-3">
                <h2 class="font-semibold leading-6 text-paper">{{ $index + 1 }}. {{ $questionText }}</h2>
                <span class="shrink-0 rounded-pill border border-ink-border bg-ink-800 px-2 py-1 text-xs text-mist">{{ $questionPoints }} ball</span>
            </div>

            @if($question?->image_path)
                <img src="{{ asset('storage/' . $question->image_path) }}" alt="Savol rasmi" class="max-h-80 max-w-full object-contain {{ match($question->image_shape ?? 'rectangle') { 'square' => 'h-56 w-56 rounded-md', 'circle' => 'h-56 w-56 rounded-full', 'rounded' => 'rounded-2xl', default => 'rounded-md' } }}">
            @endif

            @if($questionType === 'text')
                @php($answerGuide = filled($question?->expected_answer) ? $question->expected_answer : $question?->explanation)
                <div class="rounded-btn border border-amber-500/25 bg-amber-500/5 p-4">
                    <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-amber-300">Ustoz/admin kiritgan namunaviy javob yoki mezon</p>
                    @if(filled($answerGuide))
                        @if(!filled($question?->expected_answer))
                            <p class="mb-2 text-xs text-mist">Maxsus namuna kiritilmagan; mavjud izoh ko‘rsatilmoqda.</p>
                        @endif
                        <p class="whitespace-pre-wrap break-words text-sm leading-6 text-paper">{{ $answerGuide }}</p>
                    @else
                        <p class="text-sm italic text-mist">Bu savol uchun test yaratilayotganda namunaviy javob yoki baholash mezoni kiritilmagan.</p>
                    @endif
                </div>
                <div class="rounded-btn border border-ink-border bg-ink-950/70 p-4">
                    <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-mist">O‘quvchining yozma javobi</p>
                    <p class="whitespace-pre-wrap break-words text-sm leading-6 text-paper">{{ $answer['written_answer'] ?? 'Javob berilmagan' }}</p>
                </div>
                @if($attempt->review_status === 'pending')
                    <label class="block text-sm font-semibold text-paper" for="score-{{ $index }}">Qo‘yiladigan ball (0–{{ $questionPoints }})</label>
                    <input id="score-{{ $index }}" name="scores[{{ $index }}]" type="number" min="0" max="{{ $questionPoints }}" step="1" required value="{{ old('scores.' . $index, $answer['earned_points'] ?? '') }}" class="ks-input min-h-11 w-full text-sm sm:max-w-xs">
                @else
                    <p class="text-sm font-semibold text-emerald-300">Baholangan: {{ $answer['earned_points'] ?? 0 }} / {{ $questionPoints }} ball</p>
                @endif
            @else
                <div class="space-y-2 rounded-btn border border-ink-border bg-ink-950/70 p-4 text-sm">
                    <p class="text-mist"><span class="font-semibold text-paper">O‘quvchi tanlovi:</span> {{ $answer['selected_text'] ?? 'Javob belgilanmagan' }}</p>
                    <p class="text-emerald-300"><span class="font-semibold">To‘g‘ri javob:</span> {{ $answer['correct_text'] ?? '—' }}</p>
                    @if(array_key_exists('correct', $answer))
                        <p @class(['font-semibold', 'text-emerald-300' => $answer['correct'], 'text-rose-300' => !$answer['correct']])>{{ $answer['correct'] ? 'To‘g‘ri javob' : 'Noto‘g‘ri javob' }}</p>
                    @endif
                    <p class="text-mist"><span class="font-semibold text-paper">Ball:</span> {{ $answer['earned_points'] ?? 0 }} / {{ $questionPoints }}</p>
                </div>
            @endif
        </section>
    @endforeach

    @if($attempt->review_status === 'pending')
            <button type="submit" class="ks-btn-primary inline-flex min-h-11 items-center justify-center px-5 py-3 text-sm font-bold">Baholash va yakunlash</button>
        </form>
    @endif
</div>
@endsection
