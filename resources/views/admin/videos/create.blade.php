@extends('admin.layouts.app')
@section('title', 'Yangi video dars qo\'shish')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-black text-slate-800 dark:text-white flex items-center gap-2">
                <span>🎥</span> Yangi video dars qo'shish
            </h2>
            <p class="text-sm text-slate-500">Video fayl yoki YouTube/tashqi video havolasini kitobga bog'lash</p>
        </div>
        <a href="{{ route('admin.videos.index') }}" class="px-4 py-2 bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl text-sm font-semibold hover:bg-slate-300 dark:hover:bg-slate-600 transition-colors">
            ← Orqaga
        </a>
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 sm:p-8 shadow-sm">
        <form action="{{ route('admin.videos.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            {{-- 1. Bog'langan kitob --}}
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">Qaysi kitobga bog'lansin? *</label>
                <select name="book_id" required class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    <option value="">Kitobni tanlang...</option>
                    @foreach($books as $b)
                        <option value="{{ $b->id }}" {{ (old('book_id', $selectedBookId) == $b->id) ? 'selected' : '' }}>
                            {{ $b->week_number ? "{$b->week_number}-Hafta: " : '' }}{{ $b->title }} ({{ $b->author }})
                        </option>
                    @endforeach
                </select>
                @error('book_id') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- 2. Video sarlavhasi --}}
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">Video sarlavhasi *</label>
                <input type="text" name="title" value="{{ old('title') }}" required placeholder="Masalan: Kitob tahlili va amaliy qo'llanishi"
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                @error('title') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- 3. Video turi va Bob raqami --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4" x-data="{ videoType: '{{ old('type', 'overview') }}' }">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">Video turi *</label>
                    <select name="type" x-model="videoType" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                        <option value="overview">📺 Umumiy sharh / Tahlil videosi</option>
                        <option value="chapter">📖 Muayyan bob videosi</option>
                    </select>
                </div>

                <div x-show="videoType === 'chapter'" style="display: none;">
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">Bob raqami</label>
                    <input type="number" name="chapter_number" value="{{ old('chapter_number', 1) }}" min="1"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>
            </div>

            {{-- 4. Davomiyligi --}}
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">Davomiyligi (daqiqa hisobida)</label>
                <input type="number" step="0.5" name="duration" value="{{ old('duration') }}" placeholder="Masalan: 25.5"
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>

            {{-- 5. Video manbasi: Fayl yoki Havola --}}
            <div x-data="{
                mode: 'url',
                videoFileName: '',
                handleVideo(e) {
                    const f = e.target.files[0];
                    this.videoFileName = f ? f.name + ' (' + (f.size / (1024*1024)).toFixed(2) + ' MB)' : '';
                }
            }" class="space-y-3">
                <div class="flex items-center justify-between">
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Video manbasi *</label>
                    <div class="flex items-center gap-2 text-xs">
                        <button type="button" @click="mode = 'url'" :class="mode === 'url' ? 'bg-indigo-600 text-white font-bold' : 'text-slate-400 hover:text-white'" class="px-2.5 py-1 rounded-lg transition-colors">
                            🔗 Havola (YouTube / CDN / MP4)
                        </button>
                        <button type="button" @click="mode = 'file'" :class="mode === 'file' ? 'bg-indigo-600 text-white font-bold' : 'text-slate-400 hover:text-white'" class="px-2.5 py-1 rounded-lg transition-colors">
                            📁 Video fayl yuklash (200MB)
                        </button>
                    </div>
                </div>

                {{-- URL input tab --}}
                <div x-show="mode === 'url'">
                    <input type="url" name="video_url" value="{{ old('video_url') }}" placeholder="https://www.youtube.com/watch?v=... yoki to'g'ridan-to'g'ri MP4 havolasi"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    <span class="text-[11px] text-slate-400 mt-1 block">YouTube, Vimeo yoki bulutli serverdagi video havolasi</span>
                </div>

                {{-- File upload tab --}}
                <div x-show="mode === 'file'" style="display: none;">
                    <label for="video_file" class="group flex flex-col items-center justify-center w-full min-h-[140px] p-6 rounded-2xl border-2 border-dashed border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-900/40 hover:border-cyan-500 hover:bg-cyan-500/5 transition-all cursor-pointer">
                        <span class="text-3xl mb-2 group-hover:scale-110 transition-transform">🎥</span>
                        <span class="text-xs font-bold text-slate-700 dark:text-slate-200 group-hover:text-cyan-500 transition-colors">
                            Video faylni tanlang (MP4, WebM, MOV)
                        </span>
                        <span class="text-[11px] text-amber-500 font-semibold mt-1">200 MB gacha ruxsat berilgan</span>
                        <span x-show="videoFileName" x-text="videoFileName" class="mt-2 text-xs font-bold text-emerald-500 bg-emerald-500/10 px-3 py-1 rounded-lg"></span>
                        <input type="file" name="video_file" id="video_file" accept=".mp4,.webm,.mov,.avi,.mkv" class="hidden" @change="handleVideo($event)">
                    </label>
                </div>

                @error('video_file') <p class="text-rose-500 text-xs">{{ $message }}</p> @enderror
                @error('video_url') <p class="text-rose-500 text-xs">{{ $message }}</p> @enderror
            </div>

            {{-- 6. Video muqova rasmi (Thumbnail) --}}
            <div x-data="{
                thumbPreview: null,
                handleThumb(e) {
                    const f = e.target.files[0];
                    this.thumbPreview = f ? URL.createObjectURL(f) : null;
                }
            }">
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">Video muqova rasmi (Thumbnail - ixtiyoriy)</label>
                <div class="flex items-center gap-4">
                    <template x-if="thumbPreview">
                        <img :src="thumbPreview" class="w-24 h-16 object-cover rounded-xl border border-slate-600 shadow shrink-0">
                    </template>
                    <label class="cursor-pointer px-4 py-2 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 text-xs font-bold rounded-xl transition-colors">
                        <span>Rasm tanlash</span>
                        <input type="file" name="thumbnail" accept="image/*" class="hidden" @change="handleThumb($event)">
                    </label>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-700">
                <a href="{{ route('admin.videos.index') }}" class="px-5 py-2.5 rounded-xl text-slate-600 dark:text-slate-400 font-semibold text-sm hover:bg-slate-100 dark:hover:bg-slate-700">Bekor qilish</a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 text-white font-bold text-sm hover:bg-indigo-500 transition-colors shadow-lg shadow-indigo-600/30">Saqlash va Bog'lash</button>
            </div>
        </form>
    </div>
</div>
@endsection
