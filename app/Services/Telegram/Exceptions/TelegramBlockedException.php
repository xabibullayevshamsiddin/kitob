<?php

namespace App\Services\Telegram\Exceptions;

class TelegramBlockedException extends TelegramApiException
{
    protected int|string|null $chatId;

    public function __construct(
        int|string|null $chatId = null,
        string $description = "Foydalanuvchi botni bloklagan yoki chat mavjud emas.",
        int $errorCode = 403,
        ?\Throwable $previous = null
    ) {
        $this->chatId = $chatId;
        parent::__construct(
            "Telegram bot bloklangan (Chat ID: {$chatId}): {$description}",
            403,
            $errorCode,
            $description,
            [],
            $previous
        );
    }

    public function getChatId(): int|string|null
    {
        return $this->chatId;
    }
}
