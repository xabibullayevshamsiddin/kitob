<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Modullashtirilgan Marshrutlar Arxitekturasi
|--------------------------------------------------------------------------
|
| Ushbu fayl barcha kichik marshrut modullarini birlashtiradi:
|  - routes/web/public.php   : Ommaviy sahifalar, landing, qidiruv va katalog
|  - routes/web/student.php  : O'quvchi/Talaba xizmatlari (Dashboard, Shop, Mutolaa, Guruhlar, Jonli)
|  - routes/web/teacher.php  : O'qituvchi paneli (Teacher Dashboard, Kitoblar, Topshiriqlar)
|  - routes/web/admin.php    : Administrator boshqaruv paneli va analitika
|  - routes/web/telegram.php : Telegram Webhook, TMA, Bot 2FA va telefon autentifikatsiyasi
|
*/

// 1. Ommaviy marshrutlar (Public)
require __DIR__ . '/web/public.php';

// 2. Talaba & O'quvchi marshrutlari (Student & Shared Auth)
require __DIR__ . '/web/student.php';

// 3. O'qituvchi paneli (Teacher)
require __DIR__ . '/web/teacher.php';

// 4. Administrator paneli (Admin)
require __DIR__ . '/web/admin.php';

// 5. Telegram integratsiyasi, Webhook va 2FA (Telegram & Auth)
require __DIR__ . '/web/telegram.php';
