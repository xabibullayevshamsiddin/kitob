<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_activities', function (Blueprint $table) {
            if (!Schema::hasColumn('daily_activities', 'hourly_bonus_claimed')) {
                $table->boolean('hourly_bonus_claimed')->default(false)->after('points_earned');
            }
        });
    }

    public function down(): void
    {
        Schema::table('daily_activities', function (Blueprint $table) {
            if (Schema::hasColumn('daily_activities', 'hourly_bonus_claimed')) {
                $table->dropColumn('hourly_bonus_claimed');
            }
        });
    }
};
