@extends('admin.layouts.app')
@section('title', 'Yangi kitob qo\'shish')
@section('breadcrumb', 'Yangi kitob')

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
                        Yangi kitob qo'shish
                    </h1>
                    <span class="px-2 py-0.5 rounded-badge bg-amber-500/15 border border-amber-500/30 text-amber-400 text-[10px] font-mono font-bold">
                        Yangi nashr
                    </span>
                </div>
                <p class="text-xs text-mist font-mono mt-1">Platformaga yangi kitob ma'lumotlarini kiritish va elektron fayllarini yuklash</p>
            </div>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <a href="{{ route('admin.books.index') }}" class="ks-btn-ghost py-1.5 px-3.5 text-xs font-mono">
                ← Barcha kitoblar
            </a>
        </div>
    </div>

    {{-- Asosiy Kitob Yaratish Formasi --}}
    <div class="bg-ink-900 border border-ink-border rounded-panel p-5 sm:p-7 shadow-lg space-y-6">
        <div class="border-b border-ink-border pb-4">
            <span class="text-[10px] font-mono text-amber-500 uppercase tracking-widest block font-bold">MA'LUMOTLAR</span>
            <h2 class="text-base font-bold font-serif text-paper">Kitob parametrlari va fayllar</h2>
            <p class="text-xs text-mist font-mono mt-0.5">Yulduzcha (*) bilan belgilangan maydonlar to'ldirilishi shart</p>
        </div>

        <form action="{{ route('admin.books.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                {{-- Kitob nomi --}}
                <div class="sm:col-span-2 space-y-1.5">
                    <label class="block text-xs font-mono text-mist uppercase tracking-wider font-semibold">
                        Kitob nomi <span class="text-rose-400">*</span>
                    </label>
                    <input type="text" name="title" value="{{ old('title') }}" required
                           placeholder="Masalan: Atom Odatlar"
                           class="w-full ks-input px-3.5 py-2.5 text-xs text-paper placeholder-mist/60 focus:border-amber-400">
                    @error('title') <p class="text-rose-400 text-[11px] font-mono mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Muallif --}}
                <div class="space-y-1.5">
                    <label class="block text-xs font-mono text-mist uppercase tracking-wider font-semibold">
                        Muallif <span class="text-rose-400">*</span>
                    </label>
                    <input type="text" name="author" value="{{ old('author') }}" required
                           placeholder="Masalan: James Clear"
                           class="w-full ks-input px-3.5 py-2.5 text-xs text-paper placeholder-mist/60 focus:border-amber-400">
                    @error('author') <p class="text-rose-400 text-[11px] font-mono mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Hafta raqami --}}
                <div class="space-y-1.5">
                    <label class="block text-xs font-mono text-mist uppercase tracking-wider font-semibold">
                        Hafta raqami
                    </label>
                    <input type="number" name="week_number" value="{{ old('week_number', (\App\Models\Book::max('week_number') ?? 0) + 1) }}" min="1"
                           class="w-full ks-input px-3.5 py-2.5 text-xs font-mono text-paper placeholder-mist/60 focus:border-amber-400">
                    @error('week_number') <p class="text-rose-400 text-[11px] font-mono mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Janr --}}
                <div class="sm:col-span-4 space-y-1.5">
                    <label class="block text-xs font-mono text-mist uppercase tracking-wider font-semibold">
                        Janr / Toifa <span class="text-rose-400">*</span>
                    </label>
                    <input type="text" name="genre" value="{{ old('genre', 'Shaxsiy rivojlanish') }}" required
                           placeholder="Masalan: Biznes, Psixologiya, Badiiy adabiyot..."
                           class="w-full ks-input px-3.5 py-2.5 text-xs text-paper placeholder-mist/60 focus:border-amber-400">
                    @error('genre') <p class="text-rose-400 text-[11px] font-mono mt-1">{{ $message }}</p> @enderror
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
                          placeholder="Kitobning asosiy mazmuni, mundarijasi va o'quvchiga beradigan foydasi haqida..."
                          class="w-full ks-input px-3.5 py-2.5 text-xs text-paper placeholder-mist/60 focus:border-amber-400 leading-relaxed">{{ old('description') }}</textarea>
                @error('description') <p class="text-rose-400 text-[11px] font-mono mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Media yuklash: Muqova rasmi va 200MB PDF --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 pt-2">

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
                                    Muqova rasmini tanlang
                                </span>
                                <span class="text-[11px] text-mist/80 font-mono mt-1">2:3 nisbatda (JPG, PNG, WEBP, maks 15 MB)</span>
                            </div>
                        </template>

                        <template x-if="previewUrl">
                            <div class="flex items-center gap-4 w-full">
                                <img :src="previewUrl" alt="Muqova preview" class="w-20 aspect-[2/3] object-cover rounded-btn shadow-lg border border-amber-500/30 shrink-0">
                                <div class="min-w-0 flex-1 space-y-1">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 text-[10px] font-mono font-bold uppercase">
                                        ✓ Rasm tanlandi
                                    </span>
                                    <p class="text-xs font-bold text-paper truncate group-hover:text-amber-400 transition-colors">
                                        Almashtirish uchun bosing
                                    </p>
                                    <p class="text-[11px] text-mist font-mono">Boshqa rasm tanlash mumkin</p>
                                </div>
                            </div>
                        </template>

                        <input type="file" name="cover_image" id="cover_image" accept="image/png,image/jpeg,image/webp,image/jpg" class="hidden" @change="handleFile($event)">
                    </label>
                    @error('cover_image') <p class="text-rose-400 text-[11px] font-mono mt-1">{{ $message }}</p> @enderror
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
                                    PDF faylni tanlang
                                </span>
                                <span class="text-[11px] text-mist/80 font-mono mt-1">To'liq elektron variant (maks. 200 MB)</span>
                            </div>
                        </template>

                        <template x-if="fileName">
                            <div class="flex items-center gap-3 w-full p-3 bg-emerald-500/10 border border-emerald-500/30 rounded-btn">
                                <span class="text-2xl shrink-0">✅</span>
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs font-bold text-paper truncate" x-text="fileName"></p>
                                    <p class="text-[11px] text-emerald-400 font-mono" x-text="'Hajmi: ' + fileSize"></p>
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
                        Kitob darhol faollashtirilsin
                    </label>
                    <p class="text-[11px] text-mist font-mono">
                        Belgilansa, kitob saqlangandan so'ng darhol umumiy katalogda kitobxonlarga ko'rinadi.
                    </p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer shrink-0">
                    <input type="checkbox" name="is_active" id="is_active" value="1" checked class="sr-only peer">
                    <div class="w-11 h-6 bg-ink-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-ink-border after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-amber-500"></div>
                </label>
            </div>

            {{-- Telegram orqali tarqatish (Checkbox) --}}
            <div class="p-3.5 rounded-card bg-ink-950 border border-ink-border flex items-center justify-between gap-4">
                <div class="space-y-0.5">
                    <label for="telegram_broadcast" class="text-xs font-bold text-paper cursor-pointer block flex items-center gap-1.5">
                        <span>📢</span>
                        <span>Telegram kanal va botga avtomatik e'lon yuborish</span>
                    </label>
                    <p class="text-[11px] text-mist font-mono">
                        Kitob saqlangach, Telegram kanal va bot foydalanuvchilariga rasm va xulosa bilan e'lon jo'natiladi.
                    </p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer shrink-0">
                    <input type="checkbox" name="telegram_broadcast" id="telegram_broadcast" value="1" checked class="sr-only peer">
                    <div class="w-11 h-6 bg-ink-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-ink-border after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#229ED9]"></div>
                </label>
            </div>

            {{-- Tugmalar qatori --}}
            <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-ink-border">
                <a href="{{ route('admin.books.index') }}" class="ks-btn-ghost py-2 px-4 text-xs font-mono">
                    Bekor qilish
                </a>
                <button type="submit" class="ks-btn-primary py-2 px-6 text-xs font-mono font-bold inline-flex items-center gap-1.5 shadow-md">
                    <span>Kitobni saqlash</span>
                    <span>→</span>
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
