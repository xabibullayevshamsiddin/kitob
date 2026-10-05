@extends('admin.layouts.app')
@section('title', 'Kitob fon musiqalari — ' . $book->title)
@section('breadcrumb', 'Kitob musiqalari')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.books.edit', $book->id) }}"
               class="p-2 rounded-btn bg-ink-900 border border-ink-border text-mist hover:text-paper hover:border-amber-400/40 transition-colors"
               title="Kitobni tahrirlashga qaytish">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div>
                <h1 class="text-xl font-bold font-serif text-paper flex items-center gap-2">
                    <span>🎵</span>
                    <span>Kitob fon musiqalari: «{{ $book->title }}»</span>
                </h1>
                <p class="text-xs text-mist font-mono mt-0.5">O'quvchilar kitobni o'qiyotganda eshitishi mumkin bo'lgan ambient pleylist</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.books.index') }}" class="ks-btn-ghost py-1.5 px-3.5 text-xs font-mono">
                ← Barcha kitoblar
            </a>
            <a href="{{ route('books.show', $book->slug) }}" target="_blank" class="ks-btn-primary py-1.5 px-3.5 text-xs font-mono inline-flex items-center gap-1.5">
                <span>Kitobni ko'rish</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
            </a>
        </div>
    </div>

    {{-- Flash Notifications --}}
    @if(session('success'))
        <div class="p-4 rounded-panel bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-xs flex items-center gap-2 animate-fade-in">
            <span class="text-base">✅</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if($errors->any())
        <div class="p-4 rounded-panel bg-rose-500/10 border border-rose-500/30 text-rose-300 text-xs space-y-1 animate-fade-in">
            @foreach($errors->all() as $error)
                <p class="flex items-center gap-2"><span>⚠️</span> <span>{{ $error }}</span></p>
            @endforeach
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Left Form: Add New Music --}}
        <div class="lg:col-span-1 bg-ink-900 border border-ink-border rounded-panel p-5 space-y-5" x-data="{
            mode: 'file',
            selectedPreset: '',
            fileName: '',
            handleAudio(e) {
                const f = e.target.files[0];
                if (f) {
                    this.fileName = f.name;
                    if (!document.getElementById('music-title').value) {
                        const clean = f.name.replace(/\.[^/.]+$/, '').replace(/[-_]/g, ' ');
                        document.getElementById('music-title').value = clean;
                    }
                }
            }
        }">
            <div>
                <h2 class="text-sm font-bold font-serif text-paper flex items-center gap-1.5">
                    <span>➕</span>
                    <span>Yangi musiqa ulash</span>
                </h2>
                <p class="text-[11px] text-mist font-sans mt-0.5">Ushbu kitob uchun yangi trek qo'shing</p>
            </div>

            <form action="{{ route('admin.books.music.store', $book->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs font-sans">
                @csrf

                {{-- Mode Switcher (Fayl yuklash yoki Tayyor preset) --}}
                <div class="flex items-center p-1 rounded-btn bg-ink-950 border border-ink-border font-mono text-[11px]">
                    <button type="button" @click="mode = 'file'" :class="{ 'bg-amber-500 text-ink-950 font-bold': mode === 'file', 'text-mist hover:text-paper': mode !== 'file' }" class="flex-1 py-1 px-2 rounded-btn transition-colors text-center">
                        📁 Fayl yuklash
                    </button>
                    <button type="button" @click="mode = 'preset'" :class="{ 'bg-amber-500 text-ink-950 font-bold': mode === 'preset', 'text-mist hover:text-paper': mode !== 'preset' }" class="flex-1 py-1 px-2 rounded-btn transition-colors text-center">
                        ✨ Tayyor ambient
                    </button>
                </div>

                {{-- Title --}}
                <div>
                    <label class="block font-semibold text-mist uppercase font-mono text-[10px] tracking-wider mb-1">
                        Musiqa nomi *
                    </label>
                    <input type="text" name="title" id="music-title" required
                           placeholder="Masalan: Sokin yomg'ir yoki Pianino ohangi"
                           class="w-full px-3 py-2 bg-ink-950 border border-ink-border rounded-input text-paper text-xs focus:outline-none focus:border-amber-400 focus:ring-1 focus:ring-amber-400">
                </div>

                {{-- Mode 1: File Upload --}}
                <div x-show="mode === 'file'" class="space-y-2">
                    <label class="block font-semibold text-mist uppercase font-mono text-[10px] tracking-wider">
                        Audio fayl (.mp3, .wav, .ogg, .m4a) *
                    </label>
                    <label for="music_file" class="flex flex-col items-center justify-center p-4 rounded-card border-2 border-dashed border-ink-border hover:border-amber-400/50 bg-ink-950/60 hover:bg-ink-950 cursor-pointer transition-all text-center">
                        <template x-if="!fileName">
                            <div class="space-y-1">
                                <span class="text-2xl block">🎼</span>
                                <span class="text-[11px] font-bold text-paper block">Kompyuterdan audio fayl tanlang</span>
                                <span class="text-[10px] text-mist block font-mono">MP3, WAV, OGG, M4A (maks. 50 MB)</span>
                            </div>
                        </template>
                        <template x-if="fileName">
                            <div class="flex items-center gap-2 text-emerald-400 font-mono text-[11px]">
                                <span>✅</span>
                                <span class="truncate max-w-[200px]" x-text="fileName"></span>
                            </div>
                        </template>
                        <input type="file" name="music_file" id="music_file" accept="audio/*,.mp3,.wav,.ogg,.m4a,.aac" class="hidden" @change="handleAudio($event)">
                    </label>
                </div>

                {{-- Mode 2: Presets --}}
                <div x-show="mode === 'preset'" class="space-y-2">
                    <label class="block font-semibold text-mist uppercase font-mono text-[10px] tracking-wider">
                        Tayyor Ambient ohangni tanlang
                    </label>
                    <div class="space-y-2">
                        <label class="flex items-center gap-2.5 p-2.5 rounded-card bg-ink-950 border border-ink-border hover:border-amber-400/40 cursor-pointer">
                            <input type="radio" name="preset_track" value="rain" @change="if(!document.getElementById('music-title').value) document.getElementById('music-title').value = 'Sokin Yomg\'ir & Tabiat'" class="text-amber-500 focus:ring-amber-400">
                            <div class="min-w-0">
                                <p class="text-paper font-semibold text-xs">🌧️ Sokin Yomg'ir & Tabiat</p>
                                <p class="text-[10px] text-mist font-mono">Tinchlantiruvchi mayin yomg'ir shiviri</p>
                            </div>
                        </label>
                        <label class="flex items-center gap-2.5 p-2.5 rounded-card bg-ink-950 border border-ink-border hover:border-amber-400/40 cursor-pointer">
                            <input type="radio" name="preset_track" value="piano" @change="if(!document.getElementById('music-title').value) document.getElementById('music-title').value = 'Klassik Pianino Oromi'" class="text-amber-500 focus:ring-amber-400">
                            <div class="min-w-0">
                                <p class="text-paper font-semibold text-xs">🎹 Klassik Pianino Oromi</p>
                                <p class="text-[10px] text-mist font-mono">Mutolaa uchun sokin akkordlar</p>
                            </div>
                        </label>
                        <label class="flex items-center gap-2.5 p-2.5 rounded-card bg-ink-950 border border-ink-border hover:border-amber-400/40 cursor-pointer">
                            <input type="radio" name="preset_track" value="forest" @change="if(!document.getElementById('music-title').value) document.getElementById('music-title').value = 'O\'rmon Shabodasi & Qushlar'" class="text-amber-500 focus:ring-amber-400">
                            <div class="min-w-0">
                                <p class="text-paper font-semibold text-xs">🌲 O'rmon Shabodasi & Qushlar</p>
                                <p class="text-[10px] text-mist font-mono">Yengil shamol va qushlar sayrashi</p>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- Order --}}
                <div>
                    <label class="block font-semibold text-mist uppercase font-mono text-[10px] tracking-wider mb-1">
                        Tartib raqami (pleylistdagi o'rni)
                    </label>
                    <input type="number" name="order" min="1" max="999" value="{{ $musics->count() + 1 }}"
                           class="w-full px-3 py-2 bg-ink-950 border border-ink-border rounded-input text-paper text-xs font-mono focus:outline-none focus:border-amber-400">
                </div>

                <button type="submit" class="w-full ks-btn-primary py-2.5 px-4 text-xs font-semibold justify-center">
                    <span>Kitobga musiqa qo'shish</span>
                </button>
            </form>
        </div>

        {{-- Right: Playlist Table / List --}}
        <div class="lg:col-span-2 space-y-4">
            <div class="bg-ink-900 border border-ink-border rounded-panel p-5">
                <div class="flex items-center justify-between mb-4 border-b border-ink-border pb-3">
                    <div>
                        <h2 class="text-sm font-bold font-serif text-paper flex items-center gap-2">
                            <span>📜</span>
                            <span>Mavjud Fon Musiqalari</span>
                        </h2>
                        <p class="text-[11px] text-mist font-mono mt-0.5">Jami: <strong class="text-amber-400">{{ $musics->count() }}</strong> ta trek ulandi</p>
                    </div>
                </div>

                @if($musics->isEmpty())
                    <div class="p-8 text-center rounded-card bg-ink-950/60 border border-ink-border space-y-3">
                        <span class="text-3xl block">🎧</span>
                        <p class="text-xs font-semibold text-paper font-serif">Ushbu kitobga hali fon musiqasi ulanmagan.</p>
                        <p class="text-[11px] text-mist font-sans max-w-sm mx-auto">
                            Chap tomondagi forma orqali MP3 fayl yuklang yoki tayyor ambient ohanglardan birini qo'shing. O'quvchilar kitob mutolaasi paytida musiqani yoqib o'qiy olishadi!
                        </p>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($musics as $index => $music)
                            <div class="p-3.5 rounded-card bg-ink-950 border border-ink-border flex flex-col sm:flex-row sm:items-center justify-between gap-3 transition-colors hover:border-amber-400/30">
                                
                                {{-- Track Info --}}
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-8 h-8 rounded-badge bg-ink-900 border border-ink-border flex items-center justify-center text-amber-400 font-mono font-bold text-xs shrink-0">
                                        {{ $music->order ?? ($index + 1) }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2">
                                            <h4 class="text-xs font-bold text-paper truncate font-sans">{{ $music->title }}</h4>
                                            @if($music->is_active)
                                                <span class="px-1.5 py-0.2 rounded-badge bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 text-[9px] font-mono font-bold uppercase">Faol</span>
                                            @else
                                                <span class="px-1.5 py-0.2 rounded-badge bg-rose-500/15 border border-rose-500/30 text-rose-400 text-[9px] font-mono font-bold uppercase">Nofaol</span>
                                            @endif
                                        </div>
                                        <p class="text-[10px] text-mist font-mono mt-0.5 truncate">
                                            {{ basename($music->file_path) }} • {{ $music->created_at->format('d.m.Y H:i') }}
                                        </p>
                                    </div>
                                </div>

                                {{-- Audio preview player & actions --}}
                                <div class="flex items-center gap-2 sm:gap-3 flex-wrap justify-between sm:justify-end">
                                    <audio controls preload="none" class="h-8 max-w-[180px] sm:max-w-[210px] rounded-lg">
                                        <source src="{{ $music->file_url }}">
                                        Brauzeringiz audio qo'llab-quvvatlamaydi.
                                    </audio>

                                    <div class="flex items-center gap-1.5 shrink-0">
                                        {{-- Toggle Active --}}
                                        <form action="{{ route('admin.books.music.toggle', ['book' => $book->id, 'music' => $music->id]) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" 
                                                    class="p-1.5 rounded-btn border text-xs transition-colors {{ $music->is_active ? 'border-emerald-500/30 text-emerald-400 hover:bg-emerald-500/10' : 'border-ink-border text-mist hover:text-paper' }}"
                                                    title="{{ $music->is_active ? 'Musiqani o\'chirish (nofaol qilish)' : 'Musiqani yoqish (faollashtirish)' }}">
                                                {{ $music->is_active ? '🟢' : '⚪️' }}
                                            </button>
                                        </form>

                                        {{-- Delete --}}
                                        <form action="{{ route('admin.books.music.destroy', ['book' => $book->id, 'music' => $music->id]) }}" method="POST"
                                              onsubmit="return confirm('«{{ addslashes($music->title) }}» fon musiqasini o\'chirishni xohlaysizmi?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 rounded-btn border border-rose-500/30 text-rose-400 hover:bg-rose-500/10 transition-colors" title="O'chirish">
                                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18m-2 0v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </div>

                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Informational banner --}}
            <div class="p-4 rounded-panel bg-ink-900 border border-ink-border flex items-start gap-3 text-xs text-mist font-sans">
                <span class="text-xl shrink-0">💡</span>
                <div class="space-y-1">
                    <p class="font-semibold text-paper">O'quvchilar bu musiqalarni qayerda eshitishadi?</p>
                    <p class="leading-relaxed">
                        Foydalanuvchilar kitobni onlayn o'qiyotganda (boblar bo'ylab matnli o'quvchi yoki 3D varaqlash rejimida) ekranning yuqori qismida yoki pastki burchagida <strong>«Fon musiqasi 🎵»</strong> boshqaruvini ko'rishadi. Ular xohlagan vaqtda musiqani yoqishi/o'chirishi, ovoz balandligini sozlashi yoki ushbu pleylistdan istalgan ohangni tanlashi mumkin.
                    </p>
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
