@extends('admin.layouts.app')
@section('title', "Yangi test topshirig'i yaratish")
@section('breadcrumb', "Test topshiriqlari")

@section('content')
<div class="max-w-4xl mx-auto space-y-5">

    {{-- Page header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <span class="ks-eyebrow">Test topshiriqlari</span>
            <h1 class="text-xl font-bold font-serif text-paper mt-0.5">Yangi test topshirig'i yaratish</h1>
            <p class="text-xs text-mist font-mono mt-0.5">Kitob bo'yicha interaktiv test savollari, mukofot bali va to'g'ri javoblarni tuzish</p>
        </div>
        <a href="{{ route('admin.quizzes.index') }}" class="ks-btn-ghost py-2 px-4 text-xs shrink-0 self-start sm:self-auto">
            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
            Orqaga
        </a>
    </div>

    @if($errors->any())
        <div class="ks-panel p-4 bg-rose-500/10 border border-rose-500/20 text-rose-300 text-xs font-semibold space-y-1">
            <p class="font-bold font-mono uppercase tracking-wider text-[11px]">Iltimos, quyidagi xatoliklarni to'g'rilang:</p>
            <ul class="list-disc pl-5 space-y-0.5">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.quizzes.store') }}" method="POST"
          x-data="{
              questions: [
                  {
                      text: '',
                      explanation: '',
                      options: ['', '', '', ''],
                      correct: 0
                  }
              ],
              addQuestions(count = 1) {
                  for (let i = 0; i < count; i++) {
                      this.questions.push({
                          text: '',
                          explanation: '',
                          options: ['', '', '', ''],
                          correct: 0
                      });
                  }
              },
              removeQuestion(index) {
                  if (this.questions.length > 1) {
                      this.questions.splice(index, 1);
                  } else {
                      alert('Kamida bitta savol qolishi kerak!');
                  }
              }
          }" class="space-y-5">
        @csrf

        {{-- 1. Asosiy ma'lumotlar --}}
        <div class="ks-panel p-5 sm:p-6 space-y-5">
            <h3 class="text-sm font-bold font-serif text-paper border-b border-ink-border pb-3">
                <span class="ks-eyebrow mr-2">01</span>Test umumiy ma'lumotlari &amp; Mukofot bali
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Bog'langan kitob --}}
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-paper mb-1">
                        Qaysi kitobga tegishli? <span class="text-rose-400">*</span>
                        <span class="text-mist font-normal font-mono text-[11px] ml-2">(Har bir kitob uchun faqat bitta test biriktiriladi)</span>
                    </label>
                    <select name="book_id" required class="ks-input">
                        <option value="">-- Kitobni tanlang --</option>
                        @foreach($books as $b)
                            @php
                                $hasExisting = ($b->quizzes_count > 0);
                            @endphp
                            <option value="{{ $b->id }}"
                                    {{ (old('book_id', $selectedBookId) == $b->id && !$hasExisting) ? 'selected' : '' }}
                                    {{ $hasExisting ? 'disabled' : '' }}>
                                {{ $b->week_number ? "{$b->week_number}-Hafta: " : '' }}{{ $b->title }} ({{ $b->author }})@if($hasExisting) — test mavjud @endif
                            </option>
                        @endforeach
                    </select>
                    @error('book_id') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Test nomi --}}
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-paper mb-1">Test nomi <span class="text-rose-400">*</span></label>
                    <input type="text" name="title" value="{{ old('title') }}" required placeholder="Masalan: 1-hafta kitobi bo'yicha yakuniy bilim tekshiruvi" class="ks-input">
                    @error('title') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Mukofot bali --}}
                <div>
                    <label class="flex items-center justify-between text-xs font-semibold text-paper mb-1">
                        <span>Mukofot bali (Maksimal: 200 ball) <span class="text-rose-400">*</span></span>
                        <span class="text-[11px] text-amber-400 font-mono font-bold">Admin: maks 200</span>
                    </label>
                    <div class="relative">
                        <input type="number" name="reward_points" value="{{ old('reward_points', 100) }}" min="5" max="200" required
                               class="ks-input font-mono font-bold pr-14">
                        <span class="absolute right-3.5 top-1/2 -translate-y-1/2 text-[11px] text-mist font-mono font-semibold pointer-events-none">BALL</span>
                    </div>
                    <p class="text-[11px] text-mist mt-1.5">
                        O'quvchi testni necha foiz to'g'ri topsa, shunga mutanosib ball oladi (masalan: 100 balldan 80% to'g'ri topsa = 80 ball).
                    </p>
                </div>

                {{-- Murakkablik --}}
                <div>
                    <label class="block text-xs font-semibold text-paper mb-1">Murakkablik darajasi <span class="text-rose-400">*</span></label>
                    <select name="difficulty" required class="ks-input">
                        <option value="easy">Oson (Boshlovchilar uchun)</option>
                        <option value="medium" selected>O'rta (Standart daraja)</option>
                        <option value="hard">Qiyin (Chuqur mutolaa qilganlar uchun)</option>
                    </select>
                </div>

                {{-- Vaqt limiti --}}
                <div>
                    <label class="block text-xs font-semibold text-paper mb-1">Vaqt limiti (daqiqa)</label>
                    <input type="number" name="time_limit_minutes" value="{{ old('time_limit_minutes', 15) }}" min="1" max="180" class="ks-input font-mono">
                </div>

                {{-- Tavsif --}}
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-paper mb-1">Qisqacha tavsif yoki yo'riqnoma</label>
                    <textarea name="description" rows="2" placeholder="O'quvchiga testdan oldin ko'rinadigan qisqa tushuntirish..." class="ks-input">{{ old('description') }}</textarea>
                </div>
            </div>
        </div>

        {{-- 2. Savollar --}}
        <div class="space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <h3 class="text-sm font-bold font-serif text-paper">
                    <span class="ks-eyebrow mr-2">02</span>Savollar va Variantlar (<span x-text="questions.length" class="font-mono text-amber-400"></span> ta)
                </h3>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" @click="addQuestions(1)" class="ks-btn-gold min-h-11 py-1.5 px-3 text-xs font-mono font-bold">+ 1 ta savol</button>
                    <button type="button" @click="addQuestions(5)" class="ks-btn-ghost min-h-11 py-1.5 px-3 text-xs font-mono">+ 5 ta</button>
                    <button type="button" @click="addQuestions(10)" title="Bir vaqtning o'zida 10 ta savol shablonini qo'shish" class="ks-btn-ghost min-h-11 py-1.5 px-3 text-xs font-mono text-amber-400 hover:text-amber-300">+ 10 ta savol</button>
                </div>
            </div>

            <template x-for="(q, qIdx) in questions" :key="qIdx">
                <div class="ks-panel p-5 space-y-4 relative animate-fade-in">
                    {{-- Savol sarlavhasi va o'chirish --}}
                    <div class="flex items-center justify-between border-b border-ink-border pb-3">
                        <span class="inline-flex items-center gap-2 text-sm font-bold font-serif text-paper">
                            <span class="w-6 h-6 rounded-badge bg-amber-500/15 border border-amber-500/30 flex items-center justify-center text-xs font-mono text-amber-400" x-text="qIdx + 1"></span>
                            <span>-savol</span>
                        </span>
                        <button type="button" @click="removeQuestion(qIdx)"
                                class="text-xs font-mono text-rose-300 hover:text-rose-200 px-2 py-1 rounded-badge hover:bg-rose-500/10 transition-colors">
                            ✕ O'chirish
                        </button>
                    </div>

                    {{-- Savol matni --}}
                    <div>
                        <label class="block text-xs font-semibold text-paper mb-1">Savol matni <span class="text-rose-400">*</span></label>
                        <textarea :name="'questions[' + qIdx + '][text]'" x-model="q.text" rows="2" required placeholder="Savolni kiriting..." class="ks-input text-sm"></textarea>
                    </div>

                    {{-- Variantlar --}}
                    <div class="space-y-2.5">
                        <label class="block text-xs font-semibold text-paper">
                            Javob variantlari <span class="text-mist font-normal font-mono text-[11px]">(To'g'ri javobni radio tugma orqali belgilang)</span>
                        </label>

                        <template x-for="(opt, oIdx) in q.options" :key="oIdx">
                            <div class="flex items-center gap-3">
                                <label class="flex items-center gap-2 cursor-pointer shrink-0" :title="'Variant ' + (oIdx + 1) + ' ni to\'g\'ri javob deb belgilash'">
                                    <input type="radio" :name="'questions[' + qIdx + '][correct]'" :value="oIdx" x-model="q.correct"
                                           class="w-4 h-4 accent-amber-500">
                                    <span class="w-6 text-xs font-mono font-bold text-mist" x-text="['A', 'B', 'C', 'D'][oIdx] ?? (oIdx + 1)"></span>
                                </label>
                                <input type="text" :name="'questions[' + qIdx + '][options][' + oIdx + ']'" x-model="q.options[oIdx]" required
                                       :placeholder="'Javob ' + (['A', 'B', 'C', 'D'][oIdx] ?? (oIdx + 1)) + ' matni...'"
                                       :class="q.correct == oIdx ? 'border-amber-500 bg-amber-500/5' : ''"
                                       class="ks-input text-sm transition-colors">
                                <span x-show="q.correct == oIdx" class="text-xs font-mono font-bold text-amber-400 shrink-0">✓ To'g'ri</span>
                            </div>
                        </template>
                    </div>

                    {{-- Tushuntirish --}}
                    <div>
                        <label class="block text-xs font-medium text-mist mb-1">To'g'ri javob izohi / tushuntirish <span class="text-[10px]">(o'quvchi test yakunida ko'radi, ixtiyoriy)</span></label>
                        <input type="text" :name="'questions[' + qIdx + '][explanation]'" x-model="q.explanation" placeholder="Nima uchun bu javob to'g'ri ekanligi haqida qisqa izoh..." class="ks-input text-xs">
                    </div>
                </div>
            </template>
        </div>

        {{-- Pastki tugmalar --}}
        <div class="flex flex-wrap items-center justify-between gap-3 pt-4 pb-12 border-t border-ink-border">
            <div class="flex flex-wrap items-center gap-2">
                <button type="button" @click="addQuestions(1)" class="ks-btn-ghost min-h-11 py-2 px-3.5 text-xs font-mono">+ 1 ta savol</button>
                <button type="button" @click="addQuestions(10)" class="ks-btn-ghost min-h-11 py-2 px-3.5 text-xs font-mono">+ 10 ta savol</button>
            </div>

            <button type="submit" class="ks-btn-primary py-2.5 px-8 text-sm font-bold">
                Testni saqlash va faollashtirish
            </button>
        </div>
    </form>
</div>
@endsection
