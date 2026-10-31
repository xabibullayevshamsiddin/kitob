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
    /**
     * Kitobning PDF faylidan bet-bet matn ajratadi, sifatini baholaydi va book_pages jadvaliga saqlaydi.
     * Ko'p sahifali (masalan 120+ betli) kitoblarni partiyalab to'liq qamrab oladi va matnli/skaner
     * sahifalar aralash bo'lsa, faqat kerakli sahifalargagina OCR qo'llaydi.
     *
     * @return array{
     *     passed: bool,
     *     quality_score: float,
     *     pages_count: int,
     *     total_pages: int,
     *     valid_text_pages: int,
     *     ocr_pages: int,
     *     blank_pages: int,
     *     error_pages: int,
     *     processed_percentage: float,
     *     quality_percentage: float,
     *     avg_chars_per_page: float,
     *     extractor: string,
     *     error_message: string|null,
     *     metrics: array<string, mixed>
     * }
     */
    public function extractBookPdf(Book $book, bool $force = false, ?callable $onProgress = null): array
    {
        @ini_set('memory_limit', '1024M');
        @set_time_limit(600);

        $pdfFullPath = $this->resolvePdfPath($book);

        if (!$pdfFullPath || !is_file($pdfFullPath)) {
            return [
                'passed'               => false,
                'quality_score'        => 0.0,
                'pages_count'          => 0,
                'total_pages'          => 0,
                'valid_text_pages'     => 0,
                'ocr_pages'            => 0,
                'blank_pages'          => 0,
                'error_pages'          => 0,
                'processed_percentage' => 0.0,
                'quality_percentage'   => 0.0,
                'avg_chars_per_page'   => 0.0,
                'extractor'            => 'none',
                'error_message'        => 'Kitob PDF fayli topilmadi.',
                'metrics'              => [],
            ];
        }

        $pdfContentHash = hash_file('sha256', $pdfFullPath);
        if (!is_string($pdfContentHash)) {
            return [
                'passed'               => false,
                'quality_score'        => 0.0,
                'pages_count'          => 0,
                'total_pages'          => 0,
                'valid_text_pages'     => 0,
                'ocr_pages'            => 0,
                'blank_pages'          => 0,
                'error_pages'          => 0,
                'processed_percentage' => 0.0,
                'quality_percentage'   => 0.0,
                'avg_chars_per_page'   => 0.0,
                'extractor'            => 'none',
                'error_message'        => 'PDF faylini o\'qib bo\'lmadi.',
                'metrics'              => [],
            ];
        }

        $totalPdfPages = $this->detectTotalPdfPages($pdfFullPath);
        $existingPagesCount = BookPage::where('book_id', $book->id)->count();
        $cachedPdfHash = (string) ($book->ai_pdf_content_hash ?? '');

        if (!$force && $existingPagesCount > 0 && $cachedPdfHash !== '' && hash_equals($cachedPdfHash, $pdfContentHash)) {
            return $this->evaluateSavedPages($book, 'cached', $totalPdfPages);
        }

        if ($onProgress) {
            $onProgress('extracting_native_pages', 0, max(1, $totalPdfPages));
        }

        // 1. Asosiy matnli (native) ekstraksiya
        $extraction = $this->extractRawPagesFromFile($pdfFullPath);
        $rawPages = $extraction['pages'];
        $extractorName = $extraction['extractor'];

        $totalPagesCount = max(count($rawPages), $totalPdfPages);
        if ($totalPagesCount === 0) {
            $totalPagesCount = count($rawPages);
        }

        // 2. Har bir sahifaning sifatini tekshirish va OCR talab qiladigan sahifalarni aniqlash
        $pagesNeedingOcr = [];
        $pageData = []; // page_number => ['text' => ..., 'source' => ..., 'quality_score' => ...]

        for ($i = 0; $i < $totalPagesCount; $i++) {
            $pageNum = $i + 1;
            $rawText = $rawPages[$i] ?? '';
            $cleaned = $this->sanitizeUtf8Text((string) $rawText);
            $score = $this->scorePageTextQuality($cleaned);
            $trimmedLen = mb_strlen(trim($cleaned));

            $pageData[$pageNum] = [
                'page_number'   => $pageNum,
                'text'          => $cleaned,
                'source'        => 'pdf',
                'quality_score' => $score,
                'is_blank'      => false,
            ];

            // Agar matn yetarli bo'lmasa yoki sifati past bo'lsa (skanerlangan / tasvirli sahifalar ham kiradi)
            if ($score < 0.45 || $trimmedLen < 40) {
                $pagesNeedingOcr[] = $pageNum;
            }
        }

        $ocrAvailable = config('ai.ocr.enabled', true) && $this->isOcrAvailable();
        $ocrSuccessCount = 0;

        // 3. OCR zarur bo'lgan sahifalarni partiyalab (batch: 1-50, 51-100 va h.k.) qayta ishlash
        if (!empty($pagesNeedingOcr) && $ocrAvailable) {
            $batchSize = max(1, (int) config('ai.ocr.max_pages', 50));
            $ocrBatches = array_chunk($pagesNeedingOcr, $batchSize);
            $totalBatches = count($ocrBatches);

            foreach ($ocrBatches as $bIdx => $pageBatch) {
                if ($onProgress) {
                    $onProgress('ocr_processing', $bIdx + 1, $totalBatches);
                }

                $pagesListStr = implode(',', $pageBatch);
                $ocrRes = $this->extractViaOcr($pdfFullPath, $onProgress, 1, $batchSize, $pagesListStr);
                $ocrPages = $ocrRes['pages'] ?? [];

                // Olingan OCR matnlarini o'z o'rniga joylashtiramiz
                foreach ($ocrPages as $ocrP) {
                    $pNum = (int) ($ocrP['page_number'] ?? 0);
                    $ocrText = $this->sanitizeUtf8Text((string) ($ocrP['text'] ?? ''));
                    if ($pNum > 0 && isset($pageData[$pNum])) {
                        $ocrScore = $this->scorePageTextQuality($ocrText);
                        $nativeScore = $pageData[$pNum]['quality_score'];

                        // MUHIM QOIDA: To'g'ri ajratilgan matnni sifati pastroq OCR natijasi bilan almashtirmaymiz!
                        if ($ocrScore > $nativeScore && mb_strlen($ocrText) >= 30) {
                            $pageData[$pNum]['text'] = $ocrText;
                            $pageData[$pNum]['source'] = 'ocr';
                            $pageData[$pNum]['quality_score'] = $ocrScore;
                            $ocrSuccessCount++;
                        }
                    }
                }
            }
        }

        // OCR'dan so'ng har bir sahifaning haqiqiy holatini (yakuniy matn bo'shligini) aniqlaymiz
        foreach ($pageData as $pNum => &$pInfo) {
            $finalText = $pInfo['text'];
            $finalLen = mb_strlen(trim($finalText));
            $pInfo['is_blank'] = ($finalLen < 20 && preg_match('/^[\s\p{P}]*$/u', $finalText));
        }
        unset($pInfo);

        // 4. Ma'lumotlarni book_pages jadvaliga saqlash
        $now = now();
        $rowsToUpsert = [];
        $validTextPages = 0;
        $blankPages = 0;
        $errorPages = 0;
        $ocrPages = 0;
        $totalChars = 0;
        $totalScore = 0.0;

        foreach ($pageData as $pageNum => $pInfo) {
            $cleanedText = $pInfo['text'];
            $pScore = $pInfo['quality_score'];
            $pSource = $pInfo['source'];

            if ($pInfo['is_blank'] && mb_strlen(trim($cleanedText)) < 15) {
                $blankPages++;
            } elseif ($pScore >= 0.45) {
                $validTextPages++;
            } else {
                $errorPages++;
            }

            if ($pSource === 'ocr') {
                $ocrPages++;
            }

            $totalChars += mb_strlen(trim($cleanedText));
            $totalScore += $pScore;

            $rowsToUpsert[] = [
                'book_id'       => $book->id,
                'page_number'   => $pageNum,
                'text'          => $cleanedText,
                'source'        => $pSource,
                'quality_score' => $pScore,
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
            ->where('page_number', '>', $totalPagesCount)
            ->delete();

        $book->forceFill(['ai_pdf_content_hash' => $pdfContentHash])->save();

        return $this->evaluateSavedPages($book, $extractorName, $totalPagesCount);
    }

    /**
     * Saqlangan book_pages asosida umumiy kitob matni sifatini tekshiradi va to'liq hisobot tuzadi.
     */
    public function evaluateSavedPages(Book $book, string $extractorName = 'pdf', int $detectedTotalPages = 0): array
    {
        $pages = BookPage::where('book_id', $book->id)
            ->orderBy('page_number')
            ->get(['page_number', 'text', 'quality_score', 'source']);

        $pagesCount = $pages->count();
        $totalPages = max($pagesCount, $detectedTotalPages);

        if ($pagesCount === 0) {
            return [
                'passed'               => false,
                'quality_score'        => 0.0,
                'pages_count'          => 0,
                'total_pages'          => $totalPages,
                'valid_text_pages'     => 0,
                'ocr_pages'            => 0,
                'blank_pages'          => 0,
                'error_pages'          => 0,
                'processed_percentage' => 0.0,
                'quality_percentage'   => 0.0,
                'avg_chars_per_page'   => 0.0,
                'extractor'            => $extractorName,
                'error_message'        => trans('ai.low_text_quality'),
                'metrics'              => [],
            ];
        }

        $totalChars = 0;
        $totalScore = 0.0;
        $uniqueHashes = [];
        $validTextPages = 0;
        $ocrPages = 0;
        $blankPages = 0;
        $errorPages = 0;

        foreach ($pages as $p) {
            $trimmed = trim((string) $p->text);
            $len = mb_strlen($trimmed);
            $score = (float) $p->quality_score;
            $source = (string) $p->source;

            $totalChars += $len;
            $totalScore += $score;

            if ($len < 20 && preg_match('/^[\s\p{P}]*$/u', $trimmed)) {
                $blankPages++;
            } elseif ($score >= 0.45) {
                $validTextPages++;
            } else {
                $errorPages++;
            }

            if ($source === 'ocr') {
                $ocrPages++;
            }

            if ($trimmed !== '') {
                $uniqueHashes[md5(mb_strtolower($trimmed))] = true;
            }
        }

        $avgCharsPerPage = round($totalChars / $pagesCount, 1);
        $avgQuality = round($totalScore / $pagesCount, 3);

        // Agar ko'p betli kitobda barcha betlarda faqat bitta watermark takrorlansa
        if ($pagesCount >= 4 && count($uniqueHashes) <= 1) {
            $avgQuality = min($avgQuality, 0.05);
        }

        $minQuality = (float) config('ai.min_text_quality', 0.45);
        $minAvgChars = (int) config('ai.min_avg_chars_per_page', 120);

        // Qamrov va sifat foizlari
        $processedPercentage = $totalPages > 0 ? round(($pagesCount / $totalPages) * 100, 1) : 100.0;
        $activePages = max(1, $totalPages - $blankPages);
        $qualityPercentage = round(($validTextPages / $activePages) * 100, 1);

        $passed = ($avgQuality >= $minQuality) && ($avgCharsPerPage >= $minAvgChars);
        $errorMessage = $passed ? null : trans('ai.low_text_quality');

        $metrics = [
            'total_pages'          => $totalPages,
            'processed_pages'      => $pagesCount,
            'valid_text_pages'     => $validTextPages,
            'ocr_pages'            => $ocrPages,
            'blank_pages'          => $blankPages,
            'error_pages'          => $errorPages,
            'processed_percentage' => $processedPercentage,
            'quality_percentage'   => $qualityPercentage,
            'avg_chars_per_page'   => $avgCharsPerPage,
            'avg_quality_score'    => $avgQuality,
            'extractor'            => $extractorName,
        ];

        $book->update([
            'ai_text_quality'    => $avgQuality,
            'ai_index_error'     => $errorMessage,
            'ai_total_pages'     => $totalPages,
            'ai_processed_pages' => $pagesCount,
            'ai_index_metrics'   => $metrics,
        ]);

        return [
            'passed'               => $passed,
            'quality_score'        => $avgQuality,
            'pages_count'          => $pagesCount,
            'total_pages'          => $totalPages,
            'valid_text_pages'     => $validTextPages,
            'ocr_pages'            => $ocrPages,
            'blank_pages'          => $blankPages,
            'error_pages'          => $errorPages,
            'processed_percentage' => $processedPercentage,
            'quality_percentage'   => $qualityPercentage,
            'avg_chars_per_page'   => $avgCharsPerPage,
            'extractor'            => $extractorName,
            'error_message'        => $errorMessage,
            'metrics'              => $metrics,
        ];
    }

    /**
     * PDF faylining haqiqiy sahifalar sonini mustaqil aniqlaydi.
     */
    public function detectTotalPdfPages(string $pdfFullPath): int
    {
        if (!is_file($pdfFullPath)) {
            return 0;
        }

        // 1. PDF /Count regex in binary stream (juda tez va samarali)
        $fh = @fopen($pdfFullPath, 'rb');
        if ($fh) {
            $buffer = '';
            while (!feof($fh) && strlen($buffer) < 1500000) {
                $chunk = fread($fh, 65536);
                if ($chunk === false) break;
                $buffer .= $chunk;
            }
            fclose($fh);

            if (preg_match_all('/\/Count\s+(\d+)/', $buffer, $matches)) {
                $counts = array_map('intval', $matches[1]);
                $maxCount = !empty($counts) ? max($counts) : 0;
                if ($maxCount > 0 && $maxCount < 10000) {
                    return $maxCount;
                }
            }
        }

        // 2. smalot/pdfparser orqali tekshirish
        try {
            $cfg = new PdfParserConfig();
            $cfg->setRetainImageContent(false);
            $parser = new PdfParser([], $cfg);
            $doc = $parser->parseFile($pdfFullPath);
            $count = count($doc->getPages());
            if ($count > 0) {
                return $count;
            }
        } catch (\Throwable $e) {}

        return 0;
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
        @ini_set('memory_limit', '1024M');
        @set_time_limit(600);

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

    /**
     * Serverda RapidOCR (Python/uv) yoki Poppler (pdftoppm) va Tesseract OCR o'rnatilganligini tekshiradi.
     */
    public function isOcrAvailable(): bool
    {
        if ($this->isPythonOcrAvailable()) {
            return true;
        }

        $pdftoppm = (string) config('ai.ocr.pdftoppm_bin', 'pdftoppm');
        $tesseract = (string) config('ai.ocr.tesseract_bin', 'tesseract');

        return $this->isBinaryCallable($pdftoppm, ['-v'])
            && $this->isBinaryCallable($tesseract, ['--version']);
    }

    /**
     * Python RapidOCR skripti va uv/python vositasi mavjudligini tekshiradi.
     */
    public function isPythonOcrAvailable(): bool
    {
        $engine = (string) config('ai.ocr.engine', 'auto');
        if ($engine === 'tesseract') {
            return false;
        }

        $script = app_path('Services/AI/BookAssistant/Scripts/ocr_pdf.py');
        if (!is_file($script)) {
            return false;
        }

        return $this->resolvePythonOrUvCommand() !== null;
    }

    /**
     * Python RapidOCR skriptini chaqirish uchun buyruq prefiksini aniqlaydi.
     *
     * @return array<int, string>|null
     */
    public function resolvePythonOrUvCommand(): ?array
    {
        $uvBin = (string) config('ai.ocr.uv_bin', 'uv');
        $candidates = array_filter([
            $uvBin,
            'C:\\Users\\ASUS\\.local\\bin\\uv.exe',
            getenv('LOCALAPPDATA') ? (getenv('LOCALAPPDATA') . '\\Programs\\uv\\uv.exe') : null,
            getenv('USERPROFILE') ? (getenv('USERPROFILE') . '\\.local\\bin\\uv.exe') : null,
        ]);

        foreach ($candidates as $cand) {
            if ($this->isBinaryCallable($cand, ['--version'])) {
                return [
                    $cand,
                    'run',
                    '--offline',
                    '--with', 'rapidocr-onnxruntime',
                    '--with', 'pymupdf',
                    'python',
                ];
            }
        }

        $pythonBin = (string) config('ai.ocr.python_bin', 'python');
        $pyCandidates = array_filter([
            $pythonBin,
            'C:\\Users\\ASUS\\.local\\bin\\python.exe',
        ]);

        foreach ($pyCandidates as $cand) {
            if ($this->isBinaryCallable($cand, ['--version'])) {
                return [$cand];
            }
        }

        return null;
    }

    protected function isBinaryCallable(string $binary, array $args): bool
    {
        if (is_file($binary)) {
            return true;
        }

        try {
            $p = new Process(array_merge([$binary], $args));
            $p->setTimeout(5);
            $p->run();

            return $p->getExitCode() === 0 || !empty($p->getOutput()) || !empty($p->getErrorOutput());
        } catch (\Throwable $e) {
            // Windows fallback: check via where.exe if Process execution threw a system/temp exception
            if (PHP_OS_FAMILY === 'Windows' && !str_contains($binary, ' ') && !str_contains($binary, '\\') && !str_contains($binary, '/')) {
                $out = @shell_exec("where.exe " . escapeshellarg($binary) . " 2>nul");
                return !empty(trim((string) $out));
            }

            return false;
        }
    }

    /**
     * Tesseract serverida o'rnatilgan tillar ro'yxatini oladi.
     *
     * @return string[]
     */
    public function getAvailableTesseractLanguages(): array
    {
        $bin = (string) config('ai.ocr.tesseract_bin', 'tesseract');
        try {
            $p = new Process([$bin, '--list-langs']);
            $p->setTimeout(8);
            $p->run();
            if (!$p->isSuccessful()) {
                return ['eng'];
            }
            $lines = preg_split('/\r?\n/', trim($p->getOutput())) ?: [];
            $langs = [];
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line !== '' && !str_contains($line, 'List of available languages')) {
                    $langs[] = $line;
                }
            }
            return $langs ?: ['eng'];
        } catch (\Throwable $e) {
            return ['eng'];
        }
    }

    /**
     * Sozlangan OCR tillarini serverdagi mavjud tillar bilan solishtiradi.
     *
     * @return array{lang_string: string, warnings: array}
     */
    public function resolveOcrLanguages(): array
    {
        $configured = (string) config('ai.ocr.languages', 'uzb+rus+eng');
        $configuredList = explode('+', $configured);
        $available = $this->getAvailableTesseractLanguages();

        $valid = [];
        $warnings = [];

        foreach ($configuredList as $lang) {
            $lang = trim($lang);
            if ($lang === '') {
                continue;
            }
            if (in_array($lang, $available, true)) {
                $valid[] = $lang;
            } else {
                $warnings[] = "Tesseract til paketi «{$lang}» serverda o'rnatilmagan.";
            }
        }

        if (empty($valid)) {
            $valid = in_array('eng', $available, true) ? ['eng'] : (array) ($available[0] ?? 'eng');
        }

        return [
            'lang_string' => implode('+', $valid),
            'warnings'    => $warnings,
        ];
    }

    /**
     * OCR yordamida skanerlangan PDF fayldan betma-bet matn ajratadi.
     * RapidOCR mavjud bo'lsa undan, aks holda Poppler va Tesseract OCR'dan foydalanadi.
     *
     * @return array{pages: array<int, array{page_number: int, text: string}>, warnings: array}
     */
    public function extractViaOcr(
        string $pdfFullPath,
        ?callable $onProgress = null,
        int $startPage = 1,
        int $maxPages = 50,
        string $pagesList = ''
    ): array {
        if ($this->isPythonOcrAvailable()) {
            return $this->extractViaPythonOcr($pdfFullPath, $onProgress, $startPage, $maxPages, $pagesList);
        }

        $pdftoppm = (string) config('ai.ocr.pdftoppm_bin', 'pdftoppm');
        $tesseract = (string) config('ai.ocr.tesseract_bin', 'tesseract');
        $dpi = (int) config('ai.ocr.dpi', 150);
        $timeout = (int) config('ai.ocr.timeout_seconds', 300);

        $langRes = $this->resolveOcrLanguages();
        $langString = $langRes['lang_string'];

        $tmpDir = storage_path('app/temp_ocr_' . uniqid());
        if (!is_dir($tmpDir)) {
            @mkdir($tmpDir, 0755, true);
        }

        $pages = [];

        try {
            // PDF sahifalarini PNG formatiga o'tkazish
            $ppmArgs = [$pdftoppm, '-png', '-r', (string) $dpi];
            if ($pagesList === '' && $startPage > 0 && $maxPages > 0) {
                $ppmArgs[] = '-f';
                $ppmArgs[] = (string) $startPage;
                $ppmArgs[] = '-l';
                $ppmArgs[] = (string) ($startPage + $maxPages - 1);
            }
            $ppmArgs[] = $pdfFullPath;
            $ppmArgs[] = $tmpDir . DIRECTORY_SEPARATOR . 'page';

            $renderProcess = new Process($ppmArgs);
            $renderProcess->setTimeout($timeout);
            $renderProcess->run();

            // Hosil bo'lgan rasmlarni tartiblash
            $imageFiles = glob($tmpDir . DIRECTORY_SEPARATOR . 'page-*.png') ?: [];
            natsort($imageFiles);
            $imageFiles = array_values($imageFiles);

            $totalImages = count($imageFiles);
            foreach ($imageFiles as $idx => $imgFile) {
                if ($onProgress) {
                    $onProgress('ocr', $idx + 1, $totalImages);
                }

                // Fayl nomidan sahifa raqamini ajratish (masalan page-000001.png -> 1)
                $realPageNum = $startPage + $idx;
                if (preg_match('/page-(\d+)\.png$/', $imgFile, $m)) {
                    $realPageNum = (int) $m[1];
                }

                $ocrProcess = new Process([
                    $tesseract,
                    $imgFile,
                    'stdout',
                    '-l', $langString
                ]);
                $ocrProcess->setTimeout(60);
                $ocrProcess->run();

                $text = $ocrProcess->isSuccessful() ? (string) $ocrProcess->getOutput() : '';
                $pages[] = [
                    'page_number' => $realPageNum,
                    'text'        => $text,
                ];
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('OCR extraction failed', ['error' => $e->getMessage()]);
        } finally {
            $this->cleanDirectory($tmpDir);
        }

        return [
            'pages'    => $pages,
            'warnings' => $langRes['warnings'],
        ];
    }

    protected function cleanDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        $files = glob($dir . DIRECTORY_SEPARATOR . '*') ?: [];
        foreach ($files as $f) {
            if (is_file($f)) {
                @unlink($f);
            }
        }
        @rmdir($dir);
    }

    /**
     * Python RapidOCR (ONNX Runtime + PyMuPDF) orqali skanerlangan PDF fayldan matn ajratadi.
     * Partiyalab (start-page, max-pages) yoki maxsus tanlangan sahifalar (pagesList) bo'yicha ishlaydi.
     *
     * @return array{pages: array<int, array{page_number: int, text: string}>, warnings: array}
     */
    public function extractViaPythonOcr(
        string $pdfFullPath,
        ?callable $onProgress = null,
        int $startPage = 1,
        int $maxPages = 50,
        string $pagesList = ''
    ): array {
        $cmdPrefix = $this->resolvePythonOrUvCommand();
        if (!$cmdPrefix) {
            return [
                'pages'    => [],
                'warnings' => ["Python yoki uv vositasi topilmadi."],
            ];
        }

        $script = app_path('Services/AI/BookAssistant/Scripts/ocr_pdf.py');
        $timeout = (int) config('ai.ocr.timeout_seconds', 600);

        $cmd = array_merge($cmdPrefix, [
            $script,
            $pdfFullPath,
        ]);

        if (trim($pagesList) !== '') {
            $cmd[] = '--pages';
            $cmd[] = trim($pagesList);
        } else {
            $cmd[] = '--start-page';
            $cmd[] = (string) max(1, $startPage);
            $cmd[] = '--max-pages';
            $cmd[] = (string) max(1, $maxPages);
        }

        $pages = [];
        $warnings = [];

        try {
            $process = new Process($cmd);
            $process->setTimeout($timeout);
            $process->run();

            if (!$process->isSuccessful()) {
                $err = $process->getErrorOutput() ?: $process->getOutput();
                \Illuminate\Support\Facades\Log::warning('Python OCR failed', ['error' => $err]);
                $warnings[] = "Python OCR xatolik: " . \Illuminate\Support\Str::limit($err, 150);
            } else {
                $rawOut = trim($process->getOutput());
                $data = json_decode($rawOut, true);
                if (is_array($data) && !empty($data['pages'])) {
                    foreach ($data['pages'] as $p) {
                        $pNum = (int) ($p['page_number'] ?? 0);
                        $pText = (string) ($p['text'] ?? '');
                        if ($pNum > 0) {
                            $pages[] = [
                                'page_number' => $pNum,
                                'text'        => $pText,
                            ];
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            // Fallback via exec in case Process pipes fail on Windows
            try {
                $commandStr = implode(' ', array_map('escapeshellarg', $cmd));
                $outputLines = [];
                $exitCode = 0;
                @exec($commandStr, $outputLines, $exitCode);
                $rawOut = trim(implode("\n", $outputLines));
                if ($exitCode === 0 && !empty($rawOut)) {
                    $data = json_decode($rawOut, true);
                    if (is_array($data) && !empty($data['pages'])) {
                        foreach ($data['pages'] as $p) {
                            $pNum = (int) ($p['page_number'] ?? 0);
                            $pText = (string) ($p['text'] ?? '');
                            if ($pNum > 0) {
                                $pages[] = [
                                    'page_number' => $pNum,
                                    'text'        => $pText,
                                ];
                            }
                        }
                    }
                } else {
                    \Illuminate\Support\Facades\Log::error('Python OCR exception', ['error' => $e->getMessage()]);
                    $warnings[] = "Python OCR ijro xatosi: " . $e->getMessage();
                }
            } catch (\Throwable $e2) {
                \Illuminate\Support\Facades\Log::error('Python OCR exception', ['error' => $e2->getMessage()]);
                $warnings[] = "Python OCR ijro xatosi: " . $e2->getMessage();
            }
        }

        return [
            'pages'    => $pages,
            'warnings' => $warnings,
        ];
    }
}

