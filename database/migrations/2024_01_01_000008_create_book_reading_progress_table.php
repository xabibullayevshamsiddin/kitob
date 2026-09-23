<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('book_reading_progress', function (Blueprint $table) {
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
            $table->integer('last_position')->default(0)->comment('Scroll position or paragraph index');
            $table->decimal('percent_complete', 5, 2)->default(0.00);
            $table->timestamps();

            $table->unique(['user_id', 'book_id', 'chapter_id']);
            $table->index(['user_id', 'book_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_reading_progress');
    }
};
