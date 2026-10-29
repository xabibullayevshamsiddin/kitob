<?php
// Update TELEGRAM_BOT_TOKEN and TELEGRAM_BOT_USERNAME in .env
// Usage: php scripts/update_bot_token.php <token> [username]
$newToken = $argv[1] ?? null;
$newUsername = $argv[2] ?? null;

if (!$newToken) {
    die("Usage: php scripts/update_bot_token.php <token> [username]\n");
}

$envPath = __DIR__ . '/../.env';
if (!file_exists($envPath)) {
    die("❌ .env fayli topilmadi: {$envPath}\n");
}

$content = file_get_contents($envPath);

$content = preg_replace(
    '/^TELEGRAM_BOT_TOKEN=.*/m',
    'TELEGRAM_BOT_TOKEN=' . $newToken,
    $content
);

if ($newUsername) {
    $content = preg_replace(
        '/^TELEGRAM_BOT_USERNAME=.*/m',
        'TELEGRAM_BOT_USERNAME=' . $newUsername,
        $content
    );
}

file_put_contents($envPath, $content);
echo "✅ .env yangilandi!\n";

// Verify
preg_match('/^TELEGRAM_BOT_TOKEN=(.+)$/m', $content, $m1);
preg_match('/^TELEGRAM_BOT_USERNAME=(.+)$/m', $content, $m2);
echo "TOKEN: " . ($m1[1] ?? 'NOT FOUND') . "\n";
echo "USERNAME: " . ($m2[1] ?? 'NOT FOUND') . "\n";
