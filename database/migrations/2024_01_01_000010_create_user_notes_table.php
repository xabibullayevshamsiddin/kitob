<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');
            $table->foreignId('book_id')
                  ->constrained('books')
                  ->onDelete('cascade');
            $table->foreignId('chapter_id')
                  ->constrained('book_chapters')
                  ->onDelete('cascade');
            $table->text('selected_text')->nullable();
            $table->text('note_text');
            $table->timestamps();

            $table->index('user_id');
            $table->index('book_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_notes');
    }
};
