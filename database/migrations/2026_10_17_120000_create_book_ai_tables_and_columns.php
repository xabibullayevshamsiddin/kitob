<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. book_pages — PDF'dan bet-bet ajratilgan matn
        if (!Schema::hasTable('book_pages')) {
            Schema::create('book_pages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('book_id')->constrained('books')->cascadeOnDelete();
                $table->unsignedInteger('page_number');
                $table->longText('text');
                $table->string('source', 20)->default('pdf'); // pdf | ocr | manual
                $table->float('quality_score')->default(0);
                $table->timestamps();

                $table->unique(['book_id', 'page_number'], 'book_pages_book_page_unique');
            });
        }

        // 2. book_chunks — Kitob bo'laklari va normallashtirilgan float32 embeddinglar
        if (!Schema::hasTable('book_chunks')) {
            Schema::create('book_chunks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('book_id')->constrained('books')->cascadeOnDelete();
                $table->foreignId('chapter_id')->nullable()->constrained('book_chapters')->nullOnDelete();
                $table->unsignedInteger('page_start')->nullable();
                $table->unsignedInteger('page_end')->nullable();
                $table->unsignedInteger('ordinal')->default(0);
                $table->longText('text');
                $table->unsignedInteger('token_count')->default(0);
                $table->string('content_hash', 64)->index('book_chunks_content_hash_idx');
                $table->binary('embedding')->nullable();
                $table->string('embedding_model', 100)->nullable();
                $table->timestamps();

                $table->index('book_id', 'book_chunks_book_id_idx');
                $table->index(['book_id', 'ordinal'], 'book_chunks_book_ordinal_idx');
            });

            if (DB::connection()->getDriverName() === 'mysql') {
                DB::statement('ALTER TABLE book_chunks MODIFY embedding LONGBLOB NULL');
                DB::statement('ALTER TABLE book_chunks ADD FULLTEXT INDEX book_chunks_text_fulltext (text)');
            }
        }

        // 3. books jadvaliga AI ustunlarini qo'shish
        Schema::table('books', function (Blueprint $table) {
            if (!Schema::hasColumn('books', 'ai_enabled')) {
                $table->boolean('ai_enabled')->default(false)->after('is_active');
            }
            if (!Schema::hasColumn('books', 'ai_index_status')) {
                $table->string('ai_index_status', 20)->default('none')->after('ai_enabled');
            }
            if (!Schema::hasColumn('books', 'ai_indexed_at')) {
                $table->timestamp('ai_indexed_at')->nullable()->after('ai_index_status');
            }
            if (!Schema::hasColumn('books', 'ai_summary')) {
                $table->text('ai_summary')->nullable()->after('ai_indexed_at');
            }
            if (!Schema::hasColumn('books', 'ai_text_quality')) {
                $table->float('ai_text_quality')->nullable()->after('ai_summary');
            }
            if (!Schema::hasColumn('books', 'ai_index_error')) {
                $table->string('ai_index_error', 500)->nullable()->after('ai_text_quality');
            }
        });

        // 4. book_chapters jadvaliga ai_summary qo'shish
        Schema::table('book_chapters', function (Blueprint $table) {
            if (!Schema::hasColumn('book_chapters', 'ai_summary')) {
                $table->text('ai_summary')->nullable()->after('content');
            }
        });

        // 5. ai_chat_histories jadvaliga yangi kuzatuv ustunlari va indekslar (eski qatorlarga tegilmaydi)
        Schema::table('ai_chat_histories', function (Blueprint $table) {
            if (!Schema::hasColumn('ai_chat_histories', 'sources')) {
                $table->json('sources')->nullable()->after('tokens_used');
            }
            if (!Schema::hasColumn('ai_chat_histories', 'refused')) {
                $table->boolean('refused')->default(false)->after('sources');
            }
            if (!Schema::hasColumn('ai_chat_histories', 'refusal_reason')) {
                $table->string('refusal_reason', 30)->nullable()->after('refused');
            }
            if (!Schema::hasColumn('ai_chat_histories', 'prompt_tokens')) {
                $table->unsignedInteger('prompt_tokens')->nullable()->after('refusal_reason');
            }
            if (!Schema::hasColumn('ai_chat_histories', 'completion_tokens')) {
                $table->unsignedInteger('completion_tokens')->nullable()->after('prompt_tokens');
            }
            if (!Schema::hasColumn('ai_chat_histories', 'latency_ms')) {
                $table->unsignedInteger('latency_ms')->nullable()->after('completion_tokens');
            }
            if (!Schema::hasColumn('ai_chat_histories', 'top_score')) {
                $table->float('top_score')->nullable()->after('latency_ms');
            }

            $table->index('book_id', 'ai_chat_histories_book_id_idx');
            $table->index(['user_id', 'book_id'], 'ai_chat_histories_user_book_idx');
        });
    }

    public function down(): void
    {
        Schema::table('ai_chat_histories', function (Blueprint $table) {
            $table->dropIndex('ai_chat_histories_book_id_idx');
            $table->dropIndex('ai_chat_histories_user_book_idx');
            $cols = array_filter([
                'sources',
                'refused',
                'refusal_reason',
                'prompt_tokens',
                'completion_tokens',
                'latency_ms',
                'top_score',
            ], fn ($c) => Schema::hasColumn('ai_chat_histories', $c));

            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });

        Schema::table('book_chapters', function (Blueprint $table) {
            if (Schema::hasColumn('book_chapters', 'ai_summary')) {
                $table->dropColumn('ai_summary');
            }
        });

        Schema::table('books', function (Blueprint $table) {
            $cols = array_filter([
                'ai_enabled',
                'ai_index_status',
                'ai_indexed_at',
                'ai_summary',
                'ai_text_quality',
                'ai_index_error',
            ], fn ($c) => Schema::hasColumn('books', $c));

            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });

        Schema::dropIfExists('book_chunks');
        Schema::dropIfExists('book_pages');
    }
};
