<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('books', 'ai_pdf_content_hash')) {
            Schema::table('books', function (Blueprint $table) {
                $table->string('ai_pdf_content_hash', 64)->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('books', 'ai_pdf_content_hash')) {
            Schema::table('books', function (Blueprint $table) {
                $table->dropColumn('ai_pdf_content_hash');
            });
        }
    }
};
