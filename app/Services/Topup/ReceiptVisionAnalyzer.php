<?php

namespace App\Services\Topup;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * To'lov chekini (skrinshot) Gemini vision modeli orqali o'qiydi:
 * summa, sana, qabul qiluvchi karta, tranzaksiya raqami va tahrirlash/AI belgilari.
 *
 * Natija faqat admin uchun MASLAHAT — yakuniy qaror har doim admin tomonidan,
 * bank ilovasida pul haqiqatan tushganini ko'rib qabul qilinadi.
 */
class ReceiptVisionAnalyzer
{
    public function isAvailable(): bool
    {
        $key = (string) config('ai.providers.gemini.api_key', '');

        return $key !== '' && !str_starts_with($key, 'your-');
    }

    /**
     * @return array|null Normallashtirilgan natija yoki xatolikda null
     */
    public function analyze(string $absolutePath, string $mime, array $context): ?array
    {
        if (!$this->isAvailable() || !is_file($absolutePath)) {
            return null;
        }

        $cfg = config('ai.providers.gemini', []);
        $baseUrl = rtrim((string) ($cfg['base_url'] ?? 'https://generativelanguage.googleapis.com/v1beta'), '/');
        $model = (string) config('topup.ai_model', $cfg['chat_model'] ?? 'gemini-2.5-flash');

        $payload = [
            'systemInstruction' => ['parts' => [['text' => $this->systemPrompt()]]],
            'contents' => [[
                'role'  => 'user',
                'parts' => [
                    ['text' => $this->userPrompt($context)],
                    ['inline_data' => ['mime_type' => $mime, 'data' => base64_encode((string) file_get_contents($absolutePath))]],
                ],
            ]],
            'generationConfig' => [
                'temperature'      => 0,
                'maxOutputTokens'  => 800,
                'responseMimeType' => 'application/json',
            ],
        ];

        try {
            $response = Http::withHeaders(['x-goog-api-key' => (string) $cfg['api_key']])
                ->timeout((int) config('topup.ai_timeout', 25))
                ->acceptJson()
                ->post($baseUrl . '/models/' . urlencode($model) . ':generateContent', $payload);
        } catch (\Throwable $e) {
            Log::warning('Chek AI tahlili: tarmoq xatosi — ' . $e->getMessage());
            return null;
        }

        if (!$response->successful()) {
            Log::warning('Chek AI tahlili: HTTP ' . $response->status() . ' — ' . $response->json('error.message'));
            return null;
        }

        $text = '';
        foreach ((array) $response->json('candidates.0.content.parts', []) as $part) {
            $text .= (string) ($part['text'] ?? '');
        }

        // Ba'zan model JSON'ni ```json ... ``` ichida qaytaradi
        $text = trim(preg_replace('/^```(?:json)?|```$/m', '', trim($text)) ?? $text);
        $data = json_decode($text, true);

        if (!is_array($data)) {
            Log::warning('Chek AI tahlili: JSON emas javob qaytdi.');
            return null;
        }

        return $this->normalize($data, $model);
    }

    protected function systemPrompt(): string
    {
        return <<<'PROMPT'
You are a strict payment-receipt forensic checker for an Uzbek website. Users send screenshots of
card-to-card transfers (Click, Payme, Uzum, Paynet, bank apps; Uzcard/Humo/Visa). Extract the facts
exactly as shown and judge whether the image looks genuine. Never guess values that are not visible —
use null instead. Reply ONLY with JSON matching this schema:
{
  "is_payment_receipt": bool,
  "transfer_successful": bool|null,
  "amount": number|null,              // in UZS (so'm), digits only, e.g. 15037
  "currency": string|null,
  "datetime": string|null,            // ISO 8601 if visible, e.g. 2026-10-31T14:05:00
  "recipient_card_last4": string|null,
  "recipient_name": string|null,
  "sender_card_last4": string|null,
  "transaction_id": string|null,      // cheque/receipt/transaction number exactly as printed
  "app_name": string|null,
  "edit_suspected": bool,             // misaligned digits, mixed fonts, blur patches, cloned areas
  "ai_generated_suspected": bool,     // synthetic look, garbled text, impossible UI
  "suspicion_reasons": [string],      // short reasons in Uzbek
  "confidence": number                // 0..1 confidence in the extraction
}
PROMPT;
    }

    protected function userPrompt(array $context): string
    {
        return "Kutilgan to'lov: " . ($context['expected_amount'] ?? '?') . " so'm, "
            . "qabul qiluvchi karta oxiri: " . ($context['card_last4'] ?? '?') . ", "
            . "so'rov yaratilgan vaqt: " . ($context['created_at'] ?? '?') . " (Asia/Tashkent). "
            . "Chekdagi ma'lumotlarni kutilganlarga moslashtirmang — faqat ko'ringanini yozing.";
    }

    protected function normalize(array $data, string $model): array
    {
        $digits = static fn ($value): ?string => ($value === null || $value === '')
            ? null
            : (preg_replace('/\D+/', '', (string) $value) ?: null);

        $amount = $data['amount'] ?? null;
        if (is_string($amount)) {
            $amount = preg_replace('/[^\d.,]/', '', $amount);
            $amount = str_replace([' ', ','], ['', '.'], (string) $amount);
        }

        $last4 = static function ($value) use ($digits): ?string {
            $d = $digits($value);

            return $d !== null ? substr($d, -4) : null;
        };

        return [
            'is_payment_receipt'     => (bool) ($data['is_payment_receipt'] ?? false),
            'transfer_successful'    => isset($data['transfer_successful']) ? (bool) $data['transfer_successful'] : null,
            'amount'                 => is_numeric($amount) ? (int) round((float) $amount) : null,
            'currency'               => isset($data['currency']) ? mb_substr((string) $data['currency'], 0, 10) : null,
            'datetime'               => isset($data['datetime']) ? mb_substr((string) $data['datetime'], 0, 40) : null,
            'recipient_card_last4'   => $last4($data['recipient_card_last4'] ?? null),
            'recipient_name'         => isset($data['recipient_name']) ? mb_substr((string) $data['recipient_name'], 0, 100) : null,
            'sender_card_last4'      => $last4($data['sender_card_last4'] ?? null),
            'transaction_id'         => isset($data['transaction_id']) && trim((string) $data['transaction_id']) !== ''
                ? mb_substr(trim((string) $data['transaction_id']), 0, 100)
                : null,
            'app_name'               => isset($data['app_name']) ? mb_substr((string) $data['app_name'], 0, 60) : null,
            'edit_suspected'         => (bool) ($data['edit_suspected'] ?? false),
            'ai_generated_suspected' => (bool) ($data['ai_generated_suspected'] ?? false),
            'suspicion_reasons'      => array_slice(array_map(fn ($r) => mb_substr((string) $r, 0, 200), (array) ($data['suspicion_reasons'] ?? [])), 0, 6),
            'confidence'             => max(0, min(1, (float) ($data['confidence'] ?? 0))),
            'model'                  => $model,
        ];
    }
}
