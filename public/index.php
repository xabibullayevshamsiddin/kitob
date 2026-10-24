<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

/*
|--------------------------------------------------------------------------
| Check If The Application Is Under Maintenance
|--------------------------------------------------------------------------
|
| If the application is in maintenance / demo mode via the "down" command
| we will load this file so that any pre-rendered content can be shown
| instead of starting the framework, which could cause an exception.
|
*/

if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    $isExempt = preg_match('#/(admin|login|logout|admin-bypass)(\b|/|\?)#i', $uri) || isset($_COOKIE['laravel_maintenance']);
    if (! $isExempt) {
        require $maintenance;
    }
}

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader for
| this application. We'll attempt to load it from the root vendor directory
| or common deployment locations. If missing, render a friendly guide.
|
*/

$autoloadCandidates = [
    __DIR__ . '/../vendor/autoload.php',
    __DIR__ . '/vendor/autoload.php',
    dirname(__DIR__, 2) . '/vendor/autoload.php',
];

$autoloadPath = null;
foreach ($autoloadCandidates as $candidate) {
    if (file_exists($candidate)) {
        $autoloadPath = $candidate;
        break;
    }
}

if (! $autoloadPath) {
    http_response_code(503);
    header('Content-Type: text/html; charset=utf-8');
    ?>
    <!DOCTYPE html>
    <html lang="uz">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Kitobxon — Vendor kutubxonalari topilmadi</title>
        <style>
            * { box-sizing: border-box; margin: 0; padding: 0; }
            body {
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
                background-color: #0b0f19;
                color: #f1f5f9;
                display: flex;
                align-items: center;
                justify-content: center;
                min-height: 100vh;
                padding: 24px;
            }
            .card {
                background: #111827;
                border: 1px solid #1f2937;
                border-radius: 16px;
                max-width: 680px;
                width: 100%;
                padding: 32px;
                box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5), 0 8px 10px -6px rgba(0, 0, 0, 0.5);
            }
            .badge {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                background: rgba(245, 158, 11, 0.15);
                color: #fbbf24;
                border: 1px solid rgba(245, 158, 11, 0.3);
                font-size: 11px;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                padding: 4px 10px;
                border-radius: 9999px;
                margin-bottom: 16px;
            }
            h1 { font-size: 22px; font-weight: 700; color: #ffffff; margin-bottom: 8px; }
            p { font-size: 14px; line-height: 1.6; color: #94a3b8; margin-bottom: 16px; }
            .box {
                background: #0b0f19;
                border: 1px solid #1e293b;
                border-radius: 10px;
                padding: 16px;
                margin-bottom: 18px;
            }
            .box-title {
                font-size: 13px;
                font-weight: 700;
                color: #e2e8f0;
                margin-bottom: 8px;
                display: flex;
                align-items: center;
                gap: 6px;
            }
            code {
                display: block;
                background: #030712;
                border: 1px solid #1f2937;
                color: #38bdf8;
                font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
                font-size: 13px;
                padding: 10px 14px;
                border-radius: 8px;
                overflow-x: auto;
                user-select: all;
            }
            ol { padding-left: 20px; color: #cbd5e1; font-size: 13px; line-height: 1.6; }
            li { margin-bottom: 10px; }
            .muted { color: #64748b; font-size: 12px; margin-top: 16px; text-align: center; }
        </style>
    </head>
    <body>
        <div class="card">
            <span class="badge">⚠️ Server sozlamasi / Deploy</span>
            <h1>Vendor kutubxonalari topilmadi</h1>
            <p>
                Loyiha serverga (hostingga) yuklangan, biroq <code>vendor/autoload.php</code> fayli topilmadi.
                Git orqali yuklanganda xavfsizlik va hajm sababli <code>vendor</code> papkasi avtomatik yuklanmaydi.
            </p>

            <div class="box">
                <div class="box-title">⚡ 1-yechim: Terminal / SSH orqali (Tavsiya etiladi)</div>
                <p style="margin-bottom: 8px; font-size: 12px;">Server konsolida loyiha ildiz papkasiga kiring va ushbu buyruqni ishga tushiring:</p>
                <code>composer install --no-dev --optimize-autoloader</code>
            </div>

            <div class="box">
                <div class="box-title">📦 2-yechim: File Manager (Pterodactyl / cPanel) orqali</div>
                <ol>
                    <li>Lokal kompyuteringizdagi loyiha ichidagi <strong>vendor</strong> papkasini arxivlang (masalan, <code>vendor.zip</code>).</li>
                    <li>Serverdagi <strong>/home/container/</strong> (loyiha ildiz) papkasiga yuklang.</li>
                    <li>Hosting panelida faylni tanlab <strong>"Unarchive" (Extract)</strong> qiling. <code>/home/container/vendor/autoload.php</code> fayli paydo bo'ladi.</li>
                </ol>
            </div>

            <div class="box" style="margin-bottom: 0;">
                <div class="box-title">🔑 3-yechim: Papkalar ruxsatini berish</div>
                <p style="margin-bottom: 8px; font-size: 12px;">Storage va kesh papkalariga yozish huquqini ta'minlang:</p>
                <code>chmod -R 775 storage bootstrap/cache</code>
            </div>

            <p class="muted">Kitobxon Platformasi &bull; Laravel Framework</p>
        </div>
    </body>
    </html>
    <?php
    exit;
}

require $autoloadPath;

/*
|--------------------------------------------------------------------------
| Run The Application
|--------------------------------------------------------------------------
|
| Once we have the application, we can handle the incoming request using
| the application's HTTP kernel. Then, we will send the response back
| to this client's browser, allowing them to enjoy our application.
|
*/

$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Kernel::class);

$response = $kernel->handle(
    $request = Request::capture()
)->send();

$kernel->terminate($request, $response);
