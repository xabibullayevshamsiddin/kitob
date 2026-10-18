@extends('admin.layouts.app')
@section('title', 'Kitobni tahrirlash')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-black text-slate-800 dark:text-white flex items-center gap-2">
                <span>✏️</span> Kitobni tahrirlash: {{ $book->title }}
            </h2>
            <p class="text-sm text-slate-500">Kitob ma'lumotlarini o'zgartirish, yangi muqova yoki PDF yuklash</p>
        </div>
        <a href="{{ route('admin.books.index') }}" class="px-4 py-2 bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl text-sm font-semibold hover:bg-slate-300 dark:hover:bg-slate-600 transition-colors">
            ← Orqaga
        </a>
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 sm:p-8 shadow-sm">
        <form action="{{ route('admin.books.update', $book->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">Kitob nomi *</label>
                    <input type="text" name="title" value="{{ old('title', $book->title) }}" required
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    @error('title') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">Muallif *</label>
                    <input type="text" name="author" value="{{ old('author', $book->author) }}" required
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    @error('author') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">Janr *</label>
                    <input type="text" name="genre" value="{{ old('genre', $book->genre) }}" required
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    @error('genre') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">Hafta raqami</label>
                    <input type="number" name="week_number" value="{{ old('week_number', $book->week_number) }}"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">Qisqacha tavsif *</label>
                <textarea name="description" rows="4" required
                          class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">{{ old('description', $book->description) }}</textarea>
                @error('description') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Media: Muqova rasmi va 200MB PDF fayl --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2">
                {{-- 1. Muqova rasmi --}}
                <div x-data="{
                    previewUrl: '{{ $book->cover_image ? asset('storage/' . $book->cover_image) : '' }}',
                    handleFile(e) {
                        const file = e.target.files[0];
                        if (file) {
                            this.previewUrl = URL.createObjectURL(file);
                        }
                    }
                }" class="space-y-2">
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200">
                        🖼️ Kitob muqovasi (Rasm)
                    </label>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Yangi rasm tanlasangiz, eski muqova almashtiriladi. Tavsiya etilgan o'lcham: <strong class="text-amber-500">2:3 nisbat (masalan, 800×1200 px)</strong> (maks. 15 MB)
                    </p>

                    <label for="cover_image" class="group relative flex flex-col items-center justify-center w-full min-h-[170px] p-4 rounded-2xl border-2 border-dashed border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-900/40 hover:border-amber-500 hover:bg-amber-500/5 transition-all cursor-pointer overflow-hidden">
                        <template x-if="!previewUrl">
                            <div class="flex flex-col items-center justify-center text-center">
                                <span class="text-3xl mb-2 group-hover:scale-110 transition-transform">🎨</span>
                                <span class="text-xs font-bold text-slate-700 dark:text-slate-200 group-hover:text-amber-500 transition-colors">
                                    Yangi muqova rasmini tanlang
                                </span>
                                <span class="text-[11px] text-slate-400 mt-1">2:3 nisbatda (800×1200 px) JPG, PNG, WEBP</span>
                            </div>
                        </template>

                        <template x-if="previewUrl">
                            <div class="flex items-center gap-4 w-full">
                                <img :src="previewUrl" alt="Muqova" class="w-20 aspect-[2/3] object-cover rounded-xl shadow-md border border-white/20 shrink-0">
                                <div class="min-w-0">
                                    <span class="inline-block px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-400 text-[10px] font-bold uppercase mb-1">Mavjud muqova</span>
                                    <p class="text-xs font-semibold text-slate-800 dark:text-white truncate">Almashtirish uchun bosing</p>
                                    <span class="text-[11px] text-slate-400">Yangi rasm yuklash ixtiyoriy</span>
                                </div>
                            </div>
                        </template>

                        <input type="file" name="cover_image" id="cover_image" accept="image/png,image/jpeg,image/webp,image/jpg" class="hidden" @change="handleFile($event)">
                    </label>
                    @error('cover_image') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- 2. PDF fayl (200MB gacha) --}}
                <div x-data="{
                    hasExisting: {{ $book->pdf_path ? 'true' : 'false' }},
                    fileName: '',
                    fileSize: '',
                    handlePdf(e) {
                        const file = e.target.files[0];
                        if (file) {
                            this.fileName = file.name;
                            const mb = (file.size / (1024 * 1024)).toFixed(2);
                            this.fileSize = mb + ' MB';
                        }
                    }
                }" class="space-y-2">
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200">
                        📕 Kitob PDF fayli (200 MB limit)
                    </label>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        @if($book->pdf_path)
                            <span class="text-emerald-500 font-semibold">✓ PDF allaqachon yuklangan.</span> Yangi fayl tanlasangiz, u yangilanadi.
                        @else
                            Hozircha PDF yuklanmagan. Yangi PDF yuklashingiz mumkin (maks. 200 MB).
                        @endif
                    </p>

                    <label for="pdf_file" class="group relative flex flex-col items-center justify-center w-full min-h-[170px] p-4 rounded-2xl border-2 border-dashed border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-900/40 hover:border-indigo-500 hover:bg-indigo-500/5 transition-all cursor-pointer overflow-hidden">
                        <template x-if="!fileName">
                            <div class="flex flex-col items-center justify-center text-center">
                                <span class="text-3xl mb-2 group-hover:scale-110 transition-transform">📄</span>
                                <span class="text-xs font-bold text-slate-700 dark:text-slate-200 group-hover:text-indigo-500 transition-colors">
                                    {{ $book->pdf_path ? 'PDF faylni yangilash uchun bosing' : 'PDF faylni tanlang' }}
                                </span>
                                <span class="text-[11px] text-amber-500 font-semibold mt-1">200 MB gacha ruxsat berilgan</span>
                            </div>
                        </template>

                        <template x-if="fileName">
                            <div class="flex items-center gap-3 w-full p-2 bg-emerald-500/10 border border-emerald-500/25 rounded-xl">
                                <span class="text-2xl shrink-0">✅</span>
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs font-bold text-slate-800 dark:text-white truncate" x-text="fileName"></p>
                                    <p class="text-[11px] text-emerald-500 font-semibold" x-text="'Yangi fayl hajmi: ' + fileSize"></p>
                                </div>
                            </div>
                        </template>

                        <input type="file" name="pdf_file" id="pdf_file" accept=".pdf,application/pdf" class="hidden" @change="handlePdf($event)">
                    </label>
                    @error('pdf_file') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <input type="checkbox" name="is_active" id="is_active" value="1" {{ $book->is_active ? 'checked' : '' }} class="w-5 h-5 text-indigo-600 rounded focus:ring-indigo-500 border-slate-300">
                <label for="is_active" class="text-sm font-medium text-slate-700 dark:text-slate-300">Kitob faol holatda (Katalogda ko'rinadi)</label>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-700">
                <a href="{{ route('admin.books.index') }}" class="px-5 py-2.5 rounded-xl text-slate-600 dark:text-slate-400 font-semibold text-sm hover:bg-slate-100 dark:hover:bg-slate-700">Bekor qilish</a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 text-white font-bold text-sm hover:bg-indigo-500 transition-colors shadow-lg shadow-indigo-600/30">Yangilash</button>
            </div>
        </form>
    </div>

    {{-- Kitob AI Yordamchi (RAG Indekslash va Boshqaruv) bloki --}}
    @php
        $aiStatus = $book->ai_index_status ?? 'none';
        $aiChunksCount = $book->chunks()->count();
        $aiPagesCount = $book->pages()->count();
        $aiQuality = $book->ai_text_quality !== null ? round((float) $book->ai_text_quality * 100, 1) : null;
        $statusMap = [
            'ready'    => ['label' => 'Tayyor', 'class' => 'bg-emerald-500/15 text-emerald-500 border-emerald-500/30'],
            'partial'  => ['label' => 'Qisman (faqat konspekt)', 'class' => 'bg-amber-500/15 text-amber-500 border-amber-500/30'],
            'stale'    => ['label' => 'Eskirgan (qayta indekslang)', 'class' => 'bg-amber-500/15 text-amber-500 border-amber-500/30'],
            'indexing' => ['label' => 'Indekslanmoqda...', 'class' => 'bg-sky-500/15 text-sky-400 border-sky-500/30'],
            'failed'   => ['label' => 'Xato (Matn sifati past)', 'class' => 'bg-rose-500/15 text-rose-500 border-rose-500/30'],
            'none'     => ['label' => 'Indekslanmagan', 'class' => 'bg-slate-500/15 text-slate-400 border-slate-500/30'],
        ];
        $currentStatusBadge = $statusMap[$aiStatus] ?? $statusMap['none'];
    @endphp
    <div id="ai-assistant-panel" x-data="{ indexing: false }" class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 sm:p-8 shadow-sm space-y-5 scroll-mt-20">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-200 dark:border-slate-700 pb-4">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-500 flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M12 3l1.912 5.813a2 2 0 001.275 1.275L21 12l-5.813 1.912a2 2 0 00-1.275 1.275L12 21l-1.912-5.813a2 2 0 00-1.275-1.275L3 12l5.813-1.912a2 2 0 001.275-1.275L12 3z"/>
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <h3 class="text-lg font-bold text-slate-800 dark:text-white">
                            AI yordamchi (Kitob ichidagi RAG chat)
                        </h3>
                        <span class="px-2.5 py-0.5 rounded-full border text-xs font-mono font-bold {{ $currentStatusBadge['class'] }}">
                            {{ $currentStatusBadge['label'] }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        PDF betlari va boblarni bo'laklarga ajratib, kitob sahifasida faqat shu kitob bo'yicha javob beradigan AI yordamchini boshqarish
                    </p>
                </div>
            </div>

            {{-- Indekslash va O'chirish formalari --}}
            <div class="flex items-center gap-2 flex-wrap shrink-0">
                <form action="{{ route('admin.books.ai.index', $book->id) }}" method="POST" @submit="indexing = true; $el.querySelector('[name=ai_summary]').value = document.getElementById('ai_summary')?.value || ''">
                    @csrf
                    <input type="hidden" name="force" value="1">
                    <input type="hidden" name="ai_summary" value="">
                    <button type="submit"
                            :disabled="indexing"
                            class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 disabled:opacity-60 text-white text-xs font-bold inline-flex items-center gap-2 transition-colors shadow-md shadow-emerald-600/20">
                        <svg x-show="!indexing" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <polyline points="23 4 23 10 17 10"/>
                            <polyline points="1 20 1 14 7 14"/>
                            <path d="M3.51 9a9 9 0 0114.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0020.49 15"/>
                        </svg>
                        <svg x-show="indexing" x-cloak class="w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <circle cx="12" cy="12" r="10" stroke-opacity="0.25"/>
                            <path d="M12 2a10 10 0 0110 10"/>
                        </svg>
                        <span x-text="indexing ? 'Indekslanmoqda (kutib turing)...' : '{{ $aiStatus === 'ready' ? 'Qayta indekslash' : 'Hozir indekslash' }}'">
                            {{ $aiStatus === 'ready' ? 'Qayta indekslash' : 'Hozir indekslash' }}
                        </span>
                    </button>
                </form>

                @if($aiStatus !== 'none' || $aiChunksCount > 0)
                    <form action="{{ route('admin.books.ai.clear', $book->id) }}" method="POST" onsubmit="return confirm('Haqiqatan ham «{{ addslashes($book->title) }}» kitobining AI indeksini va barcha bo\'laklarini o\'chirmoqchimisiz? AI yordamchi o\'chiriladi.')">
                        @csrf
                        <button type="submit"
                                :disabled="indexing"
                                class="px-3.5 py-2.5 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-500 border border-rose-500/30 text-xs font-bold inline-flex items-center gap-1.5 transition-colors"
                                title="Faqat shu kitobning AI bo'laklari va indeksini tozalash">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <polyline points="3 6 5 6 21 6"/>
                                <path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/>
                            </svg>
                            <span>Indeksni o'chirish</span>
                        </button>
                    </form>
                @endif
            </div>
        </div>

        {{-- Ko'rsatkichlar kartalari --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div class="p-3.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50">
                <span class="block text-[11px] font-mono text-slate-400" title="PDF yoki OCR orqali o'qilgan matnning tozalik darajasi (100% = toza raqamli matn)">Matn sifati (aniqligi)</span>
                <span class="text-base font-bold text-slate-800 dark:text-white font-mono">
                    {{ $aiQuality !== null ? $aiQuality . '%' : '—' }}
                </span>
            </div>
            <div class="p-3.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50">
                <span class="block text-[11px] font-mono text-slate-400">Ajratilgan betlar</span>
                <span class="text-base font-bold text-slate-800 dark:text-white font-mono">
                    {{ $aiPagesCount }} ta
                </span>
            </div>
            <div class="p-3.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50">
                <span class="block text-[11px] font-mono text-slate-400">Matn bo'laklari (chunks)</span>
                <span class="text-base font-bold text-slate-800 dark:text-white font-mono">
                    {{ $aiChunksCount }} ta
                </span>
            </div>
            <div class="p-3.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50">
                <span class="block text-[11px] font-mono text-slate-400">So'nggi indekslash</span>
                <span class="text-xs font-bold text-slate-800 dark:text-white font-mono">
                    {{ $book->ai_indexed_at ? $book->ai_indexed_at->timezone('Asia/Tashkent')->format('d.m.Y H:i') : 'Hali indekslanmagan' }}
                </span>
            </div>
        </div>

        {{-- Xato yoki ogohlantirish xabari --}}
        @if($aiStatus === 'failed' || !empty($book->ai_index_error))
            @php
                $isAmberBox = ($aiStatus === 'partial' || $aiStatus === 'ready');
                $boxClasses = $isAmberBox
                    ? 'bg-amber-500/10 border-amber-500/30 text-amber-400'
                    : 'bg-rose-500/10 border-rose-500/30 text-rose-400';
                $boxSubClasses = $isAmberBox
                    ? 'text-amber-300/90'
                    : 'text-rose-300/80';
            @endphp
            <div class="p-4 rounded-xl {{ $boxClasses }} border text-xs flex items-start gap-2.5">
                <svg class="w-4 h-4 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    @if($isAmberBox)
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="16" x2="12" y2="12"/>
                        <line x1="12" y1="8" x2="12.01" y2="8"/>
                    @else
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="8" x2="12" y2="12"/>
                        <line x1="12" y1="16" x2="12.01" y2="16"/>
                    @endif
                </svg>
                <div class="space-y-1">
                    <p class="font-bold">{{ $book->ai_index_error ?: __('ai.low_text_quality') }}</p>
                    @if(str_contains((string) $book->ai_index_error, 'Embedding xizmati'))
                        <p class="text-[11px] {{ $boxSubClasses }} mt-0.5">
                            Matnli qidiruv ishlayapti; vektorli qidiruv embedding xizmati tiklangach qayta indekslashda tiklanadi.
                        </p>
                    @elseif($aiStatus === 'failed' || str_contains((string) $book->ai_index_error, 'PDF matnidan') || str_contains((string) $book->ai_index_error, 'skaner'))
                        <p class="text-[11px] {{ $boxSubClasses }} mt-1 leading-relaxed">
                            <strong>Sabab:</strong> Ushbu PDF bosma sahifalar nusxasi (skaner) bo'lib, ichida raqamli matn qatlami bo'lmagan. Tizim RapidOCR orqali matnni o'qishga harakat qiladi yoki konspekt orqali ishlaydi.
                        </p>
                        <p class="text-[11px] {{ $boxSubClasses }} mt-0.5 leading-relaxed">
                            <strong>Tavsiya:</strong> Kitobni qayta indekslash («Qayta indekslash» tugmasi) bosing, yoki pastdagi «Kitobning AI uchun qisqa mazmuni» maydoniga konspekt yozib saqlang.
                        </p>
                    @endif
                </div>
            </div>
        @endif

        {{-- Yoqish/o'chirish va Qisqa mazmun (ai_summary) formasi --}}
        <form action="{{ route('admin.books.ai.settings', $book->id) }}" method="POST" class="space-y-4 pt-2 border-t border-slate-200 dark:border-slate-700">
            @csrf

            <div class="flex items-center gap-3">
                <input type="checkbox"
                       name="ai_enabled"
                       id="ai_enabled"
                       value="1"
                       {{ $book->ai_enabled ? 'checked' : '' }}
                       class="w-5 h-5 text-emerald-600 rounded focus:ring-emerald-500 border-slate-300">
                <label for="ai_enabled" class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                    Ushbu kitob sahifasida «AI yordamchi»ni yoqish (faqat holati «Tayyor» yoki «Qisman» bo'lganda kitobxonga ko'rinadi)
                </label>
            </div>

            <div>
                <label for="ai_summary" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                    Kitobning AI uchun qisqa mazmuni (ixtiyoriy — "Bu kitob nima haqida?" savollarida kontekst sifatida yordam beradi)
                </label>
                <textarea name="ai_summary"
                          id="ai_summary"
                          rows="3"
                          placeholder="Indekslash paytida avtomatik to'ldiriladi yoki o'zingiz tahrirlashingiz mumkin..."
                          class="w-full px-3.5 py-2 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">{{ old('ai_summary', $book->ai_summary) }}</textarea>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="px-5 py-2 rounded-xl bg-slate-800 dark:bg-slate-700 hover:bg-slate-700 dark:hover:bg-slate-600 text-white text-xs font-bold transition-colors">
                    AI sozlamalarini saqlash
                </button>
            </div>
        </form>
    </div>

    {{-- Fon Musiqalari (Ambient Audio) bloki --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 sm:p-8 shadow-sm space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-200 dark:border-slate-700 pb-4">
            <div>
                <h3 class="text-lg font-bold text-slate-800 dark:text-white flex items-center gap-2">
                    <span>🎵</span>
                    <span>Kitob fon musiqalari (Ambient Audio)</span>
                    <span class="px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-500 text-xs font-mono font-bold">{{ $book->musics()->count() }} ta</span>
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                    O'quvchilar ushbu kitobni o'qiyotganda tinglashi mumkin bo'lgan fon musiqalari va ohanglari
                </p>
            </div>
            <a href="{{ route('admin.books.music.index', $book->id) }}" class="px-4 py-2 bg-amber-500 text-slate-900 rounded-xl text-xs font-bold hover:bg-amber-400 transition-colors inline-flex items-center gap-1.5 shadow-md shadow-amber-500/20">
                <span>+ Musiqalarni boshqarish / qo'shish</span>
                <span>→</span>
            </a>
        </div>

        @if($book->musics()->exists())
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-2">
                @foreach($book->musics as $music)
                    <div class="p-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-xs font-bold text-slate-800 dark:text-white truncate flex items-center gap-1.5">
                                <span>{{ $music->is_active ? '🟢' : '⚪️' }}</span>
                                <span>{{ $music->title }}</span>
                            </p>
                            <p class="text-[10px] text-slate-400 font-mono mt-0.5 truncate">{{ basename($music->file_path) }}</p>
                        </div>
                        <audio controls preload="none" class="h-7 w-28 shrink-0">
                            <source src="{{ $music->file_url }}">
                        </audio>
                    </div>
                @endforeach
            </div>
        @else
            <div class="py-6 text-center text-xs text-slate-400">
                <p>Ushbu kitobga hali fon musiqasi qo'shilmagan.</p>
                <a href="{{ route('admin.books.music.index', $book->id) }}" class="text-amber-500 font-semibold underline mt-1 inline-block">
                    Hozir qo'shish →
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
