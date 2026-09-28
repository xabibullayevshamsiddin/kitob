@extends('layouts.app')

@section('title', 'Video darslar — ' . ($book ? $book->title : 'Barcha video darslar'))

@section('content')
<div class="max-w-6xl mx-auto space-y-8 pb-16">
    <!-- Header Navigation -->
    <div class="flex items-center justify-between flex-wrap gap-4 border-b border-white/10 pb-4">
        <div class="flex items-center gap-3">
            @if($book)
                <a href="{{ route('books.show', $book->slug) }}" 
                   class="px-4 py-2 rounded-xl bg-ink-900 border border-white/10 text-slate-300 hover:text-white hover:border-amber-400/30 text-xs font-semibold transition-all flex items-center gap-2">
                    <span>← Kitob sahifasiga qaytish</span>
                </a>
            @else
                <a href="{{ route('books.catalog') }}" 
                   class="px-4 py-2 rounded-xl bg-ink-900 border border-white/10 text-slate-300 hover:text-white hover:border-amber-400/30 text-xs font-semibold transition-all flex items-center gap-2">
                    <span>← Katalogga qaytish</span>
                </a>
            @endif
            <div>
                <h1 class="text-xl sm:text-2xl font-black text-white">Video sharhlar va darslar</h1>
                <p class="text-xs text-slate-400">
                    {{ $book ? '«' . $book->title . '» bo\'yicha video darslar to\'plami' : 'Platformadagi barcha video darslar va sharhlar to\'plami' }}
                </p>
            </div>
        </div>

        @if($book)
            <div class="flex items-center gap-2">
                <a href="{{ route('audio.show', $book->id) }}" class="px-3.5 py-1.5 rounded-lg bg-white/5 border border-white/10 text-xs text-slate-300 hover:text-amber-400 transition-colors">
                    🎧 Audio
                </a>
                <a href="{{ route('quiz.show', $book->id) }}" class="px-3.5 py-1.5 rounded-lg bg-white/5 border border-white/10 text-xs text-slate-300 hover:text-amber-400 transition-colors">
                    🧠 Test
                </a>
            </div>
        @endif
    </div>

    @php
        $rawVideos = $book ? $book->videos : ($videos ?? collect());
    @endphp

    @if ($rawVideos->isEmpty())
        <div class="p-12 sm:p-16 rounded-3xl bg-ink-900/80 border border-white/10 text-center space-y-4 shadow-card-depth">
            <div class="w-16 h-16 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-400 flex items-center justify-center text-3xl mx-auto">
                🎬
            </div>
            <h2 class="text-lg font-bold text-white">Hozircha video darslar mavjud emas</h2>
            <p class="text-xs text-slate-400 max-w-md mx-auto leading-relaxed">
                Ustozlarimiz tomonidan video tahlillar tez orada tayyorlanadi va ushbu bo'limga qo'shiladi. Hozirda kitoblarimizni o'qishingiz yoki audio shaklini tinglashingiz mumkin.
            </p>
            <div class="pt-2 flex flex-wrap justify-center gap-3">
                @if($book)
                    <a href="{{ route('books.show', $book->slug) }}" 
                       class="px-5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-ink-950 font-bold text-xs uppercase tracking-wider transition-all">
                        📖 Kitobni o'qish
                    </a>
                    <a href="{{ route('audio.show', $book->id) }}" 
                       class="px-5 py-2.5 rounded-xl bg-ink-800 hover:bg-ink-700 text-slate-200 border border-white/10 text-xs font-semibold transition-all">
                        🎧 Audio eshitish
                    </a>
                @else
                    <a href="{{ route('books.catalog') }}" 
                       class="px-5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-ink-950 font-bold text-xs uppercase tracking-wider transition-all">
                        📚 Kitoblar katalogi
                    </a>
                @endif
            </div>
        </div>
    @else
        @php
            $videosList = $rawVideos->map(function ($v) {
                return [
                    'id'            => $v->id,
                    'title'         => $v->title,
                    'type'          => $v->type,
                    'chapter'       => $v->chapter_number,
                    'duration'      => $v->duration ? round($v->duration / 60) . ' daqiqa' : null,
                    'stream_url'    => $v->stream_url,
                    'embed_url'     => $v->embed_url,
                    'thumbnail_url' => $v->thumbnail_url,
                    'book_title'    => $v->book ? $v->book->title : null,
                    'book_author'   => $v->book ? $v->book->author : null,
                ];
            });
            $firstVideo = $videosList->first();
        @endphp

        <!-- Interactive Video Theater with Alpine.js -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6"
             x-data="{
                 activeVideo: {{ Js::from($firstVideo) }},
                 videos: {{ Js::from($videosList) }},
                 selectVideo(v) {
                     this.activeVideo = v;
                     this.$nextTick(() => {
                         if (this.$refs.videoPlayer) {
                             this.$refs.videoPlayer.load();
                             this.$refs.videoPlayer.play().catch(() => {});
                         }
                     });
                     window.scrollTo({ top: 0, behavior: 'smooth' });
                 }
             }">

            <!-- Main Video Player Stage (Col 8) -->
            <div class="lg:col-span-8 space-y-4">
                <div class="rounded-3xl bg-black overflow-hidden shadow-2xl border border-white/10 aspect-video relative flex items-center justify-center">
                    
                    <!-- YouTube / Vimeo Embed Iframe -->
                    <template x-if="activeVideo && activeVideo.embed_url">
                        <iframe :src="activeVideo.embed_url + '?autoplay=1'" 
                                class="w-full h-full border-0" 
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                allowfullscreen></iframe>
                    </template>

                    <!-- Direct Video File Player (MP4 / WebM / HLS) -->
                    <template x-if="activeVideo && !activeVideo.embed_url && activeVideo.stream_url">
                        <video :key="activeVideo.id"
                               x-ref="videoPlayer"
                               :src="activeVideo.stream_url" 
                               :poster="activeVideo.thumbnail_url"
                               controls 
                               playsinline
                               preload="metadata"
                               controlsList="nodownload"
                               class="w-full h-full object-contain bg-black">
                            <source :src="activeVideo.stream_url" :type="activeVideo.stream_url.endsWith('.webm') ? 'video/webm' : 'video/mp4'">
                            Brauzeringiz ushbu videoni qo'llab-quvvatlamaydi.
                        </video>
                    </template>

                    <!-- Fallback / Empty -->
                    <template x-if="!activeVideo || (!activeVideo.embed_url && !activeVideo.stream_url)">
                        <div class="text-center p-8 text-slate-500 text-sm">
                            Video fayl topilmadi.
                        </div>
                    </template>

                </div>

                <!-- Active Video Meta -->
                <div class="p-6 rounded-2xl bg-ink-900/80 border border-white/10 space-y-2">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="px-2.5 py-1 rounded bg-amber-500/10 border border-amber-500/20 text-amber-400 font-mono text-[11px] font-bold uppercase tracking-wider"
                              x-text="activeVideo ? (activeVideo.type === 'overview' ? 'Umumiy Tahlil' : (activeVideo.chapter ? activeVideo.chapter + '-Bob' : 'Video Dars')) : ''"></span>
                        
                        <template x-if="activeVideo && activeVideo.book_title">
                            <span class="px-2.5 py-1 rounded bg-indigo-500/10 border border-indigo-500/20 text-indigo-300 font-mono text-[11px] font-medium"
                                  x-text="'«' + activeVideo.book_title + '»'"></span>
                        </template>

                        <template x-if="activeVideo && activeVideo.duration">
                            <span class="text-xs text-slate-400" x-text="'Davomiyligi: ' + activeVideo.duration"></span>
                        </template>
                    </div>

                    <h2 class="text-xl sm:text-2xl font-bold text-white" x-text="activeVideo ? activeVideo.title : ''"></h2>
                    
                    @if($book)
                        <p class="text-xs text-slate-400">
                            «{{ $book->title }}» • Muallif: {{ $book->author }}
                        </p>
                    @else
                        <template x-if="activeVideo && activeVideo.book_title">
                            <p class="text-xs text-slate-400" x-text="'Kitob: ' + activeVideo.book_title + (activeVideo.book_author ? ' • Muallif: ' + activeVideo.book_author : '')"></p>
                        </template>
                        <template x-if="activeVideo && !activeVideo.book_title">
                            <p class="text-xs text-slate-400">Mustaqil ta'limiy video dars</p>
                        </template>
                    @endif
                </div>
            </div>

            <!-- Video Playlist Sidebar (Col 4) -->
            <div class="lg:col-span-4 space-y-3">
                <div class="flex items-center justify-between px-1">
                    <h3 class="text-sm font-bold text-white font-mono uppercase tracking-wider">
                        Darslar ro'yxati ({{ $rawVideos->count() }})
                    </h3>
                </div>

                <div class="space-y-2.5 max-h-[580px] overflow-y-auto pr-1">
                    <template x-for="v in videos" :key="v.id">
                        <button type="button"
                                @click="selectVideo(v)"
                                class="w-full p-3 rounded-2xl border text-left transition-all duration-200 flex gap-3 group"
                                :class="activeVideo && activeVideo.id === v.id 
                                    ? 'bg-amber-500/10 border-amber-500/30 text-white shadow-md' 
                                    : 'bg-ink-900/60 border-white/5 hover:border-white/15 text-slate-300 hover:text-white'">
                            
                            <!-- Thumbnail -->
                            <div class="w-24 h-16 rounded-xl overflow-hidden shrink-0 relative bg-black border border-white/10">
                                <img :src="v.thumbnail_url" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" :alt="v.title" loading="lazy">
                                <div class="absolute inset-0 bg-black/40 flex items-center justify-center">
                                    <span class="w-6 h-6 rounded-full bg-white/80 text-black flex items-center justify-center text-[10px] font-bold">
                                        ▶
                                    </span>
                                </div>
                            </div>

                            <!-- Details -->
                            <div class="flex-1 min-w-0 space-y-1">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-[10px] font-mono uppercase"
                                          :class="activeVideo && activeVideo.id === v.id ? 'text-amber-400 font-bold' : 'text-slate-500'"
                                          x-text="v.type === 'overview' ? 'Umumiy' : (v.chapter ? v.chapter + '-Bob' : 'Video')"></span>
                                    <template x-if="v.book_title">
                                        <span class="text-[9px] text-slate-400 truncate max-w-[120px]" x-text="'• ' + v.book_title"></span>
                                    </template>
                                </div>
                                <h4 class="text-xs font-semibold truncate leading-snug" x-text="v.title"></h4>
                                <template x-if="v.duration">
                                    <p class="text-[10px] text-slate-500" x-text="v.duration"></p>
                                </template>
                            </div>

                        </button>
                    </template>
                </div>
            </div>

        </div>
    @endif

    {{-- Video dars tomoshasi vaqtini sanash va ball/tanga taqdim etish --}}
    @auth
        @include('components.reading-tracker', [
            'bookId'    => $book ? $book->id : null,
            'chapterId' => null,
            'pageType'  => 'video'
        ])
    @endauth
</div>
@endsection
