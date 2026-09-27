<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('avatar')->nullable();
            // ENUM'ni to'liq rol ro'yxati bilan yaratamiz (2026_09_24 migratsiya sqlite'da ishlamagani uchun)
            $table->enum('role', ['reader', 'moderator', 'admin', 'teacher', 'student', 'author'])->default('reader');
            $table->unsignedBigInteger('total_points')->default(0);
            $table->unsignedBigInteger('coin_balance')->default(0);
            $table->text('bio')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            $table->index('email');
            $table->index('username');
            $table->index('role');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
