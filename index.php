<?php
// Auto redirect to public/
$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? ''
);

if ($uri === '/Kitob' || $uri === '/Kitob/') {
    header('Location: /Kitob/public/', true, 301);
    exit;
}

require_once __DIR__ . '/public/index.php';
