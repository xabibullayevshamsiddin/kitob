<?php
require __DIR__ . '/elitehost.php';

$env = EliteHost::getFile('.env');
preg_match_all('/^(APP_URL|TELEGRAM_[A-Z_]+)=(.*)$/m', $env, $matches, PREG_SET_ORDER);

$config = [];
foreach ($matches as $m) {
    $config[$m[1]] = trim($m[2]);
    echo "{$m[1]} = " . ($m[1] === 'TELEGRAM_BOT_TOKEN' ? substr($m[2], 0, 15) . '...' : $m[2]) . "\n";
}

$token = $config['TELEGRAM_BOT_TOKEN'] ?? '';
if ($token) {
    echo "\n--- Telegram getWebhookInfo ---\n";
    $info = @file_get_contents("https://api.telegram.org/bot{$token}/getWebhookInfo");
    echo $info . "\n";

    echo "\n--- Telegram getMe ---\n";
    $me = @file_get_contents("https://api.telegram.org/bot{$token}/getMe");
    echo $me . "\n";
}
