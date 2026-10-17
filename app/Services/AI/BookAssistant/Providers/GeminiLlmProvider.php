<?php

namespace App\Services\AI\BookAssistant\Providers;

use App\Services\AI\BookAssistant\Contracts\LlmProviderInterface;
use App\Services\AI\BookAssistant\Exceptions\AiProviderException;
use Illuminate\Support\Facades\Http;

class GeminiLlmProvider implements LlmProviderInterface
{
    protected ?string $apiKey;
    protected string $baseUrl;
    protected string $model;
    protected int $timeout;
    protected int $maxOutputTokens;

    public function __construct()
    {
        $cfg = config('ai.providers.gemini', []);
        $this->apiKey = $cfg['api_key'] ?? env('GEMINI_API_KEY');
        $this->baseUrl = rtrim((string) ($cfg['base_url'] ?? 'https://generativelanguage.googleapis.com/v1beta'), '/');
        $this->model = (string) ($cfg['chat_model'] ?? 'gemini-3.8-flash');
        $this->timeout = (int) ($cfg['timeout'] ?? 30);
        $this->maxOutputTokens = (int) config('ai.max_output_tokens', 700);
    }

    public function getProviderName(): string
    {
        return 'gemini';
    }

    public function getModelName(): string
    {
        return $this->model;
    }

    public function chat(array $messages, array $options = []): array
    {
        if (empty($this->apiKey) || str_starts_with($this->apiKey, 'your-')) {
            throw new AiProviderException("Gemini API kaliti sozlanmagan (GEMINI_API_KEY).");
        }

        $model = (string) ($options['model'] ?? $this->model);
        $maxTokens = (int) ($options['max_tokens'] ?? $this->maxOutputTokens);
        $temperature = (float) ($options['temperature'] ?? 0.2);

        $systemContent = '';
        $contents = [];

        foreach ($messages as $m) {
            $role = (string) ($m['role'] ?? 'user');
            $text = (string) ($m['content'] ?? '');

            if ($role === 'system') {
                $systemContent .= ($systemContent !== '' ? "\n\n" : '') . $text;
            } else {
                $contents[] = [
                    'role'  => $role === 'assistant' ? 'model' : 'user',
                    'parts' => [['text' => $text]],
                ];
            }
        }

        // Gemini kamida bitta content talab qiladi
        if (empty($contents)) {
            $contents[] = [
                'role'  => 'user',
                'parts' => [['text' => $systemContent ?: 'Salom']],
            ];
            $systemContent = '';
        }

        $payload = [
            'contents'         => $contents,
            'generationConfig' => [
                'maxOutputTokens' => $maxTokens,
                'temperature'     => $temperature,
            ],
        ];

        if ($systemContent !== '') {
            $payload['systemInstruction'] = [
                'parts' => [['text' => $systemContent]],
            ];
        }

        $url = $this->baseUrl . '/models/' . urlencode($model) . ':generateContent?key=' . urlencode($this->apiKey);

        $response = Http::withHeaders([
            'x-goog-api-key' => $this->apiKey,
        ])
            ->timeout($this->timeout)
            ->acceptJson()
            ->post($url, $payload);

        if (!$response->successful()) {
            $errMsg = $response->json('error.message') ?: ('HTTP ' . $response->status());
            throw new AiProviderException('Gemini xatosi: ' . $errMsg);
        }

        $data = $response->json();
        $candidate = $data['candidates'][0] ?? null;
        $content = '';

        if (!empty($candidate['content']['parts'])) {
            foreach ($candidate['content']['parts'] as $part) {
                if (isset($part['text'])) {
                    $content .= $part['text'];
                }
            }
        }

        $content = trim($content);
        if ($content === '') {
            $finishReason = $candidate['finishReason'] ?? 'UNKNOWN';
            throw new AiProviderException("Gemini bo'sh javob qaytardi (sabab: {$finishReason}).");
        }

        $usage = $data['usageMetadata'] ?? [];
        $promptTokens = (int) ($usage['promptTokenCount'] ?? 0);
        $completionTokens = (int) ($usage['candidatesTokenCount'] ?? 0);
        $totalTokens = (int) ($usage['totalTokenCount'] ?? ($promptTokens + $completionTokens));

        return [
            'content'           => $content,
            'prompt_tokens'     => $promptTokens,
            'completion_tokens' => $completionTokens,
            'total_tokens'      => $totalTokens,
            'provider'          => 'gemini',
            'model'             => $model,
        ];
    }
}
