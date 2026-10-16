<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" 
      x-data="readerApp(@js($bookmarks->toArray()), @js($notes->toArray()))" 
      :class="{ 'dark': theme === 'dark', 'reader-sepia': theme === 'sepia', 'reader-light': theme === 'light' }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $chapter->title }} — {{ $book->title }} | Kitobxon</title>

    @include('partials.design-system')
    <style>
        input[type="number"] {
            -moz-appearance: textfield;
            appearance: textfield;
        }
        input[type="number"]::-webkit-outer-spin-button,
        input[type="number"]::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        /* 3 Editorial Reading Themes */
        .reader-light { background-color: #FAF7F2 !important; color: #1A1D24 !important; }
        .reader-light .reader-topbar { background-color: rgba(250, 247, 242, 0.95) !important; border-color: #E5DFD5 !important; }
        .reader-light .reader-text { color: #1A1D24 !important; }
        .reader-light .reader-ctrl { background-color: #F0EAE1 !important; border-color: #E5DFD5 !important; color: #1A1D24 !important; }

        .reader-sepia { background-color: #FBF0D9 !important; color: #433422 !important; }
        .reader-sepia .reader-topbar { background-color: rgba(251, 240, 217, 0.95) !important; border-color: #EBD6AE !important; }
        .reader-sepia .reader-text { color: #3B2C1B !important; }
        .reader-sepia .reader-ctrl { background-color: #F4E6C8 !important; border-color: #EBD6AE !important; color: #433422 !important; }

        .dark { background-color: #07090E !important; color: #F0EDE6 !important; }
        .dark .reader-topbar { background-color: rgba(7, 9, 14, 0.95) !important; border-color: #1F293D !important; }
        .dark .reader-text { color: #F0EDE6 !important; }
        .dark .reader-ctrl { background-color: #0F141F !important; border-color: #1F293D !important; color: #F0EDE6 !important; }

        .reader-serif { font-family: 'Spectral', Georgia, serif !important; }
        .reader-sans { font-family: 'DM Sans', system-ui, sans-serif !important; }
    </style>
@include('components.pwa-head')
</head>
<body class="bg-ink-950 text-paper min-h-screen flex flex-col font-sans transition-colors duration-200">

    <!-- Fixed Reader Top Bar -->
    <header class="sticky top-0 z-40 backdrop-blur-md border-b px-2 sm:px-4 py-2.5 sm:py-3 reader-topbar">
        <div class="max-w-4xl mx-auto flex items-center justify-between gap-1.5 sm:gap-4">
            <!-- Left: Back link & chapter info -->
            <div class="flex items-center gap-1.5 sm:gap-3 min-w-0">
                <a href="{{ route('books.show', $book->slug) }}" class="p-1 sm:p-1.5 rounded-btn text-mist hover:text-paper transition-colors shrink-0" title="Kitob sahifasiga qaytish">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                <div class="min-w-0">
                    <h2 class="text-xs font-bold font-serif text-paper truncate max-w-[90px] sm:max-w-xs">{{ $chapter->title }}</h2>
                    <span class="text-[10px] sm:text-[11px] font-mono text-mist truncate hidden sm:block">«{{ $book->title }}»</span>
                </div>
            </div>

            <!-- Active Reading Timer Pill -->
            <div class="flex items-center gap-1 sm:gap-2 px-2 py-0.5 sm:px-3 sm:py-1 rounded-badge bg-ink-900 border border-ink-border text-amber-400 font-mono text-[11px] sm:text-xs shrink-0"
                 :class="{ 'opacity-50': isIdle }">
                <span class="w-1.5 h-1.5 sm:w-2 sm:h-2 rounded-full" :class="isIdle ? 'bg-amber-500' : 'bg-emerald-400 animate-pulse'"></span>
                <span x-text="formatTime(activeSeconds)">00:00</span>
                <span x-show="isIdle" class="text-[9px] sm:text-[10px] text-amber-400 font-sans hidden sm:inline">(pauza)</span>
            </div>

            <!-- Right Controls: Font size, Serif/Sans, Theme, Music -->
            <div class="flex items-center gap-1 sm:gap-2 shrink-0">
                <!-- Ambient Music Quick Toggle in Topbar -->
                <button type="button" 
                        @click="window.dispatchEvent(new CustomEvent('open-ambient-music'))" 
                        class="px-2 py-0.5 sm:px-2.5 sm:py-1 rounded-btn text-xs font-mono font-bold border reader-ctrl hover:border-amber-400/50 hover:text-amber-400 transition-colors flex items-center gap-1"
                        title="Fon musiqasi pleyeri (M klavishi)">
                    <span>🎵</span>
                    <span class="hidden md:inline">Musiqa</span>
                </button>

                <!-- Font Family Toggle -->
                <button @click="toggleFont()" class="hidden sm:inline-flex px-2 py-0.5 sm:px-2.5 sm:py-1 rounded-btn text-xs font-mono font-bold border reader-ctrl transition-colors">
                    <span x-text="fontFamily === 'serif' ? 'Serif' : 'Sans'"></span>
                </button>

                <!-- Font Size Controls -->
                <div class="flex items-center border reader-ctrl rounded-btn overflow-hidden">
                    <button @click="decreaseFont()" class="px-1.5 sm:px-2 py-0.5 text-xs font-mono font-bold hover:bg-black/10 transition-colors">A-</button>
                    <button @click="increaseFont()" class="px-1.5 sm:px-2 py-0.5 text-xs font-mono font-bold hover:bg-black/10 transition-colors">A+</button>
                </div>

                <!-- Theme Toggle (Light, Sepia, Dark) -->
                <div class="flex items-center gap-0.5 sm:gap-1 border reader-ctrl rounded-btn p-0.5">
                    <button @click="setTheme('light')" 
                            :class="{ 'ring-1 ring-amber-500': theme === 'light' }"
                            class="w-5 h-5 sm:w-6 sm:h-6 rounded-badge bg-[#FAF7F2] text-xs flex items-center justify-center text-slate-800" title="Yorug' rejim">
                        <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
                    </button>
                    <button @click="setTheme('sepia')" 
                            :class="{ 'ring-1 ring-amber-500': theme === 'sepia' }"
                            class="w-5 h-5 sm:w-6 sm:h-6 rounded-badge bg-[#FBF0D9] text-xs flex items-center justify-center text-amber-900" title="Sepiya (ko'zga qulay)">
                        <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                    </button>
                    <button @click="setTheme('dark')" 
                            :class="{ 'ring-1 ring-amber-500': theme === 'dark' }"
                            class="w-5 h-5 sm:w-6 sm:h-6 rounded-badge bg-[#07090E] text-xs flex items-center justify-center text-amber-400" title="Tungi rejim">
                        <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Amber Reading Progress bar -->
        <div class="absolute bottom-0 inset-x-0 h-0.5 bg-black/10 dark:bg-white/10">
            <div class="bg-amber-500 h-0.5 transition-all duration-150" :style="`width: ${scrollPercent}%`"></div>
        </div>
    </header>

    <!-- Reading Content Area -->
    <main class="flex-1 max-w-[66ch] mx-auto px-4 py-8 sm:px-6 sm:py-12 w-full">
        <!-- Chapter Header -->
        <div class="mb-10 text-center border-b pb-8" style="border-color: rgba(139,155,173,0.2);">
            <span class="text-xs font-mono uppercase tracking-widest text-amber-500 block mb-2 font-bold">
                {{ $chapter->chapter_number }}-Bob
            </span>
            <h1 class="text-3xl sm:text-4xl font-bold font-serif tracking-tight reader-text leading-tight">{{ $chapter->title }}</h1>
        </div>

        <section class="mb-8 rounded-panel border border-ink-border bg-ink-900/60 p-4 sm:p-5 reader-ctrl" aria-labelledby="reader-notes-title">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 id="reader-notes-title" class="text-sm font-bold">Xatcho‘p va qaydlar</h2>
                    <p class="mt-1 text-xs opacity-70">O‘qiyotgan joyingizni belgilang yoki fikringizni yozib qoldiring.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button type="button" @click="addBookmark()" :disabled="savingBookmark"
                            class="inline-flex min-h-11 items-center justify-center gap-2 rounded-btn border border-amber-500/30 bg-amber-500/10 px-3.5 py-2 text-xs font-semibold text-amber-500 transition hover:bg-amber-500/20 disabled:opacity-50">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 4.75A1.75 1.75 0 0 1 7.75 3h8.5A1.75 1.75 0 0 1 18 4.75V21l-6-4-6 4z"/></svg>
                        <span x-text="savingBookmark ? 'Saqlanmoqda…' : 'Shu joyni belgilash'"></span>
                    </button>
                    <button type="button" @click="openNoteForm()"
                            class="inline-flex min-h-11 items-center justify-center gap-2 rounded-btn border border-ink-border px-3.5 py-2 text-xs font-semibold transition hover:border-amber-500/40 hover:text-amber-500">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4z"/></svg>
                        Qayd yozish
                    </button>
                </div>
            </div>

            <div x-show="noteFormOpen" x-cloak class="mt-4 space-y-3 border-t border-ink-border pt-4">
                <p class="text-xs text-mist">Qayd hozir o‘qiyotgan joyingizga saqlanadi: <span class="font-semibold text-amber-400" x-text="formatLocation(currentPosition)"></span>.</p>
                <div class="flex items-center justify-between">
                    <label for="reader-note-text" class="block text-xs font-semibold">Shaxsiy qayd va matn rangi</label>
                    <div class="flex items-center gap-1.5">
                        <button type="button" @click="noteColor = 'amber'" :aria-pressed="noteColor === 'amber'" :class="noteColor === 'amber' ? 'ring-2 ring-amber-400' : 'opacity-70'" class="inline-flex min-h-11 min-w-11 items-center justify-center rounded-full transition" aria-label="Sariq rang"><span class="h-5 w-5 rounded-full bg-amber-400"></span></button>
                        <button type="button" @click="noteColor = 'emerald'" :aria-pressed="noteColor === 'emerald'" :class="noteColor === 'emerald' ? 'ring-2 ring-emerald-400' : 'opacity-70'" class="inline-flex min-h-11 min-w-11 items-center justify-center rounded-full transition" aria-label="Yashil rang"><span class="h-5 w-5 rounded-full bg-emerald-400"></span></button>
                        <button type="button" @click="noteColor = 'sky'" :aria-pressed="noteColor === 'sky'" :class="noteColor === 'sky' ? 'ring-2 ring-sky-400' : 'opacity-70'" class="inline-flex min-h-11 min-w-11 items-center justify-center rounded-full transition" aria-label="Moviy rang"><span class="h-5 w-5 rounded-full bg-sky-400"></span></button>
                        <button type="button" @click="noteColor = 'rose'" :aria-pressed="noteColor === 'rose'" :class="noteColor === 'rose' ? 'ring-2 ring-rose-400' : 'opacity-70'" class="inline-flex min-h-11 min-w-11 items-center justify-center rounded-full transition" aria-label="Qizil rang"><span class="h-5 w-5 rounded-full bg-rose-400"></span></button>
                        <button type="button" @click="noteColor = 'purple'" :aria-pressed="noteColor === 'purple'" :class="noteColor === 'purple' ? 'ring-2 ring-purple-400' : 'opacity-70'" class="inline-flex min-h-11 min-w-11 items-center justify-center rounded-full transition" aria-label="Binafsha rang"><span class="h-5 w-5 rounded-full bg-purple-400"></span></button>
                    </div>
                </div>
                <template x-if="selectedText">
                    <div class="p-2.5 rounded-btn bg-ink-950/70 border text-xs italic font-serif flex items-center justify-between gap-2"
                         :class="{
                             'border-amber-400/50 text-amber-200': noteColor === 'amber',
                             'border-emerald-400/50 text-emerald-200': noteColor === 'emerald',
                             'border-sky-400/50 text-sky-200': noteColor === 'sky',
                             'border-rose-400/50 text-rose-200': noteColor === 'rose',
                             'border-purple-400/50 text-purple-200': noteColor === 'purple',
                         }">
                        <span class="truncate">«<span x-text="selectedText"></span>»</span>
                        <button type="button" @click="selectedText = ''" class="text-mist hover:text-paper shrink-0">×</button>
                    </div>
                </template>
                <textarea id="reader-note-text" x-model="noteDraft" rows="3" maxlength="5000"
                          class="ks-input resize-y" placeholder="Ushbu bobdan eslab qolmoqchi bo‘lgan fikringiz…"></textarea>
                <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                    <button type="button" @click="noteFormOpen = false" class="min-h-11 rounded-btn px-4 py-2 text-xs font-semibold opacity-75 hover:opacity-100">Bekor qilish</button>
                    <button type="button" @click="saveNote()" :disabled="savingNote || !noteDraft.trim()"
                            class="min-h-11 rounded-btn bg-amber-500 px-4 py-2 text-xs font-bold text-ink-950 transition hover:bg-amber-400 disabled:opacity-50"
                            x-text="savingNote ? 'Saqlanmoqda…' : 'Qaydni saqlash'"></button>
                </div>
            </div>

            <div class="mt-4 grid gap-4 border-t border-ink-border pt-4 md:grid-cols-2">
                <div>
                    <h3 class="mb-2 text-[11px] font-bold uppercase tracking-wider opacity-70">Xatcho‘plar (<span x-text="bookmarks.length"></span>)</h3>
                    <template x-if="bookmarks.length === 0"><p class="text-xs opacity-60">Hozircha xatcho‘p yo‘q.</p></template>
                    <ul class="space-y-2">
                        <template x-for="bookmark in bookmarks" :key="bookmark.id">
                            <li class="flex items-center justify-between gap-2 rounded-btn border border-ink-border/70 px-3 py-2">
                                <button type="button" @click="jumpToBookmark(bookmark)" class="min-h-9 min-w-0 flex-1 truncate text-left text-xs font-medium hover:text-amber-500" x-text="'Belgilangan joy ' + formatPosition(bookmark.position)"></button>
                                <button type="button" @click="removeBookmark(bookmark.id)" class="min-h-9 min-w-9 rounded-btn text-xs text-rose-400 hover:bg-rose-500/10" :aria-label="'Xatcho‘pni o‘chirish'">×</button>
                            </li>
                        </template>
                    </ul>
                </div>
                <div>
                    <h3 class="mb-2 text-[11px] font-bold uppercase tracking-wider opacity-70">Qaydlar (<span x-text="notes.length"></span>)</h3>
                    <template x-if="notes.length === 0"><p class="text-xs opacity-60">Hozircha qayd yo‘q.</p></template>
                    <ul class="max-h-52 space-y-2 overflow-y-auto">
                        <template x-for="note in notes" :key="note.id">
                            <li class="rounded-btn border border-ink-border/70 px-3 py-2">
                                <button type="button" @click="jumpToBookmark(note)"
                                        class="mb-2 inline-flex min-h-11 w-full items-center justify-between gap-2 rounded-btn px-2 text-left text-xs font-semibold text-amber-400 transition hover:bg-amber-500/10 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-amber-400"
                                        :aria-label="'Qayd joyiga qaytish: ' + formatLocation(note.position)">
                                    <span>Qayd qoldirilgan joy</span>
                                    <span class="shrink-0" x-text="formatLocation(note.position)"></span>
                                </button>
                                <template x-if="note.selected_text">
                                    <blockquote class="mb-1 border-l-2 pl-2 text-[11px] italic"
                                                :class="{
                                                    'border-amber-400 text-amber-300/90': note.color === 'amber' || !note.color,
                                                    'border-emerald-400 text-emerald-300/90': note.color === 'emerald',
                                                    'border-sky-400 text-sky-300/90': note.color === 'sky',
                                                    'border-rose-400 text-rose-300/90': note.color === 'rose',
                                                    'border-purple-400 text-purple-300/90': note.color === 'purple',
                                                }"
                                                x-text="'«' + note.selected_text + '»'"></blockquote>
                                </template>
                                <p class="whitespace-pre-wrap break-words text-xs" x-text="note.note_text"></p>
                                <p class="mt-1 text-[10px] opacity-60" x-text="note.created_at"></p>
                            </li>
                        </template>
                    </ul>
                </div>
            </div>
            <p x-show="annotationMessage" x-text="annotationMessage" role="status" aria-live="polite" class="mt-3 text-xs text-emerald-500"></p>
        </section>

        <!-- Main Chapter Text -->
        <article class="reader-content leading-[1.75] transition-all reader-text text-justify"
                 :class="{ 'reader-serif': fontFamily === 'serif', 'reader-sans': fontFamily === 'sans' }"
                 :style="`font-size: ${fontSize}px;`">
            {!! nl2br(e($chapter->content)) !!}
        </article>

        <!-- Chapter Navigation Footer -->
        <div class="mt-16 pt-8 border-t flex items-center justify-between" style="border-color: rgba(139,155,173,0.2);">
            <a href="{{ route('books.show', $book->slug) }}" class="ks-btn-ghost py-1.5 px-3 text-xs inline-flex items-center gap-1.5 font-mono">
                ← Boblar ro'yxatiga qaytish
            </a>
            <button @click="saveProgress(100)" class="ks-btn-primary py-2 px-4 text-xs font-semibold inline-flex items-center gap-1.5">
                <span>Bobni yakunlash</span>
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            </button>
        </div>
    </main>

    <script>
        function readerApp(initialBookmarks = [], initialNotes = []) {
            return {
                theme: localStorage.getItem('reader_theme') || 'dark',
                fontFamily: localStorage.getItem('reader_font') || 'serif',
                fontSize: parseInt(localStorage.getItem('reader_size')) || 19,
                scrollPercent: 0,
                currentPosition: 0,
                activeSeconds: 0,
                isIdle: false,
                idleTimer: null,
                heartbeatInterval: null,
                bookmarks: initialBookmarks,
                notes: initialNotes,
                noteFormOpen: false,
                noteDraft: '',
                noteColor: 'amber',
                savingBookmark: false,
                savingNote: false,
                annotationMessage: '',
                annotationTimer: null,
                selectedText: '',

                async annotationRequest(url, method, body = null) {
                    const response = await fetch(url, {
                        method,
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: body ? JSON.stringify(body) : null
                    });
                    const data = await response.json().catch(() => ({}));
                    if (!response.ok) throw new Error(data.message || 'Saqlashda xatolik yuz berdi.');
                    return data;
                },

                showAnnotationMessage(message) {
                    this.annotationMessage = message;
                    clearTimeout(this.annotationTimer);
                    this.annotationTimer = setTimeout(() => this.annotationMessage = '', 3500);
                },

                async addBookmark() {
                    if (this.savingBookmark) return;
                    this.savingBookmark = true;
                    try {
                        const data = await this.annotationRequest("{{ route('reader.bookmarks.store') }}", 'POST', {
                            book_id: {{ $book->id }}, chapter_id: {{ $chapter->id }}, position: Math.round(window.scrollY)
                        });
                        this.bookmarks.push(data.bookmark);
                        this.showAnnotationMessage('Xatcho‘p saqlandi.');
                    } catch (error) {
                        this.showAnnotationMessage(error.message || 'Xatcho‘pni saqlab bo‘lmadi.');
                    } finally {
                        this.savingBookmark = false;
                    }
                },

                async removeBookmark(id) {
                    try {
                        const destroyUrl = @js(route('reader.bookmarks.destroy', ['bookmark' => '__BOOKMARK_ID__']));
                        await this.annotationRequest(destroyUrl.replace('__BOOKMARK_ID__', encodeURIComponent(id)), 'DELETE');
                        this.bookmarks = this.bookmarks.filter(bookmark => bookmark.id !== id);
                        this.showAnnotationMessage('Xatcho‘p o‘chirildi.');
                    } catch (error) {
                        this.showAnnotationMessage(error.message || 'Xatcho‘pni o‘chirib bo‘lmadi.');
                    }
                },

                jumpToBookmark(bookmark) {
                    window.scrollTo({ top: Number(bookmark.position) || 0, behavior: 'smooth' });
                },

                formatPosition(position) {
                    const article = document.querySelector('.reader-content');
                    const articleTop = article ? article.getBoundingClientRect().top + window.scrollY : 0;
                    const articleScrollHeight = Math.max(1, (article?.offsetHeight || document.documentElement.scrollHeight) - window.innerHeight);
                    const percent = Math.max(0, Math.min(100, Math.round(((Number(position) - articleTop) / articleScrollHeight) * 100)));
                    return percent + '%';
                },

                formatLocation(position) {
                    return 'Bobning ' + this.formatPosition(position) + ' qismi';
                },

                openNoteForm() {
                    this.selectedText = window.getSelection()?.toString().trim().slice(0, 5000) || this.selectedText;
                    this.noteFormOpen = true;
                    this.$nextTick(() => document.getElementById('reader-note-text')?.focus());
                },

                async saveNote() {
                    const noteText = this.noteDraft.trim();
                    if (!noteText || this.savingNote) return;
                    this.savingNote = true;
                    try {
                        const data = await this.annotationRequest("{{ route('reader.notes.store') }}", 'POST', {
                            book_id: {{ $book->id }}, chapter_id: {{ $chapter->id }},
                            position: Math.round(window.scrollY),
                            selected_text: this.selectedText,
                            color: this.noteColor,
                            note_text: noteText
                        });
                        this.notes.unshift(data.note);
                        this.noteDraft = '';
                        this.noteFormOpen = false;
                        this.showAnnotationMessage('Qayd saqlandi. Profilingizdagi “Qaydlar” bo‘limida ham topasiz.');
                    } catch (error) {
                        this.showAnnotationMessage(error.message || 'Qaydni saqlab bo‘lmadi.');
                    } finally {
                        this.savingNote = false;
                    }
                },

                init() {
                    this.currentPosition = Math.round(window.scrollY);
                    window.addEventListener('scroll', () => {
                        const winScroll = document.documentElement.scrollTop;
                        const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
                        this.scrollPercent = Math.min(100, Math.round((winScroll / height) * 100)) || 0;
                        this.currentPosition = Math.round(window.scrollY);
                    }, { passive: true });
                    document.addEventListener('selectionchange', () => {
                        const selection = window.getSelection();
                        const selected = selection?.toString().trim();
                        const article = document.querySelector('.reader-content');
                        if (selected && article?.contains(selection.anchorNode)) {
                            this.selectedText = selected.slice(0, 5000);
                        }
                    });
                },

                toggleFont() {
                    this.fontFamily = this.fontFamily === 'serif' ? 'sans' : 'serif';
                    localStorage.setItem('reader_font', this.fontFamily);
                },

                increaseFont() {
                    if (this.fontSize < 28) {
                        this.fontSize += 2;
                        localStorage.setItem('reader_size', this.fontSize);
                    }
                },

                decreaseFont() {
                    if (this.fontSize > 14) {
                        this.fontSize -= 2;
                        localStorage.setItem('reader_size', this.fontSize);
                    }
                },

                setTheme(t) {
                    this.theme = t;
                    localStorage.setItem('reader_theme', t);
                },

                formatTime(sec) {
                    const m = Math.floor(sec / 60).toString().padStart(2, '0');
                    const s = (sec % 60).toString().padStart(2, '0');
                    return `${m}:${s}`;
                },

                saveProgress(percent) {
                    fetch('{{ route('reading.progress') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            book_id: {{ $book->id }},
                            chapter_id: {{ $chapter->id }},
                            last_position: window.scrollY,
                            percent_complete: percent
                        })
                    }).then(() => {
                        window.location.href = "{{ route('books.show', $book->slug) }}";
                    }).catch(() => {
                        window.location.href = "{{ route('books.show', $book->slug) }}";
                    });
                }
            }
        }
    </script>

    <!-- Ambient Background Music Player -->
    <x-ambient-music-player :book="$book" />

    <!-- Reading Tracker & 5-minute AFK Inactivity Modal -->
    @include('components.reading-tracker', [
        'bookId' => $book->id,
        'chapterId' => $chapter->id,
        'pageType' => 'chapter'
    ])
</body>
</html>
