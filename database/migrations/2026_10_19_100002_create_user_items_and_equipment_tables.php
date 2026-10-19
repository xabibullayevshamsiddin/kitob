<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('shop_item_id')->constrained('shop_items')->cascadeOnDelete();
            $table->unsignedInteger('quantity')->default(1);
            $table->timestamp('acquired_at');
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamps();

            $table->unique(['user_id', 'shop_item_id']);
            $table->index(['user_id', 'expires_at']);
        });

        Schema::create('user_equipment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('slot', 32);
            $table->foreignId('shop_item_id')->constrained('shop_items')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'slot']);
            $table->index(['user_id', 'shop_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_equipment');
        Schema::dropIfExists('user_items');
    }
};
