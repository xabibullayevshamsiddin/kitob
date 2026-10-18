<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$urls = [
    '/',
    '/catalog',
    '/leaderboard',
    '/chat',
    '/about',
    '/faq',
    '/contact',
];

$locales = ['uz', 'ru', 'en'];

foreach ($locales as $loc) {
    echo "================ Testing Locale: [{$loc}] ================\n";
    foreach ($urls as $url) {
        $request = Illuminate\Http\Request::create($url . '?lang=' . $loc, 'GET');
        $response = $kernel->handle($request);
        $status = $response->getStatusCode();
        $content = $response->getContent();

        $hasHtmlLang = strpos($content, '<html lang="' . $loc . '"') !== false;
        echo "URL: {$url} -> Status: {$status} | html lang='{$loc}': " . ($hasHtmlLang ? 'YES' : 'NO') . PHP_EOL;
        $kernel->terminate($request, $response);
    }
}
