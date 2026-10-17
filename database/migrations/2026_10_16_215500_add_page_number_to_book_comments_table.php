<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('book_comments', function (Blueprint $table) {
            $table->unsignedInteger('page_number')->nullable()->after('parent_id');
            $table->index(['book_id', 'page_number']);
        });
    }

    public function down(): void
    {
        Schema::table('book_comments', function (Blueprint $table) {
            $table->dropIndex(['book_id', 'page_number']);
            $table->dropColumn('page_number');
        });
    }
};
