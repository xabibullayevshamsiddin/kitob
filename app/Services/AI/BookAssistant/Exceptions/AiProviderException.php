<?php

namespace App\Services\AI\BookAssistant\Exceptions;

use RuntimeException;

class AiProviderException extends RuntimeException
{
    protected ?int $statusCode;
    protected ?int $retryAfter;

    public function __construct(
        string $message = "",
        int $code = 0,
        ?\Throwable $previous = null,
        ?int $statusCode = null,
        ?int $retryAfter = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->statusCode = $statusCode ?? ($code > 0 ? $code : null);
        $this->retryAfter = $retryAfter;
    }

    public function getStatusCode(): ?int
    {
        return $this->statusCode;
    }

    public function getRetryAfter(): ?int
    {
        return $this->retryAfter;
    }
}
