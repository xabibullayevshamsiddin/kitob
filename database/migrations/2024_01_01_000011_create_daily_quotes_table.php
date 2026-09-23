<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_quotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')
                  ->constrained('books')
                  ->onDelete('cascade');
            $table->text('quote_text');
            $table->date('send_date')->unique();
            $table->timestamps();

            $table->index('send_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_quotes');
    }
};
