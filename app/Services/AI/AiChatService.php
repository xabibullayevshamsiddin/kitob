<?php

namespace App\Services\AI;

use App\Models\AiChatHistory;
use App\Models\Book;
use App\Models\User;
use App\Services\AI\Contracts\AiProviderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiChatService implements AiProviderInterface
{
    protected string $provider;
    protected ?string $apiKey;
    protected int $maxTokens;

    public function __construct()
    {
        $this->provider = config('app.ai_provider', env('AI_PROVIDER', 'openai'));
        $this->maxTokens = (int) env('AI_MAX_TOKENS', 500);

        $this->apiKey = match ($this->provider) {
            'anthropic' => env('ANTHROPIC_API_KEY'),
            'gemini' => env('GEMINI_API_KEY'),
            default => env('OPENAI_API_KEY'),
        };
    }

    /**
     * Foydalanuvchidan savol qabul qilish va javob qaytarish.
     */
    public function ask(string $prompt, array $context = []): array
    {
        // 1. Agar haqiqiy API kaliti mavjud bo'lsa, tashqi API ga so'rov yuborish
        if ($this->apiKey && $this->apiKey !== 'your-openai-key-here' && !str_starts_with($this->apiKey, 'your-')) {
            try {
                return $this->callExternalApi($prompt, $context);
            } catch (\Throwable $e) {
                Log::error('AI API error: ' . $e->getMessage());
            }
        }

        // 2. Mock / Smart fallback javob (offline yoki sinov rejimi uchun)
        return $this->generateFallbackResponse($prompt, $context);
    }

    /**
     * Suhbat tarixini bazaga yozish
     */
    public function saveHistory(User $user, ?Book $book, string $message, string $response, int $tokens = 0): AiChatHistory
    {
        return AiChatHistory::create([
            'user_id' => $user->id,
            'book_id' => $book?->id,
            'message' => $message,
            'response' => $response,
            'provider' => $this->provider,
            'model' => 'gpt-3.5-turbo / claude-3 / fallback',
            'tokens_used' => $tokens,
        ]);
    }

    protected function callExternalApi(string $prompt, array $context): array
    {
        if ($this->provider === 'openai') {
            $systemPrompt = "Siz Kitobxon platformasining aqlli maslahatchisisiz. Kitob: " . ($context['book_title'] ?? 'Platforma kitoblari');

            $res = Http::withToken($this->apiKey)
                ->timeout(15)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => 'gpt-3.5-turbo',
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'max_tokens' => $this->maxTokens,
                ]);

            if ($res->successful()) {
                $data = $res->json();
                return [
                    'response' => $data['choices'][0]['message']['content'] ?? '',
                    'tokens_used' => $data['usage']['total_tokens'] ?? 50,
                ];
            }
        }

        return $this->generateFallbackResponse($prompt, $context);
    }

    protected function generateFallbackResponse(string $prompt, array $context): array
    {
        $bookTitle = $context['book_title'] ?? 'Atom Odatlar';
        $lower = mb_strtolower($prompt);

        if (str_contains($lower, 'qonun') || str_contains($lower, 'qoida')) {
            $text = "«{$bookTitle}» kitobida James Clear muvaffaqiyatli odat shakllantirishning 4 ta fundamental qonunini beradi:\n" .
                "1. Uni ko'zga yaqqol tashlanadigan qiling (Make it obvious)\n" .
                "2. Uni jozibador qiling (Make it attractive)\n" .
                "3. Uni oson qiling (Make it easy)\n" .
                "4. Undan mamnuniyat hosil qiling (Make it satisfying)";
        } elseif (str_contains($lower, 'maslahat') || str_contains($lower, 'qanday')) {
            $text = "Har kuni atigi 1% yaxshilanishga e'tibor qarating. Katta maqsadlarni emas, kichik kundalik maromni shakllantiring. O'qishni har kuni bir xil vaqtda (masalan, kechki 21:00 da) atigi 10 daqiqadan boshlang.";
        } else {
            $text = "«{$bookTitle}» kitobi bo'yicha ajoyib savol! Asosiy g'oya shuki: biz o'z maqsadlarimiz darajasiga ko'tarilmaymiz, balki o'z tizimlarimiz darajasiga qulaymiz. Kichik qadamlar katta marralarni zabt etadi.";
        }

        return [
            'response' => $text,
            'tokens_used' => mb_strlen($text) / 4,
        ];
    }
}
