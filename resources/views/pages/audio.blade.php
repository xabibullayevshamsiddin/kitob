@extends('layouts.app')

@section('title', 'Audio mutolaa — ' . $book->title)

@section('content')
<div class="max-w-4xl mx-auto space-y-6 pb-16">
    <div class="flex items-center gap-3">
        <a href="{{ route('books.show', $book->slug) }}" class="p-2 rounded-btn bg-ink-900 border border-ink-border text-mist hover:text-paper hover:bg-ink-800 transition-colors">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-xl sm:text-2xl font-bold font-serif text-paper">Audio mutolaa</h1>
            <p class="text-xs font-mono text-mist">{{ $book->title }} • {{ $book->author }}</p>
        </div>
    </div>

    @if ($book->audios->isEmpty())
        <div class="p-12 rounded-panel bg-ink-900 border border-ink-border text-center space-y-3">
            <div class="w-12 h-12 mx-auto rounded-btn bg-ink-800 border border-ink-border flex items-center justify-center text-amber-400">
                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 18v-6a9 9 0 0 1 18 0v6"/><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/></svg>
            </div>
            <h2 class="text-base font-bold text-paper font-serif">Audio fayllar hozircha yuklanmagan</h2>
            <p class="text-xs text-mist font-mono max-w-sm mx-auto">Bu kitob uchun audio yozuvlar tez orada qo'shiladi.</p>
            @if ($book->chapters->first())
                <a href="{{ route('reader.show', ['book' => $book->id, 'chapter' => $book->chapters->first()->id]) }}"
                   class="ks-btn-primary text-xs py-2 px-4 inline-flex items-center gap-1.5 mt-2">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20 M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                    <span>Matnni o'qish</span>
                </a>
            @endif
        </div>
    @else
        <!-- Audio Player Card -->
        <div class="p-6 sm:p-8 rounded-panel bg-ink-900 border border-ink-border text-paper shadow-soft text-center space-y-5 relative overflow-hidden"
             x-data="{
                 currentId: {{ $book->audios->first()->id }},
                 isPlaying: false,
                 currentTime: 0,
                 duration: {{ (int) ($book->audios->first()->duration ?? 0) }},
                 speed: 1,
                 speeds: [0.75, 1, 1.25, 1.5, 2],
                 format(sec) {
                     if (!sec || sec < 0) sec = 0;
                     const m = Math.floor(sec / 60);
                     const s = Math.floor(sec % 60).toString().padStart(2, '0');
                     return `${m}:${s}`;
                 },
                 play(id, url, dur) {
                     const el = this.$refs.player;
                     if (this.currentId === id) {
                         this.isPlaying ? el.pause() : el.play();
                         return;
                     }
                     this.currentId = id;
                     this.duration = dur;
                     this.currentTime = 0;
                     el.src = url;
                     el.play();
                 },
                 togglePlay() {
                     const el = this.$refs.player;
                     this.isPlaying ? el.pause() : el.play();
                 },
                 seek(t) {
                     this.$refs.player.currentTime = t;
                 },
                 changeSpeed() {
                     let idx = this.speeds.indexOf(this.speed);
                     this.speed = this.speeds[(idx + 1) % this.speeds.length];
                     this.$refs.player.playbackRate = this.speed;
                 }
             }">

            <audio x-ref="player" preload="metadata"
                   src="{{ $book->audios->first()->file_url }}"
                   @play="isPlaying = true"
                   @pause="isPlaying = false"
                   @timeupdate="currentTime = $event.target.currentTime"
                   @loadedmetadata="duration = $event.target.duration"></audio>

            <div class="w-32 h-44 mx-auto rounded-panel overflow-hidden shadow-2xl border border-ink-border">
                <img src="{{ $book->cover_url }}" class="w-full h-full object-cover" alt="{{ $book->title }}">
            </div>

            <div>
                <h2 class="text-xl sm:text-2xl font-bold font-serif text-paper">{{ $book->title }}</h2>
                <p class="text-xs font-mono text-mist mt-1">Muallif: {{ $book->author }} • {{ $book->audios->count() }} ta audio bob</p>
            </div>

            <!-- Progress scrubber -->
            <div class="max-w-md mx-auto space-y-1">
                <input type="range" min="0" :max="duration || 1" :value="currentTime"
                       @input="seek($event.target.value)"
                       class="w-full accent-amber-400 h-1.5 bg-ink-800 rounded-pill cursor-pointer">
                <div class="flex justify-between text-[11px] font-mono text-mist">
                    <span x-text="format(currentTime)">0:00</span>
                    <span x-text="format(duration)">0:00</span>
                </div>
            </div>

            <!-- Playback Buttons -->
            <div class="flex items-center justify-center gap-5">
                <button @click="seek(Math.max(0, currentTime - 10))" class="text-mist hover:text-paper text-xs font-mono p-2 transition-colors flex items-center gap-1" title="10 soniya orqaga">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                    <span>10s</span>
                </button>

                <button @click="togglePlay()" class="w-12 h-12 rounded-full bg-amber-400 hover:bg-amber-300 text-ink-950 flex items-center justify-center font-bold shadow-lg transform active:scale-95 transition-all">
                    <template x-if="!isPlaying">
                        <svg class="w-5 h-5 ml-0.5 fill-current" viewBox="0 0 24 24"><polygon points="6 3 20 12 6 21 6 3"/></svg>
                    </template>
                    <template x-if="isPlaying">
                        <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/></svg>
                    </template>
                </button>

                <button @click="seek(Math.min(duration, currentTime + 10))" class="text-mist hover:text-paper text-xs font-mono p-2 transition-colors flex items-center gap-1" title="10 soniya oldinga">
                    <span>10s</span>
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 1 1-9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/></svg>
                </button>

                <button @click="changeSpeed()" class="px-2.5 py-1 rounded-btn bg-ink-800 hover:bg-ink-700/60 border border-ink-border text-xs font-mono font-bold text-amber-400 transition-colors">
                    <span x-text="`${speed}x`"></span>
                </button>
            </div>
        </div>

        <!-- Track List -->
        <div class="bg-ink-900 rounded-panel border border-ink-border divide-y divide-ink-border overflow-hidden">
            @foreach ($book->audios as $i => $audio)
                <button @click="play({{ $audio->id }}, '{{ $audio->file_url }}', {{ (int) ($audio->duration ?? 0) }})"
                        class="w-full p-3.5 sm:px-5 flex items-center justify-between hover:bg-ink-800/40 transition-colors text-left"
                        :class="currentId === {{ $audio->id }} ? 'bg-amber-500/10 border-l-2 border-amber-400' : ''">
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="w-7 h-7 rounded-btn bg-ink-800 border border-ink-border text-mist font-mono flex items-center justify-center text-xs shrink-0"
                              :class="currentId === {{ $audio->id }} ? 'text-amber-400 border-amber-400/30' : ''">
                            {{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}
                        </span>
                        <div class="min-w-0">
                            <span class="text-xs sm:text-sm font-semibold text-paper truncate block"
                                  :class="currentId === {{ $audio->id }} ? 'text-amber-400' : ''">{{ $audio->title ?? 'Audio ' . ($i + 1) }}</span>
                            @if ($audio->chapter)
                                <span class="text-[11px] font-mono text-mist">{{ $audio->chapter->title }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="flex items-center gap-3 shrink-0">
                        @if ($audio->duration)
                            <span class="text-[11px] font-mono text-mist">{{ floor($audio->duration / 60) }}:{{ str_pad($audio->duration % 60, 2, '0', STR_PAD_LEFT) }}</span>
                        @endif
                        <span class="text-amber-400 text-xs" x-show="currentId === {{ $audio->id }} && isPlaying">
                            <svg class="w-4 h-4 animate-pulse" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/><path d="M15.54 8.46a5 5 0 0 1 0 7.07"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14"/></svg>
                        </span>
                    </div>
                </button>
            @endforeach
        </div>
    @endif

    <!-- Reading Tracker & Inactivity Modal -->
    @include('components.reading-tracker', [
        'bookId' => $book->id,
        'chapterId' => null,
        'pageType' => 'audio'
    ])

</div>
@endsection
