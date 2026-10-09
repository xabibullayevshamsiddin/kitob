@extends('teacher.layouts.app')
@section('title', 'Yangi test topshirig\'i yaratish')

@section('content')
<div class="mx-auto max-w-4xl space-y-5">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl sm:text-2xl font-bold font-serif text-paper flex items-center gap-2">
                <span>📝</span> Yangi test topshirig'i yaratish
            </h2>
            <p class="text-sm text-mist">Kitob bo‘yicha test savollari, baholash mezonlari va mukofot bali.</p>
        </div>
        <a href="{{ route('teacher.quizzes.index') }}" class="ks-btn-ghost min-h-11 inline-flex items-center px-4 py-2 text-xs font-bold">
            ← Orqaga
        </a>
    </div>

    @if($errors->any())
        <div role="alert" class="rounded-panel border border-rose-500/30 bg-rose-500/10 p-4 text-sm font-semibold text-rose-300 space-y-1">
            <p class="font-bold">Iltimos, quyidagi xatoliklarni to'g'rilang:</p>
            <ul class="list-disc pl-5 space-y-0.5">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('teacher.quizzes.store') }}" method="POST" enctype="multipart/form-data"
          x-data="{
              questions: [
                  {
                      text: '',
                      explanation: '',
                      expected_answer: '',
                      type: 'single',
                      options: ['', '', '', ''],
                      correct: 0
                  }
              ],
              addQuestions(count = 1) {
                  for (let i = 0; i < count; i++) {
                      this.questions.push({
                          text: '',
                          explanation: '',
                          expected_answer: '',
                          type: 'single',
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
          }" class="space-y-6">
        @csrf

        {{-- 1. Asosiy ma'lumotlar --}}
        <div class="ks-panel space-y-5 p-4 sm:p-6">
            <h3 class="text-base font-bold font-serif text-paper border-b border-ink-border pb-3 flex items-center gap-2">
                <span class="text-amber-400" aria-hidden="true">01</span> 1. Test umumiy ma'lumotlari va mukofot bali
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                {{-- Bog'langan kitob (1 kitobga faqat 1 ta test qoidasi) --}}
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-paper mb-1.5">
                        Qaysi kitobga tegishli? <span class="text-rose-500">*</span>
                        <span class="ml-2 font-normal normal-case text-mist">(Har bir kitob uchun faqat bitta test biriktiriladi)</span>
                    </label>
                    <select name="book_id" required class="ks-input min-h-11 text-sm">
                        <option value="">-- Kitobni tanlang --</option>
                        @foreach($books as $b)
                            @php
                                $hasExisting = ($b->quizzes_count > 0);
                            @endphp
                            <option value="{{ $b->id }}" 
                                    {{ (old('book_id', $selectedBookId) == $b->id && !$hasExisting) ? 'selected' : '' }}
                                    {{ $hasExisting ? 'disabled' : '' }}
                                    class="{{ $hasExisting ? 'text-mist bg-ink-800' : '' }}">
                                {{ $b->week_number ? "{$b->week_number}-hafta: " : '' }}{{ $b->title }} ({{ $b->author }})
                                @if($hasExisting) — ❌ (Test mavjud) @endif
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-mist">Test tanlangan kitob sahifasida o‘quvchilarga ko‘rinadi.</p>
                </div>

                {{-- Test nomi --}}
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-paper mb-1.5">
                        Test nomi <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="title" value="{{ old('title') }}" required placeholder="Masalan: 1-hafta kitobi bo'yicha bilim tekshiruvi"
                           class="ks-input min-h-11 text-sm">
                </div>

                {{-- Mukofot bali (Maksimal 50 ball) --}}
                <div>
                    <label class="mb-1.5 flex items-center justify-between text-xs font-bold uppercase tracking-wider text-paper">
                        <span>Mukofot bali (Maksimal: 50 ball) <span class="text-rose-500">*</span></span>
                        <span class="text-[11px] text-amber-500 font-mono font-bold">Ustozlar: maks 50</span>
                    </label>
                    <div class="relative">
                        <input type="number" name="reward_points" value="{{ old('reward_points', 50) }}" min="5" max="50" required
                               class="ks-input min-h-11 pr-14 text-sm font-bold">
                        <span class="absolute right-4 top-3 text-xs font-semibold text-mist">BALL</span>
                    </div>
                    <p class="mt-1 text-[11px] text-mist">
                        O'quvchi testni necha foiz to'g'ri topsa, shunga mutanosib ball oladi (masalan: 50 balldan 80% to'g'ri topsa = 40 ball beriladi).
                    </p>
                </div>

                {{-- Murakkablik --}}
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-paper">
                        Murakkablik darajasi <span class="text-rose-500">*</span>
                    </label>
                    <select name="difficulty" required class="ks-input min-h-11 text-sm">
                        <option value="easy">🟢 Oson (Boshlovchilar uchun)</option>
                        <option value="medium" selected>🟡 O'rta (Standart daraja)</option>
                        <option value="hard">🔴 Qiyin (Chuqur mutolaa qilganlar uchun)</option>
                    </select>
                </div>

                {{-- Vaqt limiti --}}
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-paper">
                        Vaqt limiti (daqiqa)
                    </label>
                    <input type="number" name="time_limit_minutes" value="{{ old('time_limit_minutes', 15) }}" min="1" max="180"
                           class="ks-input min-h-11 text-sm">
                </div>

                {{-- Tavsif --}}
                <div class="md:col-span-2">
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-paper">
                        Qisqacha tavsif yoki yo'riqnoma
                    </label>
                    <textarea name="description" rows="2" placeholder="O'quvchiga test boshlashdan oldin ko'rinadigan qisqa tushuntirish..."
                              class="ks-input text-sm">{{ old('description') }}</textarea>
                </div>
            </div>
        </div>

        {{-- 2. Dinamik savollar va ko'p sonli savol qo'shish paneli --}}
        <div class="space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <h3 class="text-base font-bold font-serif text-paper flex items-center gap-2">
                    <span>❓</span> Savollar va Variantlar (<span x-text="questions.length"></span> ta)
                </h3>
                
                {{-- Ko'p sonli savol qo'shish tugmalari --}}
                <div class="flex items-center gap-2">
                    <button type="button" @click="addQuestions(1)"
                            class="ks-btn-gold inline-flex min-h-11 items-center gap-1 px-3 py-1.5 text-xs font-bold">
                        <span>+ 1 ta savol</span>
                    </button>
                    <button type="button" @click="addQuestions(5)"
                            class="ks-btn-ghost inline-flex min-h-11 items-center gap-1 px-3 py-1.5 text-xs font-bold">
                        <span>+ 5 ta</span>
                    </button>
                    <button type="button" @click="addQuestions(10)"
                            class="ks-btn-ghost inline-flex min-h-11 items-center gap-1 border-emerald-500/30 px-3 py-1.5 text-xs font-bold text-emerald-300 hover:bg-emerald-500/10"
                            title="Bir vaqtning o'zida 10 ta savol shablonini qo'shish">
                        <span>⚡ + 10 ta savol qo'shish</span>
                    </button>
                </div>
            </div>

            <template x-for="(q, qIdx) in questions" :key="qIdx">
                <div class="ks-panel relative space-y-4 p-4 sm:p-5">
                    {{-- Savol sarlavhasi va o'chirish --}}
                    <div class="flex items-center justify-between border-b border-ink-border pb-3">
                        <span class="inline-flex items-center gap-2 text-sm font-bold text-amber-400">
                            <span class="flex h-7 w-7 items-center justify-center rounded-btn border border-amber-500/25 bg-amber-500/10 text-xs" x-text="qIdx + 1"></span>
                            <span>-savol</span>
                        </span>
                        <div class="flex items-center gap-3">
                            <button type="button" @click="removeQuestion(qIdx)"
                                    class="min-h-10 rounded-btn px-3 text-xs font-bold text-rose-300 transition hover:bg-rose-500/10 hover:text-rose-200">
                                ✕ Savolni o'chirish
                            </button>
                        </div>
                    </div>

                    {{-- Savol matni --}}
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-paper">Savol matni *</label>
                        <textarea :name="'questions[' + qIdx + '][text]'" x-model="q.text" rows="2" required placeholder="Savolni kiriting..."
                                  class="ks-input text-sm"></textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                        <div>
                            <label class="mb-1 block text-xs font-semibold text-paper">Javob turi</label>
                            <select :name="'questions[' + qIdx + '][type]'" x-model="q.type" class="ks-input min-h-11 text-sm">
                                <option value="single">Variantlardan tanlash</option>
                                <option value="text">Yozma javob (ustoz tekshiradi)</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold text-paper">Savolga rasm qo‘shish (ixtiyoriy)</label>
                            <input type="file" :name="'questions[' + qIdx + '][image]'" accept="image/jpeg,image/png,image/webp" class="ks-input min-h-11 text-xs">
                            <p class="mt-1 text-[11px] text-mist">JPG, PNG yoki WEBP; 5 MB gacha</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold text-paper">Rasm shakli</label>
                            <select :name="'questions[' + qIdx + '][image_shape]'" class="ks-input min-h-11 text-sm">
                                <option value="rectangle">To‘rtburchak — to‘liq rasm</option>
                                <option value="square">Kvadrat</option>
                                <option value="circle">Doira</option>
                                <option value="rounded">Yumaloq burchak</option>
                            </select>
                            <p class="mt-1 text-[11px] text-mist">Rasm qirqilmaydi, tanlangan shaklga sig‘diriladi.</p>
                        </div>
                    </div>

                    {{-- Variantlar --}}
                    <div x-show="q.type === 'single'" x-cloak class="space-y-2.5">
                        <label class="block text-xs font-semibold text-paper">
                            Javob variantlari (To'g'ri javobni radio tugma orqali belgilang):
                        </label>

                        <template x-for="(opt, oIdx) in q.options" :key="oIdx">
                            <div class="flex items-center gap-3">
                                <label class="flex items-center gap-2 cursor-pointer shrink-0" :title="'Variant ' + (oIdx + 1) + ' ni to\'g\'ri javob deb belgilash'">
                                    <input type="radio" :name="'questions[' + qIdx + '][correct]'" :value="oIdx" x-model="q.correct" :disabled="q.type !== 'single'"
                                           class="w-4 h-4 text-emerald-600 focus:ring-emerald-500">
                                    <span class="w-6 text-xs font-mono font-bold text-mist" x-text="['A', 'B', 'C', 'D'][oIdx] ?? (oIdx + 1)"></span>
                                </label>
                                <input type="text" :name="'questions[' + qIdx + '][options][' + oIdx + ']'" x-model="q.options[oIdx]" :required="q.type === 'single'" :disabled="q.type !== 'single'"
                                       :placeholder="'Javob ' + (['A', 'B', 'C', 'D'][oIdx] ?? (oIdx + 1)) + ' matni...'"
                                       :class="q.correct == oIdx ? 'border-amber-500 bg-amber-500/10' : 'border-ink-border bg-ink-950'"
                                       class="ks-input min-h-11 flex-1 text-sm transition-colors">
                                <span x-show="q.correct == oIdx" class="text-xs font-bold text-emerald-600 shrink-0">✓ To'g'ri</span>
                            </div>
                        </template>
                    </div>

                    <div x-show="q.type === 'text'" x-cloak>
                        <label :for="'expected-answer-' + qIdx" class="mb-1 block text-xs font-semibold text-paper">Namunaviy to‘g‘ri javob yoki baholash mezoni <span class="font-normal text-mist">(ixtiyoriy, faqat ustoz/admin ko‘radi)</span></label>
                        <textarea :id="'expected-answer-' + qIdx" :name="'questions[' + qIdx + '][expected_answer]'" x-model="q.expected_answer" :disabled="q.type !== 'text'" rows="3" maxlength="10000" placeholder="Kutiladigan javob, asosiy fikrlar yoki ball qo‘yish mezonini yozing..." class="ks-input text-sm"></textarea>
                        <p class="mt-1 text-[11px] text-mist">Bu matn o‘quvchiga ko‘rsatilmaydi; tekshiruv paytida ko‘rinadi.</p>
                    </div>

                    {{-- Tushuntirish / Izoh (ixtiyoriy) --}}
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-mist">To‘g‘ri javob izohi (o‘quvchiga ko‘rinadi, ixtiyoriy)</label>
                        <input type="text" :name="'questions[' + qIdx + '][explanation]'" x-model="q.explanation" placeholder="Nima uchun bu javob to'g'ri ekanligi haqida qisqa izoh..."
                               class="ks-input min-h-11 text-sm">
                    </div>
                </div>
            </template>
        </div>

        {{-- Pastki tugmalar --}}
        <div class="flex flex-wrap items-center justify-between gap-3 border-t border-ink-border pt-4 pb-12">
            <div class="flex flex-wrap items-center gap-2">
                <button type="button" @click="addQuestions(1)"
                        class="ks-btn-ghost min-h-11 px-4 py-2.5 text-xs font-bold">
                    + 1 ta savol
                </button>
                <button type="button" @click="addQuestions(10)"
                        class="ks-btn-ghost min-h-11 border-emerald-500/30 px-4 py-2.5 text-xs font-bold text-emerald-300 hover:bg-emerald-500/10">
                    ⚡ + 10 ta savol
                </button>
            </div>

            <button type="submit"
                    class="ks-btn-primary min-h-11 px-6 py-3 text-sm font-bold">
                💾 Testni saqlash va faollashtirish
            </button>
        </div>
    </form>
</div>
@endsection
