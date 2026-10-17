<?php

namespace App\Services\AI\BookAssistant\Contracts;

interface EmbeddingProviderInterface
{
    /**
     * Provayder sozlangan va ishlashga tayyor ekanini tekshiradi.
     */
    public function isAvailable(): bool;

    /**
     * Bir nechta matnlar uchun normallashtirilgan float32 vektorlarni oladi.
     *
     * @param  string[]  $texts
     * @return array{
     *     vectors: array<int, float[]>,
     *     model: string,
     *     total_tokens: int
     * }
     */
    public function embedMany(array $texts): array;

    /**
     * Bitta qidiruv so'rovi uchun normallashtirilgan vektorni oladi.
     *
     * @return float[]|null
     */
    public function embedQuery(string $text): ?array;

    public function getModelName(): string;
}
