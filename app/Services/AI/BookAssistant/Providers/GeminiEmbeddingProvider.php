<?php

namespace App\Services\AI\BookAssistant\Providers;

use App\Models\BookChunk;
use App\Services\AI\BookAssistant\Contracts\EmbeddingProviderInterface;
use App\Services\AI\BookAssistant\Exceptions\AiProviderException;
use Illuminate\Support\Facades\Http;

class GeminiEmbeddingProvider implements EmbeddingProviderInterface
{
    protected ?string $apiKey;
    protected string $baseUrl;
    protected string $model;
    protected int $timeout;

    public function __construct()
    {
        $cfg = config('ai.providers.gemini', []);
        $this->apiKey = $cfg['api_key'] ?? env('GEMINI_API_KEY');
        $this->baseUrl = rtrim((string) ($cfg['base_url'] ?? 'https://generativelanguage.googleapis.com/v1beta'), '/');
        $this->model = (string) ($cfg['embedding_model'] ?? 'gemini-embedding-2');
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
            throw new AiProviderException('Gemini Embedding API kaliti sozlanmagan (GEMINI_API_KEY).');
        }

        $requests = [];
        $modelPath = str_starts_with($this->model, 'models/') ? $this->model : ('models/' . $this->model);

        foreach ($texts as $text) {
            $requests[] = [
                'model'   => $modelPath,
                'content' => [
                    'parts' => [['text' => (string) $text]],
                ],
            ];
        }

        $url = $this->baseUrl . '/' . $modelPath . ':batchEmbedContents?key=' . urlencode($this->apiKey);

        $response = Http::withHeaders([
            'x-goog-api-key' => $this->apiKey,
        ])
            ->timeout($this->timeout)
            ->acceptJson()
            ->post($url, [
                'requests' => $requests,
            ]);

        if (!$response->successful()) {
            $errMsg = $response->json('error.message') ?: ('HTTP ' . $response->status());
            throw new AiProviderException('Gemini Embedding xatosi: ' . $errMsg);
        }

        $data = $response->json();
        $rawEmbeddings = $data['embeddings'] ?? [];
        $vectors = [];

        foreach ($rawEmbeddings as $idx => $item) {
            $rawVec = $item['values'] ?? [];
            $vectors[$idx] = BookChunk::normalizeVector($rawVec);
        }

        return [
            'vectors'      => array_values($vectors),
            'model'        => $this->model,
            'total_tokens' => 0,
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
