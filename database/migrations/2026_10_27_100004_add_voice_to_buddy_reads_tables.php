<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE buddy_read_messages MODIFY message TEXT NULL");
        }

        Schema::table('buddy_read_messages', function (Blueprint $table) {
            $table->string('audio_path', 500)->nullable()->after('message');
            $table->unsignedInteger('audio_duration')->nullable()->after('audio_path');
        });

        Schema::table('buddy_read_members', function (Blueprint $table) {
            $table->boolean('voice_active')->default(false)->after('current_page');
            $table->timestamp('last_voice_at')->nullable()->after('voice_active');
        });
    }

    public function down(): void
    {
        Schema::table('buddy_read_messages', function (Blueprint $table) {
            $table->dropColumn(['audio_path', 'audio_duration']);
        });

        Schema::table('buddy_read_members', function (Blueprint $table) {
            $table->dropColumn(['voice_active', 'last_voice_at']);
        });
    }
};
