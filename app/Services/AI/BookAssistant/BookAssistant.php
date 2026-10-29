<?php

namespace App\Services\AI\BookAssistant;

use App\Models\AiChatHistory;
use App\Models\Book;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class BookAssistant
{
    public function __construct(
        protected Retriever $retriever,
        protected RelevanceGate $relevanceGate,
        protected PromptBuilder $promptBuilder,
        protected ProviderManager $providers
    ) {
    }

    /**
     * Foydalanuvchining kitob bo'yicha savoliga javob qaytaradi.
     *
     * Tartib:
     * 1. Ruxsat / holat / savol uzunligi / throttle / kunlik limit tekshiruvi
     * 2. FAQAT shu kitobning eng mos 6 bo'lagini qidirish (Retriever)
     * 3. RelevanceGate: mos kelmasa AI chaqirilmasdan rad javobi qaytariladi
     * 4. LLM chaqiruvi (<bolak> teglari va oxirgi 6 suhbat navbati bilan, PII yo'q)
     * 5. [n] manbalarni tekshirish va XSS-safe Markdown hosil qilish
     * 6. ai_chat_histories ga saqlash (provayder xato bersa SAQLANMAYDI)
     *
     * @return array{
     *     status: string,
     *     answer: string,
     *     answer_html: string,
     *     sources: array<int, array<string, mixed>>,
     *     refused: bool,
     *     refusal_reason: string|null,
     *     provider_called: bool,
     *     top_score: float,
     *     history_id: int|null
     * }
     */
    public function ask(?User $user, int $bookId, string $question): array
    {
        // 1. Global AI o'chirgich tekshiruvi
        if (!config('ai.enabled', true)) {
            return $this->errorResult(trans('ai.disabled'));
        }

        // 2. Foydalanuvchi avtorizatsiyasi
        if (!$user) {
            return $this->errorResult(trans('ai.unauthorized'));
        }

        // 3. Kitobga ruxsatni qayta tekshirish (Livewire public property tampering himoyasi)
        $book = Book::availableForUser($user)->find($bookId);
        if (!$book || !$book->is_active) {
            return $this->errorResult(trans('ai.unauthorized'));
        }

        if (!$book->ai_enabled || !in_array($book->ai_index_status, ['ready', 'partial'], true)) {
            return $this->errorResult(trans('ai.book_not_ready'));
        }

        // 4. Savol bo'shligi va uzunligi chegarasi
        $question = trim($question);
        if ($question === '') {
            return $this->errorResult(trans('ai.question_empty'));
        }

        $maxChars = (int) config('ai.max_question_chars', 500);
        if (mb_strlen($question) > $maxChars) {
            return $this->errorResult(trans('ai.question_too_long', ['max' => $maxChars]));
        }

        // 5. Daqiqalik throttle va kunlik limit tekshiruvi
        $throttleKey = 'book_ai_ask:' . $user->id;
        $perMinuteLimit = max(1, (int) config('ai.minute_throttle', 6));
        if (RateLimiter::tooManyAttempts($throttleKey, $perMinuteLimit)) {
            return $this->errorResult(trans('ai.rate_limited'));
        }

        $isAdmin = method_exists($user, 'isAdmin') && $user->isAdmin();
        $dailyLimit = $isAdmin
            ? max(1, (int) config('ai.daily_limit_per_admin', 50))
            : max(1, (int) config('ai.daily_limit_per_user', 10));

        $nowTashkent = Carbon::now('Asia/Tashkent');
        $todayStart = $nowTashkent->copy()->startOfDay()->utc();
        $todayUsedCount = AiChatHistory::where('user_id', $user->id)
            ->where('created_at', '>=', $todayStart)
            ->count();

        $mustConsumePaidQuestion = false;
        if ($todayUsedCount >= $dailyLimit) {
            $shopService = app(\App\Services\Shop\ShopService::class);
            if (!$shopService->hasAvailableAiQuestions($user)) {
                $resetAt = $nowTashkent->copy()->endOfDay();
                $diffHours = (int) $nowTashkent->diffInHours($resetAt);
                $diffMinutes = (int) ($nowTashkent->diffInMinutes($resetAt) % 60);

                if ($diffHours > 0) {
                    $timeLeft = $diffMinutes > 0
                        ? "{$diffHours} soat {$diffMinutes} daqiqa"
                        : "{$diffHours} soat";
                } else {
                    $timeLeft = max(1, $diffMinutes) . " daqiqa";
                }

                return $this->errorResult(trans('ai.daily_limit_reached', [
                    'limit'     => $dailyLimit,
                    'time_left' => $timeLeft,
                ]));
            }
            $mustConsumePaidQuestion = true;
        }

        RateLimiter::hit($throttleKey, 60);

        // 6. Oxirgi 6 ta suhbat navbatini olish (faqat shu foydalanuvchi va shu kitob uchun)
        $historyLimit = max(1, (int) config('ai.history_turns', 6));
        $recentHistory = AiChatHistory::where('user_id', $user->id)
            ->where('book_id', $book->id)
            ->where('refused', false)
            ->orderByDesc('id')
            ->limit($historyLimit)
            ->get(['message', 'response'])
            ->reverse()
            ->values();

        $previousUserQuestion = $recentHistory->isNotEmpty()
            ? (string) $recentHistory->last()->message
            : null;

        // 7. FAQAT shu kitobning eng mos 6 ta bo'lagini qidirish
        $maxChunks = max(1, (int) config('ai.max_context_chunks', 6));
        $retrieval = $this->retriever->retrieve(
            $book->id,
            $question,
            $maxChunks,
            $previousUserQuestion
        );

        // 8. RelevanceGate tekshiruvi — chegaradan past bo'lsa AI chaqirilmasdan rad javobi!
        $gate = $this->relevanceGate->evaluate($question, $retrieval);
        if (!$gate['passed']) {
            $reason = (string) ($gate['reason'] ?? 'off_topic');

            // Do'stona jonli muloqot (salomlashish, hol-ahvol so'rash, minnatdorchilik, xayrlashish)
            if ($reason === 'conversational') {
                $convType = (string) ($gate['conversational_type'] ?? 'greeting');
                $answerText = $this->buildConversationalResponse($book, $convType, $user);

                if ($mustConsumePaidQuestion) {
                    $shopService = app(\App\Services\Shop\ShopService::class);
                    $shopService->consumeAiQuestion($user);
                }

                $history = AiChatHistory::create([
                    'user_id'           => $user->id,
                    'book_id'           => $book->id,
                    'message'           => $question,
                    'response'          => $answerText,
                    'provider'          => 'companion',
                    'model'             => 'conversational_companion',
                    'tokens_used'       => 0,
                    'sources'           => [[
                        'index'      => 0,
                        'chunk_id'   => 0,
                        'page_start' => 1,
                        'page_end'   => null,
                        'chapter_id' => null,
                        'label'      => 'Kitob hamrohi',
                    ]],
                    'refused'           => false,
                    'refusal_reason'    => null,
                    'prompt_tokens'     => 0,
                    'completion_tokens' => 0,
                    'latency_ms'        => 30,
                    'top_score'         => 1.0,
                ]);

                return [
                    'status'          => 'ok',
                    'answer'          => $answerText,
                    'answer_html'     => $this->renderSafeMarkdown($answerText),
                    'sources'         => [[
                        'index'      => 0,
                        'chunk_id'   => 0,
                        'page_start' => 1,
                        'page_end'   => null,
                        'chapter_id' => null,
                        'label'      => 'Kitob hamrohi',
                    ]],
                    'refused'         => false,
                    'refusal_reason'  => null,
                    'provider_called' => false,
                    'top_score'       => 1.0,
                    'history_id'      => $history->id,
                ];
            }

            if ($reason === 'unintelligible') {
                $refusalText = trans('ai.unintelligible');
            } elseif ($reason === 'no_evidence') {
                $refusalText = trans('ai.no_evidence');
            } elseif ($reason === 'unsupported_page') {
                $refusalText = trans('ai.unsupported_page');
            } else {
                $refusalText = trans('ai.off_topic', ['book' => $book->title]);
            }

            $history = AiChatHistory::create([
                'user_id'           => $user->id,
                'book_id'           => $book->id,
                'message'           => $question,
                'response'          => $refusalText,
                'provider'          => 'gate',
                'model'             => 'relevance_gate',
                'tokens_used'       => 0,
                'sources'           => [],
                'refused'           => true,
                'refusal_reason'    => $reason,
                'prompt_tokens'     => 0,
                'completion_tokens' => 0,
                'latency_ms'        => 0,
                'top_score'         => $gate['top_score'],
            ]);

            return [
                'status'          => 'refused',
                'answer'          => $refusalText,
                'answer_html'     => $this->renderSafeMarkdown($refusalText),
                'sources'         => [],
                'refused'         => true,
                'refusal_reason'  => $reason,
                'provider_called' => false,
                'top_score'       => $gate['top_score'],
                'history_id'      => $history->id,
            ];
        }

        // 9. Promt tuzish va LLM provayderga murojaat qilish
        $messages = $this->promptBuilder->buildMessages(
            $book,
            $retrieval['chunks'],
            $question,
            $recentHistory->toArray()
        );

        $startedAt = microtime(true);
        try {
            $llm = $this->providers->llm();
            $llmResponse = $llm->chat($messages);
        } catch (\Throwable $e) {
            Log::error('BookAssistant LLM error', [
                'book_id' => $book->id,
                'error'   => $e->getMessage(),
            ]);

            // MUHIM: Provayder xato bersa — xato holati qaytariladi, "javob" sifatida saqlanmaydi!
            return $this->errorResult(trans('ai.provider_error'), $gate['top_score']);
        }

        $latencyMs = (int) round((microtime(true) - $startedAt) * 1000);
        $rawContent = (string) ($llmResponse['content'] ?? '');

        // 10. Manba va faktik dalillarni qat'iy tekshirish (GroundingVerifier)
        $verified = $this->promptBuilder->verifyAndExtractSources(
            $rawContent,
            $retrieval['chunks'],
            $question,
            $retrieval['target_page'] ?? null,
            $book
        );

        $cleanedAnswer = $verified['cleaned_text'];
        $isRefused = !$verified['verified'] || $verified['is_no_answer'];
        $refusalReason = $isRefused ? ($verified['refusal_reason'] ?: 'no_evidence') : null;

        $safeHtml = $this->renderSafeMarkdown($cleanedAnswer);

        if ($mustConsumePaidQuestion) {
            $shopService = app(\App\Services\Shop\ShopService::class);
            $shopService->consumeAiQuestion($user);
        }

        $history = AiChatHistory::create([
            'user_id'           => $user->id,
            'book_id'           => $book->id,
            'message'           => $question,
            'response'          => $cleanedAnswer,
            'provider'          => (string) ($llmResponse['provider'] ?? config('ai.provider', 'groq')),
            'model'             => (string) ($llmResponse['model'] ?? ''),
            'tokens_used'       => (int) ($llmResponse['total_tokens'] ?? 0),
            'sources'           => $verified['sources'],
            'refused'           => $isRefused,
            'refusal_reason'    => $refusalReason,
            'prompt_tokens'     => (int) ($llmResponse['prompt_tokens'] ?? 0),
            'completion_tokens' => (int) ($llmResponse['completion_tokens'] ?? 0),
            'latency_ms'        => $latencyMs,
            'top_score'         => $gate['top_score'],
        ]);

        return [
            'status'          => $isRefused ? 'refused' : 'ok',
            'answer'          => $cleanedAnswer,
            'answer_html'     => $safeHtml,
            'sources'         => $verified['sources'],
            'refused'         => $isRefused,
            'refusal_reason'  => $refusalReason,
            'provider_called' => true,
            'top_score'       => $gate['top_score'],
            'history_id'      => $history->id,
        ];
    }

    /**
     * Javob matnini XSS-xavfsiz Markdown formatiga o'giradi.
     * <script> va xavfli havolalar to'liq olib tashlanadi.
     */
    public function renderSafeMarkdown(string $text): string
    {
        // 1. Oddiy foydalanuvchilar uchun matn ichidagi texnik citation qavslarini ([0], [1], [0, 1] va h.k.)
        // tozalab, matnni ravon, qisqa va chiroyli ko'rinishga keltiramiz. Manbalar pastdagi "Manbalar" bo'limida ko'rsatiladi.
        $cleanCitations = preg_replace('/(?:\s*(?:,|va|and)\s*)?\[\s*\d+(?:\s*,\s*\d+)*\s*\]/u', '', $text) ?? $text;
        // Ortiqcha qolib ketgan vergullarni tinish belgilaridan oldin tozalash (masalan: ",." yoki ", .")
        $cleanCitations = preg_replace('/,\s*([.!?])/u', '$1', $cleanCitations) ?? $cleanCitations;
        // Tinish belgilari oldidagi bo'sh joylarni tozalash (masalan: "Mirzaahmedov ." -> "Mirzaahmedov.")
        $cleanCitations = preg_replace('/\s+([.,;:!?])/u', '$1', $cleanCitations) ?? $cleanCitations;
        $cleanCitations = preg_replace('/,{2,}/u', ',', $cleanCitations) ?? $cleanCitations;

        // 2. <script>...</script> bloklarini ichidagi matni bilan birga o'chirish
        $sanitized = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $cleanCitations) ?? $cleanCitations;

        return (string) Str::markdown($sanitized, [
            'html_input'         => 'strip',
            'allow_unsafe_links' => false,
        ]);
    }

    protected function errorResult(string $message, float $topScore = 0.0): array
    {
        return [
            'status'          => 'error',
            'answer'          => $message,
            'answer_html'     => '<p>' . e($message) . '</p>',
            'sources'         => [],
            'refused'         => false,
            'refusal_reason'  => null,
            'provider_called' => false,
            'top_score'       => $topScore,
            'history_id'      => null,
        ];
    }

    /**
     * Do'stona jonli muloqot (salomlashish, hol-ahvol, minnatdorchilik, xayrlashish) uchun
     * kitob bilan bog'langan xushmuomala javob qaytaradi.
     */
    public function buildConversationalResponse(Book $book, string $convType, ?User $user = null): string
    {
        $bookTitle = trim((string) $book->title);
        $author = trim((string) ($book->author ?? ''));
        $authorSuffix = $author !== '' ? " (muallif: {$author})" : '';

        return match ($convType) {
            'how_are_you' => "Assalomu alaykum! Rahmat, hammasi a'lo darajada! Men «{$bookTitle}»{$authorSuffix} kitobi bo'yicha sizning o'qish hamrohingizman. Mutolaa qanday ketyapti? Kitobdagi qaysi mavzu, bob yoki savol bo'yicha yordam bera olaman?",

            'greeting' => "Assalomu alaykum! Xush kelibsiz. Men «{$bookTitle}»{$authorSuffix} kitobi bo'yicha sizning intellektual yordamchingizman. Ushbu kitob mazmuni, boblari, asosiy g'oyalari yoki amaliy xulosalari bo'yicha qanday savollaringiz bor?",

            'thanks' => "Arzimaydi, sizga foydali bo'lganidan juda xursandman! «{$bookTitle}» kitobini mutolaa qilishda davom eting. Yana savollaringiz bo'lsa, bemalol murojaat qiling!",

            'farewell' => "Xayr, salomat bo'ling! «{$bookTitle}» mutolaasi unumli va maroqli bo'lsin. Yangi savollar paydo bo'lganda istalgan vaqtda qaytishingiz mumkin.",

            'identity' => "Men «{$bookTitle}»{$authorSuffix} kitobining rasmiy repetitor va AI yordamchisiman. Vazifam — kitobni chuqurroq tushunishingizga ko'maklashish: har qanday bob, tushuncha, misol yoki savollarga kitob matni asosida aniq va tezkor javob beraman. Qaysi qismidan boshlaymiz?",

            default => "Assalomu alaykum! Men «{$bookTitle}» kitobi bo'yicha sizning yordamchingizman. Kitob bo'yicha qanday savolingiz bor?",
        };
    }
}
