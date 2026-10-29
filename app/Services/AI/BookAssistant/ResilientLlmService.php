<?php

namespace App\Services\AI\BookAssistant;

use App\Services\AI\BookAssistant\Contracts\LlmProviderInterface;
use App\Services\AI\BookAssistant\Exceptions\AiProviderException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ResilientLlmService implements LlmProviderInterface
{
    public const CIRCUIT_BREAKER_PREFIX = 'ai_circuit_breaker:';
    public const DEFAULT_COOLDOWN_SECONDS = 60;
    public const MAX_COOLDOWN_SECONDS = 300;
    public const MIN_COOLDOWN_SECONDS = 5;

    protected LlmProviderInterface $primary;
    protected LlmProviderInterface $fallback;
    protected string $primaryName;
    protected string $fallbackName;

    public function __construct(LlmProviderInterface $primary, LlmProviderInterface $fallback)
    {
        $this->primary = $primary;
        $this->fallback = $fallback;
        $this->primaryName = strtolower(trim($primary->getProviderName()));
        $this->fallbackName = strtolower(trim($fallback->getProviderName()));
    }

    public function getProviderName(): string
    {
        return $this->primaryName;
    }

    public function getModelName(): string
    {
        return $this->primary->getModelName();
    }

    public function getPrimary(): LlmProviderInterface
    {
        return $this->primary;
    }

    public function getFallback(): LlmProviderInterface
    {
        return $this->fallback;
    }

    /**
     * Chat so'rovini avval asosiy (Groq), xatolik yoki cheklov bo'lsa zaxira (Gemini) provayderga yo'naltiradi.
     */
    public function chat(array $messages, array $options = []): array
    {
        $cacheKey = self::CIRCUIT_BREAKER_PREFIX . $this->primaryName;

        // 1. Agar asosiy provayder vaqtincha cheklangan (cooldown) bo'lsa, to'g'ridan-to'g'ri zaxiraga o'tish
        if (Cache::has($cacheKey)) {
            Log::info("AI: Asosiy provayder ({$this->primaryName}) vaqtincha sovutish (cooldown) rejimida. So'rov to'g'ridan-to'g'ri zaxiraga ({$this->fallbackName}) yo'naltirilmoqda.");
            return $this->invokeFallback(
                $messages,
                $options,
                "Asosiy provayder ({$this->primaryName}) vaqtincha faol emas (cooldown rejimida)."
            );
        }

        // 2. Asosiy provayderga (Groq) murojaat qilish
        try {
            return $this->primary->chat($messages, $options);
        } catch (\Throwable $primaryError) {
            // 3. Asosiy provayderda limit, timeout, 5xx yoki noto'g'ri kalit/model yuz bersa
            $cooldownSeconds = $this->determineCooldown($primaryError);

            if ($cooldownSeconds > 0) {
                Cache::put($cacheKey, true, now()->addSeconds($cooldownSeconds));
                Cache::put($cacheKey . ':until', now()->addSeconds($cooldownSeconds)->toIso8601String(), now()->addSeconds($cooldownSeconds));
            }

            Log::warning("AI: Asosiy provayder ({$this->primaryName}) javob bera olmadi. Zaxira provayderga ({$this->fallbackName}) o'tilmoqda. Xatolik: " . $primaryError->getMessage(), [
                'primary'          => $this->primaryName,
                'fallback'         => $this->fallbackName,
                'cooldown_seconds' => $cooldownSeconds,
                'exception'        => get_class($primaryError),
            ]);

            // 4. Aynan shu savol va kontekstni zaxiraga (Gemini) uzatish
            return $this->invokeFallback($messages, $options, $primaryError->getMessage());
        }
    }

    /**
     * Zaxira provayderga murojaat qilish. Ikkalasi ham ishlamasa xavfsiz xabar qaytarish.
     */
    protected function invokeFallback(array $messages, array $options, string $primaryReason): array
    {
        try {
            $fallbackResponse = $this->fallback->chat($messages, $options);
            $fallbackResponse['fallback_used'] = true;
            $fallbackResponse['primary_fallback_reason'] = $primaryReason;

            return $fallbackResponse;
        } catch (\Throwable $fallbackError) {
            // Ikkala xizmat ham ishlamadi — maxfiy xatolarni faqat server logiga yozamiz
            Log::error("AI: Har ikkala provayder ham javob bera olmadi! Asosiy ({$this->primaryName}): {$primaryReason} | Zaxira ({$this->fallbackName}): " . $fallbackError->getMessage(), [
                'primary_provider'  => $this->primaryName,
                'primary_reason'    => $primaryReason,
                'fallback_provider' => $this->fallbackName,
                'fallback_error'    => $fallbackError->getMessage(),
                'fallback_class'    => get_class($fallbackError),
            ]);

            // Foydalanuvchiga faqat tushunarli xabar chiqariladi
            $friendlyMessage = trans('ai.provider_error') ?: 'AI xizmati hozir vaqtincha band. Birozdan keyin qayta urinib ko‘ring.';

            throw new AiProviderException($friendlyMessage, 503, $fallbackError);
        }
    }

    /**
     * Xatolik turiga qarab sovutish (cooldown) vaqtini soniyalarda aniqlash.
     */
    public function determineCooldown(\Throwable $e): int
    {
        // 1. Agar Retry-After mavjud bo'lsa
        if ($e instanceof AiProviderException && $e->getRetryAfter()) {
            return max(self::MIN_COOLDOWN_SECONDS, min(self::MAX_COOLDOWN_SECONDS, (int) $e->getRetryAfter()));
        }

        $msg = strtolower($e->getMessage());
        $status = ($e instanceof AiProviderException) ? $e->getStatusCode() : 0;

        // 2. Retry-After matn ichidan (masalan: "try again in 12.5s" yoki "retry after 30 seconds")
        if (preg_match('/(?:try again in|retry after)\s+(\d+(?:\.\d+)?)\s*s/i', $msg, $m)) {
            $parsedSec = (int) ceil((float) $m[1]);
            return max(self::MIN_COOLDOWN_SECONDS, min(self::MAX_COOLDOWN_SECONDS, $parsedSec));
        }

        // 3. 429 Too Many Requests / Rate limit / Quota
        if ($status === 429 || str_contains($msg, '429') || str_contains($msg, 'rate limit') || str_contains($msg, 'quota') || str_contains($msg, 'too many requests')) {
            return self::DEFAULT_COOLDOWN_SECONDS; // 60s
        }

        // 4. Timeout / Tarmoq uzilishi
        if ($status === 408 || str_contains($msg, 'timeout') || str_contains($msg, 'timed out') || str_contains($msg, 'curl error 28') || str_contains($msg, 'connection')) {
            return 30; // 30s
        }

        // 5. Server xatolari (500, 502, 503, 504)
        if (in_array($status, [500, 502, 503, 504], true) || str_contains($msg, '500') || str_contains($msg, '502') || str_contains($msg, '503') || str_contains($msg, '504')) {
            return 30; // 30s
        }

        // 6. Kalit yoki model noto'g'ri sozlangan holatda (401, 404, not configured)
        if ($status === 401 || $status === 404 || str_contains($msg, 'api kaliti sozlanmagan') || (str_contains($msg, 'model') && str_contains($msg, 'not found'))) {
            return 60; // 60s
        }

        return 15;
    }

    /**
     * Keshdagi sovutish (cooldown) cheklovini tozalash (test yoki admin amallari uchun).
     */
    public function clearCooldown(): void
    {
        Cache::forget(self::CIRCUIT_BREAKER_PREFIX . $this->primaryName);
        Cache::forget(self::CIRCUIT_BREAKER_PREFIX . $this->primaryName . ':until');
    }
}
