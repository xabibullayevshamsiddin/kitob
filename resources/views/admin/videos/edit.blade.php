@extends('admin.layouts.app')
@section('title', 'Video darsni tahrirlash')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-black text-slate-800 dark:text-white flex items-center gap-2">
                <span>✏️</span> Video darsni tahrirlash
            </h2>
            <p class="text-sm text-slate-500">Video dars ma'lumotlarini yoki bog'langan kitobni o'zgartirish</p>
        </div>
        <a href="{{ route('admin.videos.index') }}" class="px-4 py-2 bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl text-sm font-semibold hover:bg-slate-300 dark:hover:bg-slate-600 transition-colors">
            ← Orqaga
        </a>
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 sm:p-8 shadow-sm">
        <form action="{{ route('admin.videos.update', $video->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            {{-- 1. Bog'langan kitob --}}
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">Qaysi kitobga bog'lansin? (Ixtiyoriy)</label>
                <select name="book_id" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    <option value="">-- Alohida video (hech qaysi kitobga bog'lanmagan / Mustaqil) --</option>
                    @foreach($books as $b)
                        <option value="{{ $b->id }}" {{ (old('book_id', $video->book_id) == $b->id) ? 'selected' : '' }}>
                            {{ $b->week_number ? "{$b->week_number}-Hafta: " : '' }}{{ $b->title }} ({{ $b->author }})
                        </option>
                    @endforeach
                </select>
                <p class="text-[11px] text-slate-500 mt-1">Agar kitob tanlamasangiz, video mustaqil video dars sifatida saqlanadi.</p>
                @error('book_id') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- 2. Video sarlavhasi --}}
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">Video sarlavhasi *</label>
                <input type="text" name="title" value="{{ old('title', $video->title) }}" required placeholder="Masalan: Kitob tahlili va amaliy qo'llanishi"
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                @error('title') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- 3. Video turi va Bob raqami --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4" x-data="{ videoType: '{{ old('type', $video->type) }}' }">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">Video turi *</label>
                    <select name="type" x-model="videoType" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        <option value="overview">📺 Umumiy sharh / Tahlil videosi</option>
                        <option value="chapter">📖 Muayyan bob videosi</option>
                    </select>
                </div>

                <div x-show="videoType === 'chapter'" style="{{ $video->type === 'chapter' ? '' : 'display: none;' }}">
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">Bob raqami</label>
                    <input type="number" name="chapter_number" value="{{ old('chapter_number', $video->chapter_number ?: 1) }}" min="1"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>
            </div>

            {{-- 4. Davomiyligi --}}
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">Davomiyligi (daqiqa hisobida)</label>
                <input type="number" step="0.5" name="duration" value="{{ old('duration', $video->duration ? round($video->duration / 60, 1) : '') }}" placeholder="Masalan: 25.5"
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>

            {{-- 5. Video manbasi: Yangilash (ixtiyoriy) --}}
            <div x-data="{
                mode: '{{ str_starts_with($video->video_path, 'http') ? 'url' : 'file' }}',
                dragOver: false
            }" class="space-y-3">
                <div class="flex items-center justify-between">
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Video fayl yoki manba (O'zgartirish ixtiyoriy)</label>
                    <div class="flex gap-1 bg-slate-100 dark:bg-slate-700 p-0.5 rounded-lg text-xs">
                        <button type="button" @click="mode = 'file'" :class="mode === 'file' ? 'bg-white dark:bg-slate-600 shadow text-indigo-600 dark:text-white font-bold' : 'text-slate-500'" class="px-2.5 py-1 rounded-md transition-colors">
                            📁 Faylni almashtirish
                        </button>
                        <button type="button" @click="mode = 'url'" :class="mode === 'url' ? 'bg-white dark:bg-slate-600 shadow text-indigo-600 dark:text-white font-bold' : 'text-slate-500'" class="px-2.5 py-1 rounded-md transition-colors">
                            🔗 Havola (URL)
                        </button>
                    </div>
                </div>

                @if($video->video_path)
                    <div class="text-xs text-slate-500 bg-slate-50 dark:bg-slate-750 p-2.5 rounded-xl border border-slate-200 dark:border-slate-700 flex items-center justify-between">
                        <span>Hozirgi video: <strong class="text-slate-700 dark:text-slate-300">{{ basename($video->video_path) }}</strong></span>
                        <a href="{{ $video->stream_url }}" target="_blank" class="text-indigo-500 hover:underline">Ko'rish ↗</a>
                    </div>
                @endif

                {{-- Fayl yuklash rejimi --}}
                <div x-show="mode === 'file'">
                    <div class="relative border-2 border-dashed rounded-2xl p-6 text-center transition-colors cursor-pointer"
                         :class="dragOver ? 'border-indigo-500 bg-indigo-50/10' : 'border-slate-300 dark:border-slate-600 hover:border-indigo-400'"
                         @dragover.prevent="dragOver = true"
                         @dragleave.prevent="dragOver = false"
                         @drop.prevent="dragOver = false; $refs.videoInput.files = $event.dataTransfer.files">
                        <input type="file" name="video_file" x-ref="videoInput" accept="video/mp4,video/webm,video/quicktime,video/x-msvideo"
                               class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                        <div class="text-3xl mb-2">🎬</div>
                        <p class="text-sm font-semibold text-slate-700 dark:text-slate-200">Yangi video fayl tanlang yoki shu yerga tashlang</p>
                        <p class="text-xs text-slate-400 mt-1">MP4, WebM, MOV (Maksimal 200 MB). Agar almashtirmoqchi bo'lmasangiz, bo'sh qoldiring.</p>
                    </div>
                </div>

                {{-- URL rejimi --}}
                <div x-show="mode === 'url'" style="display: none;">
                    <input type="url" name="video_url" value="{{ old('video_url', str_starts_with($video->video_path, 'http') ? $video->video_path : '') }}"
                           placeholder="https://www.youtube.com/watch?v=... yoki to'g'ridan-to'g'ri .mp4 URL"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none text-sm">
                </div>
                @error('video_file') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- 6. Video muqovasi (Thumbnail) --}}
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">Video muqova rasmi (Thumbnail)</label>
                
                @if($video->thumbnail)
                    <div class="flex items-center gap-3 mb-3 p-2 bg-slate-50 dark:bg-slate-750 rounded-xl border border-slate-200 dark:border-slate-700 w-fit">
                        <img src="{{ $video->thumbnail_url }}" alt="Thumbnail" class="w-16 h-10 object-cover rounded-lg border border-slate-300 dark:border-slate-600">
                        <span class="text-xs text-slate-500">Hozirgi rasm saqlanadi, almashtirish uchun yangisini tanlang</span>
                    </div>
                @endif

                <input type="file" name="thumbnail" accept="image/jpeg,image/png,image/webp"
                       class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-slate-700 dark:file:text-slate-200">
                <p class="text-xs text-slate-400 mt-1">JPG, PNG, WebP (Maksimal 10 MB)</p>
                @error('thumbnail') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Tugmalar --}}
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-700">
                <a href="{{ route('admin.videos.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 font-semibold text-sm hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                    Bekor qilish
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 text-white font-bold text-sm hover:bg-indigo-500 shadow-md transition-colors flex items-center gap-2">
                    <span>💾</span> O'zgarishlarni saqlash
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
