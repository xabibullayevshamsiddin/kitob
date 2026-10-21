<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('telegram_star_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');
            $table->string('payload')->unique();
            $table->string('title');
            $table->unsignedInteger('stars_amount');
            $table->unsignedInteger('coins_awarded')->default(0);
            $table->string('telegram_payment_charge_id')->nullable()->unique();
            $table->string('provider_payment_charge_id')->nullable();
            $table->string('status', 30)->default('pending'); // pending, completed, refunded, failed
            $table->timestamp('refunded_at')->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('telegram_star_payments');
    }
};
