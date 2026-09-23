<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');
            $table->date('activity_date');
            $table->integer('minutes_read')->default(0);
            $table->boolean('logged_in')->default(false);
            $table->integer('points_earned')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'activity_date']);
            $table->index('user_id');
            $table->index('activity_date');
        });
    }

    public function down(): void
    {            Schema::dropIfExists('daily_activities');
    }
};
