@extends('admin.layouts.app')
@section('title', "Yangi video dars qo'shish")
@section('breadcrumb', 'Videolar')

@section('content')
<div class="max-w-3xl mx-auto space-y-5">

    {{-- Page header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <span class="ks-eyebrow">Videolar</span>
            <h1 class="text-xl font-bold font-serif text-paper mt-0.5">Yangi video dars qo'shish</h1>
            <p class="text-xs text-mist font-mono mt-0.5">Video fayl yoki YouTube/tashqi video havolasini kitobga bog'lash</p>
        </div>
        <a href="{{ route('admin.videos.index') }}" class="ks-btn-ghost py-2 px-4 text-xs shrink-0 self-start sm:self-auto">
            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
            Orqaga
        </a>
    </div>

    <div class="ks-panel p-5 sm:p-6">
        <form action="{{ route('admin.videos.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            {{-- 1. Bog'langan kitob --}}
            <div>
                <label class="block text-xs font-semibold text-paper mb-1">Qaysi kitobga bog'lansin? <span class="text-mist font-normal font-mono text-[11px]">(ixtiyoriy)</span></label>
                <select name="book_id" class="ks-input">
                    <option value="">-- Alohida video (hech qaysi kitobga bog'lanmagan / Mustaqil) --</option>
                    @foreach($books as $b)
                        <option value="{{ $b->id }}" {{ (old('book_id', $selectedBookId) == $b->id) ? 'selected' : '' }}>
                            {{ $b->week_number ? "{$b->week_number}-Hafta: " : '' }}{{ $b->title }} ({{ $b->author }})
                        </option>
                    @endforeach
                </select>
                <p class="text-[11px] text-mist mt-1.5">Agar kitob tanlamasangiz, video mustaqil video dars sifatida saqlanadi.</p>
                @error('book_id') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- 2. Video sarlavhasi --}}
            <div>
                <label class="block text-xs font-semibold text-paper mb-1">Video sarlavhasi <span class="text-rose-400">*</span></label>
                <input type="text" name="title" value="{{ old('title') }}" required placeholder="Masalan: Kitob tahlili va amaliy qo'llanishi" class="ks-input">
                @error('title') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- 3. Video turi va Bob raqami --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4" x-data="{ videoType: '{{ old('type', 'overview') }}' }">
                <div>
                    <label class="block text-xs font-semibold text-paper mb-1">Video turi <span class="text-rose-400">*</span></label>
                    <select name="type" x-model="videoType" class="ks-input">
                        <option value="overview">Umumiy sharh / Tahlil videosi</option>
                        <option value="chapter">Muayyan bob videosi</option>
                    </select>
                </div>

                <div x-show="videoType === 'chapter'" style="display: none;">
                    <label class="block text-xs font-semibold text-paper mb-1">Bob raqami</label>
                    <input type="number" name="chapter_number" value="{{ old('chapter_number', 1) }}" min="1" class="ks-input font-mono">
                </div>
            </div>

            {{-- 4. Davomiyligi --}}
            <div>
                <label class="block text-xs font-semibold text-paper mb-1">Davomiyligi (daqiqa hisobida)</label>
                <input type="number" step="0.5" name="duration" value="{{ old('duration') }}" placeholder="Masalan: 25.5" class="ks-input font-mono">
            </div>

            {{-- 5. Video manbasi --}}
            <div x-data="{
                mode: 'url',
                videoFileName: '',
                handleVideo(e) {
                    const f = e.target.files[0];
                    this.videoFileName = f ? f.name + ' (' + (f.size / (1024*1024)).toFixed(2) + ' MB)' : '';
                }
            }" class="space-y-3">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                    <label class="block text-xs font-semibold text-paper">Video manbasi <span class="text-rose-400">*</span></label>
                    <div class="flex flex-wrap items-center gap-1.5 font-mono text-xs">
                        <button type="button" @click="mode = 'url'"
                                class="min-h-11 px-3 py-1 rounded-badge border transition-colors"
                                :class="mode === 'url' ? 'bg-amber-500 text-ink-950 border-amber-500 font-bold' : 'border-ink-border text-mist hover:text-paper'">
                            Havola (YouTube / CDN / MP4)
                        </button>
                        <button type="button" @click="mode = 'file'"
                                class="min-h-11 px-3 py-1 rounded-badge border transition-colors"
                                :class="mode === 'file' ? 'bg-amber-500 text-ink-950 border-amber-500 font-bold' : 'border-ink-border text-mist hover:text-paper'">
                            Video fayl (200MB)
                        </button>
                    </div>
                </div>

                {{-- URL --}}
                <div x-show="mode === 'url'">
                    <input type="url" name="video_url" value="{{ old('video_url') }}" placeholder="https://www.youtube.com/watch?v=... yoki to'g'ridan-to'g'ri MP4 havolasi" class="ks-input font-mono text-sm">
                    <span class="text-[11px] text-mist mt-1.5 block">YouTube, Vimeo yoki bulutli serverdagi video havolasi</span>
                </div>

                {{-- Fayl yuklash --}}
                <div x-show="mode === 'file'" style="display: none;">
                    <label for="video_file" class="group flex flex-col items-center justify-center w-full min-h-[140px] p-6 rounded-panel border-2 border-dashed border-ink-border bg-ink-950/60 hover:border-amber-500 hover:bg-amber-500/5 transition-all cursor-pointer">
                        <svg class="w-8 h-8 mb-2 text-mist group-hover:text-amber-400 group-hover:scale-110 transition-all" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="m22 8-6 4 6 4V8Z"/><rect width="14" height="12" x="2" y="6" rx="2" ry="2"/></svg>
                        <span class="text-xs font-bold text-paper group-hover:text-amber-400 transition-colors">
                            Video faylni tanlang (MP4, WebM, MOV)
                        </span>
                        <span class="text-[11px] text-amber-400 font-semibold mt-1 font-mono">200 MB gacha ruxsat berilgan</span>
                        <span x-show="videoFileName" x-text="videoFileName" class="mt-2 text-xs font-mono font-bold text-emerald-400 bg-emerald-500/10 border border-emerald-500/20 px-3 py-1 rounded-badge"></span>
                        <input type="file" name="video_file" id="video_file" accept=".mp4,.webm,.mov,.avi,.mkv" class="hidden" @change="handleVideo($event)">
                    </label>
                </div>

                @error('video_file') <p class="text-rose-400 text-xs">{{ $message }}</p> @enderror
                @error('video_url') <p class="text-rose-400 text-xs">{{ $message }}</p> @enderror
            </div>

            {{-- 6. Thumbnail --}}
            <div x-data="{
                thumbPreview: null,
                handleThumb(e) {
                    const f = e.target.files[0];
                    this.thumbPreview = f ? URL.createObjectURL(f) : null;
                }
            }">
                <label class="block text-xs font-semibold text-paper mb-1">Video muqova rasmi <span class="text-mist font-normal font-mono text-[11px]">(thumbnail — ixtiyoriy)</span></label>
                <div class="flex items-center gap-4">
                    <template x-if="thumbPreview">
                        <img :src="thumbPreview" class="w-24 h-16 object-cover rounded-card border border-ink-border shrink-0">
                    </template>
                    <label class="cursor-pointer px-4 py-2 bg-ink-800 hover:bg-ink-700 border border-ink-border text-paper text-xs font-bold rounded-btn transition-colors">
                        <span>Rasm tanlash</span>
                        <input type="file" name="thumbnail" accept="image/*" class="hidden" @change="handleThumb($event)">
                    </label>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-ink-border">
                <a href="{{ route('admin.videos.index') }}" class="ks-btn-ghost py-2 px-4 text-xs">Bekor qilish</a>
                <button type="submit" class="ks-btn-primary py-2 px-6 text-xs font-bold">Saqlash va Bog'lash</button>
            </div>
        </form>
    </div>
</div>
@endsection
