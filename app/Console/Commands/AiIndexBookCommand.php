<?php

namespace App\Console\Commands;

use App\Models\Book;
use App\Services\AI\BookAssistant\BookIndexer;
use App\Services\AI\BookAssistant\RelevanceGate;
use App\Services\AI\BookAssistant\Retriever;
use Illuminate\Console\Command;

class AiIndexBookCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ai:index-book
                            {book? : Indekslanadigan kitob ID raqami yoki slug nomi}
                            {--all : Barcha faol kitoblarni indekslash}
                            {--force : O\'zgarmagan matn va bo\'laklarni ham noldan qayta indekslash}
                            {--clear : Kitobning AI indeksini va barcha bo\'laklarini tozalash}
                            {--queue : Indekslashni Laravel Queue orqali fonda bajarish}
                            {--query=* : Indekslangan kitob bo\'yicha sinov savollarini tekshirish (Retriever + RelevanceGate)}';

    /**
     * The console command description.
     */
    protected $description = 'Kitob PDF betlari va boblarini AI yordamchi (RAG) uchun bo\'laklarga ajratish va indekslash';

    public function handle(BookIndexer $indexer, Retriever $retriever, RelevanceGate $gate): int
    {
        $bookArg = $this->argument('book');
        $all = (bool) $this->option('all');
        $force = (bool) $this->option('force');
        $clear = (bool) $this->option('clear');
        $useQueue = (bool) $this->option('queue');
        $queries = (array) $this->option('query');

        if (!$bookArg && !$all) {
            $this->error('Kitob ID/slug kiriting yoki --all bayrog\'ini ko\'rsating. Misol: php artisan ai:index-book 5');
            return self::FAILURE;
        }

        if ($all) {
            $books = Book::where('is_active', true)->orderBy('id')->get();
        } else {
            $book = is_numeric($bookArg)
                ? Book::find((int) $bookArg)
                : Book::where('slug', $bookArg)->first();

            if (!$book) {
                $this->error("Kitob topilmadi: {$bookArg}");
                return self::FAILURE;
            }

            $books = collect([$book]);
        }

        if ($books->isEmpty()) {
            $this->warn('Indekslash uchun kitoblar topilmadi.');
            return self::SUCCESS;
        }

        if ($clear) {
            foreach ($books as $b) {
                $res = $indexer->clearBookIndex($b);
                $this->info("[#{$b->id}] «{$b->title}» kitobining AI indeksi tozalandi ({$res['deleted_chunks']} ta bo'lak, {$res['deleted_pages']} ta bet o'chirildi).");
            }
            return self::SUCCESS;
        }

        if ($useQueue) {
            foreach ($books as $b) {
                \App\Jobs\AiIndexBookJob::dispatch($b->id, $force);
                $this->info("[#{$b->id}] «{$b->title}» indekslash navbatiga (Queue) qo'yildi.");
            }
            return self::SUCCESS;
        }

        $rows = [];
        $hasFailure = false;

        foreach ($books as $book) {
            $this->info("Indekslanmoqda: [#{$book->id}] {$book->title} ({$book->slug})...");
            $startedAt = microtime(true);

            $result = $indexer->indexBook($book, $force, function (string $step, int $current, int $total) {
                $this->output->writeln("  -> {$step} ({$current}/{$total})");
            });

            $elapsed = round(microtime(true) - $startedAt, 2);

            if (!in_array($result['status'], ['ready', 'partial'], true)) {
                $hasFailure = true;
            } else {
                // CLI orqali indekslanganda sinov uchun ai_enabled = true qilib qo'yamiz (agar hali yoqilmagan bo'lsa)
                if (!$book->ai_enabled) {
                    $book->update(['ai_enabled' => true]);
                }
            }

            $rows[] = [
                $book->id,
                mb_strimwidth($book->title, 0, 28, '...'),
                $result['status'],
                number_format((float) $result['quality_score'], 3),
                $result['pages_count'],
                $result['chunks_count'],
                $result['embedded_new_count'],
                $result['reused_embeddings_count'],
                $result['embedding_mode'],
                "{$elapsed}s",
                $result['error_message'] ? mb_strimwidth($result['error_message'], 0, 45, '...') : '-',
            ];

            if (!empty($queries) && $result['status'] === 'ready') {
                $this->runTestQueries($book, $queries, $retriever, $gate);
            }
        }

        $this->newLine();
        $this->table([
            'ID',
            'Kitob',
            'Holat',
            'Matn sifati',
            'Betlar',
            'Bo\'laklar',
            'Yangi emb.',
            'Qayta ishlatildi',
            'Rejim',
            'Vaqt',
            'Xabar',
        ], $rows);

        return $hasFailure ? self::FAILURE : self::SUCCESS;
    }

    protected function runTestQueries(Book $book, array $queries, Retriever $retriever, RelevanceGate $gate): void
    {
        $this->newLine();
        $this->info("=== Sinov savollari: [#{$book->id}] {$book->title} ===");

        foreach ($queries as $idx => $q) {
            $q = trim((string) $q);
            if ($q === '') {
                continue;
            }

            $retrieval = $retriever->retrieve($book->id, $q, (int) config('ai.retrieval_chunks', 6));
            $decision = $gate->evaluate($q, $retrieval);

            $this->line(sprintf(
                "\n[%d-savol] \"%s\"\n  Rejim: %s | Top score: %.4f | Gate: %s%s",
                $idx + 1,
                $q,
                $retrieval['mode'],
                $retrieval['top_score'],
                $decision['passed'] ? 'PASSED (Ruxsat berildi)' : 'REFUSED (Rad etildi)',
                !empty($decision['reason']) ? " [{$decision['reason']}]" : ''
            ));

            foreach (array_slice($retrieval['chunks'], 0, 3) as $rank => $chunk) {
                $snippet = preg_replace('/\s+/u', ' ', trim((string) $chunk['text'])) ?? '';
                $snippet = mb_strimwidth($snippet, 0, 140, '...');
                $this->line(sprintf(
                    "    #%d [chunk_id=%d | ordinal=%d | manba: %s | score=%.4f]: %s",
                    $rank + 1,
                    $chunk['id'],
                    $chunk['ordinal'],
                    $chunk['source_label'],
                    $chunk['score'],
                    $snippet
                ));
            }
        }
    }
}
