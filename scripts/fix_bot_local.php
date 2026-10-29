<?php
// Local PHP script - directly calls Telegram API with correct UTF-8
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

// 1. setMyDescription — yangi foydalanuvchi botni ochganda ko'radi
$description = "📖 Davra_books — o'qish sevuvchilar uchun interaktiv platforma!\n\n✨ Imkoniyatlar:\n• 📚 Minglab kitoblar katalogi\n• 🔥 Kunlik o'qish streaki\n• 🪙 Tangalar va mukofotlar tizimi\n• 🏆 O'quvchilar reytingi\n• 🛍️ Maxsus imtiyozlar do'koni\n• 🤖 AI kitob yordamchisi\n\n👉 Boshlash uchun /start bosing!";

$r1 = tgApi($token, 'setMyDescription', ['description' => $description]);
echo "setMyDescription: " . ($r1['ok'] ? '✅ OK' : '❌ ' . $r1['description']) . "\n";

// 2. setMyShortDescription — profil sahifasida ko'rinadi (max 120 belgi)
$short = "📚 Davra_books — kitob o'qish, streak va tangalar platformasi. Har kuni o'qi, o's, g'olib bo'l!";
echo "Short desc length: " . mb_strlen($short) . " belgi\n";

$r2 = tgApi($token, 'setMyShortDescription', ['short_description' => $short]);
echo "setMyShortDescription: " . ($r2['ok'] ? '✅ OK' : '❌ ' . $r2['description']) . "\n";

// 3. setChatMenuButton — Menu + web_app (Mini App)
$r3 = tgApi($token, 'setChatMenuButton', [
    'menu_button' => [
        'type'    => 'web_app',
        'text'    => 'Mini App',
        'web_app' => ['url' => 'https://kitobxon.elitehost.uz/tg-app'],
    ]
]);
echo "setChatMenuButton (web_app): " . ($r3['ok'] ? '✅ OK' : '❌ ' . $r3['description']) . "\n";

// 4. setMyCommands — buyruqlar (⊞ uchun)
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

$r4 = tgApi($token, 'setMyCommands', ['commands' => $commands]);
echo "setMyCommands: " . ($r4['ok'] ? '✅ OK' : '❌ ' . $r4['description']) . "\n";

// 5. Tekshirish
$info = tgApi($token, 'getMe', []);
echo "\nBot info: @" . $info['result']['username'] . " (" . $info['result']['first_name'] . ")\n";

echo "\nHammasi tayyor!\n";
