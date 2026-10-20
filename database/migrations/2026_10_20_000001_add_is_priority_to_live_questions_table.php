<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('live_questions', function (Blueprint $table) {
            $table->boolean('is_priority')->default(false)->after('is_answered');
            $table->index(['live_event_id', 'is_priority']);
        });
    }

    public function down(): void
    {
        Schema::table('live_questions', function (Blueprint $table) {
            $table->dropIndex(['live_event_id', 'is_priority']);
            $table->dropColumn('is_priority');
        });
    }
};
