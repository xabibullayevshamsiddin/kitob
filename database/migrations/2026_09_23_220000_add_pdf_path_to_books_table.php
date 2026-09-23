<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->string('pdf_path')->nullable()->after('cover_image')
                ->comment('Admin/teacher tomonidan yuklangan haqiqiy PDF fayl (storage/app/public/books/pdf)');
        });
    }

    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn('pdf_path');
        });
    }
};
