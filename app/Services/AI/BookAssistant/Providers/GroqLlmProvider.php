<?php

namespace App\Services\AI\BookAssistant\Providers;

use App\Services\AI\BookAssistant\Contracts\LlmProviderInterface;
use App\Services\AI\BookAssistant\Exceptions\AiProviderException;
use Illuminate\Support\Facades\Http;

class GroqLlmProvider implements LlmProviderInterface
{
    protected ?string $apiKey;
    protected string $baseUrl;
    protected string $model;
    protected int $timeout;
    protected int $maxOutputTokens;

    public function __construct()
    {
        $cfg = config('ai.providers.groq', []);
        $this->apiKey = $cfg['api_key'] ?? env('GROQ_API_KEY');
        $this->baseUrl = rtrim((string) ($cfg['base_url'] ?? 'https://api.groq.com/openai/v1'), '/');
        $this->model = (string) ($cfg['chat_model'] ?? 'llama-3.3-70b-versatile');
        $this->timeout = (int) ($cfg['timeout'] ?? 30);
        $this->maxOutputTokens = (int) config('ai.max_output_tokens', 700);
    }

    public function getProviderName(): string
    {
        return 'groq';
    }

    public function getModelName(): string
    {
        return $this->model;
    }

    public function chat(array $messages, array $options = []): array
    {
        if (empty($this->apiKey) || str_starts_with($this->apiKey, 'your-') || str_starts_with($this->apiKey, 'gsk_your_')) {
            throw new AiProviderException("Groq API kaliti sozlanmagan (GROQ_API_KEY). console.groq.com/keys saytidan bepul kalit oling.");
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
            throw new AiProviderException('Groq xatosi: ' . $errMsg);
        }

        $data = $response->json();
        $content = (string) ($data['choices'][0]['message']['content'] ?? '');
        $usage = $data['usage'] ?? [];

        return [
            'content'       => $content,
            'finish_reason' => $data['choices'][0]['finish_reason'] ?? 'stop',
            'prompt_tokens' => (int) ($usage['prompt_tokens'] ?? 0),
            'output_tokens' => (int) ($usage['completion_tokens'] ?? 0),
            'total_tokens'  => (int) ($usage['total_tokens'] ?? 0),
            'model'         => (string) ($data['model'] ?? $model),
            'provider'      => 'groq',
        ];
    }
}
