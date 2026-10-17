<?php

namespace App\Services\AI\BookAssistant\Providers;

use App\Services\AI\BookAssistant\Contracts\EmbeddingProviderInterface;

/**
 * Embedding provayderi o'chirilganda yoki API kalit sozlanmaganda
 * faqat FULLTEXT/leksik qidiruv rejimida ishlash uchun drayver.
 */
class NullEmbeddingProvider implements EmbeddingProviderInterface
{
    public function isAvailable(): bool
    {
        return false;
    }

    public function getModelName(): string
    {
        return 'none';
    }

    public function embedMany(array $texts): array
    {
        return [
            'vectors'      => [],
            'model'        => 'none',
            'total_tokens' => 0,
        ];
    }

    public function embedQuery(string $text): ?array
    {
        return null;
    }
}
