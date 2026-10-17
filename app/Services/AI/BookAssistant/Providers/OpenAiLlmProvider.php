<?php

namespace App\Services\AI\BookAssistant\Providers;

use App\Services\AI\BookAssistant\Contracts\LlmProviderInterface;
use App\Services\AI\BookAssistant\Exceptions\AiProviderException;
use Illuminate\Support\Facades\Http;

class OpenAiLlmProvider implements LlmProviderInterface
{
    protected ?string $apiKey;
    protected string $baseUrl;
    protected string $model;
    protected int $timeout;
    protected int $maxOutputTokens;

    public function __construct()
    {
        $cfg = config('ai.providers.openai', []);
        $this->apiKey = $cfg['api_key'] ?? null;
        $this->baseUrl = rtrim((string) ($cfg['base_url'] ?? 'https://api.openai.com/v1'), '/');
        $this->model = (string) ($cfg['chat_model'] ?? 'gpt-4o-mini');
        $this->timeout = (int) ($cfg['timeout'] ?? 30);
        $this->maxOutputTokens = (int) config('ai.max_output_tokens', 700);
    }

    public function getProviderName(): string
    {
        return 'openai';
    }

    public function getModelName(): string
    {
        return $this->model;
    }

    public function chat(array $messages, array $options = []): array
    {
        if (empty($this->apiKey) || str_starts_with($this->apiKey, 'your-')) {
            throw new AiProviderException('OpenAI API kaliti sozlanmagan (OPENAI_API_KEY).');
        }

        $model = (string) ($options['model'] ?? $this->model);
        $maxTokens = (int) ($options['max_tokens'] ?? $this->maxOutputTokens);
        $temperature = (float) ($options['temperature'] ?? 0.2);

        $response = Http::withToken($this->apiKey)
            ->timeout($this->timeout)
            ->acceptJson()
            ->post($this->baseUrl . '/chat/completions', [
                'model'       => $model,
                'messages'    => $messages,
                'max_tokens'  => $maxTokens,
                'temperature' => $temperature,
            ]);

        if (!$response->successful()) {
            $errMsg = $response->json('error.message') ?: ('HTTP ' . $response->status());
            throw new AiProviderException('OpenAI xatosi: ' . $errMsg);
        }

        $data = $response->json();
        $content = trim((string) ($data['choices'][0]['message']['content'] ?? ''));

        if ($content === '') {
            throw new AiProviderException("OpenAI bo'sh javob qaytardi.");
        }

        $usage = $data['usage'] ?? [];
        $promptTokens = (int) ($usage['prompt_tokens'] ?? 0);
        $completionTokens = (int) ($usage['completion_tokens'] ?? 0);
        $totalTokens = (int) ($usage['total_tokens'] ?? ($promptTokens + $completionTokens));

        return [
            'content'           => $content,
            'prompt_tokens'     => $promptTokens,
            'completion_tokens' => $completionTokens,
            'total_tokens'      => $totalTokens,
            'provider'          => 'openai',
            'model'             => (string) ($data['model'] ?? $model),
        ];
    }
}
