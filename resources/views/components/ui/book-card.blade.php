{{--
    Signature 1: Book 3D Opening — "kitob-ochilish" (MASTER §5.2)
    Props:
      book          App\Models\Book (majburiy)
      href          string|null — havola (default: books.show)
      progress      float|null  — 0..100 o'qish foizi
      showMeta      bool        — muallif/janr qatorini ko'rsatish
    Hover: muqova chap qirradan rotateY(-32deg) ochiladi, ostida varaq qirralari va annotatsiya.
    Touch: bir marta tap — ochiladi (preview), ikkinchi tap — havolaga o'tadi.
    Reduced motion: 3D yo'q, faqat opacity.
--}}
@props([
    'book',
    'href' => null,
    'progress' => null,
    'showMeta' => true,
    'ratio' => '2 / 3',
])

@php
    $link = $href ?? route('books.show', $book->slug);
    $cover = $book->cover_url ?? null;
    $hasPdf = !empty($book->pdf_path ?? null);
    $hasAudio = ($book->audios_count ?? 0) > 0;
    $hasVideo = ($book->videos_count ?? 0) > 0;
    $desc = \Illuminate\Support\Str::limit(strip_tags((string) ($book->description ?? '')), 140);
@endphp

@once
    <style>
        .ks-book { perspective: 1200px; width: 100%; }
        .ks-book__stage { position: relative; aspect-ratio: 2 / 3; width: 100%; }
        .ks-book__shadow {
            position: absolute; left: 10%; right: 6%; bottom: -10px; height: 18px; border-radius: 50%;
            background: rgba(0,0,0,.55); filter: blur(12px); opacity: .7;
            transition: transform 550ms var(--ease-book), filter 550ms var(--ease-book), opacity 550ms var(--ease-book);
        }
        /* Ochilganda ko'rinadigan ichki sahifa (annotatsiya) */
        .ks-book__inside {
            position: absolute; inset: 0; border-radius: 3px 8px 8px 3px; overflow: hidden;
            background:
                linear-gradient(90deg, rgba(0,0,0,.18) 0, rgba(0,0,0,0) 14%),
                #F0EDE6;
            color: #1A1D24; padding: 14% 12% 12% 22%;
            display: flex; flex-direction: column; justify-content: flex-end; gap: .5rem;
        }
        /* Varaq qirralari — o'ng tomonda qatlamlangan */
        .ks-book__pages {
            position: absolute; top: 2.5%; bottom: 2.5%; right: -5px; width: 6px; border-radius: 0 2px 2px 0;
            background: repeating-linear-gradient(90deg, #E5DFD5 0 1px, #CFC7B8 1px 2px);
            box-shadow: 1px 0 2px rgba(0,0,0,.35);
        }
        .ks-book__cover {
            position: absolute; inset: 0; border-radius: 3px 8px 8px 3px; overflow: hidden;
            transform-origin: left center; transform-style: preserve-3d; backface-visibility: hidden;
            transition: transform 550ms var(--ease-book), box-shadow 550ms var(--ease-book);
            box-shadow: 0 1px 0 rgba(255,255,255,.04) inset, 0 10px 24px -10px rgba(0,0,0,.8);
            background: #131926;
        }
        /* Kitob umurtqasi (spine) — chapdagi chuqurlik */
        .ks-book__cover::after {
            content: ""; position: absolute; inset: 0 auto 0 0; width: 9%;
            background: linear-gradient(90deg, rgba(0,0,0,.45), rgba(255,255,255,.08) 55%, rgba(0,0,0,0));
            pointer-events: none;
        }
        .ks-book.is-open .ks-book__cover,
        .ks-book:hover .ks-book__cover,
        .ks-book:focus-within .ks-book__cover {
            transform: rotateY(-32deg) translateX(4px);
            box-shadow: 18px 14px 30px -12px rgba(0,0,0,.85);
        }
        .ks-book.is-open .ks-book__shadow,
        .ks-book:hover .ks-book__shadow,
        .ks-book:focus-within .ks-book__shadow { transform: scaleX(1.1) translateX(6%); filter: blur(18px); opacity: .85; }

        @media (hover: none) {
            .ks-book:hover .ks-book__cover { transform: none; }
            .ks-book.is-open .ks-book__cover { transform: rotateY(-32deg) translateX(4px); }
        }
        @media (prefers-reduced-motion: reduce) {
            .ks-book .ks-book__cover { transform: none !important; transition: opacity 150ms linear !important; }
            .ks-book:hover .ks-book__cover, .ks-book.is-open .ks-book__cover, .ks-book:focus-within .ks-book__cover { opacity: .12; }
        }
    </style>
@endonce

<article
    {{ $attributes->merge(['class' => 'ks-book group flex flex-col']) }}
    x-data="{
        open: false,
        startX: 0,
        startY: 0,
        diffX: 0,
        isDragging: false,
        startDrag(e) {
            const pt = e.touches ? e.touches[0] : e;
            this.startX = pt.clientX;
            this.startY = pt.clientY;
            this.diffX = 0;
            this.isDragging = true;
        },
        moveDrag(e) {
            if (!this.isDragging) return;
            const pt = e.touches ? e.touches[0] : e;
            this.diffX = this.startX - pt.clientX;
            const diffY = Math.abs(this.startY - pt.clientY);
            if (this.diffX > 25 && diffY < 45) {
                this.open = true;
            } else if (this.diffX < -25 && diffY < 45) {
                this.open = false;
            }
        },
        endDrag(e) {
            if (!this.isDragging) return;
            this.isDragging = false;
            if (this.diffX > 60) {
                window.location.href = '{{ $link }}';
            }
        }
    }"
    :class="{ 'is-open': open }"
    @click.outside="open = false"
    @touchstart.passive="startDrag($event)"
    @touchmove="moveDrag($event)"
    @touchend="endDrag($event)"
