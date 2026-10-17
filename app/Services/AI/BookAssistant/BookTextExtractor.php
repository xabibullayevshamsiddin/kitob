<?php

namespace App\Services\AI\BookAssistant;

use App\Models\Book;
use App\Models\BookPage;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Config as PdfParserConfig;
use Smalot\PdfParser\Parser as PdfParser;
use Smalot\PdfParser\RawData\RawDataParser;
use Symfony\Component\Process\Process;

class BookTextExtractor
{
    /**
     * Kitobning PDF faylidan bet-bet matn ajratadi, sifatini baholaydi va book_pages jadvaliga saqlaydi.
     *
     * @return array{
     *     passed: bool,
     *     quality_score: float,
     *     pages_count: int,
     *     avg_chars_per_page: float,
     *     extractor: string,
     *     error_message: string|null
     * }
     */
    public function extractBookPdf(Book $book, bool $force = false): array
    {
        $pdfFullPath = $this->resolvePdfPath($book);

        if (!$pdfFullPath || !is_file($pdfFullPath)) {
            return [
                'passed'             => false,
                'quality_score'      => 0.0,
                'pages_count'        => 0,
                'avg_chars_per_page' => 0.0,
                'extractor'          => 'none',
                'error_message'      => 'Kitob PDF fayli topilmadi.',
            ];
        }

        $pdfContentHash = hash_file('sha256', $pdfFullPath);
        if (!is_string($pdfContentHash)) {
            return [
                'passed'             => false,
                'quality_score'      => 0.0,
                'pages_count'        => 0,
                'avg_chars_per_page' => 0.0,
                'extractor'          => 'none',
                'error_message'      => 'PDF faylini o\'qib bo\'lmadi.',
            ];
        }

        $existingPagesCount = BookPage::where('book_id', $book->id)->count();
        $cachedPdfHash = (string) ($book->ai_pdf_content_hash ?? '');
        if (!$force && $existingPagesCount > 0 && $cachedPdfHash !== '' && hash_equals($cachedPdfHash, $pdfContentHash)) {
            return $this->evaluateSavedPages($book, 'cached');
        }

        $extraction = $this->extractRawPagesFromFile($pdfFullPath);
        $rawPages = $extraction['pages'];
        $extractorName = $extraction['extractor'];

        if (empty($rawPages)) {
            $msg = trans('ai.low_text_quality');
            $book->update([
                'ai_text_quality' => 0.0,
                'ai_index_status' => 'failed',
                'ai_index_error'  => $msg,
            ]);

            return [
                'passed'             => false,
                'quality_score'      => 0.0,
                'pages_count'        => 0,
                'avg_chars_per_page' => 0.0,
                'extractor'          => $extractorName,
                'error_message'      => $msg,
            ];
        }

        $now = now();
        $rowsToUpsert = [];

        foreach ($rawPages as $index => $rawPageText) {
            $pageNum = $index + 1;
            $cleanedText = $this->sanitizeUtf8Text((string) $rawPageText);
            $pageScore = $this->scorePageTextQuality($cleanedText);

            $rowsToUpsert[] = [
                'book_id'       => $book->id,
                'page_number'   => $pageNum,
                'text'          => $cleanedText,
                'source'        => 'pdf',
                'quality_score' => $pageScore,
                'created_at'    => $now,
                'updated_at'    => $now,
            ];

            if (count($rowsToUpsert) >= 50) {
                BookPage::upsert(
                    $rowsToUpsert,
                    ['book_id', 'page_number'],
                    ['text', 'source', 'quality_score', 'updated_at']
                );
                $rowsToUpsert = [];
            }
        }

        if (!empty($rowsToUpsert)) {
            BookPage::upsert(
                $rowsToUpsert,
                ['book_id', 'page_number'],
                ['text', 'source', 'quality_score', 'updated_at']
            );
        }

        // Ortiqcha eski betlar bo'lsa o'chirish
        BookPage::where('book_id', $book->id)
            ->where('page_number', '>', count($rawPages))
            ->delete();

        // Eski betlar faqat yangi PDF matni saqlangandan keyin shu fayl hashiga bog'lanadi.
        $book->forceFill(['ai_pdf_content_hash' => $pdfContentHash])->save();

        return $this->evaluateSavedPages($book, $extractorName);
    }

