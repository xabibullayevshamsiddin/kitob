<?php

namespace App\Services\AI\BookAssistant;

use App\Models\Book;
use App\Models\BookChapter;
use App\Models\BookChunk;
use App\Models\BookPage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class BookIndexer
{
    public function __construct(
        protected BookTextExtractor $extractor,
        protected ProviderManager $providers
    ) {
    }

    /**
     * Kitobni (PDF betlari va/yoki boblari asosida) bo'laklarga ajratadi va indekslaydi.
     * Idempotent: content_hash o'zgarmagan bo'laklarni qayta embed qilmaydi.
     *
     * @param  callable|null  $onProgress  fn(string $step, int $current, int $total): void
     * @return array{
     *     status: string,
     *     quality_score: float,
     *     pages_count: int,
     *     chunks_count: int,
     *     embedded_new_count: int,
     *     reused_embeddings_count: int,
     *     embedding_mode: string,
     *     error_message: string|null
     * }
     */
    public function indexBook(Book $book, bool $force = false, ?callable $onProgress = null): array
    {
        @ini_set('memory_limit', '1024M');
        @set_time_limit(600);

        // Parallel yoki bir vaqtdagi indekslashdan himoyalovchi atomik qulf
        $lockKey = "ai_indexing_lock_{$book->id}";
        $lock = Cache::lock($lockKey, (int) config('ai.queue.lock_seconds', 900));

        if (!$lock->get()) {
            return [
                'status'                  => 'failed',
                'quality_score'           => (float) ($book->ai_text_quality ?? 0.0),
                'pages_count'             => (int) ($book->pages()->count()),
                'chunks_count'            => (int) ($book->chunks()->count()),
                'embedded_new_count'      => 0,
                'reused_embeddings_count' => 0,
                'embedding_mode'          => 'none',
                'error_message'           => 'Kitob ayni vaqtda boshqa jarayonda indekslanmoqda.',
            ];
        }

        try {
            return $this->performIndexBook($book, $force, $onProgress);
        } finally {
            $lock->release();
        }
    }

    /**
     * Haqiqiy indekslash mantiqi (qulf ichida xavfsiz bajariladi).
     */
    protected function performIndexBook(Book $book, bool $force = false, ?callable $onProgress = null): array
    {
        $book->update([
            'ai_index_status' => 'indexing',
            'ai_index_error'  => null,
        ]);

        // 1. Boblarda yetarli matn borligini tekshiramiz
        $chapters = BookChapter::where('book_id', $book->id)
            ->orderBy('chapter_number')
            ->get();

        $usableChapters = $chapters->filter(function (BookChapter $ch) {
            $clean = trim(strip_tags((string) $ch->content));
            return mb_strlen($clean) >= 80 && $this->extractor->scorePageTextQuality($clean) >= 0.45;
        })->values();

        // 2. Agar PDF fayl bo'lsa, PDF'dan betlarni ajratamiz va sifatini baholaymiz
        $pdfReport = [
            'passed'             => false,
            'quality_score'      => 0.0,
            'pages_count'        => 0,
            'avg_chars_per_page' => 0.0,
            'extractor'          => 'none',
            'error_message'      => null,
        ];

        $hasPdf = $this->extractor->resolvePdfPath($book) !== null;
        if ($hasPdf) {
            if ($onProgress) {
                $onProgress('extracting_pdf', 0, 1);
            }
            $pdfReport = $this->extractor->extractBookPdf($book, $force);
        }

        $manualSummaryText = trim(strip_tags((string) $book->ai_summary));
        if ($manualSummaryText === '' && !empty($book->description)) {
            $descClean = trim(strip_tags((string) $book->description));
            if (mb_strlen($descClean) >= 20) {
                $manualSummaryText = $descClean;
            }
        }
        $hasManualSummary = mb_strlen($manualSummaryText) >= 5;

        // Agar boblarda ham yaroqli matn bo'lmasa, qo'lda ai_summary kiritilmagan bo'lsa va PDF sifati past bo'lsa -> indekslashga ruxsat berilmaydi!
        if ($usableChapters->isEmpty() && !$pdfReport['passed'] && !$hasManualSummary) {
            $errMsg = $pdfReport['error_message'] ?: trans('ai.low_text_quality');
            $book->update([
                'ai_index_status' => 'failed',
                'ai_text_quality' => $pdfReport['quality_score'],
                'ai_index_error'  => $errMsg,
            ]);

            return [
                'status'                  => 'failed',
                'quality_score'           => (float) $pdfReport['quality_score'],
                'pages_count'             => (int) $pdfReport['pages_count'],
                'chunks_count'            => 0,
                'embedded_new_count'      => 0,
                'reused_embeddings_count' => 0,
                'embedding_mode'          => 'none',
                'error_message'           => $errMsg,
            ];
        }

        // Agar faqat boblardan yoki qo'lda kiritilgan konspektdan olinayotgan bo'lsa, sifatini hisoblaymiz
        $qualityScore = (float) $pdfReport['quality_score'];
        if ($usableChapters->isNotEmpty() && !$pdfReport['passed']) {
            $scores = $usableChapters->map(fn ($ch) => $this->extractor->scorePageTextQuality(strip_tags((string) $ch->content)));
            $qualityScore = round((float) $scores->avg(), 3);
            $book->update([
                'ai_text_quality' => $qualityScore,
            ]);
        } elseif ($hasManualSummary && !$pdfReport['passed']) {
            $summaryScore = $this->extractor->scorePageTextQuality($manualSummaryText);
            $qualityScore = $summaryScore > 0 ? round($summaryScore, 3) : 0.60;
            $book->update([
                'ai_text_quality' => $qualityScore,
            ]);
        }

        $indexWarnings = [];
        if ($hasPdf && !$pdfReport['passed']) {
            $indexWarnings[] = 'PDF matnidan yetarli mazmun olinmadi; yordamchi bob matni yoki qisqa konspekt bilan cheklanishi mumkin. Skanerlangan PDF uchun OCR qilingan, matn taniladigan PDF yuklang.';
        }

        // 3. Bo'laklar (chunks) ro'yxatini shakllantiramiz
        $plannedChunks = [];
        $isPartialOnly = false;

        if ($pdfReport['passed']) {
            $pages = BookPage::where('book_id', $book->id)
                ->orderBy('page_number')
                ->get(['page_number', 'text']);
            $plannedChunks = array_merge($plannedChunks, $this->buildChunksFromPages($pages->all()));
        }

        if ($usableChapters->isNotEmpty()) {
            $allPdfText = $pdfReport['passed']
                ? mb_strtolower(implode(' ', array_column($plannedChunks, 'text')))
                : '';

            $nonDuplicateChapters = [];
            foreach ($usableChapters as $ch) {
                $cleanCh = mb_strtolower($this->extractor->sanitizeUtf8Text(strip_tags((string) $ch->content)));
                $firstPhrase = mb_substr(preg_replace('/\s+/u', ' ', $cleanCh), 0, 100);

                // Agar bob matni PDF sahifalari ichida allaqachon mavjud bo'lsa, uni takroran bo'lak qilib qo'shmaymiz
                if ($allPdfText !== '' && mb_strlen($firstPhrase) >= 40 && str_contains($allPdfText, $firstPhrase)) {
                    // Mavjud PDF bo'laklariga chapter_id'ni bog'laymiz
                    foreach ($plannedChunks as &$pChunk) {
                        if ($pChunk['chapter_id'] === null && str_contains(mb_strtolower($pChunk['text']), $firstPhrase)) {
                            $pChunk['chapter_id'] = (int) $ch->id;
                        }
                    }
                    unset($pChunk);
                    continue;
                }
                $nonDuplicateChapters[] = $ch;
            }

            if (!empty($nonDuplicateChapters)) {
                $chapterChunks = $this->buildChunksFromChapters($nonDuplicateChapters, count($plannedChunks));
                $plannedChunks = array_merge($plannedChunks, $chapterChunks);
            }
        }

        // Qat'iy dublikatlarni hash bo'yicha tozalash
        $uniqueChunks = [];
        $seenHashes = [];
        foreach ($plannedChunks as $c) {
            $h = $c['content_hash'];
            if (!isset($seenHashes[$h])) {
                $seenHashes[$h] = true;
                $uniqueChunks[] = $c;
            }
        }
        $plannedChunks = $uniqueChunks;

        if (empty($plannedChunks) && $hasManualSummary) {
            $isPartialOnly = true;
            $fullSummaryChunk = "Kitob: {$book->title}\nMuallif: {$book->author}\nJanr: {$book->genre}\nTavsifi va konspekti:\n" . $manualSummaryText;
            $plannedChunks[] = [
                'chapter_id'   => null,
                'page_start'   => 1,
                'page_end'     => 1,
                'ordinal'      => 0,
                'text'         => $fullSummaryChunk,
                'token_count'  => $this->estimateTokens($fullSummaryChunk),
                'content_hash' => hash('sha256', mb_strtolower(trim($fullSummaryChunk))),
            ];
        }

        if (empty($plannedChunks)) {
            $errMsg = trans('ai.low_text_quality');
            $book->update([
                'ai_index_status' => 'failed',
                'ai_index_error'  => $errMsg,
            ]);

            return [
                'status'                  => 'failed',
                'quality_score'           => $qualityScore,
                'pages_count'             => (int) $pdfReport['pages_count'],
                'chunks_count'            => 0,
                'embedded_new_count'      => 0,
                'reused_embeddings_count' => 0,
                'embedding_mode'          => 'none',
                'error_message'           => $errMsg,
            ];
        }

        // 4. Mavjud content_hash bo'yicha embeddinglarni keshlab olamiz (idempotentlik: qayta embed qilmaslik)
        $embeddingProvider = $this->providers->embedding();
        $canEmbed = $embeddingProvider->isAvailable();
        $targetEmbedModel = $canEmbed ? $embeddingProvider->getModelName() : null;

        $existingByHash = [];
        $existingOrderedHashes = [];
        if (!$force) {
            $existingChunks = BookChunk::where('book_id', $book->id)
                ->orderBy('ordinal')
                ->get(['id', 'ordinal', 'content_hash', 'embedding', 'embedding_model']);
            foreach ($existingChunks as $ex) {
                $existingOrderedHashes[] = $ex->content_hash;
                if (!empty($ex->content_hash)) {
                    $existingByHash[$ex->content_hash] = [
                        'embedding'       => $ex->embedding,
                        'embedding_model' => $ex->embedding_model,
                    ];
                }
            }
        }

        $reusedCount = 0;
        $needsEmbeddingIndices = [];
        $plannedHashes = array_column($plannedChunks, 'content_hash');

        foreach ($plannedChunks as $idx => &$chunk) {
            $hash = $chunk['content_hash'];
            $hasExisting = !$force && isset($existingByHash[$hash]);
            $hasMatchingVector = $hasExisting
                && !empty($existingByHash[$hash]['embedding'])
                && $existingByHash[$hash]['embedding_model'] === $targetEmbedModel;

            if ($hasMatchingVector || ($hasExisting && !$canEmbed)) {
                $chunk['embedding'] = $existingByHash[$hash]['embedding'];
                $chunk['embedding_model'] = $existingByHash[$hash]['embedding_model'];
                $reusedCount++;
            } else {
                $chunk['embedding'] = null;
                $chunk['embedding_model'] = null;
                if ($canEmbed) {
                    $needsEmbeddingIndices[] = $idx;
                }
            }
        }
        unset($chunk);

        // 5. Yangi bo'laklarni partiyalab (batch) embed qilish va saqlash
        $batchSize = max(1, (int) config('ai.index_batch_size', 15));
        $embeddedNewCount = 0;
        $embeddingFailed = false;

        if ($canEmbed && !empty($needsEmbeddingIndices)) {
            $batches = array_chunk($needsEmbeddingIndices, $batchSize);
            $totalBatches = count($batches);

            foreach ($batches as $bIdx => $batchIndices) {
                if ($onProgress) {
                    $onProgress('embedding', $bIdx + 1, $totalBatches);
                }

                $textsToEmbed = [];
                foreach ($batchIndices as $planIdx) {
                    $textsToEmbed[] = $plannedChunks[$planIdx]['text'];
                }

                try {
                    $embedResult = $embeddingProvider->embedMany($textsToEmbed);
                } catch (\Throwable $e) {
                    $embeddingFailed = true;
                    Log::warning('Book AI embedding failed; keeping full-text indexing available.', [
                        'book_id' => $book->id,
                        'provider' => get_class($embeddingProvider),
                        'exception' => get_class($e),
                    ]);
                    break;
                }
                $vectors = $embedResult['vectors'] ?? [];
                $usedModel = $embedResult['model'] ?? $targetEmbedModel;

                foreach ($batchIndices as $pos => $planIdx) {
                    if (!empty($vectors[$pos])) {
                        $plannedChunks[$planIdx]['embedding'] = BookChunk::packEmbedding($vectors[$pos]);
                        $plannedChunks[$planIdx]['embedding_model'] = $usedModel;
                        $embeddedNewCount++;
                    }
                }
            }
        }

        if ($embeddingFailed) {
            // Muvaffaqiyatsiz partiya va keyingi partiyalar vektorsiz qoladi; matnli qidiruv davom etadi.
            foreach ($needsEmbeddingIndices as $planIdx) {
                if (empty($plannedChunks[$planIdx]['embedding'])) {
                    $plannedChunks[$planIdx]['embedding'] = null;
                    $plannedChunks[$planIdx]['embedding_model'] = null;
                }
            }
            $indexWarnings[] = 'Embedding xizmati vaqtincha ishlamadi; kitob matnli qidiruv bilan indekslandi.';
        }

        // 6. book_chunks jadvaliga ATOMIK tarzda saqlash
        // MUHIM QOIDA: Yangi indeks to'liq tayyor bo'lgachгина DB transaction orqali almashtiriladi.
        // Jarayon o'rtasida uzilish yoki xatolik yuz bersa, oldingi yaroqli indeks aslo o'chib ketmaydi!
        $allUnchanged = !$force
            && $embeddedNewCount === 0
            && !empty($existingOrderedHashes)
            && $existingOrderedHashes === $plannedHashes;

        $indexedAt = now();
        $finalStatus = $isPartialOnly ? 'partial' : 'ready';
        if ($isPartialOnly) {
            $indexWarnings[] = trans('ai.partial_notice');
        }
        $warningMessage = $indexWarnings ? mb_substr(implode(' ', $indexWarnings), 0, 500) : null;
        $nextVersion = ((int) ($book->ai_index_version ?? 1)) + 1;

        if (!$allUnchanged) {
            \Illuminate\Support\Facades\DB::transaction(function () use ($book, $plannedChunks, $nextVersion, $finalStatus, $indexedAt, $qualityScore, $warningMessage) {
                // Yangi bo'laklarni kiritishdan oldin eski bo'laklarni tozalash (bitta tranzaksiya ichida atomik)
                BookChunk::where('book_id', $book->id)->delete();

                $now = now();
                $insertBatch = [];
                foreach ($plannedChunks as $ordinal => $c) {
                    $insertBatch[] = [
                        'book_id'         => $book->id,
                        'chapter_id'      => $c['chapter_id'],
                        'page_start'      => $c['page_start'],
                        'page_end'        => $c['page_end'],
                        'ordinal'         => $ordinal,
                        'index_version'   => $nextVersion,
                        'text'            => $c['text'],
                        'token_count'     => $c['token_count'],
                        'content_hash'    => $c['content_hash'],
                        'embedding'       => $c['embedding'],
                        'embedding_model' => $c['embedding_model'],
                        'created_at'      => $now,
                        'updated_at'      => $now,
                    ];

                    if (count($insertBatch) >= 50) {
                        BookChunk::insert($insertBatch);
                        $insertBatch = [];
                    }
                }

                if (!empty($insertBatch)) {
                    BookChunk::insert($insertBatch);
                }

                $book->update([
                    'ai_index_status'  => $finalStatus,
                    'ai_indexed_at'    => $indexedAt,
                    'ai_text_quality'  => $qualityScore,
                    'ai_index_version' => $nextVersion,
                    'ai_index_error'   => $warningMessage,
                ]);
            });
        } else {
            $book->update([
                'ai_index_status' => $finalStatus,
                'ai_indexed_at'   => $indexedAt,
                'ai_text_quality' => $qualityScore,
                'ai_index_error'  => $warningMessage,
            ]);
        }

        // 7. Kitob va boblar qisqa mazmunini (ai_summary) ierarxik tarzda tayyorlash
        $this->populateSummaries($book, $usableChapters->all(), $plannedChunks, $force);

        // Vektor matritsasi keshini yangilash
        Cache::forget("ai_book_matrix:{$book->id}");

        return [
            'status'                  => $finalStatus,
            'quality_score'           => $qualityScore,
            'pages_count'             => (int) $pdfReport['pages_count'],
            'chunks_count'            => count($plannedChunks),
            'embedded_new_count'      => $embeddedNewCount,
            'reused_embeddings_count' => $reusedCount,
            'embedding_mode'          => ($canEmbed && ($embeddedNewCount + $reusedCount) > 0) ? 'hybrid_vector_fulltext' : 'fulltext_only',
            'error_message'           => $warningMessage,
        ];
    }

    /**
     * PDF betlaridan ~250–400 so'zli va ~15% ustma-ust tushuvchi (overlap) bo'laklar yasaydi.
     *
     * @param  BookPage[]  $pages
     * @return array<int, array<string, mixed>>
     */
    public function buildChunksFromPages(array $pages): array
    {
        $maxWords = max(80, (int) config('ai.chunk_max_words', 340));
        $overlapRatio = max(0.05, min(0.40, (float) config('ai.chunk_overlap_ratio', 0.15)));
        $overlapWordsCount = max(15, (int) round($maxWords * $overlapRatio));
        $stepWords = max(40, $maxWords - $overlapWordsCount);

        // Har bir so'zni qaysi betdan kelgani bilan bog'laymiz
        $wordTokens = [];
        foreach ($pages as $page) {
            $pageNum = (int) $page->page_number;
            $text = trim((string) $page->text);
            if ($text === '') {
                continue;
            }
            $words = preg_split('/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [];
            foreach ($words as $w) {
                $wordTokens[] = [
                    'word' => $w,
                    'page' => $pageNum,
                ];
            }
        }

        $totalWords = count($wordTokens);
        if ($totalWords === 0) {
            return [];
        }

        $chunks = [];
        $ordinal = 0;

        for ($start = 0; $start < $totalWords; $start += $stepWords) {
            $slice = array_slice($wordTokens, $start, $maxWords);
            if (empty($slice)) {
                break;
            }

            // Agar oxirgi qoldiq juda kichik bo'lsa (< 60 so'z) va oldingi bo'lak bo'lsa, oldingi bo'lak allaqachon qamrab olgan
            if (count($slice) < 60 && !empty($chunks)) {
                break;
            }

            $chunkWords = array_column($slice, 'word');
            $chunkText = implode(' ', $chunkWords);
            $pageStart = $slice[0]['page'];
            $pageEnd = $slice[count($slice) - 1]['page'];
            $wordCount = count($chunkWords);

            $chunks[] = [
                'chapter_id'   => null,
                'page_start'   => $pageStart,
                'page_end'     => $pageEnd,
                'ordinal'      => $ordinal++,
                'text'         => $chunkText,
                'token_count'  => (int) ceil($wordCount * 1.35),
                'content_hash' => hash('sha256', mb_strtolower(trim($chunkText))),
            ];

            if ($start + $maxWords >= $totalWords) {
                break;
            }
        }

        return $chunks;
    }

    /**
     * Kitob boblaridan (~250–400 so'z, ~15% overlap) bo'laklar yasaydi.
     *
     * @param  BookChapter[]  $chapters
     * @return array<int, array<string, mixed>>
     */
    public function buildChunksFromChapters(array $chapters, int $startOrdinal = 0): array
    {
        $maxWords = max(80, (int) config('ai.chunk_max_words', 340));
        $overlapRatio = max(0.05, min(0.40, (float) config('ai.chunk_overlap_ratio', 0.15)));
        $overlapWordsCount = max(15, (int) round($maxWords * $overlapRatio));
        $stepWords = max(40, $maxWords - $overlapWordsCount);

        $chunks = [];
        $ordinal = $startOrdinal;

        foreach ($chapters as $chapter) {
            $cleanText = $this->extractor->sanitizeUtf8Text(strip_tags((string) $chapter->content));
            if ($cleanText === '') {
                continue;
            }

            $words = preg_split('/\s+/u', $cleanText, -1, PREG_SPLIT_NO_EMPTY) ?: [];
            $totalWords = count($words);
            if ($totalWords === 0) {
                continue;
            }

            for ($start = 0; $start < $totalWords; $start += $stepWords) {
                $slice = array_slice($words, $start, $maxWords);
                if (empty($slice)) {
                    break;
                }
                if (count($slice) < 50 && $start > 0) {
                    break;
                }

                $chunkText = implode(' ', $slice);
                $wordCount = count($slice);

                $chunks[] = [
                    'chapter_id'   => (int) $chapter->id,
                    'page_start'   => null,
                    'page_end'     => null,
                    'ordinal'      => $ordinal++,
                    'text'         => $chunkText,
                    'token_count'  => (int) ceil($wordCount * 1.35),
                    'content_hash' => hash('sha256', 'ch:' . $chapter->id . ':' . mb_strtolower(trim($chunkText))),
                ];

                if ($start + $maxWords >= $totalWords) {
                    break;
                }
            }
        }

        return $chunks;
    }

    /**
     * Kitob va boblar uchun ierarxik qisqa xulosa (ai_summary) tayyorlaydi.
     * 1. Avval har bir bobning to'liq matnini hisobga olgan holda bob konspekti tuziladi (kesib tashlanmasdan).
     * 2. Butun kitob konspekti barcha boblar va butun kitob bo'ylab taqsimlangan bo'laklar asosida shakllantiriladi.
     */
    protected function populateSummaries(Book $book, array $chapters, array $plannedChunks, bool $force = false): void
    {
        // 1. Boblar xulosalarini yig'ish (kesilishlarsiz, to'liq mazmun bilan)
        $chapterSummaries = [];
        foreach ($chapters as $chapter) {
            if (!$force && !empty($chapter->ai_summary)) {
                $chapterSummaries[] = (string) $chapter->ai_summary;
                continue;
            }

            $clean = $this->extractor->sanitizeUtf8Text(strip_tags((string) $chapter->content));
            $cleanLen = mb_strlen($clean);

            if ($cleanLen <= 3500) {
                $sourceText = $clean;
            } else {
                // Katta boblarni 2-3 ta qismga ajratib, umumlashtiramiz
                $part1 = mb_substr($clean, 0, 1800);
                $mid = (int) floor($cleanLen / 2);
                $part2 = mb_substr($clean, max(0, $mid - 900), 1800);
                $part3 = mb_substr($clean, max(0, $cleanLen - 1800), 1800);
                $sourceText = "Bob boshi:\n{$part1}\n\nBob o'rtasi:\n{$part2}\n\nBob oxiri:\n{$part3}";
            }

            $chSummary = $this->generateSummaryText(
                "«{$chapter->title}» bobi matni asosida faqat matndagi faktlarga tayanib 2-3 gaplik qisqa xulosa yozing:",
                $sourceText
            );

            if ($chSummary !== '') {
                $chapter->ai_summary = $chSummary;
                $chapter->save();
                $chapterSummaries[] = $chSummary;
            }
        }

        // 2. Kitobning umumiy xulosasi (butun kitobni qamrab oluvchi ierarxik konspekt)
        if ($force || empty($book->ai_summary)) {
            $summaryText = '';

            if (!empty($chapterSummaries)) {
                // Barcha boblar konspektlaridan butun kitob xulosasini shakllantirish
                $combinedChapters = '';
                foreach ($chapterSummaries as $idx => $s) {
                    $combinedChapters .= ($idx + 1) . "-bob: " . $s . "\n";
                }

                $summaryText = $this->generateSummaryText(
                    "«{$book->title}» kitobining barcha boblaridan olingan xulosalar asosida butun kitob uchun 3-5 gapdan iborat umumiy xulosa yozing (faqat matndagi faktlarga tayaning):",
                    $combinedChapters
                );
            } elseif (!empty($plannedChunks)) {
                // Agar boblar bo'lmasa, kitobning butun bo'yi bo'ylab 5 ta teng nuqtadan (0%, 25%, 50%, 75%, 100%) namuna olish
                $chunkCount = count($plannedChunks);
                $samplePoints = [
                    'Boshi'   => 0,
                    'Chorak'  => (int) floor($chunkCount * 0.25),
                    'O\'rtasi'=> (int) floor($chunkCount * 0.50),
                    'Uchinchi chorak' => (int) floor($chunkCount * 0.75),
                    'Oxiri'   => max(0, $chunkCount - 1),
                ];

                $samples = [];
                foreach ($samplePoints as $label => $idx) {
                    $chunk = $plannedChunks[$idx] ?? null;
                    if ($chunk && !empty($chunk['text'])) {
                        $pNum = $chunk['page_start'] ? "({$chunk['page_start']}-bet)" : '';
                        $samples[] = "Qism {$label} {$pNum}:\n" . mb_substr($chunk['text'], 0, 1000);
                    }
                }

                $combinedPdfSections = implode("\n\n", $samples);

                $summaryText = $this->generateSummaryText(
                    "«{$book->title}» kitobining butun asar bo'ylab olingan bo'laklariga tayanib butun kitob uchun 3-5 gaplik qisqa xulosa yozing (hech narsa to'qimang):",
                    $combinedPdfSections
                );
            }

            if ($summaryText !== '') {
                $book->ai_summary = $summaryText;
                $book->ai_summary_origin = 'auto';
                $book->save();
            }
        }
    }

    protected function generateSummaryText(string $instruction, string $sourceText): string
    {
        $sourceText = trim($sourceText);
        if ($sourceText === '') {
            return '';
        }

        try {
            $llm = $this->providers->llm();
            $res = $llm->chat([
                [
                    'role'    => 'system',
                    'content' => "Sen kitob bo'laklaridan faqat matnga tayanib qisqa o'zbekcha xulosa tuzuvchi repetitor yordamchisan.",
                ],
                [
                    'role'    => 'user',
                    'content' => $instruction . "\n\n<bolak>\n" . mb_substr($sourceText, 0, 10000) . "\n</bolak>",
                ],
            ], ['max_tokens' => 300, 'temperature' => 0.1]);

            return trim($res['content'] ?? '');
        } catch (\Throwable $e) {
            // Agar API kaliti yo'q bo'lsa (masalan lokal/test muhitda), birinchi 380 belgini ekstraktiv xulosa sifatida olamiz
            return mb_substr(preg_replace('/\s+/u', ' ', $sourceText) ?? $sourceText, 0, 380);
        }
    }

    public function estimateTokens(string $text): int
    {
        $words = preg_split('/\s+/u', trim($text), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        return (int) ceil(max(1, count($words)) * 1.35);
    }

    /**
     * Faqat ushbu kitobga tegishli AI bo'laklari (chunks) va ajratilgan betlarini (pages) tozalaydi,
     * va kitobning AI holatini 'none' ga qaytaradi. Boshqa kitoblar ma'lumotlariga mutlaqo tegmaydi.
     *
     * @return array{deleted_chunks: int, deleted_pages: int}
     */
    public function clearBookIndex(Book $book): array
    {
        $bookId = (int) $book->id;

        $deletedChunks = BookChunk::where('book_id', $bookId)->delete();
        $deletedPages = BookPage::where('book_id', $bookId)->delete();

        $book->update([
            'ai_enabled'      => false,
            'ai_index_status' => 'none',
            'ai_indexed_at'   => null,
            'ai_text_quality' => null,
            'ai_index_error'  => null,
        ]);

        // Ushbu kitobning vektor keshlarini tozalash
        Cache::forget("ai_book_matrix:{$bookId}:0");
        if ($book->ai_indexed_at) {
            Cache::forget("ai_book_matrix:{$bookId}:{$book->ai_indexed_at->timestamp}");
        }

        return [
            'deleted_chunks' => $deletedChunks,
            'deleted_pages'  => $deletedPages,
        ];
    }
}
