@extends('admin.layouts.app')
@section('title', 'Video darsni tahrirlash')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <span class="ks-eyebrow">Videolar</span>
            <h1 class="text-xl sm:text-2xl font-bold font-serif text-paper mt-0.5">Video darsni tahrirlash</h1>
            <p class="text-sm text-mist">Video dars ma'lumotlarini yoki bog'langan kitobni o'zgartirish</p>
        </div>
        <a href="{{ route('admin.videos.index') }}" class="ks-btn-ghost min-h-11 inline-flex items-center px-4 py-2 text-sm font-semibold self-start sm:self-auto">
            ← Orqaga
        </a>
    </div>

    <div class="ks-panel p-5 sm:p-6">
        <form action="{{ route('admin.videos.update', $video->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            {{-- 1. Bog'langan kitob --}}
            <div>
                <label class="block text-xs font-semibold text-paper mb-1">Qaysi kitobga bog'lansin? <span class="text-mist font-normal">(ixtiyoriy)</span></label>
                <select name="book_id" class="ks-input">
                    <option value="">-- Alohida video (hech qaysi kitobga bog'lanmagan / Mustaqil) --</option>
                    @foreach($books as $b)
                        <option value="{{ $b->id }}" {{ (old('book_id', $video->book_id) == $b->id) ? 'selected' : '' }}>
                            {{ $b->week_number ? "{$b->week_number}-Hafta: " : '' }}{{ $b->title }} ({{ $b->author }})
                        </option>
                    @endforeach
                </select>
                <p class="text-[11px] text-mist mt-1.5">Agar kitob tanlamasangiz, video mustaqil video dars sifatida saqlanadi.</p>
                @error('book_id') <p class="text-rose-300 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- 2. Video sarlavhasi --}}
            <div>
                <label class="block text-xs font-semibold text-paper mb-1">Video sarlavhasi <span class="text-rose-300">*</span></label>
                <input type="text" name="title" value="{{ old('title', $video->title) }}" required placeholder="Masalan: Kitob tahlili va amaliy qo'llanishi"
                       class="ks-input">
                @error('title') <p class="text-rose-300 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- 3. Video turi va Bob raqami --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4" x-data="{ videoType: '{{ old('type', $video->type) }}' }">
                <div>
                    <label class="block text-xs font-semibold text-paper mb-1">Video turi <span class="text-rose-300">*</span></label>
                    <select name="type" x-model="videoType" class="ks-input">
                        <option value="overview">Umumiy sharh / Tahlil videosi</option>
                        <option value="chapter">Muayyan bob videosi</option>
                    </select>
                </div>

                <div x-show="videoType === 'chapter'" style="{{ $video->type === 'chapter' ? '' : 'display: none;' }}">
                    <label class="block text-xs font-semibold text-paper mb-1">Bob raqami</label>
                    <input type="number" name="chapter_number" value="{{ old('chapter_number', $video->chapter_number ?: 1) }}" min="1"
                           class="ks-input font-mono">
                </div>
            </div>

            {{-- 4. Davomiyligi --}}
            <div>
                <label class="block text-xs font-semibold text-paper mb-1">Davomiyligi (daqiqa hisobida)</label>
                <input type="number" step="0.5" name="duration" value="{{ old('duration', $video->duration ? round($video->duration / 60, 1) : '') }}" placeholder="Masalan: 25.5"
                       class="ks-input font-mono">
            </div>

            {{-- 5. Video manbasi: Yangilash (ixtiyoriy) --}}
            <div x-data="{
                mode: '{{ str_starts_with($video->video_path, 'http') ? 'url' : 'file' }}',
                dragOver: false
            }" class="space-y-3">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                    <label class="block text-xs font-semibold text-paper">Video fayl yoki manba <span class="text-mist font-normal">(o'zgartirish ixtiyoriy)</span></label>
                    <div class="flex flex-wrap gap-1 bg-ink-950 p-1 rounded-btn border border-ink-border text-xs">
                        <button type="button" @click="mode = 'file'" :class="mode === 'file' ? 'bg-amber-500 text-ink-950 font-bold' : 'text-mist hover:text-paper'" class="min-h-11 px-3 py-1 rounded-badge transition-colors">
                            Faylni almashtirish
                        </button>
                        <button type="button" @click="mode = 'url'" :class="mode === 'url' ? 'bg-amber-500 text-ink-950 font-bold' : 'text-mist hover:text-paper'" class="min-h-11 px-3 py-1 rounded-badge transition-colors">
                            Havola (URL)
                        </button>
                    </div>
                </div>

                @if($video->video_path)
                    <div class="text-xs text-mist bg-ink-950/60 p-3 rounded-card border border-ink-border flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                        <span class="min-w-0 break-all">Hozirgi video: <strong class="text-paper">{{ basename($video->video_path) }}</strong></span>
                        <a href="{{ $video->stream_url }}" target="_blank" class="text-amber-400 hover:underline shrink-0">Ko'rish ↗</a>
                    </div>
                @endif

                {{-- Fayl yuklash rejimi --}}
                <div x-show="mode === 'file'">
                    <div class="relative border-2 border-dashed border-ink-border rounded-panel p-5 sm:p-6 text-center transition-colors cursor-pointer bg-ink-950/50"
                         :class="dragOver ? 'border-amber-400 bg-amber-500/5' : 'hover:border-amber-500'"
                         @dragover.prevent="dragOver = true"
                         @dragleave.prevent="dragOver = false"
                         @drop.prevent="dragOver = false; $refs.videoInput.files = $event.dataTransfer.files">
                        <input type="file" name="video_file" x-ref="videoInput" accept="video/mp4,video/webm,video/quicktime,video/x-msvideo"
                               class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                        <svg class="w-8 h-8 mx-auto mb-2 text-mist" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="14" height="14" rx="2"/><path d="m17 10 4-2v8l-4-2z"/></svg>
                        <p class="text-sm font-semibold text-paper">Yangi video fayl tanlang yoki shu yerga tashlang</p>
                        <p class="text-xs text-mist mt-1">MP4, WebM, MOV (Maksimal 200 MB). Agar almashtirmoqchi bo'lmasangiz, bo'sh qoldiring.</p>
                    </div>
                </div>

                {{-- URL rejimi --}}
                <div x-show="mode === 'url'" style="display: none;">
                    <input type="url" name="video_url" value="{{ old('video_url', str_starts_with($video->video_path, 'http') ? $video->video_path : '') }}"
                           placeholder="https://www.youtube.com/watch?v=... yoki to'g'ridan-to'g'ri .mp4 URL"
                           class="ks-input font-mono text-sm">
                </div>
                @error('video_file') <p class="text-rose-300 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- 6. Video muqovasi (Thumbnail) --}}
            <div>
                <label class="block text-xs font-semibold text-paper mb-1">Video muqova rasmi (thumbnail)</label>
                
                @if($video->thumbnail)
                    <div class="flex items-center gap-3 mb-3 p-2 bg-ink-950/60 rounded-card border border-ink-border w-full sm:w-fit">
                        <img src="{{ $video->thumbnail_url }}" alt="Thumbnail" class="w-16 h-10 object-cover rounded-badge border border-ink-border shrink-0">
                        <span class="text-xs text-mist">Hozirgi rasm saqlanadi, almashtirish uchun yangisini tanlang</span>
                    </div>
                @endif

                <input type="file" name="thumbnail" accept="image/jpeg,image/png,image/webp"
                       class="w-full min-w-0 text-sm text-mist file:mr-3 file:min-h-11 file:px-3 file:rounded-btn file:border-0 file:text-xs file:font-semibold file:bg-ink-800 file:text-paper hover:file:bg-ink-700">
                <p class="text-xs text-mist mt-1">JPG, PNG, WebP (Maksimal 10 MB)</p>
                @error('thumbnail') <p class="text-rose-300 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Tugmalar --}}
            <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-2 sm:gap-3 pt-4 border-t border-ink-border">
                <a href="{{ route('admin.videos.index') }}" class="ks-btn-ghost min-h-11 inline-flex items-center justify-center px-5 py-2.5 text-sm font-semibold">
                    Bekor qilish
                </a>
                <button type="submit" class="ks-btn-primary min-h-11 inline-flex items-center justify-center gap-2 px-6 py-2.5 text-sm font-bold">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z"/><path d="M17 21v-8H7v8M7 3v5h8"/></svg> O'zgarishlarni saqlash
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
