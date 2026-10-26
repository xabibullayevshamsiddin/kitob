<?php
// 30 soniya kuting va webhook xatolarini tekshiring
$token = "8529445714:AAF4ngytYL5Zl3McsC7D03GEH4W2L4UeHy0";

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
