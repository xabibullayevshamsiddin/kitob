<?php
define('LARAVEL_START', microtime(true));
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$appUrl = config('app.url');
$webhookUrl = rtrim($appUrl, '/') . '/telegram/webhook';
$token = config('telegram.bot_token');
$secret = config('telegram.webhook_secret');

echo "APP_URL: $appUrl\n";
echo "Webhook URL: $webhookUrl\n";
echo "Token (first 15): " . substr($token, 0, 15) . "...\n";
echo "Username: " . config('telegram.bot_username') . "\n\n";

// Set webhook via curl
$data = ['url' => $webhookUrl];
if ($secret) {
    $data['secret_token'] = $secret;
}
$data['drop_pending_updates'] = true;

$ch = curl_init("https://api.telegram.org/bot{$token}/setWebhook");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

echo "Telegram Response: $response\n";
