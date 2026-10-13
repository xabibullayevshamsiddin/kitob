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
        Schema::table('user_streaks', function (Blueprint $table) {
            $table->unsignedInteger('freeze_count')->default(0)->after('longest_streak');
            $table->date('last_frozen_date')->nullable()->after('freeze_count');
        });

        Schema::table('user_notes', function (Blueprint $table) {
            $table->string('color', 20)->default('amber')->after('selected_text');
        });

        Schema::table('user_profiles', function (Blueprint $table) {
            $table->string('avatar_frame', 50)->nullable()->after('privacy_settings');
            $table->string('profile_theme', 50)->nullable()->after('avatar_frame');
        });
    }

    public function down()
    {
        Schema::table('user_streaks', function (Blueprint $table) {
            $table->dropColumn(['freeze_count', 'last_frozen_date']);
        });

        Schema::table('user_notes', function (Blueprint $table) {
            $table->dropColumn('color');
        });

        Schema::table('user_profiles', function (Blueprint $table) {
            $table->dropColumn(['avatar_frame', 'profile_theme']);
        });
    }
};
