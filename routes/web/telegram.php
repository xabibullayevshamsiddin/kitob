<?php

use Illuminate\Support\Facades\Route;

// Telegram Bot Webhook
Route::post('/telegram/webhook', [\App\Http\Controllers\Telegram\TelegramWebhookController::class, 'handle'])
    ->name('telegram.webhook');

// Telegram Auth & Account Linking
Route::get('/auth/telegram/callback', [\App\Http\Controllers\Auth\TelegramAuthController::class, 'callback'])
    ->name('auth.telegram.callback')
    ->middleware('throttle:30,1');

Route::get('/auth/telegram/redirect', [\App\Http\Controllers\Auth\TelegramAuthController::class, 'redirect'])
    ->name('auth.telegram.redirect')
    ->middleware('throttle:30,1');

Route::get('/auth/telegram/waiting/{token}', [\App\Http\Controllers\Auth\TelegramAuthController::class, 'waiting'])
    ->name('auth.telegram.waiting');

Route::get('/auth/telegram/check/{token}', [\App\Http\Controllers\Auth\TelegramAuthController::class, 'check'])
    ->name('auth.telegram.check');

Route::get('/auth/telegram/complete/{token}', [\App\Http\Controllers\Auth\TelegramAuthController::class, 'complete'])
    ->name('auth.telegram.complete');

Route::post('/auth/telegram/unlink', [\App\Http\Controllers\Auth\TelegramAuthController::class, 'unlink'])
    ->name('auth.telegram.unlink')
    ->middleware('auth');

Route::get('/auth/telegram/verify-phone', [\App\Http\Controllers\Auth\TelegramAuthController::class, 'verifyPhone'])
    ->name('auth.telegram.verify-phone')
    ->middleware('auth');

Route::post('/settings/telegram/notifications', [\App\Http\Controllers\Auth\TelegramAuthController::class, 'updateNotifications'])
    ->name('settings.telegram.notifications')
    ->middleware('auth');

// Parolni tiklash (Telefon, Telegram yoki Email orqali)
Route::post('/forgot-password', [\App\Http\Controllers\Auth\PasswordResetController::class, 'sendResetLink'])
    ->middleware('guest')
    ->name('password.email');

// Telegram Mini App (TMA) — /tg/app va /tg-app manzillari qo'llab-quvvatlanadi
Route::get('/tg/app', [\App\Http\Controllers\Telegram\TelegramMiniAppController::class, 'index'])
    ->name('tg.app');

Route::post('/tg/app/auth', [\App\Http\Controllers\Telegram\TelegramMiniAppController::class, 'authenticate'])
    ->name('tg.app.auth');

Route::get('/tg-app', [\App\Http\Controllers\Telegram\TelegramMiniAppController::class, 'index'])
    ->name('tg-app');

Route::post('/tg-app/auth', [\App\Http\Controllers\Telegram\TelegramMiniAppController::class, 'authenticate'])
    ->name('tg-app.auth');

// Telefon orqali ro'yxatdan o'tish va Telegram orqali tasdiqlash
Route::post('/register', [\App\Http\Controllers\Auth\PhoneRegisterController::class, 'register'])
    ->middleware(['guest', 'throttle:10,1']);

Route::get('/register/verify/{token}', [\App\Http\Controllers\Auth\PhoneRegisterController::class, 'showVerify'])
    ->name('register.verify')
    ->middleware('guest');

Route::get('/register/check/{token}', [\App\Http\Controllers\Auth\PhoneRegisterController::class, 'checkVerify'])
    ->name('register.check')
    ->middleware('guest');

// Telefon + Parol kiritilganda Telegram orqali 6 xonali kod bilan kirish (2FA Login)
// throttle:login — FortifyServiceProvider dagi limiter (login + IP bo'yicha 5/daqiqa), parolni brute-force qilishdan himoya
Route::post('/login', [\App\Http\Controllers\Auth\PhoneLoginController::class, 'login'])
    ->middleware(['guest', 'throttle:login']);

Route::get('/login/verify-code/{token}', [\App\Http\Controllers\Auth\PhoneLoginController::class, 'showVerifyCode'])
    ->name('login.verify-code')
    ->middleware('guest');

Route::post('/login/verify-code/{token}', [\App\Http\Controllers\Auth\PhoneLoginController::class, 'verifyCode'])
    ->name('login.verify-code.submit')
    ->middleware('guest');

Route::post('/login/resend-code/{token}', [\App\Http\Controllers\Auth\PhoneLoginController::class, 'resendCode'])
    ->name('login.resend-code')
    ->middleware('guest');




