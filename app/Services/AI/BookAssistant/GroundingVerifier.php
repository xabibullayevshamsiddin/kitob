<?php

namespace App\Services\AI\BookAssistant;

use App\Models\Book;

class GroundingVerifier
{
    /**
     * AI javobining manbalar bilan dalillanganligini va faktik asosga egaligini tekshiradi.
     *
     * @param  string  $rawAnswer
     * @param  array<int, array<string, mixed>>  $chunks  LLM'ga yuborilgan bo'laklar
     * @param  string  $userQuestion
     * @param  int|null  $targetPage
     * @param  Book|null  $book
     * @return array{
     *     verified: bool,
     *     cleaned_text: string,
     *     sources: array<int, array<string, mixed>>,
     *     is_no_answer: bool,
     *     refusal_reason: string|null
     * }
     */
    public function verify(
        string $rawAnswer,
        array $chunks,
        string $userQuestion = '',
        ?int $targetPage = null,
        ?Book $book = null
    ): array {
        $trimmedAnswer = trim($rawAnswer);

        // 1. Javob bo'sh yoki "topa olmadim" holati
        if ($trimmedAnswer === '' || $this->isNoAnswerResponse($trimmedAnswer)) {
            return [
                'verified'       => false,
                'cleaned_text'   => trans('ai.no_answer'),
                'sources'        => [],
                'is_no_answer'   => true,
                'refusal_reason' => 'no_answer',
            ];
        }

        $maxChunks = count($chunks);
        $foundIndices = [];

        // 2. [n] yoki [0, 1] ko'rsatkichlarini ajratib olish va mavjud bo'lmagan [99] kabi soxta indekslarni tozalash
        // MUHIM: [0] — bu kitob pasporti va umumiy ma'lumotlaridir
        $cleanedText = preg_replace_callback('/\[\s*(\d+(?:\s*,\s*\d+)*)\s*\]/', function (array $m) use ($maxChunks, &$foundIndices) {
            $rawParts = preg_split('/\s*,\s*/', trim($m[1])) ?: [];
            $validNums = [];
            foreach ($rawParts as $p) {
                $num = (int) $p;
                if ($num === 0 || ($num >= 1 && $num <= $maxChunks)) {
                    $foundIndices[$num] = true;
                    $validNums[] = $num;
                }
            }
            if (!empty($validNums)) {
                $uniqueNums = array_values(array_unique($validNums));
                sort($uniqueNums);
                return '[' . implode(', ', $uniqueNums) . ']';
            }
            // Mavjud bo'lmagan citation olib tashlanadi
            return '';
        }, $trimmedAnswer) ?? $trimmedAnswer;

        $cleanedText = preg_replace('/[ \t]{2,}/', ' ', $cleanedText) ?? $cleanedText;
        $cleanedText = trim($cleanedText);

        // 3. Agar foydalanuvchi aniq sahifani so'ragan bo'lsa (masalan: 12-bet),
        // ko'rsatilgan manbalar aynan shu betni o'z ichiga olganligini tekshiramiz
        if ($targetPage !== null) {
            $filteredIndices = [];
            foreach (array_keys($foundIndices) as $idx) {
                if ($idx === 0) {
                    continue; // 0 bu pasport, aniq sahifa so'rovida qatnashmaydi
                }
                $chunk = $chunks[$idx - 1] ?? null;
                if (!$chunk) {
                    continue;
                }
                $pStart = (int) ($chunk['page_start'] ?? 0);
                $pEnd = (int) ($chunk['page_end'] ?? $pStart);
                if ($targetPage >= $pStart && $targetPage <= $pEnd) {
                    $filteredIndices[$idx] = true;
                }
            }

            // Agar savolda so'ralgan sahifa ko'rsatilmagan boshqa bet bo'lsa, noto'g'ri citationlarni tozalaymiz
            if (!empty($foundIndices) && empty($filteredIndices)) {
                // Javob so'ralgan betdan tashqari boshqa betga tayanmoqda
                return [
                    'verified'       => false,
                    'cleaned_text'   => trans('ai.no_evidence'),
                    'sources'        => [],
                    'is_no_answer'   => true,
                    'refusal_reason' => 'unsupported_page',
                ];
            }
            $foundIndices = $filteredIndices;
        }

        // 4. Manbalarni shakllantirish va mazmunan tekshirish (claim grounding)
        $validSources = [];
        $unsupportedCitations = [];

        $totalPages = $book ? (int) ($book->pages()->max('page_number') ?: $book->chunks()->max('page_end') ?: $book->pages()->count()) : null;

        // [0] — Kitob pasporti manbasi
        if (isset($foundIndices[0])) {
            $passportLabel = 'Kitob pasporti';
            if ($totalPages && $totalPages > 0) {
                $passportLabel .= " ({$totalPages}-bet)";
            }
            $validSources[] = [
                'index'      => 0,
                'chunk_id'   => 0,
                'page_start' => 1,
                'page_end'   => $totalPages ?: null,
                'chapter_id' => null,
                'label'      => $passportLabel,
            ];
        }

        foreach (array_keys($foundIndices) as $citationNum) {
            if ($citationNum === 0) {
                continue;
            }

            $chunk = $chunks[$citationNum - 1] ?? null;
            if (!$chunk) {
                continue;
            }

            $chunkText = (string) ($chunk['text'] ?? '');

            // Bo'lak matni bilan javobning mazmuniy bog'liqligini tekshirish
            $isTargetPageChunk = false;
            if ($targetPage !== null && isset($chunk['page_start'])) {
                $pStart = (int) $chunk['page_start'];
                $pEnd = (int) ($chunk['page_end'] ?? $pStart);
                if ($targetPage >= $pStart && $targetPage <= $pEnd) {
                    $isTargetPageChunk = true;
                }
            }

            if (!$isTargetPageChunk && !$this->isClaimSupportedByChunk($cleanedText, $citationNum, $chunkText)) {
                $unsupportedCitations[] = $citationNum;
                continue;
            }

            $validSources[] = [
                'index'      => $citationNum,
                'chunk_id'   => (int) ($chunk['id'] ?? 0),
                'page_start' => isset($chunk['page_start']) ? (int) $chunk['page_start'] : null,
                'page_end'   => isset($chunk['page_end']) ? (int) $chunk['page_end'] : null,
                'chapter_id' => isset($chunk['chapter_id']) ? (int) $chunk['chapter_id'] : null,
                'label'      => (string) ($chunk['source_label'] ?? ($citationNum . "-bo'lak")),
            ];
        }

        // Agar asossiz citationlar bo'lsa, ularni matndan olib tashlaymiz
        if (!empty($unsupportedCitations)) {
            foreach ($unsupportedCitations as $badIdx) {
                $cleanedText = preg_replace('/\[' . $badIdx . '\]/', '', $cleanedText) ?? $cleanedText;
            }
            $cleanedText = trim(preg_replace('/[ \t]{2,}/', ' ', $cleanedText) ?? $cleanedText);
        }

        // 5. QAT'IY QOIDA (User requirement 3.2):
        // Agar AI birorta ham tasdiqlangan manba ko'rsatmagan bo'lsa,
        // birinchi chunkni AVTOMATIK biriktirish QAT'IYAN TAQIQLANADI!
        // Agar javob kitob matnidagi biron-bir bo'lak bilan umuman asoslanmagan bo'lsa -> rad!
        if (empty($validSources)) {
            $isGroundedWithoutExplicitCitation = $this->isAnswerGroundedInAnyChunk($cleanedText, $chunks);

            // Agar aniq sahifa bo'yicha vazifa (misol yechish, tarjima) bo'lsa va LLM [n] qo'yishni unutgan bo'lsa:
            if (!$isGroundedWithoutExplicitCitation && $targetPage !== null) {
                foreach ($chunks as $cIdx => $c) {
                    $pStart = (int) ($c['page_start'] ?? 0);
                    $pEnd = (int) ($c['page_end'] ?? $pStart);
                    if ($targetPage >= $pStart && $targetPage <= $pEnd) {
                        $isGroundedWithoutExplicitCitation = true;
                        $validSources[] = [
                            'index'      => $cIdx + 1,
                            'chunk_id'   => (int) ($c['id'] ?? 0),
                            'page_start' => $pStart ?: null,
                            'page_end'   => $pEnd ?: null,
                            'chapter_id' => isset($c['chapter_id']) ? (int) $c['chapter_id'] : null,
                            'label'      => (string) ($c['source_label'] ?? (($cIdx + 1) . "-bo'lak")),
                        ];
                        break;
                    }
                }
            }

            // Shuningdek, javob kitob pasporti yoki umumiy tavsifiga asoslanganligini tekshiramiz
            if (!$isGroundedWithoutExplicitCitation && $book) {
                $lowerAnswer = mb_strtolower($cleanedText);
                $totalSheets = $totalPages ? (int) ceil($totalPages / 2) : 0;
                $author = mb_strtolower(trim((string) $book->author));
                $title = mb_strtolower(trim((string) $book->title));

                $isPassportMatch = false;
                if ($totalPages && $totalPages > 0) {
                    if (str_contains($lowerAnswer, (string) $totalPages) || str_contains($lowerAnswer, (string) $totalSheets)) {
                        $isPassportMatch = true;
                    }
                }
                if ($author !== '' && str_contains($lowerAnswer, $author)) {
                    $isPassportMatch = true;
                }
                if ($title !== '' && str_contains($lowerAnswer, $title)) {
                    $isPassportMatch = true;
                }
                if (!empty($book->ai_summary) && $this->isAnswerGroundedInAnyChunk($cleanedText, [['text' => (string) $book->ai_summary]])) {
                    $isPassportMatch = true;
                }

                if ($isPassportMatch) {
                    $isGroundedWithoutExplicitCitation = true;
                    $validSources[] = [
                        'index'      => 0,
                        'chunk_id'   => 0,
                        'page_start' => 1,
                        'page_end'   => $totalPages ?: null,
                        'chapter_id' => null,
                        'label'      => 'Kitob pasporti' . ($totalPages && $totalPages > 0 ? " ({$totalPages}-bet)" : ''),
                    ];
                }
            }

            if (!$isGroundedWithoutExplicitCitation) {
                // Javob berilgan bo'laklarga tayanmagan (uydirma/hallucinatsiya)
                return [
                    'verified'       => false,
                    'cleaned_text'   => trans('ai.no_evidence'),
                    'sources'        => [],
                    'is_no_answer'   => true,
                    'refusal_reason' => 'no_evidence',
                ];
            }
        }

        return [
            'verified'       => true,
            'cleaned_text'   => $cleanedText,
            'sources'        => $validSources,
            'is_no_answer'   => false,
            'refusal_reason' => null,
        ];
    }

