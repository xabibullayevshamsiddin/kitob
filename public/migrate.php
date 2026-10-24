<?php
// Emergency web-migrator for shared/container hosting
define('LARAVEL_START', microtime(true));

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="utf-8">
    <title>Kitobxon — Baza migratsiyasi va sozlash</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #0f172a; color: #f8fafc; padding: 30px; margin: 0; }
        .card { max-width: 800px; margin: 0 auto; background: #1e293b; border-radius: 12px; padding: 28px; box-shadow: 0 10px 25px rgba(0,0,0,0.5); border: 1px solid #334155; }
        h1 { color: #38bdf8; font-size: 20px; margin-top: 0; margin-bottom: 16px; }
        pre { background: #0b0f19; color: #4ade80; padding: 18px; border-radius: 8px; overflow-x: auto; font-size: 13px; line-height: 1.6; border: 1px solid #1e293b; font-family: monospace; }
        .btn { display: inline-block; padding: 12px 24px; background: #f59e0b; color: #0f172a; text-decoration: none; border-radius: 6px; font-weight: bold; margin-top: 16px; font-size: 14px; }
        .btn:hover { background: #d97706; }
    </style>
</head>
<body>
<div class="card">
    <h1>🚀 Kitobxon Ma'lumotlar Bazasini Sozlash</h1>
    <pre>
<?php
try {
    echo "1. Ma'lumotlar bazasiga ulanish tekshirilmoqda...\n";
    \Illuminate\Support\Facades\DB::connection()->getPdo();
    $dbName = \Illuminate\Support\Facades\DB::connection()->getDatabaseName();
    echo "✅ Baza ulandi: " . $dbName . "\n\n";

    echo "2. Jadvallarni yaratish (php artisan migrate --force)...\n";
    $kernel->call('migrate', ['--force' => true]);
    echo $kernel->output();

    echo "\n3. Boshlang'ich ma'lumotlarni kiritish (php artisan db:seed --force)...\n";
    $kernel->call('db:seed', ['--force' => true]);
    echo $kernel->output();

    echo "\n4. Livewire JS assetlarini diskka joylashtirish...\n";
    $livewirePubDir = __DIR__ . '/livewire';
    $livewireDistDir = __DIR__ . '/../vendor/livewire/livewire/dist';
    if (is_dir($livewireDistDir)) {
        @mkdir($livewirePubDir, 0777, true);
        @copy($livewireDistDir . '/livewire.js', $livewirePubDir . '/livewire.js');
        @copy($livewireDistDir . '/livewire.js.map', $livewirePubDir . '/livewire.js.map');
        @copy($livewireDistDir . '/manifest.json', $livewirePubDir . '/manifest.json');
        echo "✅ Livewire JS aktivlashtirildi: public/livewire/livewire.js\n";
    }

    echo "\n5. Keshni tozalash...\n";
    $kernel->call('optimize:clear');
    echo $kernel->output();

    echo "\n🎉 BARCHASI MUVAFFAQIYATLI YAKUNLANDI!\n";
} catch (\Throwable $e) {
    echo "❌ XATOLIK YUZ BERDI:\n" . $e->getMessage() . "\n";
}
?>
    </pre>
    <p style="margin-top: 16px; color: #94a3b8; font-size: 13px;">
        Endi bemalol bosh sahifaga o'tishingiz mumkin:
    </p>
    <a href="/" class="btn">Bosh sahifaga o'tish →</a>
</div>
</body>
</html>
