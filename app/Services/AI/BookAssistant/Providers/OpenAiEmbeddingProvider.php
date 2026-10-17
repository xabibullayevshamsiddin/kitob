<?php

namespace App\Services\AI\BookAssistant\Providers;

use App\Models\BookChunk;
use App\Services\AI\BookAssistant\Contracts\EmbeddingProviderInterface;
use App\Services\AI\BookAssistant\Exceptions\AiProviderException;
use Illuminate\Support\Facades\Http;

class OpenAiEmbeddingProvider implements EmbeddingProviderInterface
{
    protected ?string $apiKey;
    protected string $baseUrl;
    protected string $model;
    protected int $timeout;

    public function __construct()
    {
        $cfg = config('ai.providers.openai', []);
        $this->apiKey = $cfg['api_key'] ?? null;
        $this->baseUrl = rtrim((string) ($cfg['base_url'] ?? 'https://api.openai.com/v1'), '/');
        $this->model = (string) ($cfg['embedding_model'] ?? 'text-embedding-3-small');
        $this->timeout = (int) ($cfg['timeout'] ?? 30);
    }

    public function isAvailable(): bool
    {
        return !empty($this->apiKey) && !str_starts_with($this->apiKey, 'your-');
    }

    public function getModelName(): string
    {
        return $this->model;
    }

    public function embedMany(array $texts): array
    {
        if (empty($texts)) {
            return ['vectors' => [], 'model' => $this->model, 'total_tokens' => 0];
        }

        if (!$this->isAvailable()) {
            throw new AiProviderException('OpenAI Embedding API kaliti sozlanmagan (OPENAI_API_KEY).');
        }

        $response = Http::withToken($this->apiKey)
            ->timeout($this->timeout)
            ->acceptJson()
            ->post($this->baseUrl . '/embeddings', [
                'model' => $this->model,
                'input' => array_values($texts),
            ]);

        if (!$response->successful()) {
            $errMsg = $response->json('error.message') ?: ('HTTP ' . $response->status());
            throw new AiProviderException('OpenAI Embedding xatosi: ' . $errMsg);
        }

        $data = $response->json();
        $rows = $data['data'] ?? [];
        $vectors = [];

        foreach ($rows as $idx => $row) {
            $rawVec = $row['embedding'] ?? [];
            $vectors[$row['index'] ?? $idx] = BookChunk::normalizeVector($rawVec);
        }

        ksort($vectors);

        return [
            'vectors'      => array_values($vectors),
            'model'        => (string) ($data['model'] ?? $this->model),
            'total_tokens' => (int) ($data['usage']['total_tokens'] ?? 0),
        ];
    }

    public function embedQuery(string $text): ?array
    {
        if (!$this->isAvailable()) {
            return null;
        }

        $res = $this->embedMany([$text]);

        return $res['vectors'][0] ?? null;
    }
}
