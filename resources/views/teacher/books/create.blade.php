@extends('teacher.layouts.app')
@section('title', 'Kitob qo‘shish')

@section('content')
<div class="mx-auto max-w-3xl space-y-5">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="font-mono text-[11px] uppercase tracking-widest text-amber-400">Kutubxona</p>
            <h1 class="mt-1 font-serif text-2xl font-bold text-paper">Yangi kitob qo‘shish</h1>
            <p class="mt-1 text-sm text-mist">Kitob ma’lumotlarini kiriting va xohlasangiz PDF faylini yuklang.</p>
        </div>
        <a href="{{ route('teacher.books.index') }}" class="ks-btn-ghost inline-flex min-h-11 items-center gap-2 px-4 py-2 text-sm font-semibold">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6"/></svg>
            Kitoblar ro‘yxati
        </a>
    </div>

    @if($errors->any())
        <div role="alert" class="rounded-panel border border-rose-500/30 bg-rose-500/10 p-4 text-sm text-rose-300">
            <p class="font-bold">Kitobni saqlashda xatolar bor:</p>
            <ul class="mt-2 list-inside list-disc space-y-1">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <section class="ks-panel p-4 sm:p-6">
        <form method="POST" action="{{ route('teacher.books.store') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf

            <div>
                <label for="book-title" class="mb-1.5 block text-sm font-semibold text-paper">Kitob nomi <span class="text-rose-300">*</span></label>
                <input id="book-title" type="text" name="title" value="{{ old('title') }}" required maxlength="255" autocomplete="off" placeholder="Masalan: Atom odatlar" class="ks-input min-h-11 text-sm @error('title') border-rose-400 @enderror">
                @error('title')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label for="book-author" class="mb-1.5 block text-sm font-semibold text-paper">Muallif <span class="text-rose-300">*</span></label>
                    <input id="book-author" type="text" name="author" value="{{ old('author') }}" required maxlength="255" placeholder="Masalan: James Clear" class="ks-input min-h-11 text-sm">
                    @error('author')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="book-genre" class="mb-1.5 block text-sm font-semibold text-paper">Janr <span class="text-rose-300">*</span></label>
                    <select id="book-genre" name="genre" required class="ks-input min-h-11 text-sm">
                        <option value="">Janrni tanlang</option>
                        @foreach(['Shaxsiy rivojlanish','Roman','Ilmiy','Tarix','Biznes','Psixologiya','Fantastika','Bolalar adabiyoti','Falsafa','Biografiya'] as $genre)
                            <option value="{{ $genre }}" {{ old('genre') == $genre ? 'selected' : '' }}>{{ $genre }}</option>
                        @endforeach
                    </select>
                    @error('genre')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label for="book-description" class="mb-1.5 block text-sm font-semibold text-paper">Tavsif <span class="text-rose-300">*</span></label>
                <textarea id="book-description" name="description" rows="4" required maxlength="10000" placeholder="Kitob haqida qisqacha ma’lumot..." class="ks-input resize-y text-sm">{{ old('description') }}</textarea>
                @error('description')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="pdf_file" class="mb-1.5 block text-sm font-semibold text-paper">Kitob PDF fayli <span class="font-normal text-mist">(ixtiyoriy)</span></label>
                <p class="mb-2 text-xs leading-relaxed text-mist">PDF yuklanmasa, kitob boblari matnidan fayl avtomatik yaratiladi.</p>
                <label for="pdf_file" class="group flex min-h-36 cursor-pointer flex-col items-center justify-center rounded-panel border border-dashed border-ink-border bg-ink-950/60 px-5 py-6 text-center transition hover:border-amber-500/50 hover:bg-ink-800/60 focus-within:ring-2 focus-within:ring-amber-400">
                    <svg class="mb-2 h-8 w-8 text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M7 3h7l5 5v12a1 1 0 0 1-1 1H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M14 3v6h5M12 12v6m0 0-2.5-2.5M12 18l2.5-2.5"/></svg>
                    <span id="pdf-prompt" class="text-sm font-semibold text-paper">PDF faylni tanlash</span>
                    <span class="mt-1 text-xs text-mist">PDF format · Maksimal 20 MB</span>
                    <input type="file" name="pdf_file" id="pdf_file" accept=".pdf,application/pdf" class="sr-only" aria-describedby="pdf-help" onchange="document.getElementById('pdf-file-name').textContent = this.files[0] ? this.files[0].name : ''; document.getElementById('pdf-file-selected').classList.toggle('hidden', !this.files[0]); document.getElementById('pdf-prompt').textContent = this.files[0] ? 'Fayl tanlandi' : 'PDF faylni tanlash';">
                </label>
                <p id="pdf-help" class="sr-only">PDF formatdagi fayl tanlang, hajmi 20 megabaytdan oshmasin.</p>
                <div id="pdf-file-selected" class="mt-2 hidden items-center gap-2 rounded-md border border-emerald-500/25 bg-emerald-500/10 px-3 py-2 text-emerald-300">
                    <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6"/></svg>
                    <span id="pdf-file-name" class="truncate text-xs font-semibold"></span>
                </div>
                @error('pdf_file')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
            </div>

            <div class="flex flex-wrap justify-end gap-2 border-t border-ink-border pt-4">
                <a href="{{ route('teacher.books.index') }}" class="ks-btn-ghost inline-flex min-h-11 items-center justify-center px-4 py-2 text-sm">Bekor qilish</a>
                <button type="submit" class="ks-btn-primary inline-flex min-h-11 items-center justify-center gap-2 px-5 py-2 text-sm font-bold">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6"/></svg>
                    Kitobni saqlash
                </button>
            </div>
        </form>
    </section>
</div>
@endsection
