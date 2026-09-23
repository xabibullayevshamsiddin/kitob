<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('live_events', function (Blueprint $table) {
            $table->foreignId('host_user_id')
                  ->nullable()
                  ->after('book_id')
                  ->constrained('users')
                  ->nullOnDelete();

            // Ruxsat rejimi:
            // - 'both': Ikkalasi ham mumkin (chat + ovoz)
            // - 'chat_only': Faqat yoza olsin (chat)
            // - 'voice_only': Faqat gapira olsin (audio mikrofon)
            // - 'view_only': Ikkalasi ham mumkin emas (faqat tomosha qilish / ma'ruza)
            $table->string('permission_mode', 30)
                  ->default('both')
                  ->after('status');

            $table->boolean('is_recording')->default(false)->after('permission_mode');
        });
    }

    public function down(): void
    {
        Schema::table('live_events', function (Blueprint $table) {
            $table->dropForeign(['host_user_id']);
            $table->dropColumn(['host_user_id', 'permission_mode', 'is_recording']);
        });
    }
};
