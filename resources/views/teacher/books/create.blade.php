@extends('teacher.layouts.app')
@section('title', 'Kitob qo\'shish')

@section('content')
<div class="max-w-2xl">
    <div class="mb-6">
        <a href="{{ route('teacher.books.index') }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Orqaga
        </a>
        <h2 class="text-xl font-black text-slate-800 dark:text-white mt-2">📖 Yangi kitob qo'shish</h2>
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm p-6">
        <form method="POST" action="{{ route('teacher.books.store') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Kitob nomi *</label>
                <input type="text" name="title" value="{{ old('title') }}" required
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all @error('title') border-rose-400 @enderror"
                       placeholder="Masalan: Atom Odatlar">
                @error('title') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Muallif *</label>
                <input type="text" name="author" value="{{ old('author') }}" required
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all"
                       placeholder="Masalan: James Clear">
                @error('author') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Janr *</label>
                <select name="genre" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all">
                    <option value="">Janrni tanlang</option>
                    @foreach(['Shaxsiy rivojlanish','Roman','Ilmiy','Tarix','Biznes','Psixologiya','Fantastika','Bolalar adabiyoti','Falsafa','Biografiya'] as $genre)
                        <option value="{{ $genre }}" {{ old('genre') == $genre ? 'selected' : '' }}>{{ $genre }}</option>
                    @endforeach
                </select>
                @error('genre') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Tavsif *</label>
                <textarea name="description" rows="4" required
                          class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all resize-none"
                          placeholder="Kitob haqida qisqacha ma'lumot...">{{ old('description') }}</textarea>
                @error('description') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- PDF fayl yuklash (ixtiyoriy) --}}
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Kitob PDF fayli (ixtiyoriy)</label>
                <p class="text-xs text-slate-500 dark:text-slate-400 mb-2">Yuklangan taqdir o'quvchilar aynan shu faylni yuklab oladi. Yuklanmasa, boblar matnidan avtomatik yaratiladi.</p>
                <label for="pdf_file" class="group flex flex-col items-center justify-center w-full px-6 py-7 rounded-2xl border-2 border-dashed border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-900/40 hover:border-indigo-400 hover:bg-indigo-50/50 dark:hover:bg-indigo-950/20 transition-all cursor-pointer">
                    <svg class="w-9 h-9 text-slate-400 group-hover:text-indigo-500 transition-colors mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 11v6m0 0l-2.5-2.5M12 17l2.5-2.5"/>
                    </svg>
                    <span class="text-sm font-semibold text-slate-600 dark:text-slate-300 group-hover:text-indigo-600 transition-colors">PDF faylni tanlash</span>
                    <span class="text-xs text-slate-400 mt-1">Faqat .pdf · Maksimal 20 MB</span>
                    <input type="file" name="pdf_file" id="pdf_file" accept=".pdf,application/pdf" class="hidden"
                           onchange="document.getElementById('pdf-file-name').textContent = this.files[0] ? this.files[0].name : ''; document.getElementById('pdf-file-selected').style.display = this.files[0] ? 'flex' : 'none';">
                </label>
                <div id="pdf-file-selected" style="display:none" class="hidden sm:flex items-center gap-2 mt-2 px-3 py-2 rounded-xl bg-emerald-500/10 border border-emerald-500/25 text-emerald-600 dark:text-emerald-400">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span id="pdf-file-name" class="text-xs font-semibold truncate"></span>
                </div>
                @error('pdf_file') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                        class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-sm rounded-xl transition-all active:scale-95 shadow-lg shadow-indigo-500/25">
                    Saqlash
                </button>
                <a href="{{ route('teacher.books.index') }}"
                   class="px-6 py-2.5 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 font-semibold text-sm rounded-xl transition-all">
                    Bekor qilish
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
