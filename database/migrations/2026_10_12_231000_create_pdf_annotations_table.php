<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pdf_annotations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('page_number');
            $table->string('kind', 20);
            $table->string('label', 120)->nullable();
            $table->text('note_text')->nullable();
            $table->string('color', 20)->default('amber');
            $table->timestamps();

            $table->index(['user_id', 'book_id', 'page_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pdf_annotations');
    }
};
