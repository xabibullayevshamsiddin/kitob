<?php

namespace App\Services\AI\BookAssistant\Contracts;

interface LlmProviderInterface
{
    /**
     * LLM provayderga suhbat xabarlarini yuboradi va javob qaytaradi.
     *
     * @param  array<int, array{role: string, content: string}>  $messages
     * @param  array<string, mixed>  $options
     * @return array{
     *     content: string,
     *     prompt_tokens: int,
     *     completion_tokens: int,
     *     total_tokens: int,
     *     provider: string,
     *     model: string
     * }
     */
    public function chat(array $messages, array $options = []): array;

    public function getProviderName(): string;

    public function getModelName(): string;
}
