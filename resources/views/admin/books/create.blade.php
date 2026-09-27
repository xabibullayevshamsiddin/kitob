@extends('admin.layouts.app')
@section('title', 'Yangi kitob qo\'shish')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-black text-slate-800 dark:text-white flex items-center gap-2">
                <span>📚</span> Yangi kitob qo'shish
            </h2>
            <p class="text-sm text-slate-500">Platformaga yangi kitob ma'lumotlarini kiritish va fayllarini yuklash</p>
        </div>
        <a href="{{ route('admin.books.index') }}" class="px-4 py-2 bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl text-sm font-semibold hover:bg-slate-300 dark:hover:bg-slate-600 transition-colors">
            ← Orqaga
        </a>
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 sm:p-8 shadow-sm">
        <form action="{{ route('admin.books.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">Kitob nomi *</label>
                    <input type="text" name="title" value="{{ old('title') }}" required placeholder="Masalan: Atom Odatlar"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    @error('title') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">Muallif *</label>
                    <input type="text" name="author" value="{{ old('author') }}" required placeholder="Masalan: James Clear"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    @error('author') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">Janr *</label>
                    <input type="text" name="genre" value="{{ old('genre', 'Shaxsiy rivojlanish') }}" required placeholder="Biznes, Psixologiya..."
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    @error('genre') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">Hafta raqami</label>
                    <input type="number" name="week_number" value="{{ old('week_number', (\App\Models\Book::max('week_number') ?? 0) + 1) }}"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">Qisqacha tavsif *</label>
                <textarea name="description" rows="4" required placeholder="Kitobning asosiy mazmuni va o'quvchiga beradigan foydasi haqida..."
                          class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">{{ old('description') }}</textarea>
                @error('description') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Media yuklash: Muqova rasmi va 200MB PDF --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2">
                {{-- 1. Kitob muqovasi (Rasm) --}}
                <div x-data="{
                    previewUrl: null,
                    handleFile(e) {
                        const file = e.target.files[0];
                        if (file) {
                            this.previewUrl = URL.createObjectURL(file);
                        } else {
                            this.previewUrl = null;
                        }
                    }
                }" class="space-y-2">
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200">
                        🖼️ Kitob muqovasi (Rasm)
                    </label>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Katalog va kartalarda ko'rinadigan rasm. JPG, PNG, WEBP (maks. 15 MB)
                    </p>

                    <label for="cover_image" class="group relative flex flex-col items-center justify-center w-full min-h-[170px] p-4 rounded-2xl border-2 border-dashed border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-900/40 hover:border-amber-500 hover:bg-amber-500/5 transition-all cursor-pointer overflow-hidden">
                        <template x-if="!previewUrl">
                            <div class="flex flex-col items-center justify-center text-center">
                                <span class="text-3xl mb-2 group-hover:scale-110 transition-transform">🎨</span>
                                <span class="text-xs font-bold text-slate-700 dark:text-slate-200 group-hover:text-amber-500 transition-colors">
                                    Muqova rasmini tanlang
                                </span>
                                <span class="text-[11px] text-slate-400 mt-1">yoki faylni sudrab keling</span>
                            </div>
                        </template>

                        <template x-if="previewUrl">
                            <div class="flex items-center gap-4 w-full">
                                <img :src="previewUrl" alt="Muqova preview" class="w-20 h-28 object-cover rounded-xl shadow-md border border-white/20 shrink-0">
                                <div class="min-w-0">
                                    <span class="inline-block px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-400 text-[10px] font-bold uppercase mb-1">Rasm tanlandi</span>
                                    <p class="text-xs font-semibold text-slate-800 dark:text-white truncate">Almashtirish uchun bosing</p>
                                    <span class="text-[11px] text-slate-400">Yangi rasm tanlashingiz mumkin</span>
                                </div>
                            </div>
                        </template>

                        <input type="file" name="cover_image" id="cover_image" accept="image/png,image/jpeg,image/webp,image/jpg" class="hidden" @change="handleFile($event)">
                    </label>
                    @error('cover_image') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- 2. Kitob PDF fayli (200MB gacha) --}}
                <div x-data="{
                    fileName: '',
                    fileSize: '',
                    handlePdf(e) {
                        const file = e.target.files[0];
                        if (file) {
                            this.fileName = file.name;
                            const mb = (file.size / (1024 * 1024)).toFixed(2);
                            this.fileSize = mb + ' MB';
                        } else {
                            this.fileName = '';
                            this.fileSize = '';
                        }
                    }
                }" class="space-y-2">
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200">
                        📕 Kitob PDF fayli (200 MB limit)
                    </label>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        To'liq kitob elektron varianti. Faqat .pdf (maks. 200 MB)
                    </p>

                    <label for="pdf_file" class="group relative flex flex-col items-center justify-center w-full min-h-[170px] p-4 rounded-2xl border-2 border-dashed border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-900/40 hover:border-indigo-500 hover:bg-indigo-500/5 transition-all cursor-pointer overflow-hidden">
                        <template x-if="!fileName">
                            <div class="flex flex-col items-center justify-center text-center">
                                <span class="text-3xl mb-2 group-hover:scale-110 transition-transform">📄</span>
                                <span class="text-xs font-bold text-slate-700 dark:text-slate-200 group-hover:text-indigo-500 transition-colors">
                                    PDF faylni tanlang
                                </span>
                                <span class="text-[11px] text-amber-500 font-semibold mt-1">200 MB gacha ruxsat berilgan</span>
                            </div>
                        </template>

                        <template x-if="fileName">
                            <div class="flex items-center gap-3 w-full p-2 bg-emerald-500/10 border border-emerald-500/25 rounded-xl">
                                <span class="text-2xl shrink-0">✅</span>
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs font-bold text-slate-800 dark:text-white truncate" x-text="fileName"></p>
                                    <p class="text-[11px] text-emerald-500 font-semibold" x-text="'Hajmi: ' + fileSize"></p>
                                </div>
                            </div>
                        </template>

                        <input type="file" name="pdf_file" id="pdf_file" accept=".pdf,application/pdf" class="hidden" @change="handlePdf($event)">
                    </label>
                    @error('pdf_file') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <input type="checkbox" name="is_active" id="is_active" value="1" checked class="w-5 h-5 text-indigo-600 rounded focus:ring-indigo-500 border-slate-300">
                <label for="is_active" class="text-sm font-medium text-slate-700 dark:text-slate-300">Kitob darhol faollashtirilsin (Katalogda ko'rinadi)</label>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-700">
                <a href="{{ route('admin.books.index') }}" class="px-5 py-2.5 rounded-xl text-slate-600 dark:text-slate-400 font-semibold text-sm hover:bg-slate-100 dark:hover:bg-slate-700">Bekor qilish</a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 text-white font-bold text-sm hover:bg-indigo-500 transition-colors shadow-lg shadow-indigo-600/30">Saqlash</button>
            </div>
        </form>
    </div>
</div>
@endsection
