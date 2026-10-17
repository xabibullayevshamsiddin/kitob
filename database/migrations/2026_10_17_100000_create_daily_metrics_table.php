<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_metrics', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();
            $table->unsignedInteger('dau')->default(0);
            $table->unsignedInteger('logged_in_users')->default(0);
            $table->unsignedInteger('reading_users')->default(0);
            $table->unsignedInteger('active_readers')->default(0);
            $table->unsignedInteger('new_users')->default(0);
            $table->unsignedInteger('onboarded')->default(0);
            $table->unsignedBigInteger('minutes_read')->default(0);
            $table->unsignedInteger('books_finished')->default(0);
            $table->unsignedInteger('quiz_attempts')->default(0);
            $table->unsignedBigInteger('points_issued')->default(0);
            $table->unsignedBigInteger('coins_issued')->default(0);
            $table->unsignedInteger('chat_messages')->default(0);
            $table->unsignedInteger('ai_messages')->default(0);
            $table->unsignedBigInteger('ai_tokens')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_metrics');
    }
};
