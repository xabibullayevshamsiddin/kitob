<?php
// Update TELEGRAM_BOT_TOKEN and TELEGRAM_BOT_USERNAME in remote .env
define('LARAVEL_START', microtime(true));
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$envPath = base_path('.env');
$content = file_get_contents($envPath);

// Update token
$content = preg_replace(
    '/^TELEGRAM_BOT_TOKEN=.*/m',
    'TELEGRAM_BOT_TOKEN=8529445714:AAF4ngytYL5Zl3McsC7D03GEH4W2L4UeHy0',
    $content
);

// Update username
$content = preg_replace(
    '/^TELEGRAM_BOT_USERNAME=.*/m',
    'TELEGRAM_BOT_USERNAME=davra_books_bot',
    $content
);

file_put_contents($envPath, $content);
echo "✅ .env yangilandi!\n";

// Verify
preg_match('/^TELEGRAM_BOT_TOKEN=(.+)$/m', $content, $m1);
preg_match('/^TELEGRAM_BOT_USERNAME=(.+)$/m', $content, $m2);
echo "TOKEN: " . ($m1[1] ?? 'NOT FOUND') . "\n";
echo "USERNAME: " . ($m2[1] ?? 'NOT FOUND') . "\n";
