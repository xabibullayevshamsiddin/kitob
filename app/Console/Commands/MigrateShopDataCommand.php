<?php

namespace App\Console\Commands;

use App\Models\ShopItem;
use App\Models\UserEquipment;
use App\Models\UserItem;
use App\Models\UserProfile;
use App\Models\UserStreak;
use Database\Seeders\ShopItemsSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateShopDataCommand extends Command
{
    protected $signature = 'shop:migrate-data';
    protected $description = 'Eski do‘kon ma’lumotlarini (unlocked_items, mavzular) yangi Shop jadvallariga xavfsiz va idempotent ko‘chirish';

    public function handle(): int
    {
        $this->info("=== DO'KON MA'LUMOTLARINI KO'CHIRISH BOSHLANDI ===");

        // 1. KO'CHIRISHDAN OLDINGI HOLATNI HISOBLASH
        $profiles = UserProfile::all();
        $beforeUsersWithUnlocked = 0;
        $beforeUnlockedCount = 0;
        $beforeProfileThemes = 0;
        $beforeAvatarFrames = 0;
        $beforeChatBubbles = 0;

        foreach ($profiles as $p) {
            $items = is_array($p->unlocked_items) ? $p->unlocked_items : [];
            if (!empty($items)) {
                $beforeUsersWithUnlocked++;
                $beforeUnlockedCount += count($items);
            }
            if (!empty($p->profile_theme)) $beforeProfileThemes++;
            if (!empty($p->avatar_frame)) $beforeAvatarFrames++;
            if (!empty($p->chat_bubble_theme)) $beforeChatBubbles++;
        }

        $beforeFreezeCount = (int) UserStreak::sum('freeze_count');
        $beforeUserItems = UserItem::count();
        $beforeEquipment = UserEquipment::count();

        $this->table(['Ko‘rsatkich (Oldingi holat)', 'Soni'], [
            ['Foydalanuvchi profillari soni', $profiles->count()],
            ['unlocked_items bor profillar', $beforeUsersWithUnlocked],
            ['Jami unlocked_items soni', $beforeUnlockedCount],
            ['Aktiv profile_theme (kiyib turilgan)', $beforeProfileThemes],
            ['Aktiv avatar_frame (kiyib turilgan)', $beforeAvatarFrames],
            ['Aktiv chat_bubble_theme', $beforeChatBubbles],
            ['Jami Streak Freeze soni (user_streaks)', $beforeFreezeCount],
            ['user_items mavjud qatorlar', $beforeUserItems],
            ['user_equipment mavjud qatorlar', $beforeEquipment],
        ]);

        // 2. KATALOGNI TO'LDIRISH (Seeder)
        $this->info("ShopItemsSeeder ishga tushirilmoqda...");
        (new ShopItemsSeeder())->run();
        $shopItemsCount = ShopItem::count();
        $this->info("Do'kon katalogi tayyor: {$shopItemsCount} ta buyum mavjud.");

        // 3. MA'LUMOTLARNI KO'CHIRISH
        $this->info("Egalik va kiyilgan narsalar ko'chirilmoqda...");

        DB::transaction(function () use ($profiles) {
            $now = now();

            foreach ($profiles as $profile) {
                $userId = $profile->user_id;
                $unlocked = is_array($profile->unlocked_items) ? $profile->unlocked_items : [];
                $profileUpdated = false;

                // 3.1. Foydalanuvchining unlocked_items bo'yicha ko'chirish
                foreach ($unlocked as $itemKey) {
                    $shopItem = ShopItem::where('key', $itemKey)->first();
                    if ($shopItem) {
                        UserItem::firstOrCreate(
                            ['user_id' => $userId, 'shop_item_id' => $shopItem->id],
                            ['quantity' => 1, 'acquired_at' => $now]
                        );
                    }
                }

                // 3.2. Foydalanuvchi hozir kiyib turgan mavzular (profile_theme, avatar_frame, chat_bubble_theme)
                $equippedThemeKey = $profile->profile_theme ?? $profile->avatar_frame ?? $profile->chat_bubble_theme;

                if ($equippedThemeKey) {
                    $themeItem = ShopItem::where('key', $equippedThemeKey)->first();
                    if ($themeItem) {
                        // Foydalanuvchida ushbu buyum inventarda borligini kafolatlaymiz
                        UserItem::firstOrCreate(
                            ['user_id' => $userId, 'shop_item_id' => $themeItem->id],
                            ['quantity' => 1, 'acquired_at' => $now]
                        );

                        // Orqaga moslik uchun unlocked_items ga ham qo'shib qo'yamiz
                        if (!in_array($equippedThemeKey, $unlocked, true)) {
                            $unlocked[] = $equippedThemeKey;
                            $profile->unlocked_items = $unlocked;
                            $profileUpdated = true;
                        }

                        // user_equipment ga mos slotlarni yozamiz
                        $slots = $themeItem->payload['bundle_slots'] ?? ['profile_theme', 'avatar_frame', 'chat_bubble'];
                        foreach ($slots as $slot) {
                            UserEquipment::firstOrCreate(
                                ['user_id' => $userId, 'slot' => $slot],
                                ['shop_item_id' => $themeItem->id]
                            );
                        }
                    }
                }

                // Yagona alohida avatar_frame kiyilgan bo'lsa
                if ($profile->avatar_frame && $profile->avatar_frame !== $equippedThemeKey) {
                    $frameItem = ShopItem::where('key', $profile->avatar_frame)->first();
                    if ($frameItem) {
                        UserItem::firstOrCreate(
                            ['user_id' => $userId, 'shop_item_id' => $frameItem->id],
                            ['quantity' => 1, 'acquired_at' => $now]
                        );
                        UserEquipment::firstOrCreate(
                            ['user_id' => $userId, 'slot' => 'avatar_frame'],
                            ['shop_item_id' => $frameItem->id]
                        );
                    }
                }

                if ($profileUpdated) {
                    $profile->save();
                }
            }
        });

        // 4. KO'CHIRISHDAN KEYINGI HOLATNI HISOBLASH
        $afterUserItems = UserItem::count();
        $afterEquipment = UserEquipment::count();
        $afterFreezeCount = (int) UserStreak::sum('freeze_count');

        $this->table(['Ko‘rsatkich (Ko‘chirishdan keyin)', 'Soni'], [
            ['Do‘kon katalogi (shop_items)', ShopItem::count()],
            ['Egalik qilingan buyumlar (user_items)', $afterUserItems],
            ['Jihozlangan buyumlar (user_equipment)', $afterEquipment],
            ['Streak Freeze soni (user_streaks)', $afterFreezeCount],
        ]);

        $this->info("=== DO'KON MA'LUMOTLARINI KO'CHIRISH MUVAFFAQIYATLI YAKUNLANDI ===");
        return Command::SUCCESS;
    }
}