    /**
     * Saqlangan book_pages asosida umumiy kitob matni sifatini tekshiradi.
     */
    public function evaluateSavedPages(Book $book, string $extractorName = 'pdf'): array
    {
        $pages = BookPage::where('book_id', $book->id)
            ->orderBy('page_number')
            ->get(['page_number', 'text', 'quality_score']);

        $pagesCount = $pages->count();
        if ($pagesCount === 0) {
            return [
                'passed'             => false,
                'quality_score'      => 0.0,
                'pages_count'        => 0,
                'avg_chars_per_page' => 0.0,
                'extractor'          => $extractorName,
                'error_message'      => trans('ai.low_text_quality'),
            ];
        }

        $totalChars = 0;
        $totalScore = 0.0;
        $uniqueHashes = [];

        foreach ($pages as $p) {
            $trimmed = trim((string) $p->text);
            $len = mb_strlen($trimmed);
            $totalChars += $len;
            $totalScore += (float) $p->quality_score;
            if ($trimmed !== '') {
                $uniqueHashes[md5(mb_strtolower($trimmed))] = true;
            }
        }

        $avgCharsPerPage = round($totalChars / $pagesCount, 1);
        $avgQuality = round($totalScore / $pagesCount, 3);

        // Agar ko'p betli kitobda barcha betlarda faqat bitta watermark (masalan "http://eduportal.uz") takrorlansa
        if ($pagesCount >= 4 && count($uniqueHashes) <= 1) {
            $avgQuality = min($avgQuality, 0.05);
        }

        $minQuality = (float) config('ai.min_text_quality', 0.45);
        $minAvgChars = (int) config('ai.min_avg_chars_per_page', 120);

        $passed = ($avgQuality >= $minQuality) && ($avgCharsPerPage >= $minAvgChars);
        $errorMessage = $passed ? null : trans('ai.low_text_quality');

        $book->update([
            'ai_text_quality' => $avgQuality,
            'ai_index_error'  => $errorMessage,
        ]);

        return [
            'passed'             => $passed,
            'quality_score'      => $avgQuality,
            'pages_count'        => $pagesCount,
            'avg_chars_per_page' => $avgCharsPerPage,
            'extractor'          => $extractorName,
            'error_message'      => $errorMessage,
        ];
    }

