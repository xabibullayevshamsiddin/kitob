<?php

namespace App\Support;

class PhoneNumber
{
    /**
     * Telefon raqamini xalqaro E.164 formatiga keltiradi (+998901234567).
     * Noto'g'ri bo'lsa null qaytaradi.
     */
    public static function normalize(?string $phone): ?string
    {
        if ($phone === null) {
            return null;
        }

        $cleaned = trim($phone);
        if ($cleaned === '') {
            return null;
        }

        // Barcha bo'shliqlar, qavslar, chiziqchalarni tozalaymiz
        $cleaned = preg_replace('/[^\d+]/', '', $cleaned);

        if (empty($cleaned)) {
            return null;
        }

        // 00 bilan boshlansa (xalqaro prefiks), '+' ga almashtiramiz
        if (str_starts_with($cleaned, '00')) {
            $cleaned = '+' . substr($cleaned, 2);
        }

        // '+' belgisi bilan boshlanmagan bo'lsa
        if (!str_starts_with($cleaned, '+')) {
            $digitsOnly = preg_replace('/\D/', '', $cleaned);

            // Agar 9 ta raqam bo'lsa (masalan, 901234567) -> O'zbekiston raqami
            if (strlen($digitsOnly) === 9) {
                $cleaned = '+998' . $digitsOnly;
            }
            // Agar 12 ta raqam bo'lib, 998 bilan boshlansa
            elseif (strlen($digitsOnly) === 12 && str_starts_with($digitsOnly, '998')) {
                $cleaned = '+' . $digitsOnly;
            }
            // Agar 8 bilan boshlanadigan 10 raqamli bo'lsa (8 90 1234567)
            elseif (strlen($digitsOnly) === 10 && str_starts_with($digitsOnly, '8')) {
                $cleaned = '+998' . substr($digitsOnly, 1);
            } else {
                // Boshqa xalqaro raqamlar
                $cleaned = '+' . $digitsOnly;
            }
        }

        // Faqat bitta '+' va qolgani raqamlar ekanini tekshiramiz
        if (!preg_match('/^\+[1-9]\d{7,14}$/', $cleaned)) {
            return null;
        }

        // Agar O'zbekiston raqami bo'lsa (+998), jami 12 ta raqam bo'lishi shart (+ va 12 raqam = 13 belgi)
        if (str_starts_with($cleaned, '+998')) {
            if (strlen($cleaned) !== 13) {
                return null;
            }
        }

        return $cleaned;
    }

    /**
     * Raqam to'g'ri E.164 formati yoki to'g'rilanishi mumkin ekanligini tekshiradi.
     */
    public static function isValid(?string $phone): bool
    {
        return static::normalize($phone) !== null;
    }

    /**
     * O'zbekiston raqami ekanligini tekshiradi.
     */
    public static function isUzbekistan(?string $phone): bool
    {
        $normalized = static::normalize($phone);
        return $normalized !== null && str_starts_with($normalized, '+998') && strlen($normalized) === 13;
    }

    /**
     * Maxfiy ma'lumotlarni yashirgan holda ko'rsatish:
     * Masalan: +998901234567 -> +998 ** *** 45 67
     */
    public static function mask(?string $phone): string
    {
        $normalized = static::normalize($phone);
        if ($normalized === null) {
            return $phone ?? '';
        }

        // O'zbekiston raqami: +998 AA BBB CC DD -> +998 ** *** CC DD
        if (str_starts_with($normalized, '+998') && strlen($normalized) === 13) {
            $lastFour = substr($normalized, -4); // CC DD
            $c1 = substr($lastFour, 0, 2);
            $c2 = substr($lastFour, 2, 2);
            return "+998 ** *** {$c1} {$c2}";
        }

        // Boshqa xalqaro raqamlar: birinchi 4 va oxirgi 2 belgini qoldirib, o'rtasini yashiramiz
        $len = strlen($normalized);
        if ($len <= 6) {
            return $normalized;
        }

        $prefix = substr($normalized, 0, 4);
        $suffix = substr($normalized, -2);
        $maskedLength = $len - 6;

        return $prefix . ' ' . str_repeat('*', min(6, $maskedLength)) . ' ' . $suffix;
    }

    /**
     * Chiroyli formatda ko'rsatish (+998 90 123 45 67).
     */
    public static function format(?string $phone): string
    {
        $normalized = static::normalize($phone);
        if ($normalized === null) {
            return $phone ?? '';
        }

        if (str_starts_with($normalized, '+998') && strlen($normalized) === 13) {
            $code = substr($normalized, 4, 2);
            $part1 = substr($normalized, 6, 3);
            $part2 = substr($normalized, 9, 2);
            $part3 = substr($normalized, 11, 2);
            return "+998 {$code} {$part1} {$part2} {$part3}";
        }

        return $normalized;
    }
}
