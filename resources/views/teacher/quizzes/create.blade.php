@extends('teacher.layouts.app')
@section('title', 'Yangi test topshirig\'i yaratish')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl sm:text-2xl font-black text-slate-800 dark:text-white flex items-center gap-2">
                <span>📝</span> Yangi test topshirig'i yaratish
            </h2>
            <p class="text-sm text-slate-500">Kitob bo'yicha interaktiv test savollari va to'g'ri javoblarini tuzish</p>
        </div>
        <a href="{{ route('teacher.quizzes.index') }}" class="px-4 py-2 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl text-xs font-bold hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors">
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

    <form action="{{ route('teacher.quizzes.store') }}" method="POST"
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

        {{-- 1. Asosiy ma'lumotlar --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 sm:p-8 shadow-sm space-y-5">
            <h3 class="text-base font-bold text-slate-900 dark:text-white border-b border-slate-100 dark:border-slate-700 pb-3 flex items-center gap-2">
                <span>📚</span> 1. Test umumiy ma'lumotlari
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                {{-- Bog'langan kitob --}}
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Qaysi kitobga tegishli? <span class="text-indigo-600">*</span>
                    </label>
                    <select name="book_id" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none text-sm">
                        <option value="">-- Alohida mustaqil test (kitobga bog'lanmagan) --</option>
                        @foreach($books as $b)
                            <option value="{{ $b->id }}" {{ (old('book_id', $selectedBookId) == $b->id) ? 'selected' : '' }}>
                                {{ $b->week_number ? "{$b->week_number}-hafta: " : '' }}{{ $b->title }} ({{ $b->author }})
                            </option>
                        @endforeach
                    </select>
                    <p class="text-[11px] text-slate-500 mt-1">Ushbu test kitob sahifasida va o'quvchilar profilida ko'rinadi.</p>
                </div>

                {{-- Test nomi --}}
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Test nomi <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="title" value="{{ old('title') }}" required placeholder="Masalan: 1-hafta kitobi bo'yicha yakuniy bilim tekshiruvi"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none text-sm">
                </div>

                {{-- Murakkablik --}}
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Murakkablik darajasi <span class="text-rose-500">*</span>
                    </label>
                    <select name="difficulty" required class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none text-sm">
                        <option value="easy">🟢 Oson (Boshlovchilar uchun)</option>
                        <option value="medium" selected>🟡 O'rta (Standart daraja)</option>
                        <option value="hard">🔴 Qiyin (Chuqur mutolaa qilganlar uchun)</option>
                    </select>
                </div>

                {{-- Vaqt limiti --}}
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Vaqt limiti (daqiqa)
                    </label>
                    <input type="number" name="time_limit_minutes" value="{{ old('time_limit_minutes', 15) }}" min="1" max="180"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none text-sm">
                </div>

                {{-- Tavsif --}}
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Qisqacha tavsif yoki yo'riqnoma
                    </label>
                    <textarea name="description" rows="2" placeholder="O'quvchiga test boshlashdan oldin ko'rinadigan qisqa tushuntirish..."
                              class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none text-sm">{{ old('description') }}</textarea>
                </div>
            </div>
        </div>

        {{-- 2. Dinamik savollar --}}
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
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
                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700 pb-3">
                        <span class="inline-flex items-center gap-2 text-sm font-bold text-indigo-600 dark:text-indigo-400">
                            <span class="w-6 h-6 rounded-full bg-indigo-100 dark:bg-indigo-900/40 flex items-center justify-center text-xs" x-text="qIdx + 1"></span>
                            <span>-savol</span>
                        </span>
                        <div class="flex items-center gap-3">
                            <div class="flex items-center gap-1 text-xs text-slate-500">
                                <span>Ball:</span>
                                <input type="number" :name="'questions[' + qIdx + '][points]'" x-model="q.points" min="1" max="100"
                                       class="w-16 px-2 py-1 text-xs rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-center font-bold text-slate-800 dark:text-white">
                            </div>
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
        <div class="flex items-center justify-between pt-4 pb-12">
            <button type="button" @click="addQuestion()"
                    class="px-5 py-2.5 rounded-xl border border-indigo-600 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 font-bold text-xs transition-all">
                + Yangi savol qo'shish
            </button>

            <button type="submit"
                    class="px-8 py-3 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-500 hover:to-indigo-600 text-white font-black text-sm rounded-xl shadow-lg shadow-indigo-600/30 transition-all active:scale-95">
                💾 Testni saqlash va faollashtirish
            </button>
        </div>
    </form>
</div>
@endsection
