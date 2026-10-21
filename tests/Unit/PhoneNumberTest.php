<?php

namespace Tests\Unit;

use App\Support\PhoneNumber;
use PHPUnit\Framework\TestCase;

class PhoneNumberTest extends TestCase
{
    public function test_normalizes_uzbekistan_phone_numbers_in_various_formats(): void
    {
        // Standart E.164 formati
        $this->assertEquals('+998901234567', PhoneNumber::normalize('+998901234567'));

        // Bo'shliqlar va chiziqchalar bilan
        $this->assertEquals('+998901234567', PhoneNumber::normalize('+998 90 123-45-67'));
        $this->assertEquals('+998901234567', PhoneNumber::normalize('+998 (90) 123 45 67'));

        // Plyussiz 12 ta raqam
        $this->assertEquals('+998901234567', PhoneNumber::normalize('998901234567'));

        // Mahalliy 9 ta raqam
        $this->assertEquals('+998901234567', PhoneNumber::normalize('90 123 45 67'));
        $this->assertEquals('+998901234567', PhoneNumber::normalize('901234567'));

        // 8 bilan boshlanuvchi 10 ta raqam
        $this->assertEquals('+998901234567', PhoneNumber::normalize('8 90 123 45 67'));

        // 00 xalqaro prefiks bilan
        $this->assertEquals('+998901234567', PhoneNumber::normalize('00998901234567'));
    }

    public function test_normalizes_international_numbers(): void
    {
        $this->assertEquals('+14155552671', PhoneNumber::normalize('+1 (415) 555-2671'));
        $this->assertEquals('+447911123456', PhoneNumber::normalize('+44 7911 123456'));
    }

    public function test_returns_null_for_invalid_numbers(): void
    {
        $this->assertNull(PhoneNumber::normalize(null));
        $this->assertNull(PhoneNumber::normalize(''));
        $this->assertNull(PhoneNumber::normalize('abc'));
        $this->assertNull(PhoneNumber::normalize('12345')); // Juda qisqa
        $this->assertNull(PhoneNumber::normalize('+9989012345')); // O'zbekiston raqami uzunligi noto'g'ri
        $this->assertNull(PhoneNumber::normalize('+99890123456789')); // O'zbekiston raqami ortiqcha uzun
    }

    public function test_is_valid_helper(): void
    {
        $this->assertTrue(PhoneNumber::isValid('+998901234567'));
        $this->assertTrue(PhoneNumber::isValid('901234567'));
        $this->assertFalse(PhoneNumber::isValid('not-a-number'));
        $this->assertFalse(PhoneNumber::isValid(null));
    }

    public function test_masks_phone_number_correctly(): void
    {
        $this->assertEquals('+998 ** *** 45 67', PhoneNumber::mask('+998901234567'));
        $this->assertEquals('+998 ** *** 45 67', PhoneNumber::mask('998901234567'));
        $this->assertEquals('+998 ** *** 45 67', PhoneNumber::mask('90 123 45 67'));
    }

    public function test_formats_phone_number_for_display(): void
    {
        $this->assertEquals('+998 90 123 45 67', PhoneNumber::format('+998901234567'));
        $this->assertEquals('+998 90 123 45 67', PhoneNumber::format('901234567'));
    }
}
