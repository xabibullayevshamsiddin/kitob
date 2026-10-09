@extends('admin.layouts.app')
@section('title', 'Yangi audio yuklash')
@section('breadcrumb', 'Audiolar')

@section('content')
<div class="max-w-3xl mx-auto space-y-5">

    {{-- Page header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <span class="ks-eyebrow">Audiolar</span>
            <h1 class="text-xl font-bold font-serif text-paper mt-0.5">Yangi audio dars yuklash</h1>
            <p class="text-xs text-mist font-mono mt-0.5">Audio faylni platformaga yuklab, tegishli kitobga bog'lash</p>
        </div>
        <a href="{{ route('admin.audios.index') }}" class="ks-btn-ghost py-2 px-4 text-xs shrink-0 self-start sm:self-auto">
            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
            Orqaga
        </a>
    </div>

    <div class="ks-panel p-5 sm:p-6">
        <form action="{{ route('admin.audios.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            {{-- 1. Bog'langan kitob --}}
            <div>
                <label class="block text-xs font-semibold text-paper mb-1">Qaysi kitobga bog'lansin? <span class="text-mist font-normal font-mono text-[11px]">(ixtiyoriy)</span></label>
                <select name="book_id" class="ks-input">
                    <option value="">-- Alohida audio (hech qaysi kitobga bog'lanmagan / Mustaqil) --</option>
                    @foreach($books as $b)
                        <option value="{{ $b->id }}" {{ (old('book_id', $selectedBookId) == $b->id) ? 'selected' : '' }}>
                            {{ $b->week_number ? "{$b->week_number}-Hafta: " : '' }}{{ $b->title }} ({{ $b->author }})
                        </option>
                    @endforeach
                </select>
                <p class="text-[11px] text-mist mt-1.5">Agar kitob tanlamasangiz, audio mustaqil dars sifatida saqlanadi.</p>
                @error('book_id') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- 2. Audio sarlavhasi --}}
            <div>
                <label class="block text-xs font-semibold text-paper mb-1">Audio sarlavhasi <span class="text-rose-400">*</span></label>
                <input type="text" name="title" value="{{ old('title') }}" required placeholder="Masalan: 1-bob: Kirish va asosiy tushunchalar" class="ks-input">
                @error('title') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- 3. Davomiyligi --}}
            <div>
                <label class="block text-xs font-semibold text-paper mb-1">Davomiyligi (daqiqa hisobida)</label>
                <input type="number" step="0.5" name="duration" value="{{ old('duration') }}" placeholder="Masalan: 12.5" class="ks-input font-mono">
                <span class="text-[11px] text-mist mt-1.5 block">O'quvchi pleerida va statistikasida ko'rsatiladigan vaqt</span>
            </div>

            {{-- 4. Audio manbasi --}}
            <div x-data="{
                mode: 'file',
                audioFileName: '',
                handleAudio(e) {
                    const f = e.target.files[0];
                    this.audioFileName = f ? f.name + ' (' + (f.size / (1024*1024)).toFixed(2) + ' MB)' : '';
                }
            }" class="space-y-3">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                    <label class="block text-xs font-semibold text-paper">Audio manbasi <span class="text-rose-400">*</span></label>
                    <div class="flex flex-wrap items-center gap-1.5 font-mono text-xs">
                        <button type="button" @click="mode = 'file'"
                                class="min-h-11 px-3 py-1 rounded-badge border transition-colors"
                                :class="mode === 'file' ? 'bg-amber-500 text-ink-950 border-amber-500 font-bold' : 'border-ink-border text-mist hover:text-paper'">
                            Fayl yuklash
                        </button>
                        <button type="button" @click="mode = 'url'"
                                class="min-h-11 px-3 py-1 rounded-badge border transition-colors"
                                :class="mode === 'url' ? 'bg-amber-500 text-ink-950 border-amber-500 font-bold' : 'border-ink-border text-mist hover:text-paper'">
                            Havola (URL)
                        </button>
                    </div>
                </div>

                {{-- Fayl yuklash --}}
                <div x-show="mode === 'file'">
                    <label for="audio_file" class="group flex flex-col items-center justify-center w-full min-h-[140px] p-6 rounded-panel border-2 border-dashed border-ink-border bg-ink-950/60 hover:border-amber-500 hover:bg-amber-500/5 transition-all cursor-pointer">
                        <svg class="w-8 h-8 mb-2 text-mist group-hover:text-amber-400 group-hover:scale-110 transition-all" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 14h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-7a9 9 0 0 1 18 0v7a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3"/></svg>
                        <span class="text-xs font-bold text-paper group-hover:text-amber-400 transition-colors">
                            Audio faylni tanlang (MP3, WAV, M4A, OGG)
                        </span>
                        <span class="text-[11px] text-mist mt-1 font-mono">100 MB gacha ruxsat berilgan</span>
                        <span x-show="audioFileName" x-text="audioFileName" class="mt-2 text-xs font-mono font-bold text-emerald-400 bg-emerald-500/10 border border-emerald-500/20 px-3 py-1 rounded-badge"></span>
                        <input type="file" name="audio_file" id="audio_file" accept=".mp3,.wav,.ogg,.m4a,.aac" class="hidden" @change="handleAudio($event)">
                    </label>
                </div>

                {{-- URL --}}
                <div x-show="mode === 'url'" style="display: none;">
                    <input type="url" name="audio_url" value="{{ old('audio_url') }}" placeholder="https://cdn.example.com/audio/dars-1.mp3" class="ks-input font-mono text-sm">
                    <span class="text-[11px] text-mist mt-1.5 block">To'g'ridan-to'g'ri audio oqim havolasi</span>
                </div>

                @error('audio_file') <p class="text-rose-400 text-xs">{{ $message }}</p> @enderror
                @error('audio_url') <p class="text-rose-400 text-xs">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-ink-border">
                <a href="{{ route('admin.audios.index') }}" class="ks-btn-ghost py-2 px-4 text-xs">Bekor qilish</a>
                <button type="submit" class="ks-btn-primary py-2 px-6 text-xs font-bold">Saqlash va Bog'lash</button>
            </div>
        </form>
    </div>
</div>
@endsection
