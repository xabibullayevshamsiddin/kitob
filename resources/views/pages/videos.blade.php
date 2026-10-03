@extends('layouts.app')

@section('title', 'Video darslar — ' . ($book ? $book->title : 'Barcha video darslar'))

@section('content')
<div class="max-w-6xl mx-auto space-y-6 pb-16">
    <!-- Header Navigation -->
    <div class="flex items-center justify-between flex-wrap gap-4 border-b border-ink-border pb-4">
        <div class="flex items-center gap-3">
            @if($book)
                <a href="{{ route('books.show', $book->slug) }}" 
                   class="p-2 rounded-btn bg-ink-900 border border-ink-border text-mist hover:text-paper hover:bg-ink-800 text-xs font-mono transition-all flex items-center gap-1.5">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                    <span class="hidden sm:inline">Kitob sahifasiga qaytish</span>
                </a>
            @else
                <a href="{{ route('books.catalog') }}" 
                   class="p-2 rounded-btn bg-ink-900 border border-ink-border text-mist hover:text-paper hover:bg-ink-800 text-xs font-mono transition-all flex items-center gap-1.5">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                    <span class="hidden sm:inline">Katalogga qaytish</span>
                </a>
            @endif
            <div>
                <h1 class="text-xl sm:text-2xl font-bold font-serif text-paper">Video darslar va sharhlar</h1>
                <p class="text-xs font-mono text-mist">
                    {{ $book ? '«' . $book->title . '» bo\'yicha video tahlillar to\'plami' : 'Platformadagi barcha video darslar va sharhlar to\'plami' }}
                </p>
            </div>
        </div>

        @if($book)
            <div class="flex items-center gap-2">
                <a href="{{ route('audio.show', $book->id) }}" class="px-3 py-1.5 rounded-btn bg-ink-900 border border-ink-border text-xs font-mono text-mist hover:text-amber-400 transition-colors flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 18v-6a9 9 0 0 1 18 0v6"/><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/></svg>
                    <span>Audio</span>
                </a>
                <a href="{{ route('quiz.show', $book->id) }}" class="px-3 py-1.5 rounded-btn bg-ink-900 border border-ink-border text-xs font-mono text-mist hover:text-amber-400 transition-colors flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>
                    <span>Test</span>
                </a>
            </div>
        @endif
    </div>

    @php
        $rawVideos = $book ? $book->videos : ($videos ?? collect());
    @endphp

    @if ($rawVideos->isEmpty())
        <div class="p-12 sm:p-16 rounded-panel bg-ink-900 border border-ink-border text-center space-y-3.5 shadow-soft">
            <div class="w-14 h-14 rounded-btn bg-ink-800 border border-ink-border text-amber-400 flex items-center justify-center mx-auto">
                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg>
            </div>
            <h2 class="text-base sm:text-lg font-bold font-serif text-paper">Hozircha video darslar mavjud emas</h2>
            <p class="text-xs text-mist font-mono max-w-md mx-auto leading-relaxed">
                Ustozlarimiz tomonidan video tahlillar tez orada tayyorlanadi va ushbu bo'limga qo'shiladi. Hozirda kitoblarimizni mutolaa qilishingiz yoki audiosini tinglashingiz mumkin.
            </p>
            <div class="pt-2 flex flex-wrap justify-center gap-2.5">
                @if($book)
                    <a href="{{ route('books.show', $book->slug) }}" 
                       class="ks-btn-primary text-xs py-2 px-4 inline-flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20 M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                        <span>Kitobni o'qish</span>
                    </a>
                    <a href="{{ route('audio.show', $book->id) }}" 
                       class="ks-btn-ghost text-xs py-2 px-4 inline-flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 18v-6a9 9 0 0 1 18 0v6"/><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/></svg>
                        <span>Audio eshitish</span>
                    </a>
                @else
                    <a href="{{ route('books.catalog') }}" 
                       class="ks-btn-primary text-xs py-2 px-4">
                        Kitoblar katalogi
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
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5"
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
            <div class="lg:col-span-8 space-y-3.5">
                <div class="rounded-panel bg-black overflow-hidden shadow-2xl border border-ink-border aspect-video relative flex items-center justify-center">
                    
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
                        <div class="text-center p-8 text-mist font-mono text-xs">
                            Video fayl topilmadi.
                        </div>
                    </template>

                </div>

                <!-- Active Video Meta -->
                <div class="p-4 sm:p-5 rounded-panel bg-ink-900 border border-ink-border space-y-1.5">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="px-2 py-0.5 rounded-pill bg-amber-500/10 border border-amber-500/25 text-amber-400 font-mono text-[10px] font-bold uppercase tracking-wider"
                              x-text="activeVideo ? (activeVideo.type === 'overview' ? 'Umumiy Tahlil' : (activeVideo.chapter ? activeVideo.chapter + '-Bob' : 'Video Dars')) : ''"></span>
                        
                        <template x-if="activeVideo && activeVideo.book_title">
                            <span class="px-2 py-0.5 rounded-pill bg-ink-800 border border-ink-border text-mist font-mono text-[10px]"
                                  x-text="'«' + activeVideo.book_title + '»'"></span>
                        </template>

                        <template x-if="activeVideo && activeVideo.duration">
                            <span class="text-xs font-mono text-mist" x-text="'Davomiyligi: ' + activeVideo.duration"></span>
                        </template>
                    </div>

                    <h2 class="text-lg sm:text-xl font-bold font-serif text-paper" x-text="activeVideo ? activeVideo.title : ''"></h2>
                    
                    @if($book)
                        <p class="text-xs font-mono text-mist">
                            «{{ $book->title }}» • Muallif: {{ $book->author }}
                        </p>
                    @else
                        <template x-if="activeVideo && activeVideo.book_title">
                            <p class="text-xs font-mono text-mist" x-text="'Kitob: ' + activeVideo.book_title + (activeVideo.book_author ? ' • Muallif: ' + activeVideo.book_author : '')"></p>
                        </template>
                        <template x-if="activeVideo && !activeVideo.book_title">
                            <p class="text-xs font-mono text-mist">Mustaqil ta'limiy video dars</p>
                        </template>
                    @endif
                </div>
            </div>

            <!-- Video Playlist Sidebar (Col 4) -->
            <div class="lg:col-span-4 space-y-2.5">
                <div class="flex items-center justify-between px-1">
                    <h3 class="text-xs font-bold font-mono uppercase tracking-wider text-mist">
                        Darslar ro'yxati ({{ $rawVideos->count() }})
                    </h3>
                </div>

                <div class="space-y-2 max-h-[580px] overflow-y-auto pr-1">
                    <template x-for="v in videos" :key="v.id">
                        <button type="button"
                                @click="selectVideo(v)"
                                class="w-full p-2.5 rounded-panel border text-left transition-all duration-200 flex gap-2.5 group"
                                :class="activeVideo && activeVideo.id === v.id 
                                    ? 'bg-amber-500/10 border-amber-400/40 text-paper shadow-sm' 
                                    : 'bg-ink-900 border-ink-border hover:border-amber-400/20 text-mist hover:text-paper'">
                            
                            <!-- Thumbnail -->
                            <div class="w-20 h-14 rounded-btn overflow-hidden shrink-0 relative bg-black border border-ink-border">
                                <img :src="v.thumbnail_url" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" :alt="v.title" loading="lazy">
                                <div class="absolute inset-0 bg-black/40 flex items-center justify-center">
                                    <span class="w-5 h-5 rounded-full bg-amber-400 text-ink-950 flex items-center justify-center text-[9px] font-bold">
                                        ▶
                                    </span>
                                </div>
                            </div>

                            <!-- Details -->
                            <div class="flex-1 min-w-0 space-y-0.5">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-[10px] font-mono uppercase"
                                          :class="activeVideo && activeVideo.id === v.id ? 'text-amber-400 font-bold' : 'text-mist'"
                                          x-text="v.type === 'overview' ? 'Umumiy' : (v.chapter ? v.chapter + '-Bob' : 'Video')"></span>
                                    <template x-if="v.book_title">
                                        <span class="text-[9px] font-mono text-mist truncate max-w-[120px]" x-text="'• ' + v.book_title"></span>
                                    </template>
                                </div>
                                <h4 class="text-xs font-medium truncate leading-snug font-sans text-paper" x-text="v.title"></h4>
                                <template x-if="v.duration">
                                    <p class="text-[10px] font-mono text-mist" x-text="v.duration"></p>
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
