<?php
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
    return json_decode($resp, true) ?? [];
}

// Chap tomonga: "Mini App" tugmasi (web_app)
$r1 = tgApi($token, 'setChatMenuButton', [
    'menu_button' => [
        'type'    => 'web_app',
        'text'    => 'Mini App',
        'web_app' => ['url' => 'https://kitobxon.elitehost.uz/tg-app'],
    ]
]);
echo "setChatMenuButton (web_app 'Mini App'): " . ($r1['ok'] ? '✅ OK' : '❌ ' . $r1['description']) . "\n";

// O'ng tomondagi ⊞ — commands set bo'lsa Telegram o'zi ko'rsatadi
$commands = [
    ['command' => 'start',         'description' => 'Botni ishga tushirish'],
    ['command' => 'menu',          'description' => "Asosiy menyu tugmalarini ko'rsatish"],
    ['command' => 'app',           'description' => "Davra_books Mini App ni ochish"],
    ['command' => 'profil',        'description' => 'Shaxsiy hisob va statistika'],
    ['command' => 'streak',        'description' => "Kunlik mutolaa zanjiri"],
    ['command' => 'balance',       'description' => 'Oltin tangalar va ballar'],
    ['command' => 'books',         'description' => 'Sara kitoblar katalogi'],
    ['command' => 'leaderboard',   'description' => "O'quvchilar reytingi"],
    ['command' => 'shop',          'description' => "Maxsus imtiyozlar do'koni"],
    ['command' => 'hikmat',        'description' => 'Kun hikmati'],
    ['command' => 'notifications', 'description' => 'Bildirishnomalarni boshqarish'],
    ['command' => 'help',          'description' => "Yordam va buyruqlar ro'yxati"],
    ['command' => 'stop',          'description' => "Bildirishnomalarni to'xtatish"],
];
$r2 = tgApi($token, 'setMyCommands', ['commands' => $commands]);
echo "setMyCommands (⊞ uchun): " . ($r2['ok'] ? '✅ OK' : '❌ ' . $r2['description']) . "\n";

// Tekshirish
$check = tgApi($token, 'getChatMenuButton', []);
echo "Holat: " . json_encode($check['result'], JSON_UNESCAPED_UNICODE) . "\n";

$cmds = tgApi($token, 'getMyCommands', []);
echo "Buyruqlar soni: " . count($cmds['result']) . " ta\n";
echo "\nChap: Mini App tugmasi ✅\nO'ng: ⊞ — Telegram o'zi ko'rsatadi (commands set bo'lganda) ✅\n";
