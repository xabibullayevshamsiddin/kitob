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
        // So'zlashuv, sleng va qisqartma to'ldiruvchilar (fillers)
        'brat', 'aka', 'akaxon', 'uka', 'opajon', 'ustoz', 'xullas', 'xosh', 'xo\'sh',
        'tezroq', 'aniqroq', 'oddiyroq', 'qisqacha', 'etvor', 'aytvor', 'yechvor', 'echvor',
        'tushuntirvor', 'yozvor', 'tashavor', 'korsatvor', 'topvor', 'qb', 'qilib', 'qber', 'qvor',
        'berchi', 'koraychi', 'qani', 'e', 'hey', 'salom', 'qale', 'privet', 'hello',
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
        $targetPage = $this->extractPageNumber($query);
        if ($targetPage === null && $expandedQuery !== $query && $previousUserQuestion && preg_match('/(?:shu|o[\'`ʻ’]sha|unda(?:gi)?)\s*(?:bet|sahifa|yerda)/ui', $query)) {
            $targetPage = $this->extractPageNumber($previousUserQuestion);
        }
        $targetChapter = $this->extractChapterNumber($query);
        if ($targetChapter === null && $expandedQuery !== $query && $previousUserQuestion && preg_match('/(?:shu|o[\'`ʻ’]sha|unda(?:gi)?)\s*bob/ui', $query)) {
            $targetChapter = $this->extractChapterNumber($previousUserQuestion);
        }
        $targetExercise = $this->extractExerciseNumber($query);
        $isPageTask = $this->isPageTaskQuery($expandedQuery, $targetPage, $targetChapter);

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
        $topicalTokens = $this->extractTopicalTokens($expandedQuery, $targetPage, $targetChapter, $targetExercise);
        $hasSubstantiveTopicalTokens = !empty($topicalTokens);

        $scoredChunks = [];
        $maxMysqlFt = !empty($mysqlFulltextScores) ? max($mysqlFulltextScores) : 0.0;
        $anyTopicalMatched = false;

        // 4. Umumiy savollar (overview / asosiy g'oyalar / mazmun) uchun butun kitob bo'ylab teng taqsimlangan bo'laklarni belgilash
        $totalChunksCount = $chunks->count();
        $overviewOrdinalBoosts = [];
        if ($isOverview && !$hasSubstantiveTopicalTokens && $totalChunksCount > 0) {
            if ($totalChunksCount <= $limit) {
                for ($i = 0; $i < $totalChunksCount; $i++) {
                    $overviewOrdinalBoosts[$i] = round(0.78 - ($i * 0.015), 4);
                }
            } else {
                // $limit ta bo'lakni butun kitob bo'ylab (boshi, 20%, 40%, 60%, 80%, oxiri) teng taqsimlash
                for ($step = 0; $step < $limit; $step++) {
                    $targetOrdinal = (int) round(($step / max(1, $limit - 1)) * ($totalChunksCount - 1));
                    $overviewOrdinalBoosts[$targetOrdinal] = round(0.78 - ($step * 0.015), 4);
                }
            }
        }

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
                $contentScore = max(
                    ($vecScore * 0.72) + ($lexicalScore * 0.28),
                    $vecScore,
                    $lexicalScore * 0.85
                );
            } else {
                $contentScore = $lexicalScore;
            }

            // Haqiqiy mavzuga moslikni tekshiramiz (so'ralgan kalit so'zlar bo'lakda bormi?)
            $matchedTopicalCount = $this->countMatchedTopicalTokens($topicalTokens, $searchableText);
            $hasTopicalAlignment = !$hasSubstantiveTopicalTokens
                || $matchedTopicalCount > 0
                || ($hasVectorMode && $vecScore >= 0.20);

            if ($hasTopicalAlignment && $contentScore >= 0.12) {
                $anyTopicalMatched = true;
            }

            // Sahifa (bet) mosligini tekshirish
            $isTargetPage = false;
            if ($targetPage !== null && $chunk->page_start !== null) {
                $pStart = (int) $chunk->page_start;
                $pEnd = (int) ($chunk->page_end ?? $chunk->page_start);
                if ($targetPage >= $pStart && $targetPage <= $pEnd) {
                    $isTargetPage = true;
                }
            }

            // Bob (chapter) mosligini tekshirish
            $isTargetChapter = false;
            if ($targetChapter !== null) {
                if ($chunk->chapter && (int) $chunk->chapter->chapter_number === $targetChapter) {
                    $isTargetChapter = true;
                } elseif (preg_match('/\bchapter\s*' . $targetChapter . '\b/i', $searchableText) || preg_match('/\b' . $targetChapter . '\s*[-–]?\s*bob\b/iu', $searchableText)) {
                    $isTargetChapter = true;
                }
            }

            // Agar sof sahifa/bob konspekt savoli, sahifa amaliy vazifasi (misol yechish, tarjima, qoida) yoki umumiy mazmun savoli bo'lsa
            if ((!$hasSubstantiveTopicalTokens || $isPageTask) && ($isTargetPage || $isTargetChapter || $isOverview)) {
                $anyTopicalMatched = true;
            }

            $combinedScore = $contentScore;

            // 4. Sahifa yoki bob bo'yicha boost:
            // MUHIM QOIDA: Agar savol begona mavzuni sahifa raqami bilan so'rasa ("50-betda Bitcoin narxi"),
            // lekin u betda Bitcoin haqida so'z bo'lmasa, HECH QANDAY boost berilmaydi va rad qilinadi!
            if ($isTargetPage || $isTargetChapter) {
                if (!$hasSubstantiveTopicalTokens || $isPageTask) {
                    // Sof sahifa/bob konspekt savoli yoki sahifadagi amaliy topshiriq (misol yechish, tarjima, qoida)
                    $combinedScore = max($combinedScore, $isTargetPage ? 0.75 : 0.65);
                    if ($targetExercise !== null && $this->chunkContainsExerciseNumber($searchableText, $targetExercise)) {
                        $combinedScore = min(0.98, $combinedScore + 0.18);
                    }
                } elseif ($hasTopicalAlignment && $contentScore >= 0.12) {
                    // Mavzu haqiqatan ham shu sahifada/bobda bor! Shuning uchun uni eng yuqoriga chiqaramiz
                    $combinedScore = min(0.98, $contentScore + 0.22);
                    if ($targetExercise !== null && $this->chunkContainsExerciseNumber($searchableText, $targetExercise)) {
                        $combinedScore = min(0.98, $combinedScore + 0.10);
                    }
                } else {
                    // Mavzu bu sahifada YO'Q (masalan, begona mavzu yoki noto'g'ri bet).
                    // Hech qanday boost berilmaydi, past contentScore saqlanadi.
                    $combinedScore = $contentScore;
                }
            }

            // 5. Agar foydalanuvchi "Bu kitob nima haqida?" deb umumiy savol bersa va alohida begona mavzu aytmasa:
            // Butun kitob bo'ylab teng taqsimlangan bo'laklarga boost beramiz
            if ($isOverview && !$hasSubstantiveTopicalTokens && isset($overviewOrdinalBoosts[(int) $chunk->ordinal])) {
                $combinedScore = max($combinedScore, $overviewOrdinalBoosts[(int) $chunk->ordinal]);
                $anyTopicalMatched = true;
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
                'content_score' => round($contentScore, 4),
                'vector_score'  => $vecScore,
                'lexical_score' => $lexicalScore,
                'is_target_page'=> $isTargetPage,
            ];
        }

        usort($scoredChunks, function (array $a, array $b) {
            if ($b['score'] !== $a['score']) {
                return $b['score'] <=> $a['score'];
            }
            return $a['ordinal'] <=> $b['ordinal'];
        });

        // Agar foydalanuvchi sof sahifa yoki bob so'rovini yoki sahifadagi amaliy topshiriqni (misol, tarjima) bergan bo'lsa:
        // Begona sahifalar LLM kontekstini va manba iqtiboslarini chalkashtirmasligi uchun faqat maqsadli bo'laklarni qoldiramiz.
        if (!$hasSubstantiveTopicalTokens || $isPageTask) {
            if ($targetPage !== null) {
                $targetPageChunks = array_values(array_filter($scoredChunks, static fn(array $c) => !empty($c['is_target_page'])));
                if (!empty($targetPageChunks)) {
                    $scoredChunks = $targetPageChunks;
                }
            } elseif ($targetChapter !== null) {
                $targetChapterChunks = array_values(array_filter($scoredChunks, static fn(array $c) => ($c['score'] ?? 0) >= 0.60));
                if (!empty($targetChapterChunks)) {
                    $scoredChunks = $targetChapterChunks;
                }
            }
        }

        $topChunks = array_slice($scoredChunks, 0, $limit);

        // Agar umumiy kitob konspekti/g'oyalari so'rovi bo'lsa, tanlangan bo'laklarni
        // xronologik (ordinal bo'yicha o'sish) tartibida qaytaramiz, shunda LLM
        // kitob boshidan oxirigacha bo'lgan tuzilmani to'g'ri ko'radi.
        if ($isOverview && !empty($topChunks)) {
            usort($topChunks, static fn(array $a, array $b) => ($a['ordinal'] ?? 0) <=> ($b['ordinal'] ?? 0));
        }

        $topScore = !empty($topChunks) ? (float) $topChunks[0]['score'] : 0.0;

        return [
            'chunks'            => $topChunks,
            'top_score'         => $topScore,
            'mode'              => $hasVectorMode ? 'hybrid' : 'fulltext',
            'expanded_query'    => $expandedQuery,
            'is_overview_query' => $isOverview,
            'target_page'       => $targetPage,
            'target_chapter'    => $targetChapter,
            'target_exercise'   => $targetExercise,
            'is_page_task'      => $isPageTask,
            'has_topical_match' => $anyTopicalMatched,
            'topical_tokens'    => array_column($topicalTokens, 'raw'),
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

        // Do'stona muloqot (salomlashish, minnatdorchilik) yoki butun kitob bo'yicha umumiy savollarni
        // aslo oldingi sahifa/misol savollariga ulamaymiz!
        /** @var RelevanceGate $gate */
        $gate = app(RelevanceGate::class);
        if ($gate->getConversationalType($trimmed) !== null) {
            return $trimmed;
        }

        if ($this->isOverviewQuestion($trimmed)) {
            return $trimmed;
        }

        $lower = mb_strtolower($trimmed);

        $anaphoricPatterns = [
            'buni', 'shuni', 'uni', 'bu haqda', 'shu haqda', 'u haqda',
            'tushuntir', 'batafsil', 'misol', 'nega unday', 'qanday qilib',
            'davom et', 'keyingisi', 'yana', 'explain this', 'more about',
            'подробнее', 'объясни это', 'продолжай', 'следующий',
        ];

        $hasAnaphora = false;
        foreach ($anaphoricPatterns as $p) {
            if (str_contains($lower, $p)) {
                $hasAnaphora = true;
                break;
            }
        }

        if ($hasAnaphora) {
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
            'asosiy g\'oyalar',
            'asosiy g‘oyalar',
            'asosiy goyalar',
            'asosiy fikr',
            'asosiy fikrlar',
            'asosiy tushuncha',
            'asosiy tushunchalar',
            'asosiy mavzu',
            'asosiy mavzular',
            'muhim g\'oyalar',
            'muhim fikrlar',
            'kitob maqsadi',
            'kitobning maqsadi',
            'kitob konspekti',
            'butun kitob',
            'nimalar yoritilgan',
            'nimalar haqida',
            'kitob nimalar haqida',
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
            'core ideas',
            'central idea',
            'key concepts',
            'summary of the book',
            'о чем эта книга',
            'основная идея',
            'основные идеи',
            'главные мысли',
            'necha varoq',
            'necha varoqli',
            'nechta varoq',
            'varoqlar soni',
            'necha bet',
            'necha betli',
            'nechta bet',
            'betlar soni',
            'necha sahifa',
            'necha sahifali',
            'nechta sahifa',
            'sahifalar soni',
            'necha bob',
            'nechta bob',
            'boblar soni',
            'qanday boblar',
            'muallifi kim',
            'kim yozgan',
            'muallif haqida',
            'avtori kim',
            'qachon yozilgan',
            'qachon nashr',
            'nashr yili',
            'how many pages',
            'page count',
            'how many chapters',
            'who wrote',
            'who is the author',
            'сколько страниц',
            'сколько глав',
            'кто автор',
        ];

        foreach ($overviewPhrases as $phrase) {
            if (str_contains($lower, $phrase)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Savoldan so'ralgan aniq sahifa (bet) raqamini aniqlaydi (masalan: "5 chi betda", "1-betdagi", "2-sahifa", "page 3", "3-bet").
     */
    public function extractPageNumber(string $query): ?int
    {
        $lower = mb_strtolower(trim($query));
        $lower = str_replace(['o‘', 'oʼ', 'o`', 'g‘', 'gʼ', 'g`'], ["o'", "o'", "o'", "g'", "g'", "g'"], $lower);

        // Pattern 1: "5 chi betda", "5-chi bet", "1-bet", "1-betdagi", "12-sahifa", "5-page", "3-стр", "4 bet", "5 sahifa", "2-varaq"
        if (preg_match('/(?:^|[^\d])(\d{1,4})\s*(?:[-–—]?\s*(?:(?:in)?chi|inji|nchi|nji|[яе]-?я|[йя]-?й))?\s*[-–—]?\s*(?:bet|sahifa|page|стр|страниц|varaq|varoq)[a-zа-я\']*/u', $lower, $m)) {
            $p = (int) $m[1];
            return $p > 0 ? $p : null;
        }

        // Pattern 2: "bet: 1", "bet 2", "sahifa 3", "page 4", "p. 5", "стр 6", "страница 7", "varaq 8"
        if (preg_match('/(?:^|[^\p{L}])(?:bet|sahifa|page|p|стр|страниц|varaq|varoq)[a-zа-я\']*\s*[:#\.-]?\s*(\d{1,4})\b/u', $lower, $m)) {
            $p = (int) $m[1];
            return $p > 0 ? $p : null;
        }

        return null;
    }

    /**
     * Savoldan so'ralgan aniq bob raqamini aniqlaydi (masalan: "1 chi bob", "1-bob", "2-bobdagi", "chapter 3", "1-glava").
     */
    public function extractChapterNumber(string $query): ?int
    {
        $lower = mb_strtolower(trim($query));
        $lower = str_replace(['o‘', 'oʼ', 'o`', 'g‘', 'gʼ', 'g`'], ["o'", "o'", "o'", "g'", "g'", "g'"], $lower);

        // Pattern 1: "1 chi bob", "1-bob", "1-bobdagi", "2-chapter", "3-glava", "4 bob"
        if (preg_match('/(?:^|[^\d])(\d{1,3})\s*(?:[-–—]?\s*(?:(?:in)?chi|inji|nchi|nji|[яе]-?я|[йя]-?й))?\s*[-–—]?\s*(?:bob|chapter|glava|глав|bo\'lim|qism)[a-zа-я\']*/u', $lower, $m)) {
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
     * Savoldan so'ralgan misol yoki mashq raqamini aniqlaydi (masalan: "1-misol", "2-mashq", "3-masala", "exercise 4").
     */
    public function extractExerciseNumber(string $query): ?int
    {
        $lower = mb_strtolower(trim($query));
        $lower = str_replace(['o‘', 'oʼ', 'o`', 'g‘', 'gʼ', 'g`'], ["o'", "o'", "o'", "g'", "g'", "g'"], $lower);

        // Pattern 1: "1-misol", "1-mashq", "2-masala", "3-misolni", "4-topshiriq", "exercise 5", "task 6"
        if (preg_match('/(?:^|[^\d])(\d{1,4})\s*[-–—]?\s*(?:misol|misl|mashq|mawq|masala|topshiriq|vazifa|savol|exercise|task|problem|задач|пример|упражнен)[a-zа-я\']*/u', $lower, $m)) {
            $n = (int) $m[1];
            return $n > 0 ? $n : null;
        }

        // Pattern 2: "misol: 1", "misol 2", "mashq 3", "masala 4", "№ 5", "№5", "exercise 6"
        if (preg_match('/(?:^|[^\p{L}])(?:misol|misl|mashq|mawq|masala|topshiriq|vazifa|exercise|task|problem|№)\s*[:#\.-]?\s*(\d{1,4})\b/u', $lower, $m)) {
            $n = (int) $m[1];
            return $n > 0 ? $n : null;
        }

        return null;
    }

    /**
     * Savol ko'rsatilgan sahifa/bob bo'yicha amaliy topshiriq (misol yechish, tarjima, qoida) ekanligini aniqlaydi.
     */
    public function isPageTaskQuery(string $query, ?int $targetPage = null, ?int $targetChapter = null): bool
    {
        if ($targetPage === null && $targetChapter === null) {
            return false;
        }

        $lower = mb_strtolower($query);
        $taskPatterns = [
            'misol', 'misl', 'mashq', 'mawq', 'masal', 'topshir', 'vazif', 'savol',
            'yech', 'ech', 'hisobl', 'hisbl', 'bajar', 'chiqar', 'ishl', 'javob',
            'tarjim', 'tarjm', 'perevod', 'translat', 'lugat', 'lug\'at', 'luget', 'ozbekch', 'o\'zbekch',
            'qoid', 'qoyd', 'shart', 'tarif', 'ta\'rif', 'formul', 'fomul', 'tushunt', 'tushint',
            'exercise', 'task', 'problem', 'solve', 'solution',
            'упражнен', 'задач', 'пример', 'реши', 'перевод',
        ];

        foreach ($taskPatterns as $p) {
            if (str_contains($lower, $p)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Bo'lak matnida so'ralgan misol yoki mashq raqami (masalan "1-misol", "1.", "1)", "№1", "Exercise 1") borligini tekshiradi.
     */
    public function chunkContainsExerciseNumber(string $text, int $exerciseNum): bool
    {
        $patterns = [
            '/(?:^|[^\d])' . $exerciseNum . '\s*[-–—]?\s*(?:misol|mashq|masala|topshiriq|vazifa|exercise|task|problem|задач|пример|упражнен)/iu',
            '/(?:misol|mashq|masala|topshiriq|vazifa|exercise|task|problem|№)\s*[:#\.-]?\s*' . $exerciseNum . '\b/iu',
            '/(?:^|\n|\.\s+)' . $exerciseNum . '\s*[\.\)]\s+/u',
            '/\b№\s*' . $exerciseNum . '\b/u',
        ];

        foreach ($patterns as $p) {
            if (preg_match($p, $text)) {
                return true;
            }
        }

        return false;
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
        $lower = $this->normalizeTextForMatching($text);
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

        $lowerChunk = $this->normalizeTextForMatching($chunkText);

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
                    continue;
                }
            }

            // 3. Imloviy xato / Fuzzy moslik (Levenshtein masofasi)
            if (mb_strlen($raw) >= 4) {
                $targetLen = mb_strlen($raw);
                $maxDist = $targetLen >= 7 ? 2 : 1;
                $wordsInChunk = preg_split('/[^\p{L}\p{N}\']+/u', $lowerChunk, -1, PREG_SPLIT_NO_EMPTY) ?: [];
                $fuzzyFound = false;
                foreach ($wordsInChunk as $cw) {
                    $cwClean = trim($cw, "'");
                    $cLen = mb_strlen($cwClean);
                    if (abs($cLen - $targetLen) > $maxDist || $cLen < 3) {
                        continue;
                    }
                    if (levenshtein($raw, $cwClean) <= $maxDist || levenshtein($stem, mb_substr($cwClean, 0, mb_strlen($stem))) <= $maxDist) {
                        $fuzzyFound = true;
                        break;
                    }
                }
                if ($fuzzyFound) {
                    $matchedTokens++;
                    $totalTermWeight += 0.70;
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

    /**
     * Savoldan umumiy/tuzilmaviy (bet, bob, nima, qanday va h.k.) so'zlardan tozalangan
     * haqiqiy mavzuga oid kalit so'zlarni ajratadi.
     *
     * @return array<int, array{raw: string, stem: string}>
     */
    public function extractTopicalTokens(string $query, ?int $targetPage = null, ?int $targetChapter = null, ?int $targetExercise = null): array
    {
        $allTokens = $this->tokenizeForSearch($query);
        $structuralStems = [
            'bet', 'sahif', 'page', 'bob', 'chapt', 'glav', 'qism', 'bo\'lim',
            'bolim', 'malumot', 'ma\'lumot', 'haq', 'tushunt', 'ayt', 'ber', 'nima', 'qand',
            'qays', 'kim', 'qach', 'yozil', 'ibor', 'mavz', 'fikr', 'goya', 'g\'oya', 'g‘oya',
            'uchr', 'soz', 'atama', 'mano', 'natij', 'nech', 'qanch', 'deyil', 'keltir',
            'chi', 'inchi', 'inji', 'nchi', 'nji', 'qanaq', 'borm', 'mavjud',
            'kitob', 'knig', 'book', 'darslik',
            'varoq', 'varaq', 'list', 'jild',
            'muallif', 'yozuvch', 'avtor', 'nashr', 'janr',
            // Mashq, misol, masala va yechish vazifa so'zlari
            'misol', 'mashq', 'masal', 'topshir', 'savol', 'vazif', 'exercis', 'task', 'problem',
            'yech', 'yechim', 'bajar', 'hisobl', 'chiqar', 'ishl', 'javob', 'solut', 'solv',
            'primer', 'zadach', 'uprajnen', 'pravil', 'otvet', 'reshen',
            // Tarjima va til vazifa so'zlari
            'tarjim', 'perevod', 'translat', 'lugat', 'lug\'at', 'ozbekch', 'o\'zbekch',
            'matn', 'juml', 'qil', 'yoz', 'korsat', 'ko\'rsat',
            // Sleng va norasmiy murojaatlar
            'brat', 'aka', 'akaxon', 'uka', 'ustoz', 'xullas', 'tezroq', 'aniqroq', 'oddiyroq',
            'etvor', 'aytvor', 'yechvor', 'echvor', 'tushuntirvor', 'tushintirvor', 'yozvor', 'tashavor',
            'korsatvor', 'topvor', 'qb', 'qvor', 'qber', 'salom', 'privet', 'hello',
        ];

        $topical = [];
        foreach ($allTokens as $t) {
            $raw = $t['raw'];
            $stem = $t['stem'];

            // Raqamlar yoki so'ralgan sahifa/bob/misol raqamini o'tkazib yuborish
            if (is_numeric($raw)) {
                continue;
            }
            if ($targetPage !== null && (string) $targetPage === $raw) {
                continue;
            }
            if ($targetChapter !== null && (string) $targetChapter === $raw) {
                continue;
            }
            if ($targetExercise !== null && (string) $targetExercise === $raw) {
                continue;
            }

            // Tuzilmaviy so'zlarni o'tkazib yuborish
            $isStructural = false;
            foreach ($structuralStems as $s) {
                if (str_starts_with($stem, $s) || str_starts_with($raw, $s)) {
                    $isStructural = true;
                    break;
                }
            }
            if ($isStructural) {
                continue;
            }

            $topical[] = $t;
        }

        return $topical;
    }

    /**
     * Berilgan bo'lak matnida so'ralgan mavzuga oid kalit so'zlardan nechtasi uchrashini hisoblaydi.
     *
     * @param  array<int, array{raw: string, stem: string}>  $topicalTokens
     */
    public function countMatchedTopicalTokens(array $topicalTokens, string $chunkText): int
    {
        if (empty($topicalTokens)) {
            return 0;
        }

        $lowerChunk = $this->normalizeTextForMatching($chunkText);
        $matches = 0;

        foreach ($topicalTokens as $t) {
            $raw = $this->normalizeTextForMatching($t['raw']);
            $stem = $this->normalizeTextForMatching($t['stem']);

            $pattern = '/(?<![\p{L}\p{N}])(' . preg_quote($raw, '/') . '|' . preg_quote($stem, '/') . '[\p{L}\p{N}]*)(?![\p{L}\p{N}])/u';
            if (preg_match($pattern, $lowerChunk)) {
                $matches++;
                continue;
            }

            // Imloviy xato bilan yozilgan bo'lsa (Fuzzy tekshiruv)
            if (mb_strlen($raw) >= 4) {
                $targetLen = mb_strlen($raw);
                $maxDist = $targetLen >= 7 ? 2 : 1;
                $wordsInChunk = preg_split('/[^\p{L}\p{N}\']+/u', $lowerChunk, -1, PREG_SPLIT_NO_EMPTY) ?: [];
                foreach ($wordsInChunk as $cw) {
                    $cwClean = trim($cw, "'");
                    $cLen = mb_strlen($cwClean);
                    if (abs($cLen - $targetLen) > $maxDist || $cLen < 3) {
                        continue;
                    }
                    if (levenshtein($raw, $cwClean) <= $maxDist || levenshtein($stem, mb_substr($cwClean, 0, mb_strlen($stem))) <= $maxDist) {
                        $matches++;
                        break;
                    }
                }
            }
        }

        return $matches;
    }

    /**
     * Matnni harflar, apostroflar va kirill/lotin transliteratsiyasi bo'yicha me'yorlashtiradi.
     */
    public function normalizeTextForMatching(string $text): string
    {
        $lower = mb_strtolower($text);
        $lower = str_replace(
            ['o‘', 'oʼ', 'o`', 'o’', 'g‘', 'gʼ', 'g`', 'g’', 'ʻ', 'ʼ', '’', '`', '‘'],
            ["o'", "o'", "o'", "o'", "g'", "g'", "g'", "g'", "'", "'", "'", "'", "'"],
            $lower
        );

        return $this->transliterateCyrillicToLatin($lower);
    }

    /**
     * O'zbek kirill yozuvidagi so'zlarni lotin yozuviga transliteratsiya qiladi
     * (kirillcha so'rov berilganda ham lotincha kitob matnini topa olish uchun).
     */
    public function transliterateCyrillicToLatin(string $text): string
    {
        $cyrToLat = [
            'ў' => "o'", 'ғ' => "g'", 'ш' => 'sh', 'ч' => 'ch', 'ю' => 'yu', 'я' => 'ya', 'ё' => 'yo', 'ж' => 'j',
            'ц' => 'ts', 'щ' => 'sh', 'э' => 'e', 'х' => 'x', 'ҳ' => 'h', 'қ' => 'q',
            'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'з' => 'z', 'и' => 'i',
            'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r',
            'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'ъ' => "'", 'ь' => '', 'ы' => 'i',
        ];

        return strtr($text, $cyrToLat);
    }
}
