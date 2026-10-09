@extends(auth()->user()->isAdmin() ? 'admin.layouts.app' : 'teacher.layouts.app')
@section('title', 'Testni tahrirlash')

@section('content')
@php
    $questionsForEditor = $quiz->questions->map(function ($question) {
        $options = $question->options;
        $correctIndex = $options->search(fn ($option) => $option->is_correct);

        return [
            'id' => $question->id,
            'text' => $question->question_text,
            'type' => $question->type,
            'expected_answer' => $question->expected_answer ?? '',
            'explanation' => $question->explanation ?? '',
            'image_shape' => $question->image_shape ?? 'rectangle',
            'image_url' => $question->image_path ? asset('storage/' . $question->image_path) : '',
            'options' => $options->isNotEmpty() ? $options->pluck('option_text')->values()->all() : ['', '', '', ''],
            'correct' => $correctIndex === false ? 0 : $correctIndex,
        ];
    })->values();
    $updateRoute = auth()->user()->isAdmin() ? 'admin.quizzes.update' : 'teacher.quizzes.update';
    $showRoute = auth()->user()->isAdmin() ? 'admin.quizzes.show' : 'teacher.quizzes.show';
@endphp

<div class="mx-auto max-w-4xl space-y-5" x-data="{
    questions: @js($questionsForEditor),
    addQuestion() {
        this.questions.push({ id: null, text: '', type: 'single', expected_answer: '', explanation: '', image_shape: 'rectangle', image_url: '', options: ['', '', '', ''], correct: 0 });
    },
    removeQuestion(index) {
        if (this.questions.length <= 1) {
            alert('Kamida bitta savol qolishi kerak.');
            return;
        }
        this.questions.splice(index, 1);
    },
    addOption(question) {
        if (question.options.length < 10) question.options.push('');
    },
    removeOption(question, index) {
        if (question.options.length <= 2) return;
        question.options.splice(index, 1);
        if (question.correct >= question.options.length) question.correct = 0;
    }
}">
    <header class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div class="min-w-0">
            <a href="{{ route($showRoute, ['quiz' => $quiz, 'section' => 'questions']) }}" class="inline-flex min-h-10 items-center gap-2 text-sm font-semibold text-amber-400 hover:text-amber-300 focus:outline-none focus:ring-2 focus:ring-amber-400">← Savollarga qaytish</a>
            <p class="mt-2 font-mono text-[11px] uppercase tracking-widest text-amber-400">Test sozlamalari</p>
            <h1 class="mt-1 break-words font-serif text-2xl font-bold text-paper">{{ $quiz->title }}</h1>
            <p class="mt-1 text-sm text-mist">Savollarni tahrirlang, yangisini qo‘shing yoki kerakmasini olib tashlang.</p>
        </div>
    </header>

    @if($errors->any())
        <div role="alert" class="rounded-panel border border-rose-500/25 bg-rose-500/10 p-4 text-sm text-rose-300">
            <p class="font-bold">Saqlashda xatolik:</p>
            <ul class="mt-1 list-disc space-y-1 pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    @if($quiz->attempts()->exists())
        <div class="rounded-panel border border-amber-500/25 bg-amber-500/5 p-3 text-xs leading-relaxed text-amber-200">
            O‘quvchilar topshirgan avvalgi natijalar javob varag‘ida saqlanadi. Tahrirlar keyingi topshiruvchilarga qo‘llanadi.
        </div>
    @endif

    <form action="{{ route($updateRoute, $quiz) }}" method="POST" enctype="multipart/form-data" class="space-y-5">
        @csrf
        @method('PUT')

        <section class="ks-panel grid grid-cols-1 gap-4 p-4 sm:grid-cols-2 sm:p-5">
            <div class="sm:col-span-2">
                <label for="quiz-title" class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-paper">Test nomi</label>
                <input id="quiz-title" name="title" required maxlength="255" value="{{ old('title', $quiz->title) }}" class="ks-input min-h-11 text-sm">
            </div>
            <div>
                <label for="quiz-description" class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-paper">Tavsif</label>
                <textarea id="quiz-description" name="description" rows="3" maxlength="1000" class="ks-input text-sm">{{ old('description', $quiz->description) }}</textarea>
            </div>
            <div class="grid grid-cols-1 gap-4">
                <div>
                    <label for="quiz-difficulty" class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-paper">Murakkablik</label>
                    <select id="quiz-difficulty" name="difficulty" required class="ks-input min-h-11 text-sm">
                        <option value="easy" @selected(old('difficulty', $quiz->difficulty) === 'easy')>Oson</option>
                        <option value="medium" @selected(old('difficulty', $quiz->difficulty) === 'medium')>O‘rta</option>
                        <option value="hard" @selected(old('difficulty', $quiz->difficulty) === 'hard')>Qiyin</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="quiz-points" class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-paper">Mukofot bali</label>
                        <input id="quiz-points" name="reward_points" type="number" min="5" max="{{ auth()->user()->isAdmin() ? 200 : 50 }}" required value="{{ old('reward_points', $quiz->reward_points) }}" class="ks-input min-h-11 text-sm">
                    </div>
                    <div>
                        <label for="quiz-time" class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-paper">Vaqt (daq.)</label>
                        <input id="quiz-time" name="time_limit_minutes" type="number" min="1" max="180" value="{{ old('time_limit_minutes', $quiz->time_limit_minutes) }}" class="ks-input min-h-11 text-sm">
                    </div>
                </div>
            </div>
        </section>

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-serif text-lg font-bold text-paper">Savollar <span class="font-mono text-sm text-mist" x-text="'(' + questions.length + ')'" aria-live="polite"></span></h2>
                <p class="text-xs text-mist">Rasm, yozma javob, variant va to‘g‘ri javobni shu yerda boshqaring.</p>
            </div>
            <button type="button" @click="addQuestion()" class="ks-btn-ghost inline-flex min-h-11 items-center justify-center gap-2 px-4 py-2 text-xs font-bold">
                <span aria-hidden="true">+</span> Savol qo‘shish
            </button>
        </div>

        <template x-for="(q, qIdx) in questions" :key="q.id ?? ('new-' + qIdx)">
            <section class="ks-panel space-y-4 p-4 sm:p-5">
                <input type="hidden" :name="'questions[' + qIdx + '][id]'" :value="q.id ?? ''">
                <div class="flex items-center justify-between gap-3 border-b border-ink-border pb-3">
                    <h3 class="flex items-center gap-2 font-semibold text-amber-300"><span class="inline-flex h-7 w-7 items-center justify-center rounded-btn border border-amber-500/25 bg-amber-500/10 font-mono text-xs" x-text="qIdx + 1"></span> savol</h3>
                    <button type="button" @click="removeQuestion(qIdx)" class="inline-flex min-h-10 items-center rounded-btn px-3 text-xs font-bold text-rose-300 hover:bg-rose-500/10 focus:outline-none focus:ring-2 focus:ring-rose-400">Savolni o‘chirish</button>
                </div>

                <div>
                    <label :for="'question-text-' + qIdx" class="mb-1.5 block text-xs font-semibold text-paper">Savol matni</label>
                    <textarea :id="'question-text-' + qIdx" :name="'questions[' + qIdx + '][text]'" x-model="q.text" rows="2" required maxlength="5000" class="ks-input text-sm"></textarea>
                </div>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    <div>
                        <label :for="'question-type-' + qIdx" class="mb-1.5 block text-xs font-semibold text-paper">Javob turi</label>
                        <select :id="'question-type-' + qIdx" :name="'questions[' + qIdx + '][type]'" x-model="q.type" class="ks-input min-h-11 text-sm">
                            <option value="single">Variantli</option><option value="text">Yozma</option>
                        </select>
                    </div>
                    <div>
                        <label :for="'question-image-' + qIdx" class="mb-1.5 block text-xs font-semibold text-paper">Rasm (ixtiyoriy)</label>
                        <input :id="'question-image-' + qIdx" type="file" :name="'questions[' + qIdx + '][image]'" accept="image/jpeg,image/png,image/webp" class="ks-input min-h-11 text-xs">
                    </div>
                    <div>
                        <label :for="'question-shape-' + qIdx" class="mb-1.5 block text-xs font-semibold text-paper">Rasm shakli</label>
                        <select :id="'question-shape-' + qIdx" :name="'questions[' + qIdx + '][image_shape]'" x-model="q.image_shape" class="ks-input min-h-11 text-sm">
                            <option value="rectangle">To‘rtburchak</option><option value="square">Kvadrat</option><option value="circle">Doira</option><option value="rounded">Yumaloq burchak</option>
                        </select>
                    </div>
                </div>
                <template x-if="q.image_url">
                    <img :src="q.image_url" alt="Savolga biriktirilgan rasm" class="max-h-48 max-w-full rounded-btn border border-ink-border object-contain">
                </template>

                <div x-show="q.type === 'single'" x-cloak class="space-y-2">
                    <p class="text-xs font-semibold text-paper">Javob variantlari · to‘g‘ri javobni belgilang</p>
                    <template x-for="(option, oIdx) in q.options" :key="oIdx">
                        <div class="flex items-center gap-2">
                            <input type="radio" :name="'questions[' + qIdx + '][correct]'" :value="oIdx" x-model="q.correct" :disabled="q.type !== 'single'" class="h-4 w-4 accent-amber-400">
                            <span class="w-6 shrink-0 font-mono text-xs text-mist" x-text="['A', 'B', 'C', 'D'][oIdx] ?? (oIdx + 1)"></span>
                            <input type="text" :name="'questions[' + qIdx + '][options][' + oIdx + ']'" x-model="q.options[oIdx]" :required="q.type === 'single'" :disabled="q.type !== 'single'" maxlength="1000" class="ks-input min-h-11 flex-1 text-sm">
                            <button type="button" @click="removeOption(q, oIdx)" :disabled="q.options.length <= 2" class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-btn border border-ink-border text-mist hover:border-rose-500/30 hover:text-rose-300 disabled:cursor-not-allowed disabled:opacity-40" :aria-label="'Variant ' + (oIdx + 1) + ' ni o‘chirish'">×</button>
                        </div>
                    </template>
                    <button type="button" @click="addOption(q)" :disabled="q.options.length >= 10" class="min-h-10 rounded-btn px-2 text-xs font-semibold text-amber-300 hover:bg-amber-500/10 disabled:opacity-40">+ Variant qo‘shish</button>
                </div>

                <div x-show="q.type === 'text'" x-cloak>
                    <label :for="'expected-' + qIdx" class="mb-1.5 block text-xs font-semibold text-paper">Namunaviy javob yoki baholash mezoni</label>
                    <textarea :id="'expected-' + qIdx" :name="'questions[' + qIdx + '][expected_answer]'" x-model="q.expected_answer" :disabled="q.type !== 'text'" rows="3" maxlength="10000" class="ks-input text-sm"></textarea>
                </div>
                <div>
                    <label :for="'explanation-' + qIdx" class="mb-1.5 block text-xs font-semibold text-paper">Izoh (ixtiyoriy)</label>
                    <textarea :id="'explanation-' + qIdx" :name="'questions[' + qIdx + '][explanation]'" x-model="q.explanation" rows="2" maxlength="2000" class="ks-input text-sm"></textarea>
                </div>
            </section>
        </template>

        <div class="flex flex-col-reverse gap-3 border-t border-ink-border pt-4 sm:flex-row sm:items-center sm:justify-between">
            <a href="{{ route($showRoute, ['quiz' => $quiz, 'section' => 'questions']) }}" class="ks-btn-ghost inline-flex min-h-11 items-center justify-center px-4 py-2 text-xs font-bold">Bekor qilish</a>
            <button type="submit" class="ks-btn-primary inline-flex min-h-11 items-center justify-center px-6 py-2.5 text-sm font-bold">O‘zgarishlarni saqlash</button>
        </div>
    </form>
</div>
@endsection
