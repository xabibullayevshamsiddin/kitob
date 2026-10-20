<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. MySQL bazada source ENUM ustunini string(32) ga aylantirish (ma'lumotlar saqlanadi)
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE coin_transactions MODIFY COLUMN source VARCHAR(32) NOT NULL");
        }

        // 2. coin_transactions jadvaliga qo'shimcha ustunlar
        Schema::table('coin_transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('coin_transactions', 'reference_type')) {
                $table->string('reference_type', 64)->nullable()->after('description');
            }
            if (!Schema::hasColumn('coin_transactions', 'reference_id')) {
                $table->unsignedBigInteger('reference_id')->nullable()->after('reference_type');
            }
            if (!Schema::hasColumn('coin_transactions', 'balance_after')) {
                $table->integer('balance_after')->nullable()->after('reference_id');
            }
            $table->index(['reference_type', 'reference_id']);
        });

        // 3. users jadvaliga ballarni almashtirish uchun points_spent qo'shish
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'points_spent')) {
                $table->unsignedBigInteger('points_spent')->default(0)->after('total_points');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'points_spent')) {
                $table->dropColumn('points_spent');
            }
        });

        Schema::table('coin_transactions', function (Blueprint $table) {
            $table->dropIndex(['reference_type', 'reference_id']);
            $table->dropColumn(['reference_type', 'reference_id', 'balance_after']);
        });

        if (DB::getDriverName() === 'mysql') {
            // Rollback xavfsizligi: shop, exchange, refund kabi yangi qiymatlarni
            // eski ENUM ro'yxatiga mos xavfsiz qiymatlarga o'tkazamiz (MySQL truncation xatosi bo'lmasligi uchun)
            DB::table('coin_transactions')
                ->whereIn('source', ['shop', 'shop_purchase', 'refund'])
                ->update(['source' => 'spend']);

            DB::table('coin_transactions')
                ->whereIn('source', ['exchange', 'exchange_points'])
                ->update(['source' => 'bonus']);

            DB::table('coin_transactions')
                ->whereNotIn('source', ['reading', 'quiz', 'streak', 'badge', 'bonus', 'admin', 'spend'])
                ->update(['source' => 'admin']);

            DB::statement("ALTER TABLE coin_transactions MODIFY COLUMN source ENUM('reading','quiz','streak','badge','bonus','admin','spend') NOT NULL");
        }
    }
};
