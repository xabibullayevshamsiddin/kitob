<?php

namespace Tests\Feature\Shop;

use App\Models\ShopItem;
use App\Models\User;
use App\Services\Shop\ShopService;
use Database\Seeders\ShopItemsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShopPerkEffectsRegistryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Ro'yxat barcha talab qilingan 6 ta effektni va ularning ulanish joylarini saqlaydi.
     */
    public function test_supported_effects_registry_contains_required_effects(): void
    {
        $expected = [
            'streak_freeze',
            'coin_booster',
            'ai_questions',
            'group_slot',
            'quiz_retry',
            'live_priority',
        ];

        $supported = ShopService::supportedEffects();

        foreach ($expected as $effect) {
            $this->assertContains($effect, $supported, "Effekt {$effect} registrdan topilmadi.");
            $info = ShopService::SUPPORTED_EFFECTS[$effect];
            $this->assertArrayHasKey('label', $info);
            $this->assertArrayHasKey('description', $info);
            $this->assertArrayHasKey('handler', $info);
            $this->assertNotEmpty($info['handler'], "Effekt {$effect} uchun ulanish handler'i belgilanmagan.");
        }
    }

    /**
     * Seeder'dagi barcha effektlar shu ro'yxatda ekanini tekshiradi (yangi ulanmagan effekt qo'shilsa yiqiladi).
     */
    public function test_all_seeded_effects_exist_in_supported_effects_registry(): void
    {
        (new ShopItemsSeeder())->run();

        $seededItems = ShopItem::all();
        $this->assertGreaterThan(0, $seededItems->count());

        $supported = ShopService::supportedEffects();
        $foundEffects = [];

        foreach ($seededItems as $item) {
            $payload = $item->payload;
            if (is_array($payload) && isset($payload['effect']) && $payload['effect'] !== '') {
                $effect = $payload['effect'];
                $this->assertContains(
                    $effect,
                    $supported,
                    "ShopItem «{$item->key}» buyumidagi «{$effect}» effekti ShopService registri ro'yxatida yo'q!"
                );
                $foundEffects[] = $effect;
            }
        }

        // Seeder'da kamida 6 ta effekt mavjudligini tasdiqlaymiz
        $uniqueFound = array_unique($foundEffects);
        $this->assertCount(6, $uniqueFound, "Seeder'da kutilgan 6 ta effektning barchasi topilmadi.");
    }

    /**
     * Admin forma yangi buyumga ro'yxatda yo'q effekt kiritishga ruxsat bermaydi.
     */
    public function test_admin_cannot_create_item_with_unsupported_effect(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.shop.items.store'), [
            'key'          => 'hack_item',
            'category'     => 'perk',
            'slot'         => null,
            'name_uz'      => 'Soxta buyum',
            'price_coins'  => 50,
            'rarity'       => 'common',
            'max_quantity' => 1,
            'payload_json' => json_encode(['effect' => 'unsupported_magic_effect']),
        ]);

        $response->assertSessionHasErrors('effect');
        $this->assertDatabaseMissing('shop_items', ['key' => 'hack_item']);

        // Ruxsat etilgan effekt bilan esa muvaffaqiyatli saqlanadi
        $validResponse = $this->actingAs($admin)->post(route('admin.shop.items.store'), [
            'key'          => 'valid_item',
            'category'     => 'perk',
            'slot'         => null,
            'name_uz'      => 'To‘g‘ri buyum',
            'price_coins'  => 50,
            'rarity'       => 'common',
            'max_quantity' => 1,
            'payload_json' => json_encode(['effect' => 'live_priority']),
        ]);

        $validResponse->assertSessionHasNoErrors();
        $this->assertDatabaseHas('shop_items', ['key' => 'valid_item']);
    }
}