>
    <a href="{{ $link }}"
       class="ks-book__stage block rounded-card focus-visible:outline-offset-4"
       style="aspect-ratio: {{ $ratio }}"
       aria-label="{{ $book->title }} — {{ $book->author }}"
       @click="if (window.matchMedia('(hover: none)').matches && !open) { $event.preventDefault(); open = true; }">

        <div class="ks-book__shadow" aria-hidden="true"></div>

        {{-- Ichki sahifa: annotatsiya --}}
        <div class="ks-book__inside" aria-hidden="true">
            <span class="font-mono text-[10px] uppercase tracking-[0.14em] text-[#526071]">{{ $book->genre }}</span>
            <p class="font-display text-[15px] font-semibold leading-[1.3] text-[#1A1D24]">{{ $book->title }}</p>
            @if($desc)
                <p class="text-[11.5px] leading-[1.5] text-[#3A4250] line-clamp-5">{{ $desc }}</p>
            @endif
            <span class="mt-1 inline-flex items-center gap-1 text-[11px] font-semibold text-[#B83224]">
                O'qishni boshlash
                <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
            </span>
        </div>

        <div class="ks-book__pages" aria-hidden="true"></div>

        {{-- Muqova --}}
        <div class="ks-book__cover">
            @if($cover)
                <img src="{{ $cover }}" alt="" loading="lazy" decoding="async" class="absolute inset-0 w-full h-full object-cover">
            @else
                <div class="absolute inset-0 flex flex-col justify-between p-[12%] pl-[16%] bg-[#1A0E0C]">
                    <span class="font-mono text-[10px] uppercase tracking-[0.14em] text-[#C9A15A]">{{ $book->author }}</span>
                    <span class="font-display text-lg font-semibold leading-[1.25] text-paper">{{ $book->title }}</span>
                    <span class="h-px w-10 bg-[#C9A15A]/60"></span>
                </div>
            @endif

            <div class="absolute inset-x-0 top-0 p-2.5 pl-[12%] flex items-start justify-between gap-2">
                @if(!empty($book->week_number))
                    <span class="ks-badge bg-gilt text-ink-950 shadow-sm">{{ $book->week_number }}-hafta</span>
                @else
                    <span></span>
                @endif
                <span class="flex flex-col items-end gap-1">
                    @if($hasPdf)<span class="ks-badge bg-ink-950/80 text-paper backdrop-blur-sm">PDF</span>@endif
                    @if($hasAudio)<span class="ks-badge bg-ink-950/80 text-paper backdrop-blur-sm">Audio</span>@endif
                    @if($hasVideo)<span class="ks-badge bg-ink-950/80 text-paper backdrop-blur-sm">Video</span>@endif
                </span>
            </div>
        </div>
    </a>

    @if($showMeta)
        <div class="pt-4 flex-1 flex flex-col">
            <h3 class="font-display text-[15px] font-semibold leading-[1.35] text-paper line-clamp-2">
                <a href="{{ $link }}" class="hover:text-amber-400 transition-colors duration-base">{{ $book->title }}</a>
            </h3>
            <div class="mt-1 flex items-center justify-between gap-1">
                <p class="font-mono text-[11px] uppercase tracking-[0.06em] text-mist truncate">{{ $book->author }}</p>
                <button type="button"
                        onclick="event.preventDefault(); event.stopPropagation(); if (window.openBookShare) window.openBookShare({ title: '{{ addslashes($book->title) }}', author: '{{ addslashes($book->author) }}', url: '{{ $link }}', coverUrl: '{{ $cover }}', description: '{{ addslashes($desc) }}' })"
                        class="p-1 rounded-btn text-mist hover:text-amber-400 hover:bg-white/5 transition-colors shrink-0"
                        title="Kitobni ulashish">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="18" cy="5" r="3"/>
                        <circle cx="6" cy="12" r="3"/>
                        <circle cx="18" cy="19" r="3"/>
                        <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/>
                        <line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/>
                    </svg>
                </button>
            </div>

            @if(!is_null($progress))
                <div class="mt-3" role="progressbar" aria-valuenow="{{ (int) $progress }}" aria-valuemin="0" aria-valuemax="100" aria-label="O'qish jarayoni">
                    <div class="flex justify-between font-mono text-[10px] text-mist mb-1">
                        <span>O'qildi</span><span class="tabular-nums">{{ (int) $progress }}%</span>
                    </div>
                    <div class="h-[3px] rounded-full bg-ink-700 overflow-hidden">
                        <div class="h-full bg-amber-500 rounded-full transition-[width] duration-500 ease-out" style="width: {{ max(0, min(100, (float) $progress)) }}%"></div>
                    </div>
                </div>
            @endif

            {{ $slot }}
        </div>
    @endif
</article>
