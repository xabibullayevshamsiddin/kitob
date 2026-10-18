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

        // 1. Prompt injection / tizim ko'rsatmasini buzishga urinishlarni darhol rad etish
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
}
