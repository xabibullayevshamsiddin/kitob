<?php
require_once 'D:/OSPanel/domains/localhost/Kitob/scripts/elitehost.php';

// Check users in remote DB
$res = EliteHost::request('POST', '/servers/b3357df7/command', [
    'command' => "php artisan tinker --execute=\"echo 'USERS_LIST: ' . json_encode(App\Models\User::all(['id', 'name', 'email', 'phone', 'username', 'coin_balance'])->toArray());\""
]);

echo "Command sent to EliteHost.\n";
