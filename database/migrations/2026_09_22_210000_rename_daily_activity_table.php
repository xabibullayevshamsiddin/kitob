<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Renames the legacy `daily_activity` table to `daily_activities`
     * to match Laravel's plural table naming convention expected by
     * the App\Models\DailyActivity model.
     */
    public function up(): void
    {
        // Idempotent: agar eski jadval yo'q bo'lsa (masalan, yangi baza), o'tkazib yuboradi.
        if (Schema::hasTable('daily_activity') && !Schema::hasTable('daily_activities')) {
            try {
                Schema::rename('daily_activity', 'daily_activities');
            } catch (\Throwable $e) {
                // Jadval ketma-ketlikda boshqa migratsiya tomonidan allaqachon ko'chirilgan bo'lishi mumkin — xato bermaymiz.
                if (!Schema::hasTable('daily_activities')) {
                    throw $e;
                }
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('daily_activities') && !Schema::hasTable('daily_activity')) {
            try {
                Schema::rename('daily_activities', 'daily_activity');
            } catch (\Throwable $e) {
                if (!Schema::hasTable('daily_activity')) {
                    throw $e;
                }
            }
        }
    }
};
