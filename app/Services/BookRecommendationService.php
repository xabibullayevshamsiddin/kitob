<?php

namespace App\Services;

use App\Models\Book;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BookRecommendationService
{
    public function forUser(User $user, int $limit = 4): Collection
    {
        $genreScores = collect();

        DB::table('book_reading_progress')
            ->join('books', 'books.id', '=', 'book_reading_progress.book_id')
            ->where('book_reading_progress.user_id', $user->id)
            ->whereNotNull('books.genre')
            ->where('books.genre', '!=', '')
            ->groupBy('books.genre')
            ->selectRaw('books.genre, COUNT(*) * 2 + SUM(book_reading_progress.percent_complete) / 100 as score')
            ->get()
            ->each(fn ($row) => $genreScores->put($row->genre, (float) $row->score));

        DB::table('reading_sessions')
            ->join('books', 'books.id', '=', 'reading_sessions.book_id')
            ->where('reading_sessions.user_id', $user->id)
            ->whereNotNull('books.genre')
            ->where('books.genre', '!=', '')
            ->groupBy('books.genre')
            ->selectRaw('books.genre, COUNT(*) as sessions_count')
            ->get()
            ->each(function ($row) use ($genreScores) {
                $genreScores->put(
                    $row->genre,
                    ($genreScores->get($row->genre, 0) + min(sqrt((int) $row->sessions_count), 5))
                );
            });

        $preferredGenres = $genreScores->sortDesc()->keys()->values()->all();
        $genreRanks = array_flip($preferredGenres);

        $candidates = Book::availableForUser($user)
            ->withCount(['chapters', 'audios', 'videos', 'quizzes', 'readingSessions'])
            ->whereDoesntHave('readingProgress', fn ($query) => $query->where('user_id', $user->id))
            ->orderByDesc('week_number')
            ->orderBy('id')
            ->limit(max(24, $limit * 6))
            ->get();

        return $candidates
            ->sortBy(fn (Book $book) => [
                $genreRanks[$book->genre] ?? count($genreRanks) + 1,
                -(int) $book->reading_sessions_count,
                -(int) $book->week_number,
                (int) $book->id,
            ])
            ->take($limit)
            ->values();
    }

    public function hasHistory(User $user): bool
    {
        return DB::table('book_reading_progress')->where('user_id', $user->id)->exists()
            || DB::table('reading_sessions')->where('user_id', $user->id)->exists();
    }
}
