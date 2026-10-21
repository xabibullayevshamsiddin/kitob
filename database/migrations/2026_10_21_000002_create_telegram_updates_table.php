<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('telegram_updates')) {
            Schema::create('telegram_updates', function (Blueprint $table) {
                // Telegram update_id 64-bit unsigned integer
                $table->unsignedBigInteger('update_id')->primary();
                $table->bigInteger('chat_id')->nullable()->index();
                $table->string('update_type', 64)->nullable()->index();
                $table->timestamp('created_at')->nullable()->index();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('telegram_updates');
    }
};
