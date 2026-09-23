<?php
require __DIR__ . '/vendor/autoload.php';

// phpunit.xml muhitini takrorlaymiz
$_ENV['APP_ENV'] = 'testing';
$_ENV['APP_URL'] = 'http://localhost';
$_ENV['BCRYPT_ROUNDS'] = '4';
$_ENV['CACHE_DRIVER'] = 'array';
$_ENV['MAIL_MAILER'] = 'array';
$_ENV['QUEUE_CONNECTION'] = 'sync';
$_ENV['SESSION_DRIVER'] = 'array';
$_ENV['TELESCOPE_ENABLED'] = 'false';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle($req = Illuminate\Http\Request::create('/login', 'GET'));
echo 'STATUS: ' . $response->getStatusCode() . PHP_EOL;
$content = $response->getContent();
if (preg_match('/<title>(.*?)<\/title>/s', $content, $m)) {
    echo 'TITLE: ' . trim(strip_tags($m[1])) . PHP_EOL;
}
echo substr($content, 0, 400) . PHP_EOL;
