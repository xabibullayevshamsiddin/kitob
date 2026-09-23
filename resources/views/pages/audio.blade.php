@extends('layouts.app')

@section('title', 'Audio mutolaa - ' . $book->title)

@section('content')
<div class="max-w-4xl mx-auto space-y-8 pb-16">
    <div class="flex items-center gap-3">
        <a href="{{ route('books.show', $book->slug) }}" class="p-2 rounded-xl text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800">
            ← Orqaga
        </a>
        <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white">Audio kitob</h1>
    </div>

    @if ($book->audios->isEmpty())
        <div class="p-12 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-soft text-center space-y-3">
            <span class="text-5xl block">🎧</span>
            <h2 class="text-base font-bold text-slate-900 dark:text-white">Audio fayllar hozircha yuklanmagan</h2>
            <p class="text-xs text-slate-400">Bu kitob uchun audio yozuvlar tez orada qo'shiladi.</p>
            @if ($book->chapters->first())
                <a href="{{ route('reader.show', ['book' => $book->id, 'chapter' => $book->chapters->first()->id]) }}"
                   class="inline-block px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs rounded-xl shadow-md transition-all">
                    📖 Matnni o'qish
                </a>
            @endif
        </div>
    @else
        <!-- Audio Player Card -->
        <div class="p-8 rounded-3xl bg-gradient-to-br from-indigo-950 via-slate-900 to-slate-900 border border-indigo-800/40 text-white shadow-2xl text-center space-y-6 relative overflow-hidden"
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

            <div class="w-36 h-52 mx-auto rounded-2xl overflow-hidden shadow-2xl ring-4 ring-white/10">
                <img src="{{ $book->cover_url }}" class="w-full h-full object-cover" alt="{{ $book->title }}">
            </div>

            <div>
                <h2 class="text-2xl font-black">{{ $book->title }}</h2>
                <p class="text-xs text-indigo-300 mt-1">Muallif: {{ $book->author }} • {{ $book->audios->count() }} ta audio bob</p>
            </div>

            <!-- Progress scrubber -->
            <div class="max-w-md mx-auto space-y-1">
                <input type="range" min="0" :max="duration || 1" :value="currentTime"
                       @input="seek($event.target.value)"
                       class="w-full accent-amber-500 h-1.5 bg-white/20 rounded-lg cursor-pointer">
                <div class="flex justify-between text-[11px] text-slate-400">
                    <span x-text="format(currentTime)">0:00</span>
                    <span x-text="format(duration)">0:00</span>
                </div>
            </div>

            <!-- Playback Buttons -->
            <div class="flex items-center justify-center gap-6">
                <button @click="seek(Math.max(0, currentTime - 10))" class="text-slate-400 hover:text-white text-xs font-bold p-2">
                    -10s
                </button>

                <button @click="togglePlay()" class="w-14 h-14 rounded-full bg-amber-500 hover:bg-amber-400 text-slate-950 flex items-center justify-center text-xl font-black shadow-lg shadow-amber-500/30 transform active:scale-95 transition-all">
                    <span x-text="isPlaying ? '⏸' : '▶'"></span>
                </button>

                <button @click="seek(Math.min(duration, currentTime + 10))" class="text-slate-400 hover:text-white text-xs font-bold p-2">
                    +10s
                </button>

                <button @click="changeSpeed()" class="px-2.5 py-1 rounded-lg bg-white/10 hover:bg-white/20 text-xs font-bold text-amber-400">
                    <span x-text="`${speed}x`"></span>
                </button>
            </div>
        </div>

        <!-- Track List -->
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-soft divide-y divide-slate-100 dark:divide-slate-800 overflow-hidden">
            @foreach ($book->audios as $i => $audio)
                <button @click="play({{ $audio->id }}, '{{ $audio->file_url }}', {{ (int) ($audio->duration ?? 0) }})"
                        class="w-full p-4 flex items-center justify-between hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors text-left"
                        :class="currentId === {{ $audio->id }} ? 'bg-indigo-50/50 dark:bg-indigo-950/30' : ''">
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="w-8 h-8 rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xs font-black shrink-0">
                            {{ $i + 1 }}
                        </span>
                        <div class="min-w-0">
                            <span class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white truncate block">{{ $audio->title ?? 'Audio ' . ($i + 1) }}</span>
                            @if ($audio->chapter)
                                <span class="text-[11px] text-slate-400">{{ $audio->chapter->title }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="flex items-center gap-3 shrink-0">
                        @if ($audio->duration)
                            <span class="text-[11px] text-slate-400">{{ floor($audio->duration / 60) }}:{{ str_pad($audio->duration % 60, 2, '0', STR_PAD_LEFT) }}</span>
                        @endif
                        <span class="text-indigo-500 text-xs" x-show="currentId === {{ $audio->id }} && isPlaying">🔊</span>
                    </div>
                </button>
            @endforeach
        </div>
    @endif
</div>
@endsection
