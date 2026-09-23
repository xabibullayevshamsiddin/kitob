<?php

namespace App\Services\AI\Contracts;

interface AiProviderInterface
{
    /**
     * AI provayderi orqali xabar yuborish va javob olish.
     *
     * @param string $prompt
     * @param array $context [kitob mazmuni, o'quvchi ma'lumotlari]
     * @return array ['response' => string, 'tokens_used' => int]
     */
    public function ask(string $prompt, array $context = []): array;
}
