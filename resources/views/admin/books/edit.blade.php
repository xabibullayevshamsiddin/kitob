@extends('admin.layouts.app')
@section('title', 'Kitobni tahrirlash — ' . $book->title)
@section('breadcrumb', 'Kitobni tahrirlash')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    {{-- Top Header / Breadcrumb Bar --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-ink-border">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.books.index') }}"
               class="p-2 rounded-btn bg-ink-900 border border-ink-border text-mist hover:text-paper hover:border-amber-400/40 transition-colors shrink-0"
               title="Kitoblar ro'yxatiga qaytish">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <div class="flex items-center gap-2.5 flex-wrap">
                    <h1 class="text-xl sm:text-2xl font-bold font-serif text-paper">
                        {{ $book->title }}
                    </h1>
                    @if($book->is_active)
                        <span class="px-2 py-0.5 rounded-badge bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 text-[10px] font-mono font-bold flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                            Faol
                        </span>
                    @else
                        <span class="px-2 py-0.5 rounded-badge bg-rose-500/15 border border-rose-500/30 text-rose-400 text-[10px] font-mono font-bold flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span>
                            Nofaol
                        </span>
                    @endif
                    <span class="px-2 py-0.5 rounded-badge bg-ink-900 border border-ink-border text-mist text-[10px] font-mono">
                        {{ $book->week_number }}-hafta
                    </span>
                    <span class="px-2 py-0.5 rounded-badge bg-amber-500/15 border border-amber-500/30 text-amber-400 text-[10px] font-mono font-semibold">
                        {{ $book->genre }}
                    </span>
                </div>
                <p class="text-xs text-mist font-mono mt-1">Kitob ma'lumotlarini o'zgartirish, muqova va PDF yuklash, AI yordamchini boshqarish</p>
            </div>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <a href="{{ route('admin.books.index') }}" class="ks-btn-ghost py-1.5 px-3.5 text-xs font-mono">
                ← Barcha kitoblar
            </a>
            <a href="{{ route('books.show', $book->slug) }}" target="_blank" class="ks-btn-primary py-1.5 px-3.5 text-xs font-mono inline-flex items-center gap-1.5 shadow-sm">
                <span>Kitobni ko'rish</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                </svg>
            </a>
        </div>
    </div>

    {{-- Flash Notifications --}}
    @if(session('success'))
        <div class="p-4 rounded-panel bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-xs flex items-center gap-2.5 animate-fadeIn">
            <span class="text-base">✅</span>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    @endif
    @if(session('warning'))
        <div class="p-4 rounded-panel bg-amber-500/10 border border-amber-500/30 text-amber-300 text-xs flex items-center gap-2.5 animate-fadeIn">
            <span class="text-base">⚠️</span>
            <span class="font-medium">{{ session('warning') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="p-4 rounded-panel bg-rose-500/10 border border-rose-500/30 text-rose-300 text-xs flex items-center gap-2.5 animate-fadeIn">
            <span class="text-base">⚠️</span>
            <span class="font-medium">{{ session('error') }}</span>
        </div>
    @endif

    {{-- 1. Asosiy Kitob Tahrirlash Formasi --}}
    <div class="bg-ink-900 border border-ink-border rounded-panel p-5 sm:p-7 shadow-lg space-y-6">
        <div class="border-b border-ink-border pb-4">
            <span class="text-[10px] font-mono text-amber-500 uppercase tracking-widest block font-bold">1-QADAM</span>
            <h2 class="text-base font-bold font-serif text-paper">Asosiy parametrlar va fayllar</h2>
            <p class="text-xs text-mist font-mono mt-0.5">Kitob nomi, muallifi, janri, matni va yuklanadigan elektron fayllar</p>
        </div>

        <form action="{{ route('admin.books.update', $book->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                {{-- Kitob nomi --}}
                <div class="sm:col-span-2 space-y-1.5">
                    <label class="block text-xs font-mono text-mist uppercase tracking-wider font-semibold">
                        Kitob nomi <span class="text-rose-400">*</span>
                    </label>
                    <input type="text" name="title" value="{{ old('title', $book->title) }}" required
                           placeholder="Masalan: Atom Odatlar"
                           class="w-full ks-input px-3.5 py-2.5 text-xs text-paper placeholder-mist/60 focus:border-amber-400">
                    @error('title') <p class="text-rose-400 text-[11px] font-mono mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Muallif --}}
                <div class="space-y-1.5">
                    <label class="block text-xs font-mono text-mist uppercase tracking-wider font-semibold">
                        Muallif <span class="text-rose-400">*</span>
                    </label>
                    <input type="text" name="author" value="{{ old('author', $book->author) }}" required
                           placeholder="Masalan: James Clear"
                           class="w-full ks-input px-3.5 py-2.5 text-xs text-paper placeholder-mist/60 focus:border-amber-400">
                    @error('author') <p class="text-rose-400 text-[11px] font-mono mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Hafta raqami --}}
                <div class="space-y-1.5">
                    <label class="block text-xs font-mono text-mist uppercase tracking-wider font-semibold">
                        Hafta raqami
                    </label>
                    <input type="number" name="week_number" value="{{ old('week_number', $book->week_number) }}" min="1"
                           class="w-full ks-input px-3.5 py-2.5 text-xs font-mono text-paper placeholder-mist/60 focus:border-amber-400">
                    @error('week_number') <p class="text-rose-400 text-[11px] font-mono mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Janr --}}
                <div class="sm:col-span-2 space-y-1.5">
                    <label class="block text-xs font-mono text-mist uppercase tracking-wider font-semibold">
                        Janr / Toifa <span class="text-rose-400">*</span>
                    </label>
                    <input type="text" name="genre" value="{{ old('genre', $book->genre) }}" required
                           placeholder="Masalan: Shaxsiy rivojlanish / Psixologiya"
                           class="w-full ks-input px-3.5 py-2.5 text-xs text-paper placeholder-mist/60 focus:border-amber-400">
                    @error('genre') <p class="text-rose-400 text-[11px] font-mono mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Slug ko'rinishi (read-only) --}}
                <div class="sm:col-span-2 space-y-1.5">
                    <label class="block text-xs font-mono text-mist uppercase tracking-wider font-semibold">
                        Sayt URL sarlavhasi (Slug)
                    </label>
                    <div class="w-full px-3.5 py-2.5 rounded-btn bg-ink-950 border border-ink-border text-xs font-mono text-mist truncate">
                        {{ url('/books/' . $book->slug) }}
                    </div>
                </div>
            </div>

            {{-- Tavsif --}}
            <div class="space-y-1.5">
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-mono text-mist uppercase tracking-wider font-semibold">
                        Qisqacha tavsif <span class="text-rose-400">*</span>
                    </label>
                    <span class="text-[10px] text-mist/70 font-mono">Kitobxonlarga kitob haqida tushuncha beradi</span>
                </div>
                <textarea name="description" rows="4" required
                          placeholder="Kitobning asosiy g'oyasi, mundarijasi va o'quvchiga beradigan bilimlari..."
                          class="w-full ks-input px-3.5 py-2.5 text-xs text-paper placeholder-mist/60 focus:border-amber-400 leading-relaxed">{{ old('description', $book->description) }}</textarea>
                @error('description') <p class="text-rose-400 text-[11px] font-mono mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Media yuklash: Muqova rasmi va 200MB PDF fayl --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 pt-2">

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
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-mono text-mist uppercase tracking-wider font-semibold">
                            🖼️ Kitob muqovasi (Rasm)
                        </label>
                        <span class="text-[10px] text-amber-400 font-mono">2:3 nisbat (800×1200)</span>
                    </div>

                    <label for="cover_image"
                           class="group relative flex flex-col items-center justify-center w-full min-h-[180px] p-4 rounded-card border-2 border-dashed border-ink-border bg-ink-950/70 hover:border-amber-500/50 hover:bg-amber-500/[0.02] transition-all cursor-pointer overflow-hidden">
                        <template x-if="!previewUrl">
                            <div class="flex flex-col items-center justify-center text-center p-3">
                                <span class="text-3xl mb-2 group-hover:scale-110 transition-transform">🎨</span>
                                <span class="text-xs font-bold text-paper group-hover:text-amber-400 transition-colors">
                                    Yangi muqova rasmini tanlang
                                </span>
                                <span class="text-[11px] text-mist/80 font-mono mt-1">2:3 nisbatda (JPG, PNG, WEBP, maks 15 MB)</span>
                            </div>
                        </template>

                        <template x-if="previewUrl">
                            <div class="flex items-center gap-4 w-full">
                                <img :src="previewUrl" alt="Muqova preview" class="w-20 aspect-[2/3] object-cover rounded-btn shadow-lg border border-amber-500/30 shrink-0">
                                <div class="min-w-0 flex-1 space-y-1">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 text-[10px] font-mono font-bold uppercase">
                                        ✓ Muqova mavjud
                                    </span>
                                    <p class="text-xs font-bold text-paper truncate group-hover:text-amber-400 transition-colors">
                                        Almashtirish uchun bosing
                                    </p>
                                    <p class="text-[11px] text-mist font-mono">Yangi rasm tanlansa, eskisi avtomatik almashtiriladi</p>
                                </div>
                            </div>
                        </template>

                        <input type="file" name="cover_image" id="cover_image" accept="image/png,image/jpeg,image/webp,image/jpg" class="hidden" @change="handleFile($event)">
                    </label>
                    @error('cover_image') <p class="text-rose-400 text-[11px] font-mono mt-1">{{ $message }}</p> @enderror
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
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-mono text-mist uppercase tracking-wider font-semibold">
                            📕 Kitob PDF fayli
                        </label>
                        <span class="text-[10px] text-amber-400 font-mono">200 MB gacha limit</span>
                    </div>

                    <label for="pdf_file"
                           class="group relative flex flex-col items-center justify-center w-full min-h-[180px] p-4 rounded-card border-2 border-dashed border-ink-border bg-ink-950/70 hover:border-amber-500/50 hover:bg-amber-500/[0.02] transition-all cursor-pointer overflow-hidden">
                        <template x-if="!fileName">
                            <div class="flex flex-col items-center justify-center text-center p-3">
                                <span class="text-3xl mb-2 group-hover:scale-110 transition-transform">📄</span>
                                <span class="text-xs font-bold text-paper group-hover:text-amber-400 transition-colors">
                                    {{ $book->pdf_path ? 'PDF faylni yangilash uchun bosing' : 'PDF faylni tanlang' }}
                                </span>
                                @if($book->pdf_path)
                                    <div class="flex items-center gap-1.5 mt-1.5">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 text-[10px] font-mono font-bold">
                                            ✓ PDF yuklangan
                                        </span>
                                        <a href="{{ route('books.pdf', $book) }}" target="_blank" onclick="event.stopPropagation();" class="text-[11px] font-mono text-amber-400 underline hover:text-amber-300">
                                            Ko'rish / Yuklab olish
                                        </a>
                                    </div>
                                @else
                                    <span class="text-[11px] text-mist/80 font-mono mt-1">Faqat .pdf formatdagi fayl</span>
                                @endif
                            </div>
                        </template>

                        <template x-if="fileName">
                            <div class="flex items-center gap-3 w-full p-3 bg-emerald-500/10 border border-emerald-500/30 rounded-btn">
                                <span class="text-2xl shrink-0">✅</span>
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs font-bold text-paper truncate" x-text="fileName"></p>
                                    <p class="text-[11px] text-emerald-400 font-mono" x-text="'Yangi fayl hajmi: ' + fileSize"></p>
                                </div>
                            </div>
                        </template>

                        <input type="file" name="pdf_file" id="pdf_file" accept=".pdf,application/pdf" class="hidden" @change="handlePdf($event)">
                    </label>
                    @error('pdf_file') <p class="text-rose-400 text-[11px] font-mono mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Faollik holati (Checkbox) --}}
            <div class="p-3.5 rounded-card bg-ink-950 border border-ink-border flex items-center justify-between gap-4">
                <div class="space-y-0.5">
                    <label for="is_active" class="text-xs font-bold text-paper cursor-pointer block">
                        Kitob faol holatda
                    </label>
                    <p class="text-[11px] text-mist font-mono">
                        Yoqilgan bo'lsa, kitob umumiy katalogda, qidiruvda va foydalanuvchilar ekranida ko'rinadi.
                    </p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer shrink-0">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ $book->is_active ? 'checked' : '' }} class="sr-only peer">
                    <div class="w-11 h-6 bg-ink-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-ink-border after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-amber-500"></div>
                </label>
            </div>

            {{-- Tugmalar qatori --}}
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-3 border-t border-ink-border">
                <div class="text-[11px] text-mist font-mono">
                    Yaratilgan: <span class="text-paper">{{ $book->created_at ? $book->created_at->format('d.m.Y H:i') : '—' }}</span>
                </div>

                <div class="flex items-center gap-2.5 w-full sm:w-auto justify-end">
                    <a href="{{ route('admin.books.index') }}" class="ks-btn-ghost py-2 px-4 text-xs font-mono">
                        Bekor qilish
                    </a>
                    <button type="submit" class="ks-btn-primary py-2 px-6 text-xs font-mono font-bold inline-flex items-center gap-1.5 shadow-md">
                        <span>O'zgarishlarni saqlash</span>
                        <span>→</span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- Telegram Broadcast Panel (Optional Broadcast) --}}
    @if($book->is_active)
        <div class="bg-ink-900 border border-ink-border rounded-panel p-5 sm:p-6 shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-btn bg-[#229ED9]/15 border border-[#229ED9]/30 text-[#229ED9] flex items-center justify-center shrink-0 text-lg">
                    📢
                </div>
                <div>
                    <h3 class="text-sm font-bold font-serif text-paper">Telegramga e'lon yuborish</h3>
                    <p class="text-xs text-mist font-mono mt-0.5">Ushbu kitob haqida Telegram kanal va bot foydalanuvchilariga qayta xabar yuborish</p>
                </div>
            </div>
            <form action="{{ route('admin.books.telegram-broadcast', $book->id) }}" method="POST">
                @csrf
                <button type="submit"
                        onclick="return confirm('«{{ addslashes($book->title) }}» kitobi haqida Telegramga yangi e\'lon yuborilsinmi?')"
                        class="px-4 py-2 rounded-btn bg-[#229ED9]/20 hover:bg-[#229ED9]/30 text-[#229ED9] border border-[#229ED9]/40 text-xs font-mono font-bold inline-flex items-center gap-1.5 transition-colors">
                    <span>🚀 Telegramga tarqatish</span>
                </button>
            </form>
        </div>
    @endif

    {{-- 2. Kitob AI Yordamchi (RAG Indekslash va Boshqaruv) bloki --}}
    @php
        $aiStatus = $book->ai_index_status ?? 'none';
        $aiChunksCount = $book->chunks()->count();
        $aiPagesCount = $book->pages()->count();
        $aiQuality = $book->ai_text_quality !== null ? round((float) $book->ai_text_quality * 100, 1) : null;
        $statusMap = [
            'ready'    => ['label' => 'Tayyor', 'class' => 'bg-emerald-500/15 text-emerald-400 border-emerald-500/30'],
            'partial'  => ['label' => 'Qisman (faqat konspekt)', 'class' => 'bg-amber-500/15 text-amber-400 border-amber-500/30'],
            'stale'    => ['label' => 'Eskirgan (qayta indekslang)', 'class' => 'bg-amber-500/15 text-amber-400 border-amber-500/30'],
            'indexing' => ['label' => 'Indekslanmoqda...', 'class' => 'bg-sky-500/15 text-sky-400 border-sky-500/30'],
            'failed'   => ['label' => 'Xato (Matn sifati past)', 'class' => 'bg-rose-500/15 text-rose-400 border-rose-500/30'],
            'none'     => ['label' => 'Indekslanmagan', 'class' => 'bg-ink-800 text-mist border-ink-border'],
        ];
        $currentStatusBadge = $statusMap[$aiStatus] ?? $statusMap['none'];
    @endphp

    <div id="ai-assistant-panel" x-data="{ indexing: false }" class="bg-ink-900 border border-ink-border rounded-panel p-5 sm:p-7 shadow-lg space-y-5 scroll-mt-20">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-ink-border pb-4">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-btn bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M12 3l1.912 5.813a2 2 0 001.275 1.275L21 12l-5.813 1.912a2 2 0 00-1.275 1.275L12 21l-1.912-5.813a2 2 0 00-1.275-1.275L3 12l5.813-1.912a2 2 0 001.275-1.275L12 3z"/>
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <span class="text-[10px] font-mono text-emerald-400 uppercase tracking-widest block font-bold">2-QADAM</span>
                        <h3 class="text-base font-bold font-serif text-paper">
                            AI yordamchi (Kitob ichidagi RAG chat)
                        </h3>
                        <span class="px-2.5 py-0.5 rounded-badge border text-xs font-mono font-bold {{ $currentStatusBadge['class'] }}">
                            {{ $currentStatusBadge['label'] }}
                        </span>
                    </div>
                    <p class="text-xs text-mist font-mono mt-1">
                        PDF betlari va matn bo'laklarini tahlil qilib, kitob sahifasida kitobxonlar bilan suhbatlashuvchi AI yordamchini boshqarish
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
                            class="px-4 py-2 rounded-btn bg-emerald-600 hover:bg-emerald-500 disabled:opacity-50 text-white text-xs font-mono font-bold inline-flex items-center gap-2 transition-all shadow-md shadow-emerald-950/40 cursor-pointer">
                        <svg x-show="!indexing" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <polyline points="23 4 23 10 17 10"/>
                            <polyline points="1 20 1 14 7 14"/>
                            <path d="M3.51 9a9 9 0 0114.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0020.49 15"/>
                        </svg>
                        <svg x-show="indexing" x-cloak class="w-3.5 h-3.5 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <circle cx="12" cy="12" r="10" stroke-opacity="0.25"/>
                            <path d="M12 2a10 10 0 0110 10"/>
                        </svg>
                        <span x-text="indexing ? 'Indekslanmoqda (kutib turing)...' : '{{ $aiStatus === 'ready' ? 'Qayta indekslash' : 'Hozir indekslash' }}'">
                            {{ $aiStatus === 'ready' ? 'Qayta indekslash' : 'Hozir indekslash' }}
                        </span>
                    </button>
                </form>

                @if($aiStatus !== 'none' || $aiChunksCount > 0)
                    <form action="{{ route('admin.books.ai.clear', $book->id) }}" method="POST"
                          onsubmit="return confirm('Haqiqatan ham «{{ addslashes($book->title) }}» kitobining AI indeksini va barcha bo\'laklarini o\'chirmoqchimisiz? AI yordamchi o\'chiriladi.')">
                        @csrf
                        <button type="submit"
                                :disabled="indexing"
                                class="px-3.5 py-2 rounded-btn bg-rose-500/15 hover:bg-rose-500/25 text-rose-400 border border-rose-500/30 text-xs font-mono font-bold inline-flex items-center gap-1.5 transition-colors"
                                title="Faqat shu kitobning AI bo'laklari va indeksini tozalash">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
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
            <div class="p-3.5 rounded-card border border-ink-border bg-ink-950">
                <span class="block text-[10px] font-mono text-mist uppercase tracking-wider" title="PDF yoki OCR orqali o'qilgan matnning tozalik darajasi (100% = toza raqamli matn)">Matn sifati (aniqligi)</span>
                <span class="text-base font-bold text-paper font-mono mt-0.5 block">
                    {{ $aiQuality !== null ? $aiQuality . '%' : '—' }}
                </span>
            </div>
            <div class="p-3.5 rounded-card border border-ink-border bg-ink-950">
                <span class="block text-[10px] font-mono text-mist uppercase tracking-wider">Ajratilgan betlar</span>
                <span class="text-base font-bold text-paper font-mono mt-0.5 block">
                    {{ $aiPagesCount }} ta
                </span>
            </div>
            <div class="p-3.5 rounded-card border border-ink-border bg-ink-950">
                <span class="block text-[10px] font-mono text-mist uppercase tracking-wider">Matn bo'laklari (chunks)</span>
                <span class="text-base font-bold text-paper font-mono mt-0.5 block">
                    {{ $aiChunksCount }} ta
                </span>
            </div>
            <div class="p-3.5 rounded-card border border-ink-border bg-ink-950">
                <span class="block text-[10px] font-mono text-mist uppercase tracking-wider">So'nggi indekslash</span>
                <span class="text-xs font-bold text-amber-400 font-mono mt-0.5 block truncate">
                    {{ $book->ai_indexed_at ? $book->ai_indexed_at->timezone('Asia/Tashkent')->format('d.m.Y H:i') : 'Hali qilinmagan' }}
                </span>
            </div>
        </div>

        {{-- Xato yoki ogohlantirish xabari --}}
        @if($aiStatus === 'failed' || !empty($book->ai_index_error))
            @php
                $isAmberBox = ($aiStatus === 'partial' || $aiStatus === 'ready');
                $boxClasses = $isAmberBox
                    ? 'bg-amber-500/10 border-amber-500/30 text-amber-300'
                    : 'bg-rose-500/10 border-rose-500/30 text-rose-300';
                $boxSubClasses = $isAmberBox
                    ? 'text-amber-300/90'
                    : 'text-rose-300/80';
            @endphp
            <div class="p-4 rounded-card {{ $boxClasses }} border text-xs flex items-start gap-2.5">
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
        <form action="{{ route('admin.books.ai.settings', $book->id) }}" method="POST" class="space-y-4 pt-3 border-t border-ink-border">
            @csrf

            <div class="p-3.5 rounded-card bg-ink-950 border border-ink-border flex items-center justify-between gap-4">
                <div class="space-y-0.5">
                    <label for="ai_enabled" class="text-xs font-bold text-paper cursor-pointer block">
                        Ushbu kitob sahifasida «AI yordamchi»ni yoqish
                    </label>
                    <p class="text-[11px] text-mist font-mono">
                        Faqat holati «Tayyor» yoki «Qisman» bo'lganda kitobxonga ko'rinadi va savollarga javob beradi.
                    </p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer shrink-0">
                    <input type="checkbox" name="ai_enabled" id="ai_enabled" value="1" {{ $book->ai_enabled ? 'checked' : '' }} class="sr-only peer">
                    <div class="w-11 h-6 bg-ink-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-ink-border after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                </label>
            </div>

            <div class="space-y-1.5">
                <label for="ai_summary" class="block text-xs font-mono text-mist uppercase tracking-wider font-semibold">
                    Kitobning AI uchun qisqa mazmuni (Konspekt / Xulosa)
                </label>
                <textarea name="ai_summary"
                          id="ai_summary"
                          rows="3"
                          placeholder="Indekslash paytida avtomatik to'ldiriladi yoki o'zingiz tahrirlashingiz mumkin..."
                          class="w-full ks-input px-3.5 py-2.5 text-xs text-paper placeholder-mist/60 focus:border-emerald-500 leading-relaxed">{{ old('ai_summary', $book->ai_summary) }}</textarea>
                <p class="text-[10px] text-mist/70 font-mono">Ushbu matn "Bu kitob nima haqida?" kabi umumiy savollarda AI uchun asosiy yo'naltiruvchi kontekst vazifasini bajaradi.</p>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="ks-btn-ghost py-2 px-4 text-xs font-mono text-paper hover:text-emerald-400 hover:border-emerald-500/40 inline-flex items-center gap-1.5">
                    <span>AI sozlamalarini saqlash</span>
                    <span>✓</span>
                </button>
            </div>
        </form>
    </div>

    {{-- 3. Fon Musiqalari (Ambient Audio) bloki --}}
    <div class="bg-ink-900 border border-ink-border rounded-panel p-5 sm:p-7 shadow-lg space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-ink-border pb-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-btn bg-amber-500/15 border border-amber-500/30 text-amber-400 flex items-center justify-center shrink-0 text-lg">
                    🎵
                </div>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-[10px] font-mono text-amber-500 uppercase tracking-widest block font-bold">3-QADAM</span>
                        <h3 class="text-base font-bold font-serif text-paper">
                            Kitob fon musiqalari (Ambient Audio)
                        </h3>
                        <span class="px-2 py-0.5 rounded-badge bg-amber-500/15 border border-amber-500/30 text-amber-400 text-xs font-mono font-bold">
                            {{ $book->musics()->count() }} ta musiqa
                        </span>
                    </div>
                    <p class="text-xs text-mist font-mono mt-0.5">
                        O'quvchilar kitobni o'qiyotganda fonda yoqib eshitishi mumkin bo'lgan sokin ohanglar
                    </p>
                </div>
            </div>

            <a href="{{ route('admin.books.music.index', $book->id) }}" class="ks-btn-gold py-2 px-4 text-xs font-mono font-bold inline-flex items-center gap-1.5 shrink-0 shadow-sm">
                <span>+ Musiqalarni boshqarish</span>
                <span>→</span>
            </a>
        </div>

        @if($book->musics()->exists())
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-1">
                @foreach($book->musics as $music)
                    <div class="p-3 rounded-card border border-ink-border bg-ink-950 flex items-center justify-between gap-3">
                        <div class="min-w-0 flex-1 space-y-0.5">
                            <p class="text-xs font-bold text-paper truncate flex items-center gap-1.5">
                                <span>{{ $music->is_active ? '🟢' : '⚪️' }}</span>
                                <span>{{ $music->title }}</span>
                            </p>
                            <p class="text-[10px] text-mist font-mono truncate">{{ basename($music->file_path) }}</p>
                        </div>
                        <audio controls preload="none" class="h-7 w-32 shrink-0 filter invert-[0.8] opacity-80 hover:opacity-100 transition-opacity">
                            <source src="{{ $music->file_url }}">
                        </audio>
                    </div>
                @endforeach
            </div>
        @else
            <div class="py-6 text-center text-xs text-mist font-mono p-4 rounded-card bg-ink-950/60 border border-ink-border">
                <p>Ushbu kitobga hali fon musiqalari yuklanmagan.</p>
                <a href="{{ route('admin.books.music.index', $book->id) }}" class="text-amber-400 font-bold underline mt-1.5 inline-block">
                    Hozir fon musiqasi qo'shish →
                </a>
            </div>
        @endif
    </div>

</div>
@endsection
