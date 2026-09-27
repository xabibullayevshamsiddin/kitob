<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // users.role ENUM'ni Spatie rollari bilan sinxron kengaytiramiz
        // Eslatma: 2014_10_12_000000_create_users_table migratsiyasi endi to'liq ro'yxat bilan
        // ENUM yaratadi; bu migratsiya eski DB'lar (MySQL) uchun moslashuvchan qoldiriladi.
        if (DB::getDriverName() === 'sqlite') {
            return; // sqlite ENUM bilan varchar sifatida ishlaydi, ALTER shart emas
        }
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('reader','moderator','admin','teacher','student','author') NOT NULL DEFAULT 'reader'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('reader','moderator','admin') NOT NULL DEFAULT 'reader'");
    }
};
