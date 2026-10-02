<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('global_chat_messages')) {
            Schema::table('global_chat_messages', function (Blueprint $table) {
                if (!Schema::hasColumn('global_chat_messages', 'audio_path')) {
                    $table->string('audio_path', 500)->nullable();
                }
                if (!Schema::hasColumn('global_chat_messages', 'audio_duration')) {
                    $table->integer('audio_duration')->nullable();
                }
            });
        }

        if (Schema::hasTable('group_messages')) {
            Schema::table('group_messages', function (Blueprint $table) {
                if (!Schema::hasColumn('group_messages', 'audio_path')) {
                    $table->string('audio_path', 500)->nullable();
                }
                if (!Schema::hasColumn('group_messages', 'audio_duration')) {
                    $table->integer('audio_duration')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('global_chat_messages')) {
            Schema::table('global_chat_messages', function (Blueprint $table) {
                if (Schema::hasColumn('global_chat_messages', 'audio_path')) {
                    $table->dropColumn('audio_path');
                }
                if (Schema::hasColumn('global_chat_messages', 'audio_duration')) {
                    $table->dropColumn('audio_duration');
                }
            });
        }

        if (Schema::hasTable('group_messages')) {
            Schema::table('group_messages', function (Blueprint $table) {
                if (Schema::hasColumn('group_messages', 'audio_path')) {
                    $table->dropColumn('audio_path');
                }
                if (Schema::hasColumn('group_messages', 'audio_duration')) {
                    $table->dropColumn('audio_duration');
                }
            });
        }
    }
};
