<?php

namespace App\Services\Telegram\Exceptions;

use Exception;

class TelegramApiException extends Exception
{
    protected ?int $errorCode;
    protected ?string $telegramDescription;
    protected array $responseParameters;

    public function __construct(
        string $message = '',
        int $code = 0,
        ?int $errorCode = null,
        ?string $telegramDescription = null,
        array $responseParameters = [],
        ?\Throwable $previous = null
    ) {
        $this->errorCode = $errorCode;
        $this->telegramDescription = $telegramDescription;
        $this->responseParameters = $responseParameters;

        parent::__construct($message, $code, $previous);
    }

    public function getErrorCode(): ?int
    {
        return $this->errorCode;
    }

    public function getTelegramDescription(): ?string
    {
        return $this->telegramDescription;
    }

    public function getResponseParameters(): array
    {
        return $this->responseParameters;
    }
}
