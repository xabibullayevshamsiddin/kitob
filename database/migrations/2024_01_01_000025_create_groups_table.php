<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->foreignId('book_id')
                  ->nullable()
                  ->constrained('books')
                  ->onDelete('set null');
            $table->foreignId('created_by')
                  ->constrained('users')
                  ->onDelete('cascade');
            $table->string('cover_image')->nullable();
            $table->boolean('is_private')->default(false);
            $table->string('invite_code')->nullable()->unique();
            $table->unsignedInteger('max_members')->default(100);
            $table->timestamps();
            $table->softDeletes();

            $table->index('book_id');
            $table->index('created_by');
            $table->index('is_private');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('groups');
    }
};
