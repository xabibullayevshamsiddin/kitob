<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Services\AI\BookAssistant\BookIndexer;
use Illuminate\Http\Request;

class BookAiController extends Controller
{
    /**
     * Kitob uchun AI yordamchi sozlamalarini (yoqish/o'chirish va qisqa konspekt) saqlash.
     */
    public function updateSettings(Request $request, Book $book)
    {
        $validated = $request->validate([
            'ai_enabled' => 'nullable|boolean',
            'ai_summary' => 'nullable|string|max:3000',
        ]);

        $wantsEnabled = $request->has('ai_enabled') && (bool) $request->input('ai_enabled');

        if (array_key_exists('ai_summary', $validated)) {
            $book->update(['ai_summary' => $validated['ai_summary']]);
        }

        // Agar kitob hali indekslanmagan bo'lsa va admin "AI yoqish" ni bossa, avval indekslab ko'ramiz
        if ($wantsEnabled && !in_array($book->ai_index_status, ['ready', 'partial'], true)) {
            /** @var BookIndexer $indexer */
            $indexer = app(BookIndexer::class);
            $indexResult = $indexer->indexBook($book->fresh());

            if (!in_array($indexResult['status'], ['ready', 'partial'], true)) {
                $book->update([
                    'ai_enabled' => false,
                ]);

                $targetRoute = $request->boolean('redirect_to_book')
                    ? route('books.show', $book->slug)
                    : route('admin.books.edit', $book->id);

                return redirect($targetRoute)
                    ->with('error', $indexResult['error_message'] ?: trans('ai.low_text_quality'));
            }
        }

        $book->update([
            'ai_enabled' => $wantsEnabled,
        ]);

        $targetRoute = $request->boolean('redirect_to_book')
            ? route('books.show', $book->slug)
            : route('admin.books.edit', $book->id);

        return redirect($targetRoute)
            ->with('success', $wantsEnabled ? 'Kitob uchun AI yordamchi yoqildi va saqlandi.' : 'Kitob AI yordamchi sozlamalari saqlandi.');
    }

    /**
     * Kitobni (PDF va boblarni) partiyalab indekslash ("Qayta indekslash" tugmasi).
     * Ham oddiy POST form, ham AJAX (JSON) so'rovlarini qo'llab-quvvatlaydi.
     */
    public function indexBook(Request $request, Book $book, BookIndexer $indexer)
    {
        @set_time_limit(180);

        if ($request->has('ai_summary') && trim((string) $request->input('ai_summary')) !== '') {
            $book->update(['ai_summary' => trim((string) $request->input('ai_summary'))]);
        }

        $force = (bool) $request->boolean('force', false);

        // Agar async so'ralgan bo'lsa yoki navbat yoqilgan bo'lsa -> Job orqali fonda ishga tushirish
        if ($request->boolean('async') || config('ai.queue.enabled', false)) {
            \App\Jobs\AiIndexBookJob::dispatch($book->id, $force);

            if ($request->expectsJson()) {
                return response()->json([
                    'ok'         => true,
                    'queued'     => true,
                    'message'    => "«{$book->title}» kitobini indekslash navbatga qo'yildi.",
                    'status_url' => route('admin.books.ai.status', $book->id),
                    'book'       => [
                        'id'              => $book->id,
                        'ai_index_status' => 'indexing',
                    ],
                ], 202);
            }

            return redirect()
                ->route('admin.books.edit', $book->id)
                ->with('info', "Kitobni indekslash navbatga qo'yildi. Jarayon yakunlangach holat yangilanadi.");
        }

        @set_time_limit(300);

        $stepsLog = [];
        $result = $indexer->indexBook($book, $force, function (string $step, int $current, int $total) use (&$stepsLog) {
            $stepsLog[] = [
                'step'    => $step,
                'current' => $current,
                'total'   => $total,
            ];
        });

        if (in_array($result['status'], ['ready', 'partial'], true) && $request->boolean('enable_after_index', true)) {
            $book->update(['ai_enabled' => true]);
        }

        if ($request->expectsJson()) {
            $isSuccess = in_array($result['status'], ['ready', 'partial'], true);
            return response()->json([
                'ok'     => $isSuccess,
                'result' => $result,
                'steps'  => $stepsLog,
                'book'   => [
                    'id'              => $book->id,
                    'ai_enabled'      => (bool) $book->fresh()->ai_enabled,
                    'ai_index_status' => $book->fresh()->ai_index_status,
                    'ai_text_quality' => $book->fresh()->ai_text_quality,
                    'ai_indexed_at'   => optional($book->fresh()->ai_indexed_at)->format('d.m.Y H:i'),
                    'chunks_count'    => $book->chunks()->count(),
                    'pages_count'     => $book->pages()->count(),
                ],
            ], $isSuccess ? 200 : 422);
        }

        if (!in_array($result['status'], ['ready', 'partial'], true)) {
            return redirect()
                ->route('admin.books.edit', $book->id)
                ->with('error', $result['error_message'] ?: trans('ai.low_text_quality'));
        }

        $notice = $result['status'] === 'partial'
            ? sprintf('Kitob qisman indekslandi (faqat konspekt): %d ta bo\'lak.', $result['chunks_count'])
            : sprintf('Kitob muvaffaqiyatli indekslandi: %d ta bet, %d ta bo\'lak (matn sifati: %d%%).', $result['pages_count'], $result['chunks_count'], (int) round($result['quality_score'] * 100));

        return redirect()
            ->route('admin.books.edit', $book->id)
            ->with('success', $notice);
    }

