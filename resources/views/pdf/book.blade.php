<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <title>{{ $book->title }}</title>
    <style>
        @page {
            margin: 28mm 20mm 24mm 20mm;
            header: html_bookHeader;
            footer: html_bookFooter;
        }
        @page :first {
            margin: 0;
            header: none;
            footer: none;
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            color: #1a2233;
            font-size: 11pt;
            line-height: 1.75;
        }

        /* ── Muqova sahifasi ─────────────────────────── */
        .cover-page {
            page-break-after: always;
            width: 100%;
            height: 297mm;
            background: linear-gradient(160deg, #06080d 0%, #111726 55%, #1a2236 100%);
            color: #ffffff;
            text-align: center;
            padding: 30mm 20mm 0 20mm;
        }
        .cover-brand {
            font-size: 9pt;
            letter-spacing: 4px;
            color: #f59e0b;
            text-transform: uppercase;
            margin-bottom: 26mm;
        }
        .cover-image {
            width: 88mm;
            height: 132mm;
            object-fit: cover;
            border-radius: 4mm;
            border: 0.6mm solid rgba(255,255,255,0.25);
        }
        .cover-title {
            font-size: 26pt;
            font-weight: 700;
            line-height: 1.25;
            margin-top: 14mm;
        }
        .cover-author {
            font-size: 13pt;
            color: #cbd5e1;
            margin-top: 6mm;
        }
        .cover-week {
            display: inline-block;
            margin-top: 12mm;
            font-size: 9pt;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: #06080d;
            background: #fbbf24;
            padding: 3mm 8mm;
            border-radius: 20mm;
            font-weight: 700;
        }

        /* ── Mundarija ───────────────────────────────── */
        .toc-page { page-break-after: always; }
        .section-label {
            font-size: 8.5pt;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: #d97706;
            font-weight: 700;
            margin-bottom: 4mm;
        }
        .toc-title { font-size: 19pt; font-weight: 700; margin-bottom: 10mm; }
        .toc-item {
            display: block;
            padding: 3.2mm 0;
            border-bottom: 0.25mm solid #e5e7eb;
            font-size: 11pt;
        }
        .toc-num {
            display: inline-block;
            width: 9mm;
            color: #d97706;
            font-weight: 700;
        }

        /* ── Boblar ──────────────────────────────────── */
        .chapter { page-break-before: always; }
        .chapter:first-of-type { page-break-before: avoid; }
        .chapter-num {
            font-size: 9pt;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: #d97706;
            font-weight: 700;
            margin-bottom: 3mm;
        }
        .chapter-title {
            font-size: 17pt;
            font-weight: 700;
            line-height: 1.3;
            margin-bottom: 8mm;
            padding-bottom: 4mm;
            border-bottom: 0.5mm solid #f59e0b;
        }
        .chapter-content p { margin: 0 0 5mm 0; text-align: justify; }

        .pdf-footer-brand { color: #94a3b8; }
        .pdf-footer-brand span { color: #d97706; font-weight: 700; }
    </style>
</head>
<body>

    {{-- 1. Muqova --}}
    <div class="cover-page">
        <div class="cover-brand">KITOBXON — Haftalik kitob platformasi</div>
        <img class="cover-image" src="{{ $book->cover_url }}">
        <div class="cover-title">{{ $book->title }}</div>
        <div class="cover-author">{{ $book->author }}</div>
        <div class="cover-week">{{ $book->week_number }}-hafta kitobi</div>
    </div>

    {{-- 2. Mundarija --}}
    <div class="toc-page">
        <div class="section-label">Mundarija</div>
        <div class="toc-title">Kitob boblari</div>
        @foreach ($chapters as $chapter)
            <span class="toc-item">
                <span class="toc-num">{{ str_pad($chapter->chapter_number, 2, '0', STR_PAD_LEFT) }}</span>
                {{ $chapter->title }}
            </span>
        @endforeach
    </div>

    {{-- 3. Boblar --}}
    @foreach ($chapters as $chapter)
        <div class="chapter">
            <div class="chapter-num">{{ $chapter->chapter_number }}-bob</div>
            <div class="chapter-title">{{ $chapter->title }}</div>
            <div class="chapter-content">
                @foreach (preg_split('/\n{2,}|\r\n{2,}/', trim($chapter->content)) as $paragraph)
                    @if (trim($paragraph) !== '')
                        <p>{{ trim($paragraph) }}</p>
                    @endif
                @endforeach
            </div>
        </div>
    @endforeach

    {{-- Header / Footer (muqovadan keyingi sahifalarda) --}}
    <htmlpageheader name="bookHeader">
        <div style="font-size: 8pt; color: #94a3b8; border-bottom: 0.25mm solid #e5e7eb; padding-bottom: 2mm;">
            {{ $book->title }} — {{ $book->author }}
        </div>
    </htmlpageheader>
    <htmlpagefooter name="bookFooter">
        <div style="font-size: 8pt; color: #94a3b8; border-top: 0.25mm solid #e5e7eb; padding-top: 2mm; text-align: center;">
            <span class="pdf-footer-brand"><span>📖 Kitobxon</span> — Haftalik kitob platformasi</span>
            &nbsp;&nbsp;|&nbsp;&nbsp;
            Sahifa {PAGE_NUM}
        </div>
    </htmlpagefooter>

</body>
</html>