    /**
     * Aniq [n] ko'rsatkichli jumla yoki gapning o'sha bo'lak matnida dalili bor-yo'qligini tekshiradi.
     */
    public function isClaimSupportedByChunk(string $answer, int $citationNum, string $chunkText): bool
    {
        $chunkNorm = mb_strtolower($chunkText);

        // Gap ichidagi [n] yoki [0, 1] atrofidagi jumlani ajratamiz
        $pattern = '/([^.!?\n]*\[[^\]]*\b' . $citationNum . '\b[^\]]*\][^.!?\n]*)/u';
        if (preg_match($pattern, $answer, $m)) {
            $claimSentence = trim(preg_replace('/\[[^\]]*\]/', '', $m[1]));
            $claimWords = preg_split('/[^\p{L}\p{N}]+/u', mb_strtolower($claimSentence), -1, PREG_SPLIT_NO_EMPTY) ?: [];

            // 3+ harfli mazmunli so'zlarni filtrlaymiz
            $meaningfulWords = array_filter($claimWords, fn ($w) => mb_strlen($w) >= 3);

            if (count($meaningfulWords) >= 2) {
                $matchedWords = 0;
                foreach ($meaningfulWords as $w) {
                    if ($this->wordMatchesText($w, $chunkNorm)) {
                        $matchedWords++;
                    }
                }
                // Agar jumlada kamida 20% mazmunli so'z bo'lak matnida bo'lsa — dalillangan deb hisoblaymiz
                return ($matchedWords / count($meaningfulWords)) >= 0.20;
            }
        }

        // Agar jumlani aniq ajratib bo'lmasa, umumiy javobning bo'lak bilan bog'liqligini tekshiramiz
        return $this->hasSignificantWordOverlap($answer, $chunkText);
    }

