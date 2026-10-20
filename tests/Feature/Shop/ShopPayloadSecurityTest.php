<?php

namespace Tests\Feature\Shop;

use App\Models\ShopItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShopPayloadSecurityTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
        ]);
    }

    /**
     * Payload JSON orqali noto'g'ri ranglar (masalan "red", "background:url(...)") rad etiladi.
     */
    public function test_admin_form_rejects_invalid_hex_colors_in_payload_json(): void
    {
        // 1. "red" so'zi kabi noto'g'ri rang rad etiladi
        $response1 = $this->actingAs($this->admin)->post(route('admin.shop.items.store'), [
            'key'          => 'bad_color_1',
            'category'     => 'colors',
            'slot'         => 'name_color',
            'name_uz'      => 'Noto‘g‘ri rang 1',
            'price_coins'  => 50,
            'rarity'       => 'common',
            'max_quantity' => 1,
            'payload_json' => json_encode(['color' => 'red']),
        ]);

        $response1->assertSessionHasErrors('payload_json');
        $this->assertDatabaseMissing('shop_items', ['key' => 'bad_color_1']);

        // 2. CSS inyeksiyasi (background:url(...)) rad etiladi
        $response2 = $this->actingAs($this->admin)->post(route('admin.shop.items.store'), [
            'key'          => 'bad_color_2',
            'category'     => 'colors',
            'slot'         => 'name_color',
            'name_uz'      => 'Noto‘g‘ri rang 2',
            'price_coins'  => 50,
            'rarity'       => 'common',
            'max_quantity' => 1,
            'payload_json' => json_encode(['color' => 'background:url(https://evil.com/x.jpg)']),
        ]);

        $response2->assertSessionHasErrors('payload_json');
        $this->assertDatabaseMissing('shop_items', ['key' => 'bad_color_2']);
    }

    /**
     * Oq ro'yxatda (whitelist) bo'lmagan noma'lum kalitlar rad etiladi.
     */
    public function test_admin_form_rejects_unknown_payload_keys(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.shop.items.store'), [
            'key'          => 'unknown_key_item',
            'category'     => 'perk',
            'slot'         => null,
            'name_uz'      => 'Noma‘lum kalit',
            'price_coins'  => 50,
            'rarity'       => 'common',
            'max_quantity' => 1,
            'payload_json' => json_encode(['hacked_column' => 'malicious_code']),
        ]);

        $response->assertSessionHasErrors('payload_json');
        $this->assertDatabaseMissing('shop_items', ['key' => 'unknown_key_item']);
    }

    /**
     * Diapazondan tashqaridagi sonli parametrlar rad etiladi.
     */
    public function test_admin_form_rejects_out_of_range_numbers(): void
    {
        // Multiplier > 5.0
        $response1 = $this->actingAs($this->admin)->post(route('admin.shop.items.store'), [
            'key'          => 'high_mult',
            'category'     => 'perk',
            'slot'         => null,
            'name_uz'      => 'Yuqori koeffitsient',
            'price_coins'  => 50,
            'rarity'       => 'common',
            'max_quantity' => 1,
            'payload_json' => json_encode(['multiplier' => 9.9, 'effect' => 'coin_booster']),
        ]);
        $response1->assertSessionHasErrors('payload_json');

        // Amount < 1
        $response2 = $this->actingAs($this->admin)->post(route('admin.shop.items.store'), [
            'key'          => 'zero_amount',
            'category'     => 'perk',
            'slot'         => null,
            'name_uz'      => 'Nol miqdor',
            'price_coins'  => 50,
            'rarity'       => 'common',
            'max_quantity' => 1,
            'payload_json' => json_encode(['amount' => 0, 'effect' => 'ai_questions']),
        ]);
        $response2->assertSessionHasErrors('payload_json');

        // Duration minutes > 1440
        $response3 = $this->actingAs($this->admin)->post(route('admin.shop.items.store'), [
            'key'          => 'huge_duration',
            'category'     => 'perk',
            'slot'         => null,
            'name_uz'      => 'Katta muddat',
            'price_coins'  => 50,
            'rarity'       => 'common',
            'max_quantity' => 1,
            'payload_json' => json_encode(['duration_minutes' => 5000, 'effect' => 'coin_booster']),
        ]);
        $response3->assertSessionHasErrors('payload_json');
    }

    /**
     * Payload hajmi 2 KB dan oshganda rad etiladi.
     */
    public function test_admin_form_rejects_oversized_payload_json(): void
    {
        $largeText = str_repeat('a', 2500);
        $response = $this->actingAs($this->admin)->post(route('admin.shop.items.store'), [
            'key'          => 'oversized_item',
            'category'     => 'perk',
            'slot'         => null,
            'name_uz'      => 'Katta payload',
            'price_coins'  => 50,
            'rarity'       => 'common',
            'max_quantity' => 1,
            'payload_json' => json_encode(['icon' => $largeText]),
        ]);

        $response->assertSessionHasErrors('payload_json');
        $this->assertDatabaseMissing('shop_items', ['key' => 'oversized_item']);
    }

    /**
     * ShopItem::safeColor xavfli/noto'g'ri qiymatlarni zaxira (fallback) rangga xavfsiz almashtiradi.
     */
    public function test_safe_color_helper_falls_back_on_malicious_or_invalid_values(): void
    {
        $item = new ShopItem([
            'payload' => [
                'color'        => 'red; background:url(hack.jpg)',
                'bg_dark'      => 'javascript:alert(1)',
                'accent_color' => '#10B981', // to'g'ri HEX
            ],
        ]);

        // Noto'g'ri rang zaxiraga almashadi
        $this->assertEquals('#F59E0B', $item->safeColor('color', '#F59E0B'));
        $this->assertEquals('#07090E', $item->safeColor('bg_dark', '#07090E'));

        // To'g'ri HEX esa to'liq qaytadi
        $this->assertEquals('#10B981', $item->safeColor('accent_color', '#F59E0B'));
    }
}
