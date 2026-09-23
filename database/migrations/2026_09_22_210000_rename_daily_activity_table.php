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
        Schema::rename('daily_activity', 'daily_activities');
    }

    public function down(): void
    {
        Schema::rename('daily_activities', 'daily_activity');
    }
};
