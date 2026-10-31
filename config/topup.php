<?php

/*
|--------------------------------------------------------------------------
| Karta orqali tanga/ball sotib olish (P2P + chek + admin tasdig'i)
|--------------------------------------------------------------------------
| Bu yerdagilar — standart qiymatlar. Karta raqami, paketlar va limitlar
| admin panelidagi "To'lovlar → Sozlamalar" sahifasidan o'zgartiriladi (settings jadvali).
*/

return [

    // Standart paketlar (admin panelda tahrirlanmaguncha ishlatiladi)
    'default_packages' => [
        ['key' => 'coins_100',  'title' => '100 tanga',   'price' => 5000,  'coins' => 100,  'points' => 0,    'active' => true],
        ['key' => 'coins_250',  'title' => '250 tanga',   'price' => 10000, 'coins' => 250,  'points' => 0,    'active' => true],
        ['key' => 'coins_700',  'title' => '700 tanga',   'price' => 25000, 'coins' => 700,  'points' => 0,    'active' => true],
        ['key' => 'points_500', 'title' => '500 ball',    'price' => 10000, 'coins' => 0,    'points' => 500,  'active' => true],
        ['key' => 'points_1500','title' => '1 500 ball',  'price' => 25000, 'coins' => 0,    'points' => 1500, 'active' => true],
    ],

    // Bitta foydalanuvchi bir kunda (Toshkent vaqti) to'lashi mumkin bo'lgan maksimal summa, so'm
    'daily_limit' => 200000,

    // To'lov so'rovi (summa band qilinishi) qancha soat amal qiladi
    'request_ttl_hours' => 24,

    // Unikal summa uchun qo'shiladigan tasodifiy so'm oralig'i (admin SMS bo'yicha aniq moslashtirishi uchun)
    'amount_offset_min' => 1,
    'amount_offset_max' => 99,

    // Chek fayli
    'receipt_max_kb' => 6144,

    // Perceptual hash: shu masofagacha bo'lgan rasmlar "o'xshash chek" deb belgilanadi (0..64)
    'similar_hash_distance' => 6,

    // AI tahlil (Gemini vision). Chek rasmlari Google'ga yuboriladi.
    'ai_enabled' => (bool) env('TOPUP_AI_ENABLED', true),
    'ai_model'   => env('TOPUP_AI_MODEL', env('GEMINI_CHAT_MODEL', 'gemini-2.5-flash')),
    'ai_timeout' => (int) env('TOPUP_AI_TIMEOUT', 25),
];
