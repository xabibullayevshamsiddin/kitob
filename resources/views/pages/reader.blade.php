<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" 
      data-motion-zone="calm"
      x-data="readerApp(@js($bookmarks->toArray()), @js($notes->toArray()))" 
      :class="{ 'dark': theme === 'dark', 'light': theme !== 'dark', 'reader-sepia': theme === 'sepia', 'reader-light': theme === 'light' }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $chapter->title }} — {{ $book->title }} | {{ config('app.name', 'Davra_books') }}</title>

    @include('partials.design-system')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:ital,wght@0,300;0,400;0,700;1,300;1,400&display=swap" rel="stylesheet">
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

        @auth
            @php
                $equippedReaderPalette = auth()->user()->getEquippedItem('reader_palette');
                $equippedHighlight = auth()->user()->getEquippedItem('highlight_color');
            @endphp
            @if($equippedReaderPalette)
                @php
                    $bgLight = $equippedReaderPalette->safeColor('bg_light', '#FAF7F2');
                    $bgDark = $equippedReaderPalette->safeColor('bg_dark', '#07090E');
                    $textLight = $equippedReaderPalette->safeColor('text_light', '#1A1D24');
                    $textDark = $equippedReaderPalette->safeColor('text_dark', '#F0EDE6');
                @endphp
                .dark, html.dark { background-color: {{ $bgDark }} !important; color: {{ $textDark }} !important; }
                .dark .reader-topbar { background-color: {{ $bgDark }}ee !important; }
                .dark .reader-text { color: {{ $textDark }} !important; }
                .reader-light, html.reader-light { background-color: {{ $bgLight }} !important; color: {{ $textLight }} !important; }
                .reader-light .reader-topbar { background-color: {{ $bgLight }}ee !important; }
                .reader-light .reader-text { color: {{ $textLight }} !important; }
            @endif
            @if($equippedHighlight)
                @php
                    $hlHex = $equippedHighlight->safeColor('color', '#84CC16');
                @endphp
                ::selection { background-color: {{ $hlHex }}66 !important; color: #fff !important; }
                .reader-highlight-custom { background-color: {{ $hlHex }}55 !important; border-bottom: 2px solid {{ $hlHex }} !important; }
            @endif
        @endauth

        /* Font Family Classes */
        .reader-merriweather { font-family: 'Merriweather', 'Spectral', Georgia, serif !important; }
        .reader-spectral { font-family: 'Spectral', Georgia, serif !important; }
        .reader-sans { font-family: 'DM Sans', system-ui, -apple-system, sans-serif !important; }
        .reader-mono { font-family: 'DM Mono', ui-monospace, SFMono-Regular, monospace !important; }

        /* Bionic Reading Fixation Styling */
        .bionic-bold {
            font-weight: 800 !important;
            letter-spacing: -0.015em;
        }
        .reader-light .bionic-bold {
            color: #000000 !important;
        }
        .reader-sepia .bionic-bold {
            color: #1f1206 !important;
        }
        .dark .bionic-bold {
            color: #ffffff !important;
        }
        .is-bionic-active {
            letter-spacing: 0.012em;
            line-height: 1.88 !important;
        }
    </style>
@include('components.pwa-head')
</head>
<body class="bg-ink-950 text-paper min-h-screen flex flex-col font-sans transition-colors duration-200">

    <!-- Fixed Reader Top Bar -->
    <header class="sticky top-0 z-40 backdrop-blur-md border-b px-2 sm:px-4 py-2 sm:py-3 reader-topbar">
        <div class="max-w-4xl mx-auto flex flex-wrap sm:flex-nowrap items-center justify-between gap-1.5 sm:gap-4">
            <!-- Left: Back link & chapter info -->
            <div class="flex items-center gap-1.5 sm:gap-3 min-w-0">
                <a href="{{ route('books.show', $book->slug) }}" class="p-1 sm:p-1.5 rounded-btn text-mist hover:text-paper transition-colors shrink-0" title="{{ __('site.reader.back_to_book') }}">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                <div class="min-w-0">
                    <h2 class="text-xs font-bold font-serif text-paper truncate max-w-[110px] sm:max-w-xs">{{ $chapter->title }}</h2>
                    <span class="text-[10px] sm:text-[11px] font-mono text-mist truncate hidden sm:block">«{{ $book->title }}»</span>
                </div>
            </div>

            <!-- Active Reading Timer Pill -->
            <div class="flex items-center gap-1 sm:gap-2 px-2 py-0.5 sm:px-3 sm:py-1 rounded-badge bg-ink-900 border border-ink-border text-amber-600 dark:text-amber-400 font-mono text-[11px] sm:text-xs shrink-0"
                 :class="{ 'opacity-50': isIdle }">
                <span class="w-1.5 h-1.5 sm:w-2 sm:h-2 rounded-full" :class="isIdle ? 'bg-amber-500' : 'bg-emerald-400 animate-pulse'"></span>
                <span x-text="formatTime(activeSeconds)">00:00</span>
                <span x-show="isIdle" class="text-[9px] sm:text-[10px] text-amber-600 dark:text-amber-400 font-sans hidden sm:inline">{{ __('site.reader.pause') }}</span>
            </div>

            <!-- Right Controls: Font size, Serif/Sans, Theme, Music -->
            <div class="flex items-center justify-end gap-1 sm:gap-2 shrink-0 w-full sm:w-auto pt-1 sm:pt-0 border-t border-black/5 dark:border-white/5 sm:border-0">
                <!-- Ambient Music Quick Toggle in Topbar -->
                <button type="button" 
                        @click="window.dispatchEvent(new CustomEvent('open-ambient-music'))" 
                        class="px-2 py-0.5 sm:px-2.5 sm:py-1 rounded-btn text-xs font-mono font-bold border reader-ctrl hover:border-amber-400/50 hover:text-amber-400 transition-colors flex items-center gap-1"
                        title="{{ __('site.reader.music_title') }}">
                    <span>🎵</span>
                    <span class="hidden md:inline">{{ __('site.reader.music_btn') }}</span>
                </button>

                <!-- Bionic Reading Toggle Button -->
                <button type="button" 
                        @click="toggleBionic()" 
                        class="px-2 py-0.5 sm:px-2.5 sm:py-1 rounded-btn text-xs font-mono font-bold border reader-ctrl transition-all flex items-center gap-1.5 cursor-pointer select-none"
                        :class="bionicReading ? 'border-amber-400 bg-amber-500/20 text-amber-400 shadow-sm' : 'hover:border-amber-400/50 hover:text-amber-400'"
                        title="{{ __('site.reader.bionic_title') }}">
                    <span class="font-extrabold text-amber-400 tracking-tight"><b>Bio</b>nic</span>
                    <span class="w-1.5 h-1.5 rounded-full" :class="bionicReading ? 'bg-amber-400 animate-pulse' : 'bg-mist/40'"></span>
                </button>

                <!-- Font Family Selector (Merriweather, Spectral, Sans, Mono) -->
                <div class="relative" x-data="{ fontMenuOpen: false }">
                    <button type="button" 
                            @click="fontMenuOpen = !fontMenuOpen" 
                            @click.outside="fontMenuOpen = false"
                            class="px-2 py-0.5 sm:px-2.5 sm:py-1 rounded-btn text-xs font-mono font-bold border reader-ctrl transition-colors flex items-center gap-1.5"
                            title="Shrift turini tanlash">
                        <span>🔤</span>
                        <span class="hidden sm:inline" x-text="getFontName(fontFamily)"></span>
                        <span class="text-[9px] opacity-60">▾</span>
                    </button>
                    <!-- Dropdown Menu -->
                    <div x-show="fontMenuOpen" x-cloak
                         class="absolute right-0 mt-1 w-44 rounded-panel border border-ink-border bg-ink-950/95 backdrop-blur-md shadow-2xl p-1.5 z-50 space-y-1">
                        <template x-for="f in fontOptions" :key="f.id">
                            <button type="button" 
                                    @click="setFont(f.id); fontMenuOpen = false"
                                    class="w-full text-left px-2.5 py-1.5 rounded-btn text-xs flex items-center justify-between transition-colors"
                                    :class="fontFamily === f.id ? 'bg-amber-500/20 text-amber-400 font-bold' : 'text-paper hover:bg-white/10'">
                                <span :style="`font-family: ${f.fontFamily};`" x-text="f.label"></span>
                                <span x-show="fontFamily === f.id" class="text-amber-400">✓</span>
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Font Size Controls with Live Pixel Display & Reset -->
                <div class="flex items-center border reader-ctrl rounded-btn overflow-hidden">
                    <button type="button" 
                            @click="decreaseFont()" 
                            class="px-2 py-0.5 text-xs font-mono font-bold hover:bg-black/10 transition-colors"
                            title="Shriftni kichiklashtirish (A-)">
                        A-
                    </button>
                    <button type="button" 
                            @click="resetFontSize()" 
                            class="px-1.5 py-0.5 text-[11px] font-mono font-bold text-amber-500 hover:text-amber-400 transition-colors min-w-[38px] text-center"
                            title="Standart o'lchamga qaytarish (19px)">
                        <span x-text="`${fontSize}px`"></span>
                    </button>
                    <button type="button" 
                            @click="increaseFont()" 
                            class="px-2 py-0.5 text-xs font-mono font-bold hover:bg-black/10 transition-colors"
                            title="Shriftni kattalashtirish (A+)">
                        A+
                    </button>
                </div>

                <!-- Theme Toggle (Light, Sepia, Dark) -->
                <div class="flex items-center gap-0.5 sm:gap-1 border reader-ctrl rounded-btn p-0.5">
                    <button @click="setTheme('light')" 
                            :class="{ 'ring-1 ring-amber-500': theme === 'light' }"
                            class="w-5 h-5 sm:w-6 sm:h-6 rounded-badge bg-[#FAF7F2] text-xs flex items-center justify-center text-slate-800" title="{{ __('site.theme.light') }}">
                        <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
                    </button>
                    <button @click="setTheme('sepia')" 
                            :class="{ 'ring-1 ring-amber-500': theme === 'sepia' }"
                            class="w-5 h-5 sm:w-6 sm:h-6 rounded-badge bg-[#FBF0D9] text-xs flex items-center justify-center text-amber-900" title="{{ __('site.reader.theme_sepia') }}">
                        <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                    </button>
                    <button @click="setTheme('dark')" 
                            :class="{ 'ring-1 ring-amber-500': theme === 'dark' }"
                            class="w-5 h-5 sm:w-6 sm:h-6 rounded-badge bg-[#07090E] text-xs flex items-center justify-center text-amber-400" title="{{ __('site.theme.dark') }}">
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
                {{ __('site.reader.chapter_num', ['num' => $chapter->chapter_number]) }}
            </span>
            <h1 class="text-3xl sm:text-4xl font-bold font-serif tracking-tight reader-text leading-tight">{{ $chapter->title }}</h1>
        </div>

        <section class="mb-8 rounded-panel border border-ink-border bg-ink-900/60 p-4 sm:p-5 reader-ctrl" aria-labelledby="reader-notes-title">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 id="reader-notes-title" class="text-sm font-bold">{{ __('site.reader.notes_and_bookmarks') }}</h2>
                    <p class="mt-1 text-xs opacity-70">{{ __('site.reader.notes_hint') }}</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button type="button" @click="addBookmark()" :disabled="savingBookmark"
                            class="inline-flex min-h-11 items-center justify-center gap-2 rounded-btn border border-amber-500/30 bg-amber-500/10 px-3.5 py-2 text-xs font-semibold text-amber-500 transition hover:bg-amber-500/20 disabled:opacity-50">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 4.75A1.75 1.75 0 0 1 7.75 3h8.5A1.75 1.75 0 0 1 18 4.75V21l-6-4-6 4z"/></svg>
                        <span x-text="savingBookmark ? @js(__('site.reader.saving')) : @js(__('site.reader.bookmark_here'))"></span>
                    </button>
                    <button type="button" @click="openNoteForm()"
                            class="inline-flex min-h-11 items-center justify-center gap-2 rounded-btn border border-ink-border px-3.5 py-2 text-xs font-semibold transition hover:border-amber-500/40 hover:text-amber-500">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4z"/></svg>
                        {{ __('site.reader.write_note') }}
                    </button>
                </div>
            </div>

            <div x-show="noteFormOpen" x-cloak class="mt-4 space-y-3 border-t border-ink-border pt-4">
                <p class="text-xs text-mist">{{ rtrim(__('site.reader.note_pos_hint', ['pos' => '']), ' .:') }}: <span class="font-semibold text-amber-400" x-text="formatLocation(currentPosition)"></span>.</p>
                <div class="flex items-center justify-between">
                    <label for="reader-note-text" class="block text-xs font-semibold">{{ __('site.reader.note_color_label') }}</label>
                    <div class="flex items-center gap-1.5">
                        <button type="button" @click="noteColor = 'amber'" :aria-pressed="noteColor === 'amber'" :class="noteColor === 'amber' ? 'ring-2 ring-amber-400' : 'opacity-70'" class="inline-flex min-h-11 min-w-11 items-center justify-center rounded-full transition" aria-label="{{ __('site.reader.color_amber') }}"><span class="h-5 w-5 rounded-full bg-amber-400"></span></button>
                        <button type="button" @click="noteColor = 'emerald'" :aria-pressed="noteColor === 'emerald'" :class="noteColor === 'emerald' ? 'ring-2 ring-emerald-400' : 'opacity-70'" class="inline-flex min-h-11 min-w-11 items-center justify-center rounded-full transition" aria-label="{{ __('site.reader.color_emerald') }}"><span class="h-5 w-5 rounded-full bg-emerald-400"></span></button>
                        <button type="button" @click="noteColor = 'sky'" :aria-pressed="noteColor === 'sky'" :class="noteColor === 'sky' ? 'ring-2 ring-sky-400' : 'opacity-70'" class="inline-flex min-h-11 min-w-11 items-center justify-center rounded-full transition" aria-label="{{ __('site.reader.color_sky') }}"><span class="h-5 w-5 rounded-full bg-sky-400"></span></button>
                        <button type="button" @click="noteColor = 'rose'" :aria-pressed="noteColor === 'rose'" :class="noteColor === 'rose' ? 'ring-2 ring-rose-400' : 'opacity-70'" class="inline-flex min-h-11 min-w-11 items-center justify-center rounded-full transition" aria-label="{{ __('site.reader.color_rose') }}"><span class="h-5 w-5 rounded-full bg-rose-400"></span></button>
                        <button type="button" @click="noteColor = 'purple'" :aria-pressed="noteColor === 'purple'" :class="noteColor === 'purple' ? 'ring-2 ring-purple-400' : 'opacity-70'" class="inline-flex min-h-11 min-w-11 items-center justify-center rounded-full transition" aria-label="{{ __('site.reader.color_purple') }}"><span class="h-5 w-5 rounded-full bg-purple-400"></span></button>
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
                          class="ks-input resize-y" placeholder="{{ __('site.reader.note_placeholder') }}"></textarea>
                <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                    <button type="button" @click="noteFormOpen = false" class="min-h-11 rounded-btn px-4 py-2 text-xs font-semibold opacity-75 hover:opacity-100">{{ __('site.reader.cancel') }}</button>
                    <button type="button" @click="saveNote()" :disabled="savingNote || !noteDraft.trim()"
                            class="min-h-11 rounded-btn bg-amber-500 px-4 py-2 text-xs font-bold text-ink-950 transition hover:bg-amber-400 disabled:opacity-50"
                            x-text="savingNote ? @js(__('site.reader.saving')) : @js(__('site.reader.save_note'))"></button>
                </div>
            </div>

            <div class="mt-4 grid gap-4 border-t border-ink-border pt-4 md:grid-cols-2">
                <div>
                    <h3 class="mb-2 text-[11px] font-bold uppercase tracking-wider opacity-70">{{ __('site.reader.bookmarks') }} (<span x-text="bookmarks.length"></span>)</h3>
                    <template x-if="bookmarks.length === 0"><p class="text-xs opacity-60">{{ __('site.reader.no_bookmarks') }}</p></template>
                    <ul class="space-y-2">
                        <template x-for="bookmark in bookmarks" :key="bookmark.id">
                            <li class="flex items-center justify-between gap-2 rounded-btn border border-ink-border/70 px-3 py-2">
                                <button type="button" @click="jumpToBookmark(bookmark)" class="min-h-9 min-w-0 flex-1 truncate text-left text-xs font-medium hover:text-amber-500" x-text="@js(__('site.reader.marked_place', ['pos' => ''])).replace(':pos', '') + formatPosition(bookmark.position)"></button>
                                <button type="button" @click="removeBookmark(bookmark.id)" class="min-h-9 min-w-9 rounded-btn text-xs text-rose-400 hover:bg-rose-500/10" :aria-label="@js(__('site.reader.delete_bookmark'))">×</button>
                            </li>
                        </template>
                    </ul>
                </div>
                <div>
                    <h3 class="mb-2 text-[11px] font-bold uppercase tracking-wider opacity-70">{{ __('site.reader.notes') }} (<span x-text="notes.length"></span>)</h3>
                    <template x-if="notes.length === 0"><p class="text-xs opacity-60">{{ __('site.reader.no_notes') }}</p></template>
                    <ul class="max-h-52 space-y-2 overflow-y-auto">
                        <template x-for="note in notes" :key="note.id">
                            <li class="rounded-btn border border-ink-border/70 px-3 py-2">
                                <button type="button" @click="jumpToBookmark(note)"
                                        class="mb-2 inline-flex min-h-11 w-full items-center justify-between gap-2 rounded-btn px-2 text-left text-xs font-semibold text-amber-400 transition hover:bg-amber-500/10 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-amber-400"
                                        :aria-label="@js(__('site.reader.jump_to_note', ['pos' => ''])).replace(':pos', '') + formatLocation(note.position)">
                                    <span>{{ __('site.reader.note_saved_place') }}</span>
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
        <article class="reader-content leading-[1.8] transition-all reader-text text-justify"
                 :class="{
                     'reader-merriweather': fontFamily === 'merriweather' || fontFamily === 'serif',
                     'reader-spectral': fontFamily === 'spectral',
                     'reader-sans': fontFamily === 'sans',
                     'reader-mono': fontFamily === 'mono',
                     'is-bionic-active': bionicReading
                 }"
                 :style="`font-size: ${fontSize}px;`">
            <div x-show="!bionicReading" x-ref="rawContent">
                {!! nl2br(e($chapter->content)) !!}
            </div>
            <div x-show="bionicReading" x-html="bionicHtml" x-cloak>
            </div>
        </article>

        <!-- Chapter Navigation Footer -->
        <div class="mt-16 pt-8 border-t flex items-center justify-between" style="border-color: rgba(139,155,173,0.2);">
            <a href="{{ route('books.show', $book->slug) }}" class="ks-btn-ghost py-1.5 px-3 text-xs inline-flex items-center gap-1.5 font-mono">
                {{ __('site.reader.back_to_chapters') }}
            </a>
            <button @click="saveProgress(100)" class="ks-btn-primary py-2 px-4 text-xs font-semibold inline-flex items-center gap-1.5">
                <span>{{ __('site.reader.finish_chapter') }}</span>
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            </button>
        </div>
    </main>

    <script>
        function readerApp(initialBookmarks = [], initialNotes = []) {
            return {
                theme: localStorage.getItem('reader_theme') || (localStorage.getItem('kitob_theme') === 'light' ? 'light' : 'dark'),
                fontFamily: localStorage.getItem('reader_font') || 'merriweather',
                fontSize: parseInt(localStorage.getItem('reader_size')) || 19,
                fontOptions: [
                    { id: 'merriweather', label: 'Merriweather', icon: '📖', fontFamily: "'Merriweather', serif" },
                    { id: 'spectral', label: 'Spectral', icon: '📜', fontFamily: "'Spectral', serif" },
                    { id: 'sans', label: 'DM Sans', icon: '🔤', fontFamily: "'DM Sans', sans-serif" },
                    { id: 'mono', label: 'DM Mono', icon: '⌨️', fontFamily: "'DM Mono', monospace" }
                ],
                getFontName(id) {
                    const match = this.fontOptions.find(f => f.id === id);
                    if (match) return match.label;
                    if (id === 'sans') return 'DM Sans';
                    if (id === 'spectral') return 'Spectral';
                    if (id === 'mono') return 'DM Mono';
                    return 'Merriweather';
                },
                bionicReading: localStorage.getItem('reader_bionic') === 'true',
                bionicHtml: '',
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
                    if (!response.ok) throw new Error(data.message || @js(__('site.reader.error_saving')));
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
                        if (window.KitobSound) {
                            window.KitobSound.play('bookmark');
                        }
                        this.showAnnotationMessage(@js(__('site.reader.bookmark_saved')));
                    } catch (error) {
                        this.showAnnotationMessage(error.message || @js(__('site.reader.bookmark_save_error')));
                    } finally {
                        this.savingBookmark = false;
                    }
                },

                async removeBookmark(id) {
                    try {
                        const destroyUrl = @js(route('reader.bookmarks.destroy', ['bookmark' => '__BOOKMARK_ID__']));
                        await this.annotationRequest(destroyUrl.replace('__BOOKMARK_ID__', encodeURIComponent(id)), 'DELETE');
                        this.bookmarks = this.bookmarks.filter(bookmark => bookmark.id !== id);
                        this.showAnnotationMessage(@js(__('site.reader.bookmark_deleted')));
                    } catch (error) {
                        this.showAnnotationMessage(error.message || @js(__('site.reader.bookmark_delete_error')));
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
                    return @js(__('site.reader.chapter_part', ['percent' => '___PCT___'])).replace('___PCT___', this.formatPosition(position));
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
                        if (window.KitobSound) {
                            window.KitobSound.play('note_saved');
                        }
                        this.noteDraft = '';
                        this.noteFormOpen = false;
                        this.showAnnotationMessage(@js(__('site.reader.note_saved_profile')));
                    } catch (error) {
                        this.showAnnotationMessage(error.message || @js(__('site.reader.note_save_error')));
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

                    // Agar Bionic rejim oldindan yoqilgan bo'lsa, uni render qilish
                    if (this.bionicReading) {
                        this.$nextTick(() => this.generateBionic());
                    }

                    // B klavishi orqali Bionic Reading rejimini tezkor yoqish/o'chirish
                    window.addEventListener('keydown', (e) => {
                        const tag = e.target?.tagName?.toLowerCase();
                        if (tag === 'input' || tag === 'textarea' || e.target?.isContentEditable) return;
                        if ((e.key === 'b' || e.key === 'B') && !e.ctrlKey && !e.altKey && !e.metaKey) {
                            e.preventDefault();
                            this.toggleBionic();
                        }
                    });
                },

                toggleBionic() {
                    this.bionicReading = !this.bionicReading;
                    try {
                        localStorage.setItem('reader_bionic', this.bionicReading);
                    } catch (e) {}
                    if (this.bionicReading && !this.bionicHtml) {
                        this.generateBionic();
                    }
                    this.showAnnotationMessage(this.bionicReading ? @js(__('site.reader.bionic_enabled')) : @js(__('site.reader.bionic_disabled')));
                },

                generateBionic() {
                    const rawEl = this.$refs.rawContent;
                    if (!rawEl) return;
                    this.bionicHtml = this.convertToBionic(rawEl.innerHTML);
                },

                convertToBionic(html) {
                    const tagRegex = /(<[^>]+>)/g;
                    const parts = html.split(tagRegex);
                    const wordRegex = /([\p{L}\p{N}\u02BB\u02BC\u2018\u2019'']+)/gu;

                    return parts.map(part => {
                        if (part.startsWith('<') && part.endsWith('>')) {
                            return part;
                        }
                        return part.replace(wordRegex, (word) => {
                            const len = word.length;
                            if (len <= 1) return word;
                            let b = 1;
                            if (len <= 3) b = 1;
                            else if (len <= 5) b = 2;
                            else if (len <= 8) b = 3;
                            else if (len <= 11) b = Math.ceil(len * 0.42);
                            else b = Math.ceil(len * 0.38);

                            return `<b class="bionic-bold">${word.slice(0, b)}</b>${word.slice(b)}`;
                        });
                    }).join('');
                },

                setFont(f) {
                    this.fontFamily = f;
                    localStorage.setItem('reader_font', f);
                },

                toggleFont() {
                    const ids = ['merriweather', 'spectral', 'sans', 'mono'];
                    const curIdx = ids.indexOf(this.fontFamily);
                    const nextId = ids[(curIdx + 1) % ids.length];
                    this.setFont(nextId);
                },

                increaseFont() {
                    if (this.fontSize < 34) {
                        this.fontSize += 1;
                        localStorage.setItem('reader_size', this.fontSize);
                    }
                },

                decreaseFont() {
                    if (this.fontSize > 13) {
                        this.fontSize -= 1;
                        localStorage.setItem('reader_size', this.fontSize);
                    }
                },

                resetFontSize() {
                    this.fontSize = 19;
                    localStorage.setItem('reader_size', 19);
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
