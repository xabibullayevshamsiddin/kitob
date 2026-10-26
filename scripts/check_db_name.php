<?php
// Bootstrap Laravel and check/update settings
define('LARAVEL_START', microtime(true));
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$current = DB::table('settings')->where('key', 'app_name')->value('value');
echo "Current app_name: " . $current . "\n";

if ($current !== 'Davra_books') {
    DB::table('settings')->where('key', 'app_name')->update(['value' => 'Davra_books']);
    $new = DB::table('settings')->where('key', 'app_name')->value('value');
    echo "Updated to: " . $new . "\n";
} else {
    echo "Already correct: Davra_books\n";
}
