<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookComment;
use App\Models\BookCommentLike;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BookCommentController extends Controller
{
    /**
     * Kitobga yangi izoh yoki javob yozish.
     */
    public function store(Request $request, Book $book)
    {
        $validated = $request->validate([
            'comment'     => ['required', 'string', 'min:1', 'max:2000'],
            'rating'      => ['nullable', 'integer', 'min:1', 'max:5'],
            'parent_id'   => [
                'nullable',
                Rule::exists('book_comments', 'id')->where('book_id', $book->id),
            ],
            'page_number' => ['nullable', 'integer', 'min:1'],
        ]);

        $comment = BookComment::create([
            'book_id'     => $book->id,
            'user_id'     => auth()->id(),
            'parent_id'   => $validated['parent_id'] ?? null,
            'page_number' => $validated['page_number'] ?? null,
            'rating'      => empty($validated['parent_id']) ? ($validated['rating'] ?? null) : null,
            'comment'     => trim($validated['comment']),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Izohingiz muvaffaqiyatli qoldirildi!',
                'comment' => $comment->load('user'),
                'total_comments' => $book->allComments()->count(),
                'average_rating' => $book->averageRating(),
            ]);
        }

        return back()->with('success', 'Izohingiz muvaffaqiyatli qoldirildi!');
    }

    /**
     * Izohni o'chirish (o'z egasi yoki admin).
     */
    public function destroy(Request $request, BookComment $comment)
    {
        abort_unless($comment->canDelete(auth()->user()), 403, 'Bu izohni o\'chirish huquqingiz yo\'q.');

        $book = $comment->book;
        $comment->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Izoh o\'chirildi.',
                'total_comments' => $book ? $book->allComments()->count() : 0,
            ]);
        }

        return back()->with('success', 'Izoh muvaffaqiyatli o\'chirildi.');
    }

    /**
     * Izohga layk bosish yoki bekor qilish.
     */
    public function toggleLike(Request $request, BookComment $comment)
    {
        $userId = auth()->id();
        $like = BookCommentLike::where('book_comment_id', $comment->id)
            ->where('user_id', $userId)
            ->first();

        if ($like) {
            $like->delete();
            $comment->decrement('likes_count');
            $liked = false;
        } else {
            BookCommentLike::create([
                'book_comment_id' => $comment->id,
                'user_id'         => $userId,
            ]);
            $comment->increment('likes_count');
            $liked = true;
        }

        $freshLikes = (int) $comment->fresh()->likes_count;

        if ($request->wantsJson()) {
            return response()->json([
                'success'     => true,
                'liked'       => $liked,
                'likes_count' => $freshLikes,
            ]);
        }

        return back();
    }
}
