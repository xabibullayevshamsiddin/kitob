@extends('admin.layouts.app')
@section('title', 'Yangi kitob qo\'shish')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-black text-slate-800 dark:text-white flex items-center gap-2">
                <span>📚</span> Yangi kitob qo'shish
            </h2>
            <p class="text-sm text-slate-500">Platformaga yangi kitob ma'lumotlarini kiritish</p>
        </div>
        <a href="{{ route('admin.books.index') }}" class="px-4 py-2 bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl text-sm font-semibold hover:bg-slate-300 dark:hover:bg-slate-600 transition-colors">
            ← Orqaga
        </a>
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 shadow-sm">
        <form action="{{ route('admin.books.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">Kitob nomi *</label>
                    <input type="text" name="title" value="{{ old('title') }}" required class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    @error('title') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">Muallif *</label>
                    <input type="text" name="author" value="{{ old('author') }}" required class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    @error('author') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">Janr *</label>
                    <input type="text" name="genre" value="{{ old('genre', 'Shaxsiy rivojlanish') }}" required class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">Hafta raqami</label>
                    <input type="number" name="week_number" value="{{ old('week_number', (\App\Models\Book::max('week_number') ?? 0) + 1) }}" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">Qisqacha tavsif *</label>
                <textarea name="description" rows="4" required class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">{{ old('description') }}</textarea>
            </div>

            {{-- PDF fayl yuklash (ixtiyoriy) --}}
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">Kitob PDF fayli (ixtiyoriy)</label>
                <p class="text-xs text-slate-500 dark:text-slate-400 mb-2">
                    Agar kitobning tayyor PDF fayli bo'lsa, shu yerda yuklang — o'quvchilar aynan shu faylni yuklab oladi.
                    Yuklanmasa, PDF boblar matnidan avtomatik yaratiladi.
                </p>
                <label for="pdf_file" class="group flex flex-col items-center justify-center w-full px-6 py-8 rounded-2xl border-2 border-dashed border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-900/40 hover:border-indigo-400 hover:bg-indigo-50/50 dark:hover:bg-indigo-950/20 transition-all cursor-pointer">
                    <svg class="w-10 h-10 text-slate-400 group-hover:text-indigo-500 transition-colors mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 11v6m0 0l-2.5-2.5M12 17l2.5-2.5"/>
                    </svg>
                    <span class="text-sm font-semibold text-slate-600 dark:text-slate-300 group-hover:text-indigo-600 transition-colors">
                        PDF faylni tanlash yoki bu yerga sudrab keling
                    </span>
                    <span class="text-xs text-slate-400 mt-1">Faqat .pdf · Maksimal 20 MB</span>
                    <input type="file" name="pdf_file" id="pdf_file" accept=".pdf,application/pdf" class="hidden"
                           onchange="document.getElementById('pdf-file-name').textContent = this.files[0] ? this.files[0].name : ''; document.getElementById('pdf-file-selected').style.display = this.files[0] ? 'flex' : 'none';">
                </label>
                <div id="pdf-file-selected" style="display:none" class="hidden sm:flex items-center gap-2 mt-2 px-3 py-2 rounded-xl bg-emerald-500/10 border border-emerald-500/25 text-emerald-600 dark:text-emerald-400">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span id="pdf-file-name" class="text-xs font-semibold truncate"></span>
                </div>
                @error('pdf_file') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center gap-3">
                <input type="checkbox" name="is_active" id="is_active" value="1" checked class="w-5 h-5 text-indigo-600 rounded focus:ring-indigo-500 border-slate-300">
                <label for="is_active" class="text-sm font-medium text-slate-700 dark:text-slate-300">Kitob darhol faollashtirilsin (Aktiv)</label>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-700">
                <a href="{{ route('admin.books.index') }}" class="px-5 py-2.5 rounded-xl text-slate-600 dark:text-slate-400 font-semibold text-sm hover:bg-slate-100 dark:hover:bg-slate-700">Bekor qilish</a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 text-white font-bold text-sm hover:bg-indigo-500 transition-colors shadow-lg shadow-indigo-600/30">Saqlash</button>
            </div>
        </form>
    </div>
</div>
@endsection
