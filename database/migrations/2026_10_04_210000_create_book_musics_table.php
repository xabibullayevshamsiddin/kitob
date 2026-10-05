<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('book_musics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')
                  ->constrained('books')
                  ->onDelete('cascade');
            $table->string('title');
            $table->string('file_path');
            $table->unsignedInteger('duration')->nullable()->comment('Davomiyligi sekundlarda');
            $table->unsignedSmallInteger('order')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('book_id');
            $table->index(['book_id', 'is_active', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_musics');
    }
};
