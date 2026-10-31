<?php

namespace App\Http\Controllers;

use App\Models\BookChapter;
use App\Models\Bookmark;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReaderAnnotationController extends Controller
{
    public function storeBookmark(Request $request): JsonResponse
    {
        $data = $request->validate([
            'book_id' => ['required', 'integer', 'exists:books,id'],
            'chapter_id' => ['required', 'integer', 'exists:book_chapters,id'],
            'position' => ['required', 'integer', 'min:0', 'max:10000000'],
        ]);

        BookChapter::query()
            ->whereKey($data['chapter_id'])
            ->where('book_id', $data['book_id'])
            ->firstOrFail();

        $bookmark = $request->user()->bookmarks()->create([
            'book_id' => $data['book_id'],
            'chapter_id' => $data['chapter_id'],
            'position' => $data['position'],
        ]);

        return response()->json([
            'bookmark' => [
                'id' => $bookmark->id,
                'position' => $bookmark->position,
                'note' => $bookmark->note,
            ],
        ], 201);
    }

    public function destroyBookmark(Request $request, Bookmark $bookmark): JsonResponse
    {
        abort_unless((int) $bookmark->user_id === (int) $request->user()->id, 404);

        $bookmark->delete();

        return response()->json(['deleted' => true]);
    }

    public function storeNote(Request $request): JsonResponse
    {
        $request->merge(['note_text' => trim((string) $request->input('note_text', ''))]);
        $data = $request->validate([
            'book_id' => ['required', 'integer', 'exists:books,id'],
            'chapter_id' => ['required', 'integer', 'exists:book_chapters,id'],
            'position' => ['required', 'integer', 'min:0', 'max:10000000'],
            'selected_text' => ['nullable', 'string', 'max:5000'],
            'color' => ['nullable', 'string', 'in:amber,emerald,sky,rose,purple,custom'],
            'note_text' => ['required', 'string', 'min:1', 'max:5000'],
        ]);

        BookChapter::query()
            ->whereKey($data['chapter_id'])
            ->where('book_id', $data['book_id'])
            ->firstOrFail();

        $note = $request->user()->notes()->create([
            'book_id' => $data['book_id'],
            'chapter_id' => $data['chapter_id'],
            'position' => $data['position'],
            'selected_text' => trim((string) ($data['selected_text'] ?? '')) ?: null,
            'color' => $data['color'] ?? 'amber',
            'note_text' => trim($data['note_text']),
        ]);

        return response()->json([
            'note' => [
                'id' => $note->id,
                'position' => $note->position,
                'selected_text' => $note->selected_text,
                'color' => $note->color,
                'note_text' => $note->note_text,
                'created_at' => $note->created_at->format('d.m.Y H:i'),
            ],
        ], 201);
    }
}