    /**
     * Kitobning joriy indekslash jarayoni (progress) va holatini qaytaradi.
     */
    public function status(Request $request, Book $book)
    {
        $progressKey = "ai_indexing_progress_{$book->id}";
        $progress = \Illuminate\Support\Facades\Cache::get($progressKey, [
            'status'  => $book->ai_index_status ?? 'none',
            'percent' => $book->ai_index_status === 'ready' ? 100 : 0,
            'step'    => $book->ai_index_status,
        ]);

        return response()->json([
            'ok'       => true,
            'progress' => $progress,
            'book'     => [
                'id'              => $book->id,
                'title'           => $book->title,
                'ai_enabled'      => (bool) $book->ai_enabled,
                'ai_index_status' => $book->ai_index_status,
                'ai_text_quality' => $book->ai_text_quality,
                'chunks_count'    => $book->chunks()->count(),
                'pages_count'     => $book->pages()->count(),
            ],
        ]);
    }

    /**
     * FAQAT ushbu kitobning AI indeksini va barcha bo'laklarini tozalash.
     * Boshqa kitoblar ma'lumotlariga mutlaqo tegmaydi.
     */
    public function clearIndex(Request $request, Book $book, BookIndexer $indexer)
    {
        $res = $indexer->clearBookIndex($book);

        if ($request->expectsJson()) {
            return response()->json([
                'ok'      => true,
                'message' => "«{$book->title}» kitobining AI indeksi va barcha bo'laklari tozalandi.",
                'deleted' => $res,
                'book'    => [
                    'id'              => $book->id,
                    'ai_enabled'      => false,
                    'ai_index_status' => 'none',
                    'chunks_count'    => 0,
                    'pages_count'     => 0,
                ],
            ]);
        }

        $targetRoute = $request->boolean('redirect_to_book')
            ? route('books.show', $book->slug)
            : ($request->headers->get('referer') ?: route('admin.books.index'));

        return redirect($targetRoute)->with(
            'success',
            sprintf("«%s» kitobining AI indeksi tozalandi (%d ta bo'lak, %d ta bet o'chirildi).", $book->title, $res['deleted_chunks'], $res['deleted_pages'])
        );
    }
}
