<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table) {
            if (!Schema::hasColumn('books', 'ai_total_pages')) {
                $table->unsignedInteger('ai_total_pages')->nullable()->after('ai_text_quality');
            }
            if (!Schema::hasColumn('books', 'ai_processed_pages')) {
                $table->unsignedInteger('ai_processed_pages')->nullable()->after('ai_total_pages');
            }
            if (!Schema::hasColumn('books', 'ai_index_metrics')) {
                $table->json('ai_index_metrics')->nullable()->after('ai_processed_pages');
            }
            if (!Schema::hasColumn('books', 'ai_index_version')) {
                $table->unsignedInteger('ai_index_version')->default(1)->after('ai_index_metrics');
            }
            if (!Schema::hasColumn('books', 'ai_summary_origin')) {
                $table->string('ai_summary_origin', 30)->default('auto')->after('ai_summary');
            }
        });

        Schema::table('book_chunks', function (Blueprint $table) {
            if (!Schema::hasColumn('book_chunks', 'index_version')) {
                $table->unsignedInteger('index_version')->default(1)->after('ordinal');
                $table->index(['book_id', 'index_version'], 'book_chunks_book_version_idx');
            }
        });
    }

    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $cols = ['ai_total_pages', 'ai_processed_pages', 'ai_index_metrics', 'ai_index_version', 'ai_summary_origin'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('books', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('book_chunks', function (Blueprint $table) {
            if (Schema::hasColumn('book_chunks', 'index_version')) {
                $table->dropIndex('book_chunks_book_version_idx');
                $table->dropColumn('index_version');
            }
        });
    }
};
