<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reading_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');
            $table->foreignId('book_id')
                  ->constrained('books')
                  ->onDelete('cascade');
            $table->foreignId('chapter_id')
                  ->nullable()
                  ->constrained('book_chapters')
                  ->onDelete('set null');
            $table->unsignedInteger('minutes_read');
            $table->date('session_date');
            $table->timestamps();

            $table->index(['user_id', 'session_date']);
            $table->index('book_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reading_sessions');
    }
};
