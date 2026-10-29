<?php

namespace App\Services\AI\BookAssistant;

use App\Services\AI\BookAssistant\Contracts\EmbeddingProviderInterface;
use App\Services\AI\BookAssistant\Contracts\LlmProviderInterface;
use App\Services\AI\BookAssistant\Exceptions\AiProviderException;
use App\Services\AI\BookAssistant\Providers\AnthropicLlmProvider;
use App\Services\AI\BookAssistant\Providers\GeminiEmbeddingProvider;
use App\Services\AI\BookAssistant\Providers\GeminiLlmProvider;
use App\Services\AI\BookAssistant\Providers\GroqLlmProvider;
use App\Services\AI\BookAssistant\Providers\NullEmbeddingProvider;
use App\Services\AI\BookAssistant\Providers\OpenAiEmbeddingProvider;
use App\Services\AI\BookAssistant\Providers\OpenAiLlmProvider;

class ProviderManager
{
    protected ?LlmProviderInterface $customLlm = null;
    protected ?EmbeddingProviderInterface $customEmbedding = null;

    public function setLlmProvider(?LlmProviderInterface $provider): void
    {
        $this->customLlm = $provider;
    }

    public function setEmbeddingProvider(?EmbeddingProviderInterface $provider): void
    {
        $this->customEmbedding = $provider;
    }

    /**
     * Joriy sozlangan LLM provayderni qaytaradi.
     * Standart holda (driver ko'rsatilmaganda) asosiy provayder (Groq) va
     * avtomatik zaxira (Gemini) bilan ishlaydigan ResilientLlmService qaytariladi.
     * Agar aniq $driver berilsa, to'g'ridan-to'g'ri o'sha drayver qaytariladi.
     */
    public function llm(?string $driver = null): LlmProviderInterface
    {
        if ($this->customLlm !== null && $driver === null) {
            return $this->customLlm;
        }

        if ($driver !== null) {
            return $this->resolveDriver($driver);
        }

        $primaryName = strtolower(trim((string) config('ai.provider', 'groq')));
        $fallbackName = strtolower(trim((string) config('ai.fallback_provider', 'gemini')));

        if ($fallbackName === '' || $fallbackName === 'none' || $primaryName === $fallbackName) {
            return $this->resolveDriver($primaryName);
        }

        $primary = $this->resolveDriver($primaryName);
        $fallback = $this->resolveDriver($fallbackName);

        return new ResilientLlmService($primary, $fallback);
    }

    /**
     * Berilgan nomdagi aniq drayverni yuklash.
     */
    public function resolveDriver(string $driver): LlmProviderInterface
    {
        $name = strtolower(trim($driver));

        return match ($name) {
            'groq'      => new GroqLlmProvider(),
            'gemini'    => new GeminiLlmProvider(),
            'openai'    => new OpenAiLlmProvider(),
            'anthropic' => new AnthropicLlmProvider(),
            default     => throw new AiProviderException("Noma'lum AI LLM provayderi: «{$name}». Qo'llab-quvvatlanadiganlar: groq, gemini, openai, anthropic."),
        };
    }

    /**
     * Joriy sozlangan Embedding provayderni qaytaradi.
     */
    public function embedding(?string $driver = null): EmbeddingProviderInterface
    {
        if ($this->customEmbedding !== null && $driver === null) {
            return $this->customEmbedding;
        }

        $name = strtolower(trim((string) ($driver ?? config('ai.embedding_provider', 'gemini'))));

        return match ($name) {
            'openai'               => new OpenAiEmbeddingProvider(),
            'gemini'               => new GeminiEmbeddingProvider(),
            'none', 'fulltext', '' => new NullEmbeddingProvider(),
            default                => throw new AiProviderException("Noma'lum AI Embedding provayderi: «{$name}». Qo'llab-quvvatlanadiganlar: gemini, openai, none."),
        };
    }
}
