<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');
            $table->enum('reading_place', [
                'home', 'office', 'university', 'library', 'travel', 'other'
            ])->nullable();
            $table->enum('reading_goal', [
                'knowledge', 'personal_dev', 'exam_prep', 'language', 'career', 'spiritual', 'other'
            ])->nullable();
            $table->json('privacy_settings')->default('{}');
            $table->timestamps();

            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};
