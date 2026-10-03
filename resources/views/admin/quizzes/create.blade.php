@extends('admin.layouts.app')
@section('title', 'Yangi test topshirig\'i yaratish')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-black text-slate-800 dark:text-white flex items-center gap-2">
                <span>📝</span> Yangi test topshirig'i yaratish
            </h2>
            <p class="text-sm text-slate-500">Kitob bo'yicha interaktiv test savollari, mukofot bali va to'g'ri javoblarini tuzish</p>
        </div>
        <a href="{{ route('admin.quizzes.index') }}" class="px-4 py-2 bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl text-sm font-semibold hover:bg-slate-300 dark:hover:bg-slate-600 transition-colors">
            ← Orqaga
        </a>
    </div>

    @if($errors->any())
        <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-600 dark:text-rose-400 text-xs font-semibold space-y-1">
            <p class="font-bold">Iltimos, quyidagi xatoliklarni to'g'rilang:</p>
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
          }" class="space-y-6">
        @csrf

        {{-- 1. Asosiy ma'lumotlar kartasi --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 sm:p-8 shadow-sm space-y-5">
            <h3 class="text-base font-bold text-slate-900 dark:text-white border-b border-slate-100 dark:border-slate-700 pb-3">
                1. Test umumiy ma'lumotlari & Mukofot bali
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                {{-- Bog'langan kitob --}}
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">
                        Qaysi kitobga tegishli? <span class="text-rose-500">*</span>
                        <span class="text-slate-400 font-normal text-xs ml-2">(Har bir kitob uchun faqat bitta test biriktiriladi)</span>
                    </label>
                    <select name="book_id" required class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        <option value="">-- Kitobni tanlang --</option>
                        @foreach($books as $b)
                            @php
                                $hasExisting = ($b->quizzes_count > 0);
                            @endphp
                            <option value="{{ $b->id }}" 
                                    {{ (old('book_id', $selectedBookId) == $b->id && !$hasExisting) ? 'selected' : '' }}
                                    {{ $hasExisting ? 'disabled' : '' }}
                                    class="{{ $hasExisting ? 'text-slate-400 bg-slate-100 dark:bg-slate-800' : '' }}">
                                {{ $b->week_number ? "{$b->week_number}-Hafta: " : '' }}{{ $b->title }} ({{ $b->author }})
                                @if($hasExisting) — ❌ (Test mavjud) @endif
                            </option>
                        @endforeach
                    </select>
                    @error('book_id') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Test nomi --}}
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">Test nomi *</label>
                    <input type="text" name="title" value="{{ old('title') }}" required placeholder="Masalan: 1-hafta kitobi bo'yicha yakuniy bilim tekshiruvi"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    @error('title') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Mukofot bali (Adminlar uchun maksimal 200 ball) --}}
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1 flex items-center justify-between">
                        <span>Mukofot bali (Maksimal: 200 ball) <span class="text-rose-500">*</span></span>
                        <span class="text-xs text-indigo-400 font-mono font-bold">Admin: maks 200</span>
                    </label>
                    <div class="relative">
                        <input type="number" name="reward_points" value="{{ old('reward_points', 100) }}" min="5" max="200" required
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none font-bold">
                        <span class="absolute right-4 top-2.5 text-xs text-slate-400 font-semibold">BALL</span>
                    </div>
                    <p class="text-[11px] text-slate-500 mt-1">
                        🎯 O'quvchi testni necha foiz to'g'ri topsa, shunga mutanosib ball oladi (masalan: 100 balldan 80% to'g'ri topsa = 80 ball).
                    </p>
                </div>

                {{-- Murakkablik --}}
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">Murakkablik darajasi *</label>
                    <select name="difficulty" required class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        <option value="easy">🟢 Oson (Boshlovchilar uchun)</option>
                        <option value="medium" selected>🟡 O'rta (Standart daraja)</option>
                        <option value="hard">🔴 Qiyin (Chuqur mutolaa qilganlar uchun)</option>
                    </select>
                </div>

                {{-- Vaqt limiti --}}
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">Vaqt limiti (daqiqa)</label>
                    <input type="number" name="time_limit_minutes" value="{{ old('time_limit_minutes', 15) }}" min="1" max="180"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>

                {{-- Tavsif --}}
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">Qisqacha tavsif yoki yo'riqnoma</label>
                    <textarea name="description" rows="2" placeholder="O'quvchiga testdan oldin ko'rinadigan qisqa tushuntirish..."
                              class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">{{ old('description') }}</textarea>
                </div>
            </div>
        </div>

        {{-- 2. Dinamik savollar va ko'p sonli savol qo'shish paneli --}}
        <div class="space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <span>❓</span> Savollar va Variantlar (<span x-text="questions.length"></span> ta)
                </h3>
                
                {{-- Savol qo'shish tugmalari --}}
                <div class="flex items-center gap-2">
                    <button type="button" @click="addQuestions(1)"
                            class="inline-flex items-center gap-1 px-3 py-1.5 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs rounded-xl shadow transition-all active:scale-95">
                        <span>+ 1 ta savol</span>
                    </button>
                    <button type="button" @click="addQuestions(5)"
                            class="inline-flex items-center gap-1 px-3 py-1.5 bg-indigo-700 hover:bg-indigo-600 text-white font-bold text-xs rounded-xl shadow transition-all active:scale-95">
                        <span>+ 5 ta</span>
                    </button>
                    <button type="button" @click="addQuestions(10)"
                            class="inline-flex items-center gap-1 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs rounded-xl shadow transition-all active:scale-95"
                            title="Bir vaqtning o'zida 10 ta savol shablonini qo'shish">
                        <span>⚡ + 10 ta savol qo'shish</span>
                    </button>
                </div>
            </div>

            <template x-for="(q, qIdx) in questions" :key="qIdx">
                <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm space-y-4 relative">
                    {{-- Savol sarlavhasi va o'chirish --}}
                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700 pb-3">
                        <span class="inline-flex items-center gap-2 text-sm font-bold text-indigo-600 dark:text-indigo-400">
                            <span class="w-6 h-6 rounded-full bg-indigo-100 dark:bg-indigo-900/40 flex items-center justify-center text-xs" x-text="qIdx + 1"></span>
                            <span>-savol</span>
                        </span>
                        <div class="flex items-center gap-3">
                            <button type="button" @click="removeQuestion(qIdx)"
                                    class="text-xs text-rose-500 hover:text-rose-700 p-1 rounded hover:bg-rose-50 dark:hover:bg-rose-900/20 font-bold">
                                ✕ Savolni o'chirish
                            </button>
                        </div>
                    </div>

                    {{-- Savol matni --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Savol matni *</label>
                        <textarea :name="'questions[' + qIdx + '][text]'" x-model="q.text" rows="2" required placeholder="Savolni kiriting..."
                                  class="w-full px-4 py-2 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none text-sm"></textarea>
                    </div>

                    {{-- Variantlar --}}
                    <div class="space-y-2.5">
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">
                            Javob variantlari (To'g'ri javobni radio tugma orqali belgilang):
                        </label>

                        <template x-for="(opt, oIdx) in q.options" :key="oIdx">
                            <div class="flex items-center gap-3">
                                <label class="flex items-center gap-2 cursor-pointer shrink-0" :title="'Variant ' + (oIdx + 1) + ' ni to\'g\'ri javob deb belgilash'">
                                    <input type="radio" :name="'questions[' + qIdx + '][correct]'" :value="oIdx" x-model="q.correct"
                                           class="w-4 h-4 text-emerald-600 focus:ring-emerald-500">
                                    <span class="w-6 text-xs font-mono font-bold text-slate-500" x-text="['A', 'B', 'C', 'D'][oIdx] ?? (oIdx + 1)"></span>
                                </label>
                                <input type="text" :name="'questions[' + qIdx + '][options][' + oIdx + ']'" x-model="q.options[oIdx]" required
                                       :placeholder="'Javob ' + (['A', 'B', 'C', 'D'][oIdx] ?? (oIdx + 1)) + ' matni...'"
                                       :class="q.correct == oIdx ? 'border-emerald-500 bg-emerald-50/30 dark:bg-emerald-950/20' : 'border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700'"
                                       class="flex-1 px-4 py-2 rounded-xl border text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none text-sm transition-colors">
                                <span x-show="q.correct == oIdx" class="text-xs font-bold text-emerald-600 shrink-0">✓ To'g'ri</span>
                            </div>
                        </template>
                    </div>

                    {{-- Tushuntirish / Izoh (ixtiyoriy) --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">To'g'ri javob izohi / tushuntirish (o'quvchi test yakunida ko'radi, ixtiyoriy)</label>
                        <input type="text" :name="'questions[' + qIdx + '][explanation]'" x-model="q.explanation" placeholder="Nima uchun bu javob to'g'ri ekanligi haqida qisqa izoh..."
                               class="w-full px-4 py-2 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none text-xs">
                    </div>
                </div>
            </template>
        </div>

        {{-- Pastki tugmalar --}}
        <div class="flex flex-wrap items-center justify-between gap-3 pt-4 pb-12 border-t border-slate-200 dark:border-slate-700">
            <div class="flex items-center gap-2">
                <button type="button" @click="addQuestions(1)"
                        class="px-4 py-2.5 rounded-xl border border-indigo-600 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 font-bold text-xs transition-all">
                    + 1 ta savol
                </button>
                <button type="button" @click="addQuestions(10)"
                        class="px-4 py-2.5 rounded-xl border border-emerald-600 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 font-bold text-xs transition-all">
                    ⚡ + 10 ta savol
                </button>
            </div>

            <button type="submit"
                    class="px-8 py-3 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-500 hover:to-indigo-600 text-white font-black text-sm rounded-xl shadow-lg shadow-indigo-600/30 transition-all active:scale-95">
                💾 Testni saqlash va faollashtirish
            </button>
        </div>
    </form>
</div>
@endsection
