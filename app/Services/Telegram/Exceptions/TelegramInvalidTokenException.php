<?php

namespace App\Services\Telegram\Exceptions;

class TelegramInvalidTokenException extends TelegramApiException
{
    public function __construct(string $message = "Telegram bot token sozlanmagan yoki noto'g'ri.")
    {
        parent::__construct($message, 401);
    }
}
