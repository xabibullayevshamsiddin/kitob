<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Analytics performance indexes migration.
 *
 * Tekshiruv natijalari:
 * 1. daily_activities(activity_date) — ALLAQACHON MAVJUD (`daily_activities_activity_date_index`
 *    2024_01_01_000020_create_daily_activity_table.php ichida yaratilgan). Dublikat indeks qo'shilmaydi.
 * 2. users(created_at) — MAVJUD EMAS. Davr bo'yicha yangi foydalanuvchilar va kohorta so'rovlari
 *    uchun `users_created_at_idx` qo'shiladi.
 * 3. reading_sessions(book_id, session_date) — Mavjud `rs_user_date_book_idx` (`user_id` bilan boshlanadi)
 *    kitob va sana bo'yicha guruhlashda ishlamaydi; `rs_book_date_idx` va `rs_date_book_idx` qo'shiladi.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->index('created_at', 'users_created_at_idx');
        });

        Schema::table('reading_sessions', function (Blueprint $table) {
            $table->index(['book_id', 'session_date'], 'rs_book_date_idx');
            $table->index(['session_date', 'book_id'], 'rs_date_book_idx');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_created_at_idx');
        });

        Schema::table('reading_sessions', function (Blueprint $table) {
            $table->dropIndex('rs_book_date_idx');
            $table->dropIndex('rs_date_book_idx');
        });
    }
};
