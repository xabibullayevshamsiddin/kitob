<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('settings')) {
            Schema::create('settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->longText('value')->nullable();
                $table->string('group')->default('general')->index();
                $table->string('type')->default('string'); // string, boolean, integer, json
                $table->timestamps();
            });

            // Default platform settings
            $defaults = [
                ['key' => 'app_name', 'value' => config('app.name', 'Kitobxon'), 'group' => 'general', 'type' => 'string'],
                ['key' => 'contact_email', 'value' => env('MAIL_FROM_ADDRESS', 'admin@kitobxon.uz'), 'group' => 'general', 'type' => 'string'],
                ['key' => 'app_url', 'value' => config('app.url', 'http://localhost/Kitob/public'), 'group' => 'general', 'type' => 'string'],
                ['key' => 'timezone', 'value' => 'Asia/Tashkent', 'group' => 'general', 'type' => 'string'],
                ['key' => 'per_page', 'value' => '15', 'group' => 'general', 'type' => 'integer'],
                ['key' => 'welcome_message', 'value' => 'Kitobxon platformasiga xush kelibsiz!', 'group' => 'general', 'type' => 'string'],
                ['key' => 'telegram_channel', 'value' => '@kitobxon_uz', 'group' => 'general', 'type' => 'string'],

                // Gamification rules
                ['key' => 'reading_points_per_minute', 'value' => '10', 'group' => 'gamification', 'type' => 'integer'],
                ['key' => 'reading_coins_per_minute', 'value' => '1', 'group' => 'gamification', 'type' => 'integer'],
                ['key' => 'streak_minimum_minutes', 'value' => '10', 'group' => 'gamification', 'type' => 'integer'],
                ['key' => 'quiz_passing_percent', 'value' => '70', 'group' => 'gamification', 'type' => 'integer'],
                ['key' => 'welcome_bonus_coins', 'value' => '5', 'group' => 'gamification', 'type' => 'integer'],

                // Community & Chat
                ['key' => 'global_chat_enabled', 'value' => '1', 'group' => 'community', 'type' => 'boolean'],
                ['key' => 'user_group_membership_limit', 'value' => '2', 'group' => 'community', 'type' => 'integer'],
                ['key' => 'teacher_group_membership_limit', 'value' => '5', 'group' => 'community', 'type' => 'integer'],
                ['key' => 'registration_open', 'value' => '1', 'group' => 'community', 'type' => 'boolean'],
            ];

            $now = now();
            foreach ($defaults as &$item) {
                $item['created_at'] = $now;
                $item['updated_at'] = $now;
            }

            DB::table('settings')->insert($defaults);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
