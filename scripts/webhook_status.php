<?php
// Webhook xatolarini tekshirish
$token = getenv('TELEGRAM_BOT_TOKEN') ?: '';
if (!$token) {
    $envPath = __DIR__ . '/../.env';
    if (file_exists($envPath)) {
        if (preg_match('/^TELEGRAM_BOT_TOKEN=(.*)$/m', file_get_contents($envPath), $m)) {
            $token = trim($m[1], " \t\n\r\0\x0B\"'");
        }
    }
}
if (!$token) {
    die("❌ TELEGRAM_BOT_TOKEN .env faylida topilmadi.\n");
}

$ch = curl_init("https://api.telegram.org/bot{$token}/getWebhookInfo");
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => '{}',
    CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
    CURLOPT_RETURNTRANSFER => true,
]);
$resp = json_decode(curl_exec($ch), true);
curl_close($ch);

echo "URL: " . $resp['result']['url'] . "\n";
echo "Last error: " . ($resp['result']['last_error_message'] ?? '✅ Xato yo\'q') . "\n";
echo "Pending updates: " . $resp['result']['pending_update_count'] . "\n";
if (!empty($resp['result']['last_error_date'])) {
    echo "Error vaqti: " . date('H:i:s', $resp['result']['last_error_date']) . "\n";
}
