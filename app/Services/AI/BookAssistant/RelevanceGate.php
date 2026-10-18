<?php

namespace App\Services\AI\BookAssistant;

class RelevanceGate
{
    /**
     * Foydalanuvchi savolida prompt-injection ("oldingi ko'rsatmalarni unut" va h.k.)
     * bor-yo'qligini va topilgan bo'laklarning o'xshashlik bahosi chegaradan o'tganini tekshiradi.
     *
     * @param  array{chunks: array, top_score: float, mode: string, is_overview_query?: bool}  $retrievalResult
     * @return array{
     *     passed: bool,
     *     reason: string|null,
     *     top_score: float,
     *     threshold: float
     * }
     */
    public function evaluate(string $question, array $retrievalResult): array
    {
        $topScore = (float) ($retrievalResult['top_score'] ?? 0.0);
        $mode = (string) ($retrievalResult['mode'] ?? 'fulltext');
        $chunks = $retrievalResult['chunks'] ?? [];
        $hasTopicalMatch = (bool) ($retrievalResult['has_topical_match'] ?? true);
        $targetPage = $retrievalResult['target_page'] ?? null;
        $targetChapter = $retrievalResult['target_chapter'] ?? null;
        $hasTarget = $targetPage !== null || $targetChapter !== null;

        $threshold = $mode === 'hybrid'
            ? (float) config('ai.min_similarity', 0.22)
            : (float) config('ai.min_fulltext_score', 0.12);

        // 1. Mutlaqo tushunarsiz / ma'nosiz klaviatura harflari (gibberish/abrakadabra)
        if ($this->isUnintelligibleQuery($question)) {
            return [
                'passed'    => false,
                'reason'    => 'unintelligible',
                'top_score' => 0.0,
                'threshold' => $threshold,
            ];
        }

        // 2. Prompt injection / tizim ko'rsatmasini buzishga urinishlarni darhol rad etish
        if ($this->isPromptInjectionAttempt($question)) {
            return [
                'passed'    => false,
                'reason'    => 'prompt_injection',
                'top_score' => 0.0,
                'threshold' => $threshold,
            ];
        }

        // 2. Agar bo'laklar topilmagan bo'lsa -> dalil yo'q
        if (empty($chunks)) {
            return [
                'passed'    => false,
                'reason'    => 'no_evidence',
                'top_score' => 0.0,
                'threshold' => $threshold,
            ];
        }

        // 3. Agar savol aniq sahifa yoki bobni so'ragan bo'lsa, lekin u mavzu mos kelmasa (masalan: "50-betda Bitcoin narxi")
        if ($hasTarget && !$hasTopicalMatch) {
            return [
                'passed'    => false,
                'reason'    => 'no_evidence',
                'top_score' => $topScore,
                'threshold' => $threshold,
            ];
        }

        // 4. Baho chegaradan past bo'lsa
        if ($topScore < $threshold) {
            return [
                'passed'    => false,
                'reason'    => $hasTarget ? 'no_evidence' : 'off_topic',
                'top_score' => $topScore,
                'threshold' => $threshold,
            ];
        }

        return [
            'passed'    => true,
            'reason'    => null,
            'top_score' => $topScore,
            'threshold' => $threshold,
        ];
    }

    /**
     * Foydalanuvchi savolida "oldingi ko'rsatmalarni unut", "tizim promtini ko'rsat" kabi
     * buyruqlar bor-yo'qligini aniqlaydi.
     */
    public function isPromptInjectionAttempt(string $question): bool
    {
        $lower = mb_strtolower($question);
        $lower = str_replace(
            ['o‘', 'oʼ', 'o`', 'o’', 'g‘', 'gʼ', 'g`', 'g’', 'ʻ', 'ʼ', '’', '`', '‘'],
            ["o'", "o'", "o'", "o'", "g'", "g'", "g'", "g'", "'", "'", "'", "'", "'"],
            $lower
        );

        $patterns = [
            "oldingi ko'rsatmalarni unut",
            'oldingi korsatmalarni unut',
            'barcha qoidalarni unut',
            "ko'rsatmalarni bekor qil",
            "korsatmalarni bekor qil",
            'tizim ko\'rsatmasini',
            'tizim korsatmasini',
            'tizim promtini',
            'system prompt',
            'ignore previous instructions',
            'ignore all instructions',
            'disregard previous',
            'disregard all',
            'forget previous instructions',
            'forget all instructions',
            'reveal system instructions',
            'забудь предыдущие инструкции',
            'игнорируй предыдущие',
            'покажи системный промпт',
            'act as an unfiltered',
            'jailbreak',
            'dan boshqa qoidalarga amal qilma',
        ];

        foreach ($patterns as $pattern) {
            if (str_contains($lower, $pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Savol mutlaqo ma'nosiz harflar to'plami (abrakadabra, klaviatura bo'ylab bosish, unlisiz harflar)
     * ekanligini tekshiradi (masalan: "asdfghjkl", "qwertyui", "zxcvbnm", "aaaaaa").
     */
    public function isUnintelligibleQuery(string $question): bool
    {
        $clean = trim(preg_replace('/[^\p{L}\p{N}]+/u', ' ', mb_strtolower($question)) ?? '');
        if ($clean === '') {
            return false;
        }

        // 1. Bir xil harf ketma-ket 4+ marta takrorlangan bo'lsa (masalan: "aaaaaa", "qqqqqqq")
        if (preg_match('/(\p{L})\1{3,}/u', $clean)) {
            return true;
        }

        // 2. Klaviatura bo'ylab qator terish (keyboard walks)
        $keyboardWalks = ['asdfgh', 'qwerty', 'zxcvbn', 'йцукен', 'фывапр', 'ячсмит', '123456'];
        foreach ($keyboardWalks as $kw) {
            if (str_contains($clean, $kw)) {
                return true;
            }
        }

        // 3. So'zlarning unli harflari tekshiruvi:
        // Agar so'z kamida 5 harfdan iborat bo'lsa va unda hech qanday unli harf bo'lmasa (lotin: a,e,i,o,u,y yoki kirill: а,е,ё,и,о,у,ы,э,ю,я):
        $words = preg_split('/\s+/u', $clean, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $longWordsCount = 0;
        $vowellessLongWords = 0;

        foreach ($words as $w) {
            $len = mb_strlen($w);
            if ($len >= 5 && !is_numeric($w)) {
                $longWordsCount++;
                if (!preg_match('/[aeiouyаеёиоуыэюяo\'o‘o’g\'g‘g’]/ui', $w)) {
                    $vowellessLongWords++;
                }
            }
        }

        if ($longWordsCount > 0 && $vowellessLongWords === $longWordsCount) {
            return true;
        }

        return false;
    }
}
