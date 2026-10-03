<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('live_events', function (Blueprint $table) {
            if (!Schema::hasColumn('live_events', 'likes_count')) {
                $table->unsignedBigInteger('likes_count')->default(0)->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('live_events', function (Blueprint $table) {
            if (Schema::hasColumn('live_events', 'likes_count')) {
                $table->dropColumn('likes_count');
            }
        });
    }
};
