<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->boolean('chat_enabled')->default(true)->after('password');
            $table->boolean('voice_enabled')->default(true)->after('chat_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->dropColumn(['chat_enabled', 'voice_enabled']);
        });
    }
};
