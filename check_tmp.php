<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$u = App\Models\User::withTrashed()->where('email', 'redirect_test@kitobxon.uz')->first();
echo 'User: ' . ($u ? $u->username . ' (id ' . $u->id . ', deleted=' . ($u->deleted_at ? 'YES' : 'no') . ')' : 'NOT FOUND') . PHP_EOL;
if ($u && $u->profile) { echo 'profile reading_place: [' . $u->profile->reading_place . ']' . PHP_EOL; } else { echo 'profile: none' . PHP_EOL; }
