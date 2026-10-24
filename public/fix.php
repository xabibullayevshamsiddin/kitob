<?php
// Quick Livewire Asset Fixer for Host
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

    echo "<h1>✅ Livewire muvaffaqiyatli o'rnatildi!</h1>";
    echo "<p>public/livewire/livewire.js hajmi: " . filesize($target . '/livewire.js') . " bayt</p>";
    echo "<p><a href='/groups'>Guruhlar sahifasiga qaytish →</a></p>";
} else {
    echo "<h1>❌ Xatolik: vendor/livewire topilmadi!</h1>";
}
