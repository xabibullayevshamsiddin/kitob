<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_activities', function (Blueprint $table) {
            if (!Schema::hasColumn('daily_activities', 'early_bird_claimed')) {
                $table->boolean('early_bird_claimed')->default(false)->after('hourly_bonus_claimed');
            }
        });
    }

    public function down(): void
    {
        Schema::table('daily_activities', function (Blueprint $table) {
            if (Schema::hasColumn('daily_activities', 'early_bird_claimed')) {
                $table->dropColumn('early_bird_claimed');
            }
        });
    }
};
