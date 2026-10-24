<?php
// Comprehensive Server Repair for Livewire & HTTPS
header('Content-Type: text/html; charset=utf-8');

echo "<div style='font-family:sans-serif;max-width:700px;margin:30px auto;background:#0f172a;color:#f8fafc;padding:24px;border-radius:12px;'>";
echo "<h1 style='color:#38bdf8;'>🔧 Serverni Sozlash va Livewire'ni Faollashtirish</h1><pre style='background:#1e293b;color:#4ade80;padding:16px;border-radius:8px;'>";

// 1. Fix .env file
$envPath = __DIR__ . '/../.env';
if (file_exists($envPath)) {
    $envContent = file_get_contents($envPath);
    // Force APP_URL to https://kitobxon.elitehost.uz
    $envContent = preg_replace('/^APP_URL=.*$/m', 'APP_URL=https://kitobxon.elitehost.uz', $envContent);
    // Set LIVEWIRE_ASSET_URL to https://kitobxon.elitehost.uz
    if (preg_match('/^LIVEWIRE_ASSET_URL=.*$/m', $envContent)) {
        $envContent = preg_replace('/^LIVEWIRE_ASSET_URL=.*$/m', 'LIVEWIRE_ASSET_URL=https://kitobxon.elitehost.uz', $envContent);
    } else {
        $envContent .= "\nLIVEWIRE_ASSET_URL=https://kitobxon.elitehost.uz\n";
    }
    // Set LIVEWIRE_APP_URL to https://kitobxon.elitehost.uz
    if (preg_match('/^LIVEWIRE_APP_URL=.*$/m', $envContent)) {
        $envContent = preg_replace('/^LIVEWIRE_APP_URL=.*$/m', 'LIVEWIRE_APP_URL=https://kitobxon.elitehost.uz', $envContent);
    } else {
        $envContent .= "\nLIVEWIRE_APP_URL=https://kitobxon.elitehost.uz\n";
    }
    file_put_contents($envPath, $envContent);
    echo "1. ✅ .env fayli yangilandi: APP_URL=https://kitobxon.elitehost.uz va LIVEWIRE_ASSET_URL=https://kitobxon.elitehost.uz\n";
} else {
    echo "1. ⚠️ .env fayli topilmadi!\n";
}

// 2. Ensure Livewire static JS assets exist
$dist = __DIR__ . '/../vendor/livewire/livewire/dist';
$target = __DIR__ . '/livewire';
$vendorTarget = __DIR__ . '/vendor/livewire';
@mkdir($target, 0777, true);
@mkdir($vendorTarget, 0777, true);

if (file_exists($dist . '/livewire.js')) {
    @copy($dist . '/livewire.js', $target . '/livewire.js');
    @copy($dist . '/livewire.js.map', $target . '/livewire.js.map');
    @copy($dist . '/manifest.json', $target . '/manifest.json');
    @copy($dist . '/livewire.js', $vendorTarget . '/livewire.js');
    @copy($dist . '/livewire.js.map', $vendorTarget . '/livewire.js.map');
    echo "2. ✅ public/livewire/livewire.js nusxalandi (" . filesize($target . '/livewire.js') . " bayt)\n";
} else {
    echo "2. ⚠️ vendor/livewire/dist topilmadi!\n";
}

// 3. Clear bootstrap caches
$cacheFiles = [
    __DIR__ . '/../bootstrap/cache/config.php',
    __DIR__ . '/../bootstrap/cache/routes-v7.php',
    __DIR__ . '/../bootstrap/cache/routes.php',
    __DIR__ . '/../bootstrap/cache/services.php',
    __DIR__ . '/../bootstrap/cache/packages.php',
];
foreach ($cacheFiles as $cf) {
    if (file_exists($cf)) {
        @unlink($cf);
        echo "3. 🧹 O'chirildi: " . basename($cf) . "\n";
    }
}

// 4. Clear compiled blade views
$views = glob(__DIR__ . '/../storage/framework/views/*.php');
if ($views) {
    foreach ($views as $v) {
        @unlink($v);
    }
    echo "4. 🧹 " . count($views) . " ta keshdagi Blade shablonlari tozalandi\n";
}

// 5. Force HTTPS in AppServiceProvider if not already present
$providerPath = __DIR__ . '/../app/Providers/AppServiceProvider.php';
if (file_exists($providerPath)) {
    $providerContent = file_get_contents($providerPath);
    if (! str_contains($providerContent, "URL::forceScheme('https')")) {
        $providerContent = str_replace(
            "public function boot()",
            "public function boot()\n    {\n        \\Illuminate\\Support\\Facades\\URL::forceScheme('https');",
            $providerContent
        );
        file_put_contents($providerPath, $providerContent);
        echo "5. ✅ AppServiceProvider.php faylida URL::forceScheme('https') o'rnatildi\n";
    } else {
        echo "5. ✅ AppServiceProvider.php da HTTPS allaqachon faol\n";
    }
}

// 6. Fix config/livewire.php if present
$livewireConfig = __DIR__ . '/../config/livewire.php';
if (file_exists($livewireConfig)) {
    $lwContent = file_get_contents($livewireConfig);
    $lwContent = str_replace(
        "env('LIVEWIRE_ASSET_URL', rtrim(env('APP_URL', ''), '/'))",
        "env('LIVEWIRE_ASSET_URL', 'https://kitobxon.elitehost.uz')",
        $lwContent
    );
    $lwContent = str_replace(
        "env('LIVEWIRE_APP_URL', env('APP_URL', ''))",
        "env('LIVEWIRE_APP_URL', 'https://kitobxon.elitehost.uz')",
        $lwContent
    );
    file_put_contents($livewireConfig, $lwContent);
    echo "6. ✅ config/livewire.php fayli HTTPS uchun yangilandi\n";
}

echo "\n🎉 BARCHA SOZLAMALAR MUVAFFAQIYATLI YAKUNLANDI!\n";
echo "</pre><p><a href='/groups' style='display:inline-block;padding:12px 20px;background:#f59e0b;color:#000;text-decoration:none;font-weight:bold;border-radius:6px;'>Guruhlar sahifasiga o'tish →</a></p></div>";
