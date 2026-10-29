<?php
// Token va secretni .env fayldan o'qiymiz
$token = getenv('TELEGRAM_BOT_TOKEN') ?: '';
$secret = getenv('TELEGRAM_WEBHOOK_SECRET') ?: '';

if (!$token || !$secret) {
    $envPath = __DIR__ . '/../.env';
    if (file_exists($envPath)) {
        $env = file_get_contents($envPath);
        if (!$token && preg_match('/^TELEGRAM_BOT_TOKEN=(.*)$/m', $env, $m)) {
            $token = trim($m[1], " \t\n\r\0\x0B\"'");
        }
        if (!$secret && preg_match('/^TELEGRAM_WEBHOOK_SECRET=(.*)$/m', $env, $m)) {
            $secret = trim($m[1], " \t\n\r\0\x0B\"'");
        }
    }
}

if (!$token) {
    die("❌ TELEGRAM_BOT_TOKEN .env faylida topilmadi.\n");
}

$webhookUrl = "https://kitobxon.elitehost.uz/telegram/webhook";

function tgApi(string $token, string $method, array $data = []): array {
    $ch = curl_init("https://api.telegram.org/bot{$token}/{$method}");
    $json = json_encode($data, JSON_UNESCAPED_UNICODE);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $json,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json; charset=utf-8'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
    ]);
    $resp = curl_exec($ch);
    curl_close($ch);
    return json_decode($resp, true) ?? [];
}

// 1. Eski webhookni o'chirish
echo "=== Step 1: delete old webhook ===\n";
$r0 = tgApi($token, 'deleteWebhook', ['drop_pending_updates' => true]);
echo ($r0['ok'] ? '✅ OK' : '❌ ' . $r0['description']) . "\n";
sleep(2);

// 2. Yangi webhook secret bilan o'rnatish
echo "=== Step 2: set webhook WITH secret ===\n";
$r1 = tgApi($token, 'setWebhook', [
    'url'                  => $webhookUrl,
    'secret_token'         => $secret,
    'drop_pending_updates' => true,
    'max_connections'      => 40,
]);
echo ($r1['ok'] ? '✅ OK — ' . $r1['description'] : '❌ ' . $r1['description']) . "\n";

// 3. Tekshirish
echo "\n=== Step 3: getWebhookInfo ===\n";
$info = tgApi($token, 'getWebhookInfo');
echo "URL:        " . $info['result']['url'] . "\n";
echo "Last error: " . ($info['result']['last_error_message'] ?? 'Yo\'q ✅') . "\n";
echo "Pending:    " . $info['result']['pending_update_count'] . "\n";