    /**
     * Bitta bet matnining sifatini 0.0 dan 1.0 gacha baholaydi.
     */
    public function scorePageTextQuality(string $text): float
    {
        $trimmed = trim($text);
        $totalLen = mb_strlen($trimmed);

        if ($totalLen < 30) {
            return 0.0;
        }

        // Harflar (lotin, kirill va b.), raqamlar va bo'shliqlar soni
        $lettersCount = preg_match_all('/\p{L}/u', $trimmed);
        $digitsCount = preg_match_all('/\p{N}/u', $trimmed);
        $spacesCount = preg_match_all('/\s/u', $trimmed);
        $punctCount = preg_match_all('/[.,;:!?\'"()\[\]\-–—«»\/]/u', $trimmed);

        // Tushunarsiz/buzilgan belgilar (Private Use Area, replacement char, boshqaruv belgilari)
        $garbledCount = preg_match_all('/[\x{FFFD}\x{E000}-\x{F8FF}\x{FFF0}-\x{FFFF}]/u', $trimmed);

        $readableChars = $lettersCount + $digitsCount + $spacesCount + $punctCount;
        $readableRatio = $totalLen > 0 ? ($readableChars / $totalLen) : 0.0;
        $letterRatio = $totalLen > 0 ? ($lettersCount / $totalLen) : 0.0;
        $garbledRatio = $totalLen > 0 ? ($garbledCount / $totalLen) : 1.0;

        // Agar harflar ulushi juda past bo'lsa (< 35%) yoki tushunarsiz belgilar ko'p bo'lsa (> 12%)
        if ($letterRatio < 0.35 || $garbledRatio > 0.12) {
            return round(max(0.0, min(0.25, $letterRatio * 0.5)), 3);
        }

        // Betdagi harflar soni bo'yicha uzunlik ko'rsatkichi (120 ta belgidan 450 tagacha chiziqli o'sadi)
        $lengthFactor = min(1.0, max(0.0, ($totalLen - 30) / 350.0));

        // So'zlarning tabiiylik darajasi (2..25 harfli so'zlar ulushi)
        $words = preg_split('/\s+/u', $trimmed, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $validWords = 0;
        foreach ($words as $w) {
            $cleanW = preg_replace('/[^\p{L}\p{N}]/u', '', $w) ?? '';
            $wLen = mb_strlen($cleanW);
            if ($wLen >= 2 && $wLen <= 28 && preg_match('/\p{L}/u', $cleanW)) {
                $validWords++;
            }
        }
        $wordNaturalness = count($words) > 0 ? ($validWords / count($words)) : 0.0;

        $score = ($readableRatio * 0.35) + ($lengthFactor * 0.35) + ($wordNaturalness * 0.30) - ($garbledRatio * 2.0);

        return round(max(0.0, min(1.0, $score)), 3);
    }

    /**
     * Matnni xavfsiz UTF-8 ko'rinishiga keltiradi.
     */
    public function sanitizeUtf8Text(string $raw): string
    {
        if ($raw === '') {
            return '';
        }

        // Windows-1252 / PDF smart-quote va tire belgilarini standart UTF-8 ga o'girish
        $clean = @iconv('UTF-8', 'UTF-8//IGNORE', $raw);
        if ($clean === false || $clean === '') {
            $clean = mb_convert_encoding($raw, 'UTF-8', 'UTF-8');
        }

        // Nul va boshqaruv belgilarini tozalash (\n va \t qoladi)
        $clean = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', ' ', $clean) ?? $clean;
        // Ketma-ket gorizontal bo'shliqlarni bitta probelga keltirish
        $clean = preg_replace('/[^\S\r\n]+/u', ' ', $clean) ?? $clean;
        // 3+ qator tashlashlarni 2 taga qisqartirish
        $clean = preg_replace('/\R{3,}/u', "\n\n", $clean) ?? $clean;

        return trim($clean);
    }

    /**
     * Avval pdftotext (serverda bo'lsa), bo'lmasa smalot/pdfparser orqali betlar matnini ajratadi.
     *
     * @return array{extractor: string, pages: string[]}
     */
    public function extractRawPagesFromFile(string $pdfFullPath): array
    {
        if ($this->isPdftotextAvailable()) {
            $pages = $this->extractViaPdftotext($pdfFullPath);
            if ($pages !== null) {
                return [
                    'extractor' => 'pdftotext',
                    'pages'     => $pages,
                ];
            }
        }

        return [
            'extractor' => 'smalot/pdfparser',
            'pages'     => $this->extractViaSmalotParser($pdfFullPath),
        ];
    }

    public function isPdftotextAvailable(): bool
    {
        $bin = (string) config('ai.pdftotext_bin', 'pdftotext');

        try {
            $process = new Process([$bin, '-v']);
            $process->setTimeout(5);
            $process->run();

            return $process->getExitCode() === 0 || str_contains(strtolower($process->getErrorOutput()), 'pdftotext');
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * @return string[]|null
     */
    protected function extractViaPdftotext(string $pdfFullPath): ?array
    {
        $bin = (string) config('ai.pdftotext_bin', 'pdftotext');

        try {
            $process = new Process([$bin, '-enc', 'UTF-8', '-eol', 'unix', $pdfFullPath, '-']);
            $process->setTimeout(60);
            $process->run();

            if (!$process->isSuccessful()) {
                return null;
            }

            $output = $process->getOutput();
            $pages = explode("\f", $output);
            // Oxirgi form-feed dan keyingi bo'sh elementni olib tashlash
            if (count($pages) > 1 && trim((string) end($pages)) === '') {
                array_pop($pages);
            }

            return $pages;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * smalot/pdfparser orqali rasm va shrift oqimlarini (streams) o'tkazib yuborgan holda
     * xotiraga tejamkor bet-bet matn ajratish.
     *
     * @return string[]
     */
    protected function extractViaSmalotParser(string $pdfFullPath): array
    {
        $cfg = new PdfParserConfig();
        $cfg->setRetainImageContent(false);
        $cfg->setDecodeMemoryLimit(2 * 1024 * 1024);

        $parser = new class([], $cfg) extends PdfParser {
            public function __construct($cfg = [], ?PdfParserConfig $config = null)
            {
                parent::__construct($cfg, $config);
                $this->rawDataParser = new class($cfg, $this->getConfig()) extends RawDataParser {
                    protected function shouldSkipHeavyStream(array $sdic, int $streamLen): bool
                    {
                        if ($streamLen > 524288) {
                            return true;
                        }

                        foreach ($sdic as $k => $v) {
                            if (($v[0] ?? '') === '/') {
                                $key = $v[1] ?? '';
                                $nextVal = $sdic[$k + 1][1] ?? '';
                                if ($key === 'Subtype' && in_array($nextVal, ['Image', 'CIDFontType0C', 'TrueType', 'OpenType', 'Type1C', 'XML'], true)) {
                                    return true;
                                }
                                if (in_array($key, ['BitsPerComponent', 'ColorSpace', 'FontFile', 'FontFile2', 'FontFile3', 'Metadata', 'Length1', 'Length2', 'Length3'], true)) {
                                    return true;
                                }
                                if ($key === 'Filter' && in_array($nextVal, ['DCTDecode', 'JPXDecode', 'CCITTFaxDecode', 'JBIG2Decode'], true)) {
                                    return true;
                                }
                            }
                        }

                        return false;
                    }

                    protected function getIndirectObject(string $pdfData, array $xref, string $objRef, int $offset = 0, bool $decoding = true): array
                    {
                        $arr = parent::getIndirectObject($pdfData, $xref, $objRef, $offset, $decoding);
                        $headerDic = (isset($arr[0][0]) && $arr[0][0] === '<<' && is_array($arr[0][1])) ? $arr[0][1] : [];

                        foreach ($arr as $i => $el) {
                            if (($el[0] ?? '') === 'stream') {
                                $rawLen = strlen($el[1] ?? '');
                                $arr[$i][1] = '';
                                if ($this->shouldSkipHeavyStream($headerDic, $rawLen)) {
                                    $arr[$i][3] = ['', []];
                                }
                            }
                        }

                        return $arr;
                    }

                    protected function decodeStream(string $pdfData, array $xref, array $sdic, string $stream): array
                    {
                        if ($this->shouldSkipHeavyStream($sdic, strlen($stream))) {
                            return ['', []];
                        }

                        return parent::decodeStream($pdfData, $xref, $sdic, $stream);
                    }
                };
            }
        };

        $document = $parser->parseFile($pdfFullPath);
        $pages = $document->getPages();
        $result = [];

        foreach ($pages as $page) {
            try {
                $result[] = $page->getText();
            } catch (\Throwable $e) {
                $result[] = '';
            }
        }

        return $result;
    }

    public function resolvePdfPath(Book $book): ?string
    {
        if (empty($book->pdf_path)) {
            return null;
        }

        if (is_file($book->pdf_path)) {
            return $book->pdf_path;
        }

        if (Storage::disk('public')->exists($book->pdf_path)) {
            return Storage::disk('public')->path($book->pdf_path);
        }

        $candidate = storage_path('app/public/' . ltrim($book->pdf_path, '/\\'));
        if (is_file($candidate)) {
            return $candidate;
        }

        return null;
    }
}
