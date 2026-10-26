<?php
define('LARAVEL_START', microtime(true));
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "APP_URL: " . config('app.url') . "\n";
echo "WEBHOOK URL будет: " . rtrim(config('app.url'), '/') . "/telegram/webhook\n";
echo "BOT TOKEN (first 15 chars): " . substr(config('telegram.bot_token'), 0, 15) . "...\n";
echo "BOT USERNAME: " . config('telegram.bot_username') . "\n";
