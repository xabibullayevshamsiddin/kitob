<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Shop Asosiy Holati (Master Switch)
    |--------------------------------------------------------------------------
    */
    'enabled' => env('SHOP_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Ball -> Tanga Almashtirish Parametrlari (Exchange Settings)
    |--------------------------------------------------------------------------
    | Faqat bir tomonlama (Ball -> Tanga). Tanga -> Ball yo'li qat'iyan man etilgan.
    | - rate_points_per_coin: 5 ball = 1 tanga (o'qish va viktorina balansiga mos)
    | - min_points: Bir martalik almashtirish uchun minimal ball (50 ball = 10 tanga)
    | - max_points_per_day: Kunlik almashtirish mumkin bo'lgan maksimal ball (250 ball = 50 tanga)
    */
    'exchange' => [
        'enabled'              => env('SHOP_EXCHANGE_ENABLED', true),
        'rate_points_per_coin' => (int) env('SHOP_EXCHANGE_RATE', 15),
        'min_points'           => (int) env('SHOP_EXCHANGE_MIN_POINTS', 15),
        'max_points_per_day'   => (int) env('SHOP_EXCHANGE_DAILY_LIMIT', 300),
    ],

    /*
    |--------------------------------------------------------------------------
    | Xarid Xavfsizligi va Cheklovlar (Security & Throttling)
    |--------------------------------------------------------------------------
    */
    'throttle' => [
        'purchases_per_minute' => (int) env('SHOP_PURCHASE_THROTTLE', 10),
    ],

    /*
    |--------------------------------------------------------------------------
    | Jonli Efir Savollari Sozlamalari (Live Priority Questions)
    |--------------------------------------------------------------------------
    | Bitta foydalanuvchi bitta efirda ko'pi bilan nechta ustuvor savol berishi
    | mumkinligi (navbatni egallab olmasligi uchun standart: 2).
    */
    'max_priority_questions_per_live' => (int) env('SHOP_MAX_PRIORITY_QUESTIONS_PER_LIVE', 2),

    /*
    |--------------------------------------------------------------------------
    | Qo'llab-quvvatlanadigan Jihozlash Slotlari (Slots)
    |--------------------------------------------------------------------------
    */
    'slots' => [
        'avatar_frame',
        'profile_theme',
        'chat_bubble',
        'name_color',
        'title',
        'accent',
        'reader_palette',
        'highlight_color',
    ],

    /*
    |--------------------------------------------------------------------------
    | Nodirlik Darajalari (Rarity tiers)
    |--------------------------------------------------------------------------
    */
    'rarities' => [
        'common'    => ['label' => 'Oddiy', 'color' => '#94A3B8'],
        'rare'      => ['label' => 'Noyob', 'color' => '#3B82F6'],
        'epic'      => ['label' => 'Epik', 'color' => '#A855F7'],
        'legendary' => ['label' => 'Afsonaviy', 'color' => '#F59E0B'],
    ],

];
