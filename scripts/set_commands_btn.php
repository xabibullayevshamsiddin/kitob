<?php
$token = "8529445714:AAF4ngytYL5Zl3McsC7D03GEH4W2L4UeHy0";

function tgApi(string $token, string $method, array $data): array {
    $ch = curl_init("https://api.telegram.org/bot{$token}/{$method}");
    $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $json,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json; charset=utf-8'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 10,
    ]);
    $resp = curl_exec($ch);
    curl_close($ch);
    echo "Raw: $resp\n";
    return json_decode($resp, true) ?? [];
}

// Step 1: default ga qaytarish
echo "=== Step 1: default ga qaytarish ===\n";
tgApi($token, 'setChatMenuButton', ['menu_button' => ['type' => 'default']]);
sleep(1);

// Step 2: commands ga o'rnatish
echo "=== Step 2: commands (⊞) o'rnatish ===\n";
tgApi($token, 'setChatMenuButton', ['menu_button' => ['type' => 'commands']]);
sleep(1);

// Step 3: tekshirish
echo "=== Step 3: tekshirish ===\n";
tgApi($token, 'getChatMenuButton', []);
