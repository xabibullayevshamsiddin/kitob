<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. MySQL'da email va password ustunlarini nullable qilish
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN email VARCHAR(255) NULL");
            DB::statement("ALTER TABLE users MODIFY COLUMN password VARCHAR(255) NULL");
        }

        // 2. Telegram va telefon ustunlarini qo'shish
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'telegram_id')) {
                $table->unsignedBigInteger('telegram_id')->nullable()->unique()->after('id');
            }
            if (!Schema::hasColumn('users', 'telegram_username')) {
                $table->string('telegram_username', 64)->nullable()->after('telegram_id');
            }
            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone', 20)->nullable()->unique()->after('telegram_username');
            }
            if (!Schema::hasColumn('users', 'phone_verified_at')) {
                $table->timestamp('phone_verified_at')->nullable()->after('phone');
            }
            if (!Schema::hasColumn('users', 'telegram_linked_at')) {
                $table->timestamp('telegram_linked_at')->nullable()->after('phone_verified_at');
            }
            if (!Schema::hasColumn('users', 'telegram_blocked_at')) {
                $table->timestamp('telegram_blocked_at')->nullable()->after('telegram_linked_at');
            }
            if (!Schema::hasColumn('users', 'telegram_locale')) {
                $table->string('telegram_locale', 10)->nullable()->after('telegram_blocked_at');
            }
            if (!Schema::hasColumn('users', 'notification_prefs')) {
                $table->json('notification_prefs')->nullable()->after('telegram_locale');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columnsToDrop = [];

            foreach ([
                'notification_prefs',
                'telegram_locale',
                'telegram_blocked_at',
                'telegram_linked_at',
                'phone_verified_at',
                'phone',
                'telegram_username',
                'telegram_id',
            ] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $columnsToDrop[] = $column;
                }
            }

            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
