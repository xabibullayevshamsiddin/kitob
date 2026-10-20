<?php

namespace App\Console\Commands;

use App\Models\ShopItem;
use App\Models\UserEquipment;
use App\Models\UserItem;
use Database\Seeders\ShopItemsSeeder;
use Illuminate\Console\Command;

class ShopInstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'shop:install {--refresh-defaults : Standart narx va parametrlarni bazaga qaytadan yozish (mavjud admin o\'zgartirishlarini yangilaydi)}';

    /**
     * The console command description.
     */
    protected $description = 'Shop bo‘limini o‘rnatish: standart katalog seederi va eski xaridlarni xavfsiz (idempotent) ko‘chirish';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info("==========================================");
        $this->info("  KITOBXON SHOP: O'RNATISH VA KO'CHIRISH  ");
        $this->info("==========================================");

        $refreshDefaults = (bool) $this->option('refresh-defaults');

        if ($refreshDefaults) {
            $this->warn("DIQQAT: --refresh-defaults bayrog'i faollashtirildi. Mavjud buyumlar standart qiymatlarga qaytariladi.");
            ShopItemsSeeder::$refreshDefaults = true;
        } else {
            $this->line("Xavfsiz rejim: faqat mavjud bo'lmagan buyumlar qo'shiladi (admin o'zgartirgan narxlar saqlanadi).");
            ShopItemsSeeder::$refreshDefaults = false;
        }

        // 1. Seeder'ni ishga tushirish
        $this->info("1/2. Shop katalogini to'ldirish (ShopItemsSeeder)...");
        (new ShopItemsSeeder())->run();
        $this->info("Katalog muvaffaqiyatli tekshirildi.");

        // 2. shop:migrate-data buyrug'ini ishga tushirish
        $this->info("2/2. Foydalanuvchilarning eski xaridlarini ko'chirish (shop:migrate-data)...");
        $this->call('shop:migrate-data');

        // 3. Yakuniy qisqa hisobot jadvali
        $itemsCount = ShopItem::count();
        $userItemsCount = UserItem::count();
        $equipmentCount = UserEquipment::count();

        $this->newLine();
        $this->info("=== DO'KON YAKUNIY STATISTIKASI ===");
        $this->table(
            ['Ko‘rsatkich', 'Soni'],
            [
                ['Katalogdagi buyumlar (shop_items)', $itemsCount],
                ['Ko‘chirilgan egalik qilingan buyumlar (user_items)', $userItemsCount],
                ['Jihozlangan buyumlar (user_equipment)', $equipmentCount],
            ]
        );

        $this->info("Shop muvaffaqiyatli o'rnatildi va foydalanishga tayyor!");
        return Command::SUCCESS;
    }
}
