<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Telegram Bot Credentials
    |--------------------------------------------------------------------------
    |
    | @BotFather tomonidan berilgan bot tokeni va bot usernamesi.
    | Token maxfiy ma'lumot bo'lib, hech qachon ochiq kodga yoki loglarga yozilmaydi.
    |
    */
    'bot_token' => env('TELEGRAM_BOT_TOKEN'),
    'bot_username' => env('TELEGRAM_BOT_USERNAME'),

    /*
    |--------------------------------------------------------------------------
    | Telegram Webhook Secret Token
    |--------------------------------------------------------------------------
    |
    | Telegram webhook so'rovlarini tekshirish uchun ishlatiladigan maxfiy token.
    | X-Telegram-Bot-Api-Secret-Token sarlavhasi bilan birgalikda yuboriladi.
    |
    */
    'webhook_secret' => env('TELEGRAM_WEBHOOK_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Telegram Login (OpenID Connect / OAuth) Credentials
    |--------------------------------------------------------------------------
    |
    | Telegram Login (oauth.telegram.org) orqali avtorizatsiya uchun Client ID va Secret.
    |
    */
    'login_client_id' => env('TELEGRAM_LOGIN_CLIENT_ID'),
    'login_client_secret' => env('TELEGRAM_LOGIN_CLIENT_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Telegram Admin & Alerting Chat ID
    |--------------------------------------------------------------------------
    |
    | Tizim xatoliklari, to'lovlar va muhim ogohlantirishlar yuboriladigan admin chat ID.
    |
    */
    'admin_chat_id' => env('TELEGRAM_ADMIN_CHAT_ID'),

    /*
    |--------------------------------------------------------------------------
    | Telegram Stars Payments Master Switch
    |--------------------------------------------------------------------------
    |
    | Telegram Stars orqali to'lovlar tizimi yoqilgan yoki yo'qligi (standart: false).
    |
    */
    'payments_enabled' => (bool) env('TELEGRAM_PAYMENTS_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Email Registration Availability
    |--------------------------------------------------------------------------
    |
    | Standart email/parol orqali yangi ro'yxatdan o'tish imkoniyati (standart: true).
    | Agar false qilinsa, yangi foydalanuvchilar faqat Telegram orqali ro'yxatdan o'tadi.
    |
    */
    'auth_email_registration_enabled' => (bool) env('AUTH_EMAIL_REGISTRATION_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Telegram API Base URL & HTTP Settings
    |--------------------------------------------------------------------------
    |
    | Rasmiy Telegram Bot API bazaviy manzili va so'rov sozlamalari.
    |
    */
    'api_url' => env('TELEGRAM_API_URL', 'https://api.telegram.org'),
    'timeout' => (int) env('TELEGRAM_TIMEOUT', 15),
    'retry_attempts' => (int) env('TELEGRAM_RETRY_ATTEMPTS', 2),

    /*
    |--------------------------------------------------------------------------
    | Telegram Mini App URL & Security Settings
    |--------------------------------------------------------------------------
    |
    | Telegram ichida ochiladigan Mini App (TMA) manzili (HTTPS bo'lishi shart)
    | hamda initData / Login Widget imzolarining maksimal amal qilish muddati (soniyada).
    |
    */
    'mini_app_url' => env('TELEGRAM_MINI_APP_URL', rtrim(env('APP_URL', 'https://davrabooks.elitehost.uz'), '/') . '/tg/app'),
    'auth_max_age' => (int) env('TELEGRAM_AUTH_MAX_AGE', 3600),
    'quiet_hours_enabled' => (bool) env('TELEGRAM_QUIET_HOURS_ENABLED', false),
    'quiet_hours_start' => (int) env('TELEGRAM_QUIET_HOURS_START', 23),
    'quiet_hours_end' => (int) env('TELEGRAM_QUIET_HOURS_END', 7),

];
