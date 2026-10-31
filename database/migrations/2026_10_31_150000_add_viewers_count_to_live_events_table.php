<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('live_events', function (Blueprint $table) {
            if (!Schema::hasColumn('live_events', 'viewers_count')) {
                $table->unsignedInteger('viewers_count')->default(0)->after('likes_count');
                $table->index('viewers_count');
            }
        });
    }

    public function down(): void
    {
        Schema::table('live_events', function (Blueprint $table) {
            if (Schema::hasColumn('live_events', 'viewers_count')) {
                $table->dropIndex(['viewers_count']);
                $table->dropColumn('viewers_count');
            }
        });
    }
};
