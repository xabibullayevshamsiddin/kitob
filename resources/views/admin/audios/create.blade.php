@extends('admin.layouts.app')
@section('title', 'Yangi audio yuklash')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-black text-slate-800 dark:text-white flex items-center gap-2">
                <span>🎵</span> Yangi audio dars yuklash
            </h2>
            <p class="text-sm text-slate-500">Audio faylni platformaga yuklab, tegishli kitobga bog'lash</p>
        </div>
        <a href="{{ route('admin.audios.index') }}" class="px-4 py-2 bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl text-sm font-semibold hover:bg-slate-300 dark:hover:bg-slate-600 transition-colors">
            ← Orqaga
        </a>
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 sm:p-8 shadow-sm">
        <form action="{{ route('admin.audios.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            {{-- 1. Bog'langan kitob --}}
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">Qaysi kitobga bog'lansin? (Ixtiyoriy)</label>
                <select name="book_id" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    <option value="">-- Alohida audio (hech qaysi kitobga bog'lanmagan / Mustaqil) --</option>
                    @foreach($books as $b)
                        <option value="{{ $b->id }}" {{ (old('book_id', $selectedBookId) == $b->id) ? 'selected' : '' }}>
                            {{ $b->week_number ? "{$b->week_number}-Hafta: " : '' }}{{ $b->title }} ({{ $b->author }})
                        </option>
                    @endforeach
                </select>
                <p class="text-[11px] text-slate-500 mt-1">Agar kitob tanlamasangiz, audio mustaqil dars sifatida saqlanadi.</p>
                @error('book_id') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- 2. Audio sarlavhasi --}}
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">Audio sarlavhasi *</label>
                <input type="text" name="title" value="{{ old('title') }}" required placeholder="Masalan: 1-bob: Kirish va asosiy tushunchalar"
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                @error('title') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- 3. Davomiyligi --}}
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">Davomiyligi (daqiqa hisobida)</label>
                <input type="number" step="0.5" name="duration" value="{{ old('duration') }}" placeholder="Masalan: 12.5"
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                <span class="text-[11px] text-slate-400 mt-1 block">O'quvchi pleerida va statistikasida ko'rsatiladigan vaqt</span>
            </div>

            {{-- 4. Audio faylni yuklash yoki havola --}}
            <div x-data="{
                mode: 'file',
                audioFileName: '',
                handleAudio(e) {
                    const f = e.target.files[0];
                    this.audioFileName = f ? f.name + ' (' + (f.size / (1024*1024)).toFixed(2) + ' MB)' : '';
                }
            }" class="space-y-3">
                <div class="flex items-center justify-between">
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Audio manbasi *</label>
                    <div class="flex items-center gap-2 text-xs">
                        <button type="button" @click="mode = 'file'" :class="mode === 'file' ? 'bg-indigo-600 text-white font-bold' : 'text-slate-400 hover:text-white'" class="px-2.5 py-1 rounded-lg transition-colors">
                            📁 Fayl yuklash
                        </button>
                        <button type="button" @click="mode = 'url'" :class="mode === 'url' ? 'bg-indigo-600 text-white font-bold' : 'text-slate-400 hover:text-white'" class="px-2.5 py-1 rounded-lg transition-colors">
                            🔗 Havola (URL)
                        </button>
                    </div>
                </div>

                {{-- File upload tab --}}
                <div x-show="mode === 'file'">
                    <label for="audio_file" class="group flex flex-col items-center justify-center w-full min-h-[140px] p-6 rounded-2xl border-2 border-dashed border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-900/40 hover:border-amber-500 hover:bg-amber-500/5 transition-all cursor-pointer">
                        <span class="text-3xl mb-2 group-hover:scale-110 transition-transform">🎧</span>
                        <span class="text-xs font-bold text-slate-700 dark:text-slate-200 group-hover:text-amber-500 transition-colors">
                            Audio faylni tanlang (MP3, WAV, M4A, OGG)
                        </span>
                        <span class="text-[11px] text-slate-400 mt-1">100 MB gacha ruxsat berilgan</span>
                        <span x-show="audioFileName" x-text="audioFileName" class="mt-2 text-xs font-bold text-emerald-500 bg-emerald-500/10 px-3 py-1 rounded-lg"></span>
                        <input type="file" name="audio_file" id="audio_file" accept=".mp3,.wav,.ogg,.m4a,.aac" class="hidden" @change="handleAudio($event)">
                    </label>
                </div>

                {{-- URL input tab --}}
                <div x-show="mode === 'url'" style="display: none;">
                    <input type="url" name="audio_url" value="{{ old('audio_url') }}" placeholder="https://cdn.example.com/audio/dars-1.mp3"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    <span class="text-[11px] text-slate-400 mt-1 block">To'g'ridan-to'g'ri audio oqim havolasi</span>
                </div>

                @error('audio_file') <p class="text-rose-500 text-xs">{{ $message }}</p> @enderror
                @error('audio_url') <p class="text-rose-500 text-xs">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-700">
                <a href="{{ route('admin.audios.index') }}" class="px-5 py-2.5 rounded-xl text-slate-600 dark:text-slate-400 font-semibold text-sm hover:bg-slate-100 dark:hover:bg-slate-700">Bekor qilish</a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 text-white font-bold text-sm hover:bg-indigo-500 transition-colors shadow-lg shadow-indigo-600/30">Saqlash va Bog'lash</button>
            </div>
        </form>
    </div>
</div>
@endsection