    /**
     * So'z yoki uning negizi (stem) matn ichida uchrashini tekshiradi.
     * O'zbek, rus va ingliz tillaridagi turlanishlar (affikslar) uchun.
     */
    public function wordMatchesText(string $word, string $targetText): bool
    {
        $word = mb_strtolower(trim($word));
        $len = mb_strlen($word);
        if ($len < 3) {
            return false;
        }

        if (str_contains($targetText, $word)) {
            return true;
        }

        $stemLen = min(4, $len);
        $stem = mb_substr($word, 0, $stemLen);

        return str_contains($targetText, $stem);
    }

    /**
     * Javob matni berilgan bo'laklarning biron birida haqiqatan aks etganligini tekshiradi.
     *
     * @param  array<int, array<string, mixed>>  $chunks
     */
    public function isAnswerGroundedInAnyChunk(string $answer, array $chunks): bool
    {
        foreach ($chunks as $chunk) {
            $text = (string) ($chunk['text'] ?? '');
            if ($this->hasSignificantWordOverlap($answer, $text, 2)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Ikkita matn o'rtasidagi mazmunli so'zlar kesishuvini hisoblaydi.
     */
    protected function hasSignificantWordOverlap(string $textA, string $textB, int $minMatches = 2): bool
    {
        $wordsA = preg_split('/[^\p{L}\p{N}]+/u', mb_strtolower($textA), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $wordsB = mb_strtolower($textB);

        $matched = 0;
        $seen = [];

        foreach ($wordsA as $w) {
            if (mb_strlen($w) < 3 || isset($seen[$w])) {
                continue;
            }
            $seen[$w] = true;
            if ($this->wordMatchesText($w, $wordsB)) {
                $matched++;
                if ($matched >= $minMatches) {
                    return true;
                }
            }
        }

        return $matched >= $minMatches;
    }

    /**
     * AI javobi "ma'lumot topa olmadim" yoki shunga o'xshash rad javobi ekanligini aniqlaydi.
     */
    public function isNoAnswerResponse(string $text): bool
    {
        $lower = mb_strtolower($text);

        return str_contains($lower, "ma'lumot topa olmadim")
            || str_contains($lower, 'malumot topa olmadim')
            || str_contains($lower, 'topa olmadim')
            || str_contains($lower, 'ishonchli javob bera olmayman')
            || str_contains($lower, 'bunga javob bera olmayman')
            || str_contains($lower, 'не удалось найти информацию')
            || str_contains($lower, 'could not find information');
    }
}
