<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->string('chat_style_preference', 20)->default('shop')->after('profile_theme');
            $table->json('unlocked_items')->nullable()->after('chat_style_preference');
            $table->string('chat_bubble_theme', 50)->nullable()->after('unlocked_items');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->dropColumn(['chat_style_preference', 'unlocked_items', 'chat_bubble_theme']);
        });
    }
};
