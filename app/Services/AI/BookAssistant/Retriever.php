<?php

namespace App\Services\AI\BookAssistant;

use App\Models\Book;
use App\Models\BookChunk;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class Retriever
{
    /**
     * O'zbek, rus va ingliz tillaridagi umumiy yordamchi so'zlar (stop-words).
     * Leksik / FULLTEXT baholashda shovqin bermasligi uchun filtrlanadi.
     */
    protected const STOP_WORDS = [
        'va', 'ham', 'bu', 'shu', 'u', 'uchun', 'bilan', 'haqida', 'haqda', 'nima', 'qanday',
        'qaysi', 'kim', 'qachon', 'nega', 'edi', 'ekan', 'deb', 'degan', 'degani',
        'bor', 'bormi', 'yoq', 'yo\'q', 'emas', 'bolsa', 'bolgan', 'bo\'lsa', 'bo\'lgan', 'bo\'yicha',
        'bugun', 'kecha', 'ertaga', 'hozir', 'necha', 'nechta', 'qancha', 'boldi', 'bo\'ldi', 'boladi', 'bo\'ladi',
        'yozilgan', 'uchraydi', 'so\'z', 'soz', 'so\'zlar', 'sozlar', 'so\'zlari', 'sozlari',
        'atama', 'atamalar', 'atamalari', 'ma\'nosi', 'manosi',
        'kitob', 'kitobda', 'kitobning', 'kitobdagi', 'bob', 'bobda', 'bet', 'betda', 'mengga',
        'menga', 'aytib', 'ber', 'bering', 'tushuntir', 'tushuntiring', 'iltimos',
        'the', 'is', 'are', 'was', 'were', 'what', 'how', 'why', 'when', 'where',
        'which', 'who', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'about', 'from',
        'and', 'or', 'that', 'this', 'book', 'chapter', 'page', 'tell', 'explain', 'mean', 'does', 'words', 'word',
        'что', 'как', 'почему', 'когда', 'где', 'кто', 'про', 'об', 'о', 'в', 'на',
        'и', 'или', 'для', 'из', 'это', 'книга', 'книге', 'глава',
    ];

    public function __construct(
        protected ProviderManager $providers
    ) {
    }

    /**
     * FAQAT berilgan $bookId kitobiga tegishli eng mos bo'laklarni topadi.
     * Imzoda $bookId majburiy birinchi argument — kitobsiz chaqirib bo'lmaydi.
     *
     * @return array{
     *     chunks: array<int, array<string, mixed>>,
     *     top_score: float,
     *     mode: string,
     *     expanded_query: string,
     *     is_overview_query: bool
     * }
     */
    public function retrieve(
        int $bookId,
        string $query,
        int $limit = 6,
        ?string $previousUserQuestion = null
    ): array {
        if ($bookId <= 0) {
            return [
                'chunks'            => [],
                'top_score'         => 0.0,
                'mode'              => 'none',
                'expanded_query'    => $query,
                'is_overview_query' => false,
            ];
        }

        $limit = max(1, min(12, $limit));
        $expandedQuery = $this->buildExpandedQuery($query, $previousUserQuestion);
        $isOverview = $this->isOverviewQuestion($query) || $this->isOverviewQuestion($expandedQuery);
        $targetPage = $this->extractPageNumber($query) ?? $this->extractPageNumber($expandedQuery);
        $targetChapter = $this->extractChapterNumber($query) ?? $this->extractChapterNumber($expandedQuery);

        $book = Book::select(['id', 'title', 'ai_indexed_at', 'ai_summary'])->find($bookId);
        if (!$book) {
            return [
                'chunks'            => [],
                'top_score'         => 0.0,
                'mode'              => 'none',
                'expanded_query'    => $expandedQuery,
                'is_overview_query' => $isOverview,
            ];
        }

        // FAQAT shu kitobning bo'laklari olinadi (WHERE book_id = $bookId)
        $chunks = BookChunk::with('chapter:id,chapter_number,title')
            ->where('book_id', $bookId)
            ->orderBy('ordinal')
            ->get([
                'id',
                'book_id',
                'chapter_id',
                'page_start',
                'page_end',
                'ordinal',
                'text',
                'token_count',
                'embedding_model',
            ]);

        if ($chunks->isEmpty()) {
            return [
                'chunks'            => [],
                'top_score'         => 0.0,
                'mode'              => 'none',
                'expanded_query'    => $expandedQuery,
                'is_overview_query' => $isOverview,
            ];
        }

        // 1. Vektor o'xshashligini hisoblash (agar embedding provayder va vektorlar mavjud bo'lsa)
        $vectorScores = [];
        $hasVectorMode = false;
        $embeddingProvider = $this->providers->embedding();

        if ($embeddingProvider->isAvailable()) {
            $matrix = $this->getCachedBookVectorMatrix($book);
            if (!empty($matrix)) {
                try {
                    $queryVector = $embeddingProvider->embedQuery($expandedQuery);
                    if (!empty($queryVector)) {
                        $hasVectorMode = true;
                        foreach ($matrix as $chunkId => $chunkVec) {
                            $vectorScores[(int) $chunkId] = $this->dotProduct($queryVector, $chunkVec);
                        }
                    }
                } catch (\Throwable $e) {
                    // Embedding xato bersa, FULLTEXT/leksik rejimga tushadi
                    $hasVectorMode = false;
                }
            }
        }

        // 2. MySQL FULLTEXT ballarini olish (agar MySQL bo'lsa)
        $mysqlFulltextScores = $this->queryMysqlFulltextScores($bookId, $expandedQuery);

        // 3. Leksik (BM25 + o'zak/prefiks) ballarini hisoblash
        $queryTokens = $this->tokenizeForSearch($expandedQuery);
        $scoredChunks = [];
        $maxMysqlFt = !empty($mysqlFulltextScores) ? max($mysqlFulltextScores) : 0.0;

        foreach ($chunks as $chunk) {
            // Qat'iy himoya: hech qachon boshqa kitobning bo'lagi o'tib ketmasin
            if ((int) $chunk->book_id !== $bookId) {
                continue;
            }

            $cid = (int) $chunk->id;
            $chapterTitle = $chunk->chapter ? ($chunk->chapter->chapter_number . '-bob ' . $chunk->chapter->title) : '';
            $searchableText = trim($chapterTitle . ' ' . $chunk->text);

            $phpLexical = $this->computeLexicalScore($queryTokens, $searchableText, $expandedQuery);
            $mysqlNorm = ($maxMysqlFt > 0 && isset($mysqlFulltextScores[$cid]))
                ? min(1.0, ($mysqlFulltextScores[$cid] / max(3.5, $maxMysqlFt)))
                : 0.0;

            $lexicalScore = round(max($phpLexical, ($phpLexical * 0.65) + ($mysqlNorm * 0.35)), 4);
            $vecScore = round((float) ($vectorScores[$cid] ?? 0.0), 4);

            if ($hasVectorMode) {
                $combinedScore = max(
                    ($vecScore * 0.72) + ($lexicalScore * 0.28),
                    $vecScore,
                    $lexicalScore * 0.85
                );
            } else {
                $combinedScore = $lexicalScore;
            }

            // 4. Sahifa (bet) bo'yicha aniq moslik boosti (masalan: "1-betdagi asosiy tushunchalar")
            if ($targetPage !== null && $chunk->page_start !== null) {
                $pStart = (int) $chunk->page_start;
                $pEnd = (int) ($chunk->page_end ?? $chunk->page_start);

                if ($targetPage >= $pStart && $targetPage <= $pEnd) {
                    $combinedScore = max($combinedScore, 0.85);
                } elseif (abs($targetPage - $pStart) <= 1 || abs($targetPage - $pEnd) <= 1) {
                    $combinedScore = max($combinedScore, 0.50);
                }
            }

            // 5. Bob (chapter) bo'yicha aniq moslik boosti (masalan: "1-bob", "Chapter 1")
            if ($targetChapter !== null) {
                $matchesChapter = false;
                if ($chunk->chapter && (int) $chunk->chapter->chapter_number === $targetChapter) {
                    $matchesChapter = true;
                } elseif (preg_match('/\bchapter\s*' . $targetChapter . '\b/i', $searchableText) || preg_match('/\b' . $targetChapter . '\s*[-–]?\s*bob\b/iu', $searchableText)) {
                    $matchesChapter = true;
                }

                if ($matchesChapter) {
                    $combinedScore = max($combinedScore, 0.85);
                }
            }

            // 6. Agar foydalanuvchi "Bu kitob nima haqida?" yoki "Asosiy g'oyalar nimalar?" deb umumiy savol bersa,
            // kitobning kirish bo'laklariga ustuvorlik beriladi
            if ($isOverview && $chunk->ordinal <= 2) {
                $overviewBoost = 0.65 - ($chunk->ordinal * 0.05);
                $combinedScore = max($combinedScore, $overviewBoost);
            }

            $scoredChunks[] = [
                'id'            => $cid,
                'book_id'       => (int) $chunk->book_id,
                'chapter_id'    => $chunk->chapter_id ? (int) $chunk->chapter_id : null,
                'chapter_title' => $chunk->chapter ? $chunk->chapter->title : null,
                'page_start'    => $chunk->page_start ? (int) $chunk->page_start : null,
                'page_end'      => $chunk->page_end ? (int) $chunk->page_end : null,
                'ordinal'       => (int) $chunk->ordinal,
                'text'          => (string) $chunk->text,
                'source_label'  => $chunk->source_label,
                'score'         => round($combinedScore, 4),
                'vector_score'  => $vecScore,
                'lexical_score' => $lexicalScore,
            ];
        }

        usort($scoredChunks, function (array $a, array $b) {
            if ($b['score'] !== $a['score']) {
                return $b['score'] <=> $a['score'];
            }
            return $a['ordinal'] <=> $b['ordinal'];
        });

        $topChunks = array_slice($scoredChunks, 0, $limit);
        $topScore = !empty($topChunks) ? (float) $topChunks[0]['score'] : 0.0;

        return [
            'chunks'            => $topChunks,
            'top_score'         => $topScore,
            'mode'              => $hasVectorMode ? 'hybrid' : 'fulltext',
            'expanded_query'    => $expandedQuery,
            'is_overview_query' => $isOverview,
        ];
    }

    /**
     * Qisqa/noaniq savol ("buni tushuntir", "yana misol") uchun qidiruv so'rovini oldingi savol bilan birga tuzadi.
     */
    public function buildExpandedQuery(string $currentQuestion, ?string $previousUserQuestion = null): string
    {
        $trimmed = trim($currentQuestion);
        if (!$previousUserQuestion || trim($previousUserQuestion) === '') {
            return $trimmed;
        }

        $words = preg_split('/\s+/u', $trimmed, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $lower = mb_strtolower($trimmed);

        $anaphoricPatterns = [
            'buni', 'shuni', 'uni', 'bu haqda', 'shu haqda', 'u haqda',
            'tushuntir', 'batafsil', 'misol', 'nega unday', 'qanday qilib',
            'davom et', 'explain this', 'more about', 'подробнее', 'объясни это',
        ];

        $needsExpansion = count($words) <= 4;
        if (!$needsExpansion) {
            foreach ($anaphoricPatterns as $p) {
                if (str_contains($lower, $p)) {
                    $needsExpansion = true;
                    break;
                }
            }
        }

        if ($needsExpansion) {
            return trim($previousUserQuestion) . ' ' . $trimmed;
        }

        return $trimmed;
    }

    /**
     * "Bu kitob nima haqida?", "Asosiy g'oyalar nimalar?" kabi umumiy kitob bo'yicha savollarni aniqlaydi.
     */
    public function isOverviewQuestion(string $question): bool
    {
        $lower = mb_strtolower(trim($question));

        // Agar savol ichida aniq boshqa kitob yoki begona mavzu so'ralgan bo'lsa, overview deb hisoblamaymiz
        $overviewPhrases = [
            'kitob nima haqida',
            'kitob nia haqida',
            'kitob nma haqida',
            'kitob haqida',
            'nima haqida',
            'bu kitob nima',
            'kitob mazmuni',
            'kitobning mazmuni',
            'umumiy mazmun',
            'qisqacha mazmun',
            'asosiy g\'oya',
            'asosiy g‘oya',
            'asosiy goya',
            'asosiy fikr',
            'asosiy tushuncha',
            'asosiy mavzu',
            'nimalar yoritilgan',
            'nimalar haqida',
            'nimalar bor',
            'bu yerda nima',
            'nimani o\'rgan',
            'nimani organ',
            'xulosasi nima',
            'kitob haqida ayt',
            'kitob haqida gapir',
            'what is this book about',
            'main idea',
            'main ideas',
            'key concepts',
            'summary of the book',
            'о чем эта книга',
            'основная идея',
            'основные идеи',
            'главные мысли',
        ];

        foreach ($overviewPhrases as $phrase) {
            if (str_contains($lower, $phrase)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Savoldan so'ralgan aniq sahifa (bet) raqamini aniqlaydi (masalan: "1-betdagi", "2-sahifa", "page 3", "3-bet").
     */
    public function extractPageNumber(string $query): ?int
    {
        $lower = mb_strtolower(trim($query));
        $lower = str_replace(['o‘', 'oʼ', 'o`', 'g‘', 'gʼ', 'g`'], ["o'", "o'", "o'", "g'", "g'", "g'"], $lower);

        // Pattern 1: "1-bet", "1-betdagi", "12-sahifa", "5-page", "3-стр", "4 bet", "5 sahifa"
        if (preg_match('/(?:^|[^\d])(\d{1,4})\s*[-–—]?\s*(?:bet|sahifa|page|стр|страниц)/u', $lower, $m)) {
            $p = (int) $m[1];
            return $p > 0 ? $p : null;
        }

        // Pattern 2: "bet: 1", "bet 2", "sahifa 3", "page 4", "p. 5", "стр 6", "страница 7"
        if (preg_match('/(?:^|[^\p{L}])(?:bet|sahifa|page|p|стр|страниц)[a-zа-я\']*\s*[:#\.-]?\s*(\d{1,4})\b/u', $lower, $m)) {
            $p = (int) $m[1];
            return $p > 0 ? $p : null;
        }

        return null;
    }

    /**
     * Savoldan so'ralgan aniq bob raqamini aniqlaydi (masalan: "1-bob", "2-bobdagi", "chapter 3", "1-glava").
     */
    public function extractChapterNumber(string $query): ?int
    {
        $lower = mb_strtolower(trim($query));
        $lower = str_replace(['o‘', 'oʼ', 'o`', 'g‘', 'gʼ', 'g`'], ["o'", "o'", "o'", "g'", "g'", "g'"], $lower);

        // Pattern 1: "1-bob", "1-bobdagi", "2-chapter", "3-glava", "4 bob"
        if (preg_match('/(?:^|[^\d])(\d{1,3})\s*[-–—]?\s*(?:bob|chapter|glava|глав|bo\'lim|qism)/u', $lower, $m)) {
            $ch = (int) $m[1];
            return $ch > 0 ? $ch : null;
        }

        // Pattern 2: "bob 1", "chapter 2", "glava 3", "глава 4"
        if (preg_match('/(?:^|[^\p{L}])(?:bob|chapter|ch|glava|глав|bo\'lim|qism)[a-zа-я\']*\s*[:#\.-]?\s*(\d{1,3})\b/u', $lower, $m)) {
            $ch = (int) $m[1];
            return $ch > 0 ? $ch : null;
        }

        return null;
    }

    /**
     * Kitobning normallashtirilgan float32 vektor matritsasini keshdan oladi (kalitda ai_indexed_at bor).
     *
     * @return array<int, float[]>
     */
    protected function getCachedBookVectorMatrix(Book $book): array
    {
        $ts = $book->ai_indexed_at ? $book->ai_indexed_at->timestamp : '0';
        $cacheKey = "ai_book_matrix:{$book->id}:{$ts}";

        return Cache::remember($cacheKey, 3600, function () use ($book) {
            $rows = BookChunk::where('book_id', $book->id)
                ->whereNotNull('embedding')
                ->get(['id', 'embedding']);

            $matrix = [];
            foreach ($rows as $row) {
                $vec = BookChunk::unpackEmbedding($row->embedding);
                if (!empty($vec)) {
                    $matrix[(int) $row->id] = $vec;
                }
            }

            return $matrix;
        });
    }

    /**
     * Normallashtirilgan ikkita vektorning skalyar ko'paytmasi (cosine similarity).
     *
     * @param  float[]  $a
     * @param  float[]  $b
     */
    public function dotProduct(array $a, array $b): float
    {
        $len = min(count($a), count($b));
        if ($len === 0) {
            return 0.0;
        }

        $sum = 0.0;
        for ($i = 0; $i < $len; $i++) {
            $sum += ((float) $a[$i]) * ((float) $b[$i]);
        }

        return max(0.0, min(1.0, $sum));
    }

    /**
     * MySQL FULLTEXT indeksidan foydalanib dastlabki ballarni oladi.
     *
     * @return array<int, float>
     */
    protected function queryMysqlFulltextScores(int $bookId, string $query): array
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return [];
        }

        $cleanQuery = trim(preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $query) ?? '');
        if ($cleanQuery === '') {
            return [];
        }

        try {
            $rows = DB::select(
                'SELECT id, MATCH(text) AGAINST(? IN NATURAL LANGUAGE MODE) AS ft_score
                 FROM book_chunks
                 WHERE book_id = ? AND MATCH(text) AGAINST(? IN NATURAL LANGUAGE MODE) > 0
                 LIMIT 25',
                [$cleanQuery, $bookId, $cleanQuery]
            );

            $scores = [];
            foreach ($rows as $r) {
                $scores[(int) $r->id] = (float) $r->ft_score;
            }

            return $scores;
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Qidiruv so'rovini kalit so'zlar va o'zaklarga (stem) ajratadi.
     *
     * @return array<int, array{raw: string, stem: string}>
     */
    public function tokenizeForSearch(string $text): array
    {
        $lower = mb_strtolower($text);
        $lower = str_replace(['o‘', 'oʼ', 'o`', 'g‘', 'gʼ', 'g`'], ["o'", "o'", "o'", "g'", "g'", "g'"], $lower);
        $rawWords = preg_split('/[^\p{L}\p{N}\']+/u', $lower, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        $tokens = [];
        $seen = [];

        foreach ($rawWords as $w) {
            $w = trim($w, "'");
            if (mb_strlen($w) < 2) {
                continue;
            }
            if (in_array($w, self::STOP_WORDS, true)) {
                continue;
            }
            $stem = $this->stemWord($w);
            if (isset($seen[$stem])) {
                continue;
            }
            $seen[$stem] = true;
            $tokens[] = [
                'raw'  => $w,
                'stem' => $stem,
            ];
        }

        return $tokens;
    }

    /**
     * O'zbek, ingliz va rus so'z qo'shimchalarini qisqartirib o'zak oladi.
     */
    protected function stemWord(string $word): string
    {
        $suffixes = [
            'larining', 'laridan', 'lariga', 'larini', 'larda', 'lardan', 'larga', 'larni', 'lari', 'lar',
            'ning', 'dagi', 'dan', 'ga', 'da', 'ni', 'si', 'i', 'miz', 'ngiz',
            'tion', 'ing', 'ments', 'ment', 'ness', 'able', 'ies', 'es', 'ed', 'ly', 's',
        ];

        foreach ($suffixes as $suf) {
            if (mb_strlen($word) >= (mb_strlen($suf) + 4) && str_ends_with($word, $suf)) {
                return mb_substr($word, 0, mb_strlen($word) - mb_strlen($suf));
            }
        }

        return $word;
    }

    /**
     * Savol tokenlari va bo'lak matni o'rtasidagi leksik (BM25 + o'zak) moslikni [0.0 .. 1.0] oraliqda hisoblaydi.
     *
     * @param  array<int, array{raw: string, stem: string}>  $queryTokens
     */
    protected function computeLexicalScore(array $queryTokens, string $chunkText, string $rawQuery): float
    {
        if (empty($queryTokens)) {
            return 0.0;
        }

        $lowerChunk = mb_strtolower($chunkText);
        $lowerChunk = str_replace(['o‘', 'oʼ', 'o`', 'g‘', 'gʼ', 'g`'], ["o'", "o'", "o'", "g'", "g'", "g'"], $lowerChunk);

        $matchedTokens = 0;
        $totalTermWeight = 0.0;

        foreach ($queryTokens as $t) {
            $raw = $t['raw'];
            $stem = $t['stem'];

            // 1. To'liq so'z mosligi (so'z chegarasi bilan — "ob" -> "object" ichida mos tushmasligi uchun)
            $exactPattern = '/(?<![\p{L}\p{N}])' . preg_quote($raw, '/') . '(?![\p{L}\p{N}])/u';
            $exactCount = (int) preg_match_all($exactPattern, $lowerChunk);
            if ($exactCount > 0) {
                $matchedTokens++;
                $tfScore = ($exactCount * 2.2) / ($exactCount + 1.2);
                $totalTermWeight += min(1.0, $tfScore);
                continue;
            }

            // 2. O'zak (stem) mosligi (so'z boshidan kamida 4 harfli o'zak)
            if (mb_strlen($stem) >= 4) {
                $stemPattern = '/(?<![\p{L}\p{N}])' . preg_quote($stem, '/') . '[\p{L}\p{N}]*/u';
                $stemCount = (int) preg_match_all($stemPattern, $lowerChunk);
                if ($stemCount > 0) {
                    $matchedTokens++;
                    $tfScore = ($stemCount * 1.6) / ($stemCount + 1.4);
                    $totalTermWeight += min(0.85, $tfScore * 0.8);
                }
            }
        }

        if ($matchedTokens === 0) {
            return 0.0;
        }

        $effectiveDenom = max(1, min(count($queryTokens), 3));
        $coverageRatio = min(1.0, $matchedTokens / $effectiveDenom);
        $avgWeight = min(1.0, $totalTermWeight / $effectiveDenom);

        $score = ($coverageRatio * 0.60) + ($avgWeight * 0.40);

        // Agar savoldagi bir nechta so'z ketma-ket ibora sifatida uchrasa bonus
        $cleanPhrase = trim(preg_replace('/[^\p{L}\p{N}\s]/u', '', mb_strtolower($rawQuery)) ?? '');
        if (mb_strlen($cleanPhrase) >= 8 && str_contains($lowerChunk, $cleanPhrase)) {
            $score = min(1.0, $score + 0.25);
        }

        return round(max(0.0, min(1.0, $score)), 4);
    }
}
