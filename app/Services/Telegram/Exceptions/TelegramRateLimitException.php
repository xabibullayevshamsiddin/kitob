<?php

namespace App\Services\Telegram\Exceptions;

class TelegramRateLimitException extends TelegramApiException
{
    protected int $retryAfter;

    public function __construct(
        int $retryAfter = 1,
        string $description = "Juda ko'p so'rov yuborildi (Rate limit).",
        int $errorCode = 429,
        array $parameters = [],
        ?\Throwable $previous = null
    ) {
        $this->retryAfter = $retryAfter;
        parent::__construct(
            "Telegram cheklovi (429): {$description}. Kuting: {$retryAfter} soniya.",
            429,
            $errorCode,
            $description,
            $parameters,
            $previous
        );
    }

    public function getRetryAfter(): int
    {
        return $this->retryAfter;
    }
}
