<?php

namespace App\Jobs;

use App\Models\Book;
use App\Services\AI\BookAssistant\BookIndexer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AiIndexBookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600;
    public int $tries = 2;

    public function __construct(
        public int $bookId,
        public bool $force = false
    ) {
    }

    public function handle(BookIndexer $indexer): void
    {
        @ini_set('memory_limit', '1024M');
        @set_time_limit(600);

        $book = Book::find($this->bookId);
        if (!$book) {
            return;
        }

        // Parallel indekslashdan himoya qilish uchun atomik qulf (Lock)
        $lockKey = "ai_indexing_lock_{$this->bookId}";
        $lock = Cache::lock($lockKey, 900);

        if (!$lock->get()) {
            Log::warning("Kitob #{$this->bookId} allaqachon boshqa jarayonda indekslanmoqda.");
            return;
        }

        $progressKey = "ai_indexing_progress_{$this->bookId}";
        Cache::put($progressKey, [
            'status'     => 'indexing',
            'step'       => 'starting',
            'current'    => 0,
            'total'      => 0,
            'percent'    => 0,
            'started_at' => now()->toIso8601String(),
        ], 900);

        try {
            $result = $indexer->indexBook($book, $this->force, function (string $step, int $current, int $total) use ($progressKey) {
                $percent = $total > 0 ? (int) round(($current / $total) * 100) : 0;
                Cache::put($progressKey, [
                    'status'     => 'indexing',
                    'step'       => $step,
                    'current'    => $current,
                    'total'      => $total,
                    'percent'    => $percent,
                    'updated_at' => now()->toIso8601String(),
                ], 900);
            });

            Cache::put("ai_indexing_result_{$this->bookId}", $result, 3600);
            Cache::put($progressKey, [
                'status'     => $result['status'],
                'step'       => 'completed',
                'current'    => 100,
                'total'      => 100,
                'percent'    => 100,
                'result'     => $result,
                'updated_at' => now()->toIso8601String(),
            ], 900);
        } catch (\Throwable $e) {
            Log::error("AiIndexBookJob xatosi (#{$this->bookId})", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $book->update([
                'ai_index_status' => 'failed',
                'ai_index_error'  => $e->getMessage(),
            ]);

            Cache::put($progressKey, [
                'status'     => 'failed',
                'step'       => 'failed',
                'error'      => $e->getMessage(),
                'updated_at' => now()->toIso8601String(),
            ], 900);

            throw $e;
        } finally {
            $lock->release();
        }
    }
}
