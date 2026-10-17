<?php

namespace App\Services\AI\BookAssistant\Providers;

use App\Services\AI\BookAssistant\Contracts\LlmProviderInterface;
use App\Services\AI\BookAssistant\Exceptions\AiProviderException;
use Illuminate\Support\Facades\Http;

class AnthropicLlmProvider implements LlmProviderInterface
{
    protected ?string $apiKey;
    protected string $baseUrl;
    protected string $model;
    protected string $anthropicVersion;
    protected int $timeout;
    protected int $maxOutputTokens;

    public function __construct()
    {
        $cfg = config('ai.providers.anthropic', []);
        $this->apiKey = $cfg['api_key'] ?? null;
        $this->baseUrl = rtrim((string) ($cfg['base_url'] ?? 'https://api.anthropic.com/v1'), '/');
        $this->model = (string) ($cfg['chat_model'] ?? 'claude-3-5-haiku-latest');
        $this->anthropicVersion = (string) ($cfg['anthropic_version'] ?? '2023-06-01');
        $this->timeout = (int) ($cfg['timeout'] ?? 30);
        $this->maxOutputTokens = (int) config('ai.max_output_tokens', 700);
    }

    public function getProviderName(): string
    {
        return 'anthropic';
    }

    public function getModelName(): string
    {
        return $this->model;
    }

    public function chat(array $messages, array $options = []): array
    {
        if (empty($this->apiKey) || str_starts_with($this->apiKey, 'your-')) {
            throw new AiProviderException('Anthropic API kaliti sozlanmagan (ANTHROPIC_API_KEY).');
        }

        $model = (string) ($options['model'] ?? $this->model);
        $maxTokens = (int) ($options['max_tokens'] ?? $this->maxOutputTokens);
        $temperature = (float) ($options['temperature'] ?? 0.2);

        $systemParts = [];
        $chatMessages = [];

        foreach ($messages as $msg) {
            $role = $msg['role'] ?? 'user';
            $content = (string) ($msg['content'] ?? '');
            if ($role === 'system') {
                $systemParts[] = $content;
            } else {
                $chatMessages[] = [
                    'role'    => $role === 'assistant' ? 'assistant' : 'user',
                    'content' => $content,
                ];
            }
        }

        $payload = [
            'model'       => $model,
            'max_tokens'  => $maxTokens,
            'temperature' => $temperature,
            'messages'    => $chatMessages,
        ];

        if (!empty($systemParts)) {
            $payload['system'] = implode("\n\n", $systemParts);
        }

        $response = Http::withHeaders([
            'x-api-key'         => $this->apiKey,
            'anthropic-version' => $this->anthropicVersion,
        ])
            ->timeout($this->timeout)
            ->acceptJson()
            ->post($this->baseUrl . '/messages', $payload);

        if (!$response->successful()) {
            $errMsg = $response->json('error.message') ?: ('HTTP ' . $response->status());
            throw new AiProviderException('Anthropic xatosi: ' . $errMsg);
        }

        $data = $response->json();
        $contentBlocks = $data['content'] ?? [];
        $textParts = [];
        foreach ($contentBlocks as $block) {
            if (($block['type'] ?? '') === 'text' && isset($block['text'])) {
                $textParts[] = $block['text'];
            }
        }
        $content = trim(implode("\n", $textParts));

        if ($content === '') {
            throw new AiProviderException("Anthropic bo'sh javob qaytardi.");
        }

        $usage = $data['usage'] ?? [];
        $promptTokens = (int) ($usage['input_tokens'] ?? 0);
        $completionTokens = (int) ($usage['output_tokens'] ?? 0);

        return [
            'content'           => $content,
            'prompt_tokens'     => $promptTokens,
            'completion_tokens' => $completionTokens,
            'total_tokens'      => $promptTokens + $completionTokens,
            'provider'          => 'anthropic',
            'model'             => (string) ($data['model'] ?? $model),
        ];
    }
}
