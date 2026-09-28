@extends('admin.layouts.app')
@section('title', 'Yangi test topshirig\'i yaratish')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-black text-slate-800 dark:text-white flex items-center gap-2">
                <span>📝</span> Yangi test topshirig'i yaratish
            </h2>
            <p class="text-sm text-slate-500">Kitob bo'yicha interaktiv test savollari va to'g'ri javoblarini tuzish</p>
        </div>
        <a href="{{ route('admin.quizzes.index') }}" class="px-4 py-2 bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl text-sm font-semibold hover:bg-slate-300 dark:hover:bg-slate-600 transition-colors">
            ← Orqaga
        </a>
    </div>

    <form action="{{ route('admin.quizzes.store') }}" method="POST"
          x-data="{
              questions: [
                  {
                      text: '',
                      points: 10,
                      explanation: '',
                      options: ['', '', '', ''],
                      correct: 0
                  }
              ],
              addQuestion() {
                  this.questions.push({
                      text: '',
                      points: 10,
                      explanation: '',
                      options: ['', '', '', ''],
                      correct: 0
                  });
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
                1. Test umumiy ma'lumotlari
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                {{-- Bog'langan kitob --}}
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">Qaysi kitobga tegishli? (Ixtiyoriy)</label>
                    <select name="book_id" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        <option value="">-- Alohida topshiriq / test (hech qaysi kitobga bog'lanmagan / Mustaqil) --</option>
                        @foreach($books as $b)
                            <option value="{{ $b->id }}" {{ (old('book_id', $selectedBookId) == $b->id) ? 'selected' : '' }}>
                                {{ $b->week_number ? "{$b->week_number}-Hafta: " : '' }}{{ $b->title }} ({{ $b->author }})
                            </option>
                        @endforeach
                    </select>
                    <p class="text-[11px] text-slate-500 mt-1">Agar kitob tanlamasangiz, test umumiy mustaqil bilim tekshiruvi sifatida saqlanadi.</p>
                    @error('book_id') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Test nomi --}}
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">Test nomi *</label>
                    <input type="text" name="title" value="{{ old('title') }}" required placeholder="Masalan: 1-hafta kitobi bo'yicha yakuniy bilim tekshiruvi"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    @error('title') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
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

        {{-- 2. Dinamik savollar konstruktori --}}
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <span>❓</span> Savollar va Variantlar (<span x-text="questions.length"></span> ta)
                </h3>
                <button type="button" @click="addQuestion()"
                        class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs rounded-xl shadow transition-all active:scale-95">
                    <span>+ Yana savol qo'shish</span>
                </button>
            </div>

            <template x-for="(q, qIdx) in questions" :key="qIdx">
                <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm space-y-4 relative">
                    {{-- Savol sarlavhasi va o'chirish --}}
                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700/80 pb-3">
                        <span class="inline-flex items-center gap-2 text-sm font-bold text-indigo-600 dark:text-indigo-400">
                            <span class="w-6 h-6 rounded-lg bg-indigo-500/10 text-indigo-500 flex items-center justify-center text-xs font-black" x-text="qIdx + 1"></span>
                            <span>-Savol</span>
                        </span>
                        <div class="flex items-center gap-3">
                            <div class="flex items-center gap-1.5">
                                <span class="text-xs text-slate-400">Ball:</span>
                                <input type="number" :name="'questions[' + qIdx + '][points]'" x-model="q.points" min="1" max="100"
                                       class="w-16 px-2 py-1 text-xs rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white text-center">
                            </div>
                            <button type="button" @click="removeQuestion(qIdx)"
                                    class="text-rose-500 hover:text-rose-400 text-xs font-semibold px-2 py-1 rounded-lg hover:bg-rose-500/10 transition-colors"
                                    title="Ushbu savolni o'chirish">
                                🗑 O'chirish
                            </button>
                        </div>
                    </div>

                    {{-- Savol matni --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 mb-1">Savol matni *</label>
                        <input type="text" :name="'questions[' + qIdx + '][text]'" x-model="q.text" required placeholder="Savol matnini kiriting..."
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none text-sm">
                    </div>

                    {{-- Variantlar --}}
                    <div class="space-y-2 pt-2">
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-300">
                            Javob variantlari (to'g'ri javobni tanlang):
                        </label>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <template x-for="(opt, oIdx) in q.options" :key="oIdx">
                                <div class="flex items-center gap-2 p-2.5 rounded-xl border transition-all"
                                     :class="q.correct == oIdx ? 'bg-emerald-500/10 border-emerald-500/40 ring-1 ring-emerald-500/20' : 'bg-slate-50 dark:bg-slate-900/40 border-slate-200 dark:border-slate-700'">
                                    {{-- Radio button to pick correct answer --}}
                                    <input type="radio" :name="'questions[' + qIdx + '][correct]'" :value="oIdx"
                                           :checked="q.correct == oIdx" @change="q.correct = oIdx"
                                           class="w-4 h-4 text-emerald-600 focus:ring-emerald-500 shrink-0 cursor-pointer">
                                    <span class="text-xs font-bold font-mono text-slate-400 shrink-0" x-text="['A', 'B', 'C', 'D'][oIdx] + ')'"></span>
                                    <input type="text" :name="'questions[' + qIdx + '][options][' + oIdx + ']'" x-model="q.options[oIdx]" required
                                           :placeholder="'Variant ' + ['A', 'B', 'C', 'D'][oIdx]"
                                           class="w-full px-2.5 py-1 text-xs rounded-lg border-0 bg-transparent text-slate-900 dark:text-white focus:ring-0 focus:outline-none">
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Tushuntirish / Izoh (ixtiyoriy) --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">To'g'ri javob izohi (Test yakunida o'quvchiga ko'rsatiladi)</label>
                        <input type="text" :name="'questions[' + qIdx + '][explanation]'" x-model="q.explanation" placeholder="Nima uchun bu javob to'g'ri ekanligi haqida qisqa izoh..."
                               class="w-full px-3 py-1.5 text-xs rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    </div>
                </div>
            </template>
        </div>

        {{-- Pastki qism: Faollashtirish va Saqlash --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 flex flex-col sm:flex-row items-center justify-between gap-4">
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" name="is_active" value="1" checked class="w-5 h-5 text-indigo-600 rounded focus:ring-indigo-500 border-slate-300">
                <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">Test darhol faollashtirilsin (Kitobda ko'rinadi)</span>
            </label>

            <div class="flex items-center gap-3">
                <a href="{{ route('admin.quizzes.index') }}" class="px-5 py-2.5 rounded-xl text-slate-600 dark:text-slate-400 font-semibold text-sm hover:bg-slate-100 dark:hover:bg-slate-700">Bekor qilish</a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 text-white font-bold text-sm hover:bg-indigo-500 transition-colors shadow-lg shadow-indigo-600/30">
                    Test va Savollarni Saqlash
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
