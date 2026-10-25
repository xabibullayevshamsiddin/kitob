<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Services\AI\BookAssistant\ProviderManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BookSummaryController extends Controller
{
    /**
     * Kitobning 1 daqiqalik AI konspektini qaytaradi (yoki avtomatik yaratadi).
     */
    public function show(Request $request, Book $book): JsonResponse
    {
        // 1. Agar bazada allaqachon konspekt mavjud bo'lsa
        if (!empty($book->ai_summary) && mb_strlen(trim($book->ai_summary)) > 40) {
            return response()->json([
                'ok'      => true,
                'cached'  => true,
                'summary' => $book->ai_summary,
            ]);
        }

        // 2. Yangi konspekt yaratish
        try {
            $summary = $this->generateSummary($book);

            if (!empty($summary)) {
                $book->update(['ai_summary' => $summary]);

                return response()->json([
                    'ok'      => true,
                    'cached'  => false,
                    'summary' => $summary,
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning("AI konspekt yaratishda xatolik (Book #{$book->id}): " . $e->getMessage());
        }

        // 3. Fallback: Agar AI javob bermasa, kitob tavsifidan chiroyli xulosa hosil qilish
        $fallback = $this->generateFallbackSummary($book);

        return response()->json([
            'ok'       => true,
            'cached'   => false,
            'fallback' => true,
            'summary'  => $fallback,
        ]);
    }

    /**
     * LLM (Groq / Gemini) orqali professional 1 daqiqalik konspekt tayyorlash.
     */
    protected function generateSummary(Book $book): string
    {
        $providerManager = new ProviderManager();
        $driver = config('ai.provider', 'groq');
        $llm = $providerManager->llm($driver);

        $chaptersInfo = $book->chapters()->limit(5)->pluck('title')->implode(', ');
        $desc = strip_tags($book->description ?? '');

        $prompt = <<<PROMPT
Kitob haqidagi ma'lumotlar:
- Nomi: "{$book->title}"
- Muallif: {$book->author}
- Janr: {$book->genre}
- Boblar: {$chaptersInfo}
- Tavsif: {$desc}

Vazifa:
Ushbu kitob uchun o'zbek tilida 1 daqiqada o'qiladigan, aniq va ixcham «1 daqiqalik AI konspekt» tayyorlang.

Tuzilishi:
📌 **Asosiy g'oya:** (Kitob nima haqida va asosiy maqsadi — 1-2 jumlada)
💡 **3 ta muhim xulosa:**
1. (Birinchi muhim saboq)
2. (Ikkinchi amaliy xulosa)
3. (Uchinchi asosiy fikr)
🎯 **Kimlar uchun tavsiya etiladi:** (1 jumlada)

Qoidalar:
- Faqat yuqoridagi 3 ta bo'limni yozing, ortiqcha salomlashish yoki kirish so'zlarisiz.
- O'zbek tilining lotin alifbosida, adabiy va ta'sirli tilda yozing.
PROMPT;

        $messages = [
            [
                'role'    => 'system',
                'content' => "Siz tajribali kitob tahlilchisi va adabiyotshunos mutaxassissiz. Kitobxonlar uchun eng sara, ixcham va tushunarli 1 daqiqalik kitob konspektlarini tuzasiz.",
            ],
            [
                'role'    => 'user',
                'content' => $prompt,
            ],
        ];

        $response = $llm->chat($messages, [
            'max_tokens'  => 600,
            'temperature' => 0.4,
        ]);

        return trim($response['content'] ?? '');
    }

    /**
     * Zaxira (fallback) konspekt.
     */
    protected function generateFallbackSummary(Book $book): string
    {
        $desc = strip_tags($book->description ?? 'Kitob tavsifi kiritilmagan.');
        $sentences = preg_split('/(?<=[.?!])\s+/', $desc, 3, PREG_SPLIT_NO_EMPTY);
        $lead = $sentences[0] ?? $desc;

        return "📌 **Asosiy g'oya:**\n{$lead}\n\n"
             . "💡 **Muhim jihatlari:**\n"
             . "• Muallif: {$book->author} tomonidan qalamga olingan sara asar;\n"
             . "• Janr: {$book->genre} yo'nalishidagi qiziqarli mutolaa;\n"
             . "• Kitobxon tafakkurini boyitishga xizmat qiladi.\n\n"
             . "🎯 **Tavsiya etiladi:**\n"
             . "Ushbu janr va muallif ijodiga qiziquvchi barcha kitobsevarlar uchun.";
    }
}
