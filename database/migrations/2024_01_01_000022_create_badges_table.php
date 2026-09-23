<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('badges', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->string('color')->nullable();
            $table->string('condition_type');
            $table->integer('condition_value')->default(0);
            $table->timestamps();

            $table->index('condition_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('badges');
    }
};
