<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('league', 10)->default('bronze')->after('total_points')
                ->comment('bronze, silver, gold');
            $table->unsignedInteger('weekly_points')->default(0)->after('league')
                ->comment('Joriy haftadagi ball (har yakshanba yangilanadi)');
            $table->date('league_updated_at')->nullable()->after('weekly_points');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['league', 'weekly_points', 'league_updated_at']);
        });
    }
};
