<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('author');
            $table->text('description');
            $table->string('cover_image')->nullable();
            $table->string('genre');
            $table->unsignedInteger('week_number')->unique();
            $table->timestamp('published_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('week_number');
            $table->index('genre');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
