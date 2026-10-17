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
        if ($wantsEnabled && $book->ai_index_status !== 'ready') {
            /** @var BookIndexer $indexer */
            $indexer = app(BookIndexer::class);
            $indexResult = $indexer->indexBook($book->fresh());

            if ($indexResult['status'] !== 'ready') {
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

        $force = (bool) $request->boolean('force', false);
        $stepsLog = [];

        $result = $indexer->indexBook($book, $force, function (string $step, int $current, int $total) use (&$stepsLog) {
            $stepsLog[] = [
                'step'    => $step,
                'current' => $current,
                'total'   => $total,
            ];
        });

        if ($result['status'] === 'ready' && $request->boolean('enable_after_index', true)) {
            $book->update(['ai_enabled' => true]);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'ok'     => $result['status'] === 'ready',
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
            ], $result['status'] === 'ready' ? 200 : 422);
        }

        if ($result['status'] !== 'ready') {
            return redirect()
                ->route('admin.books.edit', $book->id)
                ->with('error', $result['error_message'] ?: trans('ai.low_text_quality'));
        }

        return redirect()
            ->route('admin.books.edit', $book->id)
            ->with(
                'success',
                sprintf(
                    'Kitob muvaffaqiyatli indekslandi: %d ta bet, %d ta bo\'lak (matn sifati: %d%%).',
                    $result['pages_count'],
                    $result['chunks_count'],
                    (int) round($result['quality_score'] * 100)
                )
            );
    }
}
