<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shop_items', function (Blueprint $table) {
            $table->id();
            $table->string('key', 64)->unique();
            $table->string('category', 32)->index(); // profile, colors, consumable, perk, bundle
            $table->string('slot', 32)->nullable()->index(); // avatar_frame, profile_theme, chat_bubble, name_color, title, accent, reader_palette, highlight_color
            $table->json('name'); // {"uz": "...", "ru": "...", "en": "..."}
            $table->json('description')->nullable();
            $table->unsignedInteger('price_coins');
            $table->string('rarity', 20)->default('common')->index(); // common, rare, epic, legendary
            $table->json('payload')->nullable(); // visual params, effect configs
            $table->boolean('is_consumable')->default(false)->index();
            $table->unsignedInteger('max_quantity')->default(1);
            $table->unsignedInteger('duration_minutes')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamp('available_from')->nullable();
            $table->timestamp('available_until')->nullable();
            $table->integer('sort_order')->default(0)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_items');
    }
};
