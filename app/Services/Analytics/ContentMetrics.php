<?php

namespace App\Services\Analytics;

use App\Models\Book;
use App\Models\BookChapter;
use App\Models\BookComment;
use App\Models\BookReadingProgress;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\ReadingSession;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ContentMetrics
{
    /**
     * Eng ko'p o'qilgan kitoblar: o'quvchi soni, daqiqa, tugatish %, sharh bahosi.
     */
    public function getTopBooks(DateRange $range, int $limit = 15, bool $forceRefresh = false): array
    {
        $cacheKey = $range->cacheKey('content_top_books_' . $limit);
        $ttl = (int) config('analytics.cache_ttl', 300);

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, $ttl, function () use ($range, $limit) {
            $finishedThreshold = (float) config('analytics.book_finished_percent', 90.0);

            $sessionStats = ReadingSession::select([
                'book_id',
                DB::raw('COUNT(DISTINCT user_id) as session_readers'),
                DB::raw('SUM(minutes_read) as total_minutes'),
            ])
                ->whereNotNull('book_id')
                ->whereBetween('session_date', [$range->startDate(), $range->endDate()])
                ->groupBy('book_id')
                ->get()
                ->keyBy('book_id');

            // Barcha davr bo'yicha kitob boshlagan va tugatgan noyob foydalanuvchilar
            $progressReaders = BookReadingProgress::select([
                'book_id',
                DB::raw('COUNT(DISTINCT user_id) as total_started'),
            ])
                ->groupBy('book_id')
                ->pluck('total_started', 'book_id');

            $progressFinished = BookReadingProgress::select([
                'book_id',
                DB::raw('COUNT(DISTINCT user_id) as total_finished'),
            ])
                ->where('percent_complete', '>=', $finishedThreshold)
                ->groupBy('book_id')
                ->pluck('total_finished', 'book_id');

            // Sharh baholari
            $ratings = BookComment::select([
                'book_id',
                DB::raw('AVG(rating) as avg_rating'),
                DB::raw('COUNT(*) as comments_count'),
            ])
                ->whereNotNull('rating')
                ->groupBy('book_id')
                ->get()
                ->keyBy('book_id');

            $books = Book::select(['id', 'title', 'slug', 'author', 'genre', 'is_active'])
                ->get();

            $rows = [];
            foreach ($books as $book) {
                $bid = (int) $book->id;
                $sStat = $sessionStats->get($bid);
                $minutes = $sStat ? (int) $sStat->total_minutes : 0;
                $sessionReaders = $sStat ? (int) $sStat->session_readers : 0;
                $startedUsers = (int) ($progressReaders[$bid] ?? 0);
                $finishedUsers = (int) ($progressFinished[$bid] ?? 0);

                $readersCount = max($sessionReaders, $startedUsers);
                $denominator = max($startedUsers, $readersCount);
                $completionPct = $denominator > 0 ? round(($finishedUsers / $denominator) * 100, 1) : 0.0;

                $rStat = $ratings->get($bid);
                $avgRating = $rStat && $rStat->avg_rating !== null ? round((float) $rStat->avg_rating, 1) : null;
                $ratingsCount = $rStat ? (int) $rStat->comments_count : 0;

                $rows[] = [
                    'book_id'          => $bid,
                    'title'            => $book->title,
                    'author'           => $book->author,
                    'genre'            => $book->genre,
                    'is_active'        => (bool) $book->is_active,
                    'readers_count'    => $readersCount,
                    'minutes_read'     => $minutes,
                    'hours_read'       => round($minutes / 60, 1),
                    'finished_users'   => $finishedUsers,
                    'completion_pct'   => min(100.0, $completionPct),
                    'avg_rating'       => $avgRating,
                    'ratings_count'    => $ratingsCount,
                ];
            }

            usort($rows, function (array $a, array $b) {
                if ($b['minutes_read'] !== $a['minutes_read']) {
                    return $b['minutes_read'] <=> $a['minutes_read'];
                }
                return $b['readers_count'] <=> $a['readers_count'];
            });

            return array_slice($rows, 0, $limit);
        });
    }

    /**
     * ★ Kitob "salomatlik" ko'rsatkichi:
     * tugatish %, o'rtacha daqiqa, quiz natijasi, baho -> "eng yaxshi" va "e'tibor kerak" ro'yxatlari.
     */
    public function getBookHealth(DateRange $range, bool $forceRefresh = false): array
    {
        $cacheKey = $range->cacheKey('content_book_health');
        $ttl = (int) config('analytics.cache_ttl', 300);

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, $ttl, function () use ($range) {
            $topBooks = $this->getTopBooks($range, 100);

            // Har kitob bo'yicha baholangan quizlar o'rtacha foizi
            $quizAvgByBook = QuizAttempt::join('quizzes', 'quiz_attempts.quiz_id', '=', 'quizzes.id')
                ->whereNotNull('quiz_attempts.completed_at')
                ->where(function ($q) {
                    $q->whereNull('quiz_attempts.review_status')
                      ->orWhere('quiz_attempts.review_status', '!=', 'pending');
                })
                ->whereNotNull('quizzes.book_id')
                ->select([
                    'quizzes.book_id',
                    DB::raw('AVG(quiz_attempts.percent) as avg_quiz_pct'),
                    DB::raw('COUNT(quiz_attempts.id) as attempts_count'),
                ])
                ->groupBy('quizzes.book_id')
                ->get()
                ->keyBy('book_id');

            $evaluated = [];
            foreach ($topBooks as $b) {
                $bid = $b['book_id'];
                $qStat = $quizAvgByBook->get($bid);
                $quizPct = $qStat && $qStat->avg_quiz_pct !== null ? round((float) $qStat->avg_quiz_pct, 1) : null;
                $avgMinutesPerReader = $b['readers_count'] > 0
                    ? round($b['minutes_read'] / $b['readers_count'], 1)
                    : 0.0;

                // Salomatlik skori (0..100):
                // 40% tugatish foizi + 25% o'rtacha o'qish daqiqasi (60 daq = 100%) + 20% quiz + 15% reyting
                $compScore = $b['completion_pct'];
                $minScore = min(100.0, ($avgMinutesPerReader / 60.0) * 100.0);
                $qScore = $quizPct !== null ? $quizPct : 70.0;
                $rScore = $b['avg_rating'] !== null ? ($b['avg_rating'] / 5.0) * 100.0 : 75.0;

                $healthScore = round(
                    ($compScore * 0.40) + ($minScore * 0.25) + ($qScore * 0.20) + ($rScore * 0.15),
                    1
                );

                $reasons = [];
                if ($b['readers_count'] >= 3 && $b['completion_pct'] < 30.0) {
                    $reasons[] = "Tugatish foizi past ({$b['completion_pct']}%)";
                }
                if ($quizPct !== null && $quizPct < 55.0) {
                    $reasons[] = "Quiz natijasi past ({$quizPct}%)";
                }
                if ($b['avg_rating'] !== null && $b['avg_rating'] < 3.5) {
                    $reasons[] = "Sharh bahosi past ({$b['avg_rating']}/5)";
                }
                if ($b['readers_count'] > 0 && $avgMinutesPerReader < 10.0) {
                    $reasons[] = "O'rtacha mutolaa qisqa ({$avgMinutesPerReader} daq)";
                }

                $evaluated[] = array_merge($b, [
                    'avg_minutes_per_reader' => $avgMinutesPerReader,
                    'avg_quiz_pct'           => $quizPct,
                    'health_score'           => $healthScore,
                    'attention_reasons'      => $reasons,
                ]);
            }

            $best = $evaluated;
            usort($best, fn ($a, $b) => $b['health_score'] <=> $a['health_score']);

            $needsAttention = array_values(array_filter($evaluated, fn ($item) => !empty($item['attention_reasons']) || $item['health_score'] < 45.0));
            usort($needsAttention, fn ($a, $b) => $a['health_score'] <=> $b['health_score']);

            return [
                'best'            => array_slice($best, 0, 5),
                'needs_attention' => array_slice($needsAttention, 0, 5),
                'all'             => $evaluated,
            ];
        });
    }

    /**
     * Bob bo'yicha tushib ketish (drop-off) grafigi.
     */
    public function getChapterDropoff(?int $bookId = null): array
    {
        $finishedThreshold = (float) config('analytics.book_finished_percent', 90.0);

        if ($bookId === null) {
            $bookWithChapters = Book::whereHas('chapters')
                ->withCount('chapters')
                ->orderByDesc('chapters_count')
                ->first();
            $bookId = $bookWithChapters ? (int) $bookWithChapters->id : null;
        }

        if ($bookId === null) {
            return [
                'book_id'  => null,
                'book'     => null,
                'chapters' => [],
            ];
        }

        $book = Book::select(['id', 'title', 'author'])->find($bookId);
        if (!$book) {
            return [
                'book_id'  => null,
                'book'     => null,
                'chapters' => [],
            ];
        }

        $chapters = BookChapter::where('book_id', $book->id)
            ->orderBy('chapter_number')
            ->orderBy('id')
            ->get(['id', 'chapter_number', 'title']);

        $progressByChapter = BookReadingProgress::where('book_id', $book->id)
            ->whereNotNull('chapter_id')
            ->select([
                'chapter_id',
                DB::raw('COUNT(DISTINCT user_id) as readers_count'),
                DB::raw("COUNT(DISTINCT CASE WHEN percent_complete >= {$finishedThreshold} THEN user_id END) as completed_count"),
            ])
            ->groupBy('chapter_id')
            ->get()
            ->keyBy('chapter_id');

        $sessionReadersByChapter = ReadingSession::where('book_id', $book->id)
            ->whereNotNull('chapter_id')
            ->select([
                'chapter_id',
                DB::raw('COUNT(DISTINCT user_id) as session_readers'),
            ])
            ->groupBy('chapter_id')
            ->pluck('session_readers', 'chapter_id');

        $rows = [];
        $firstChapterReaders = null;

        foreach ($chapters as $ch) {
            $cid = (int) $ch->id;
            $pStat = $progressByChapter->get($cid);
            $pReaders = $pStat ? (int) $pStat->readers_count : 0;
            $sReaders = (int) ($sessionReadersByChapter[$cid] ?? 0);
            $readers = max($pReaders, $sReaders);
            $completed = $pStat ? (int) $pStat->completed_count : 0;

            if ($firstChapterReaders === null) {
                $firstChapterReaders = max($readers, 1);
            }

            $retentionFromCh1 = $firstChapterReaders > 0
                ? min(100.0, round(($readers / $firstChapterReaders) * 100, 1))
                : 0.0;
            $dropoffPct = round(max(0.0, 100.0 - $retentionFromCh1), 1);

            $rows[] = [
                'chapter_id'         => $cid,
                'chapter_number'     => (int) $ch->chapter_number,
                'title'              => $ch->title,
                'readers_count'      => $readers,
                'completed_count'    => $completed,
                'retention_from_ch1' => $retentionFromCh1,
                'dropoff_pct'        => $dropoffPct,
            ];
        }

        return [
            'book_id'  => (int) $book->id,
            'book'     => [
                'id'     => (int) $book->id,
                'title'  => $book->title,
                'author' => $book->author,
            ],
            'chapters' => $rows,
        ];
    }

    /**
     * Quiz tahlili:
     * - Faqat yakunlangan va baholangan urinishlar (`completed_at IS NOT NULL` va `review_status != 'pending'`)
     * - Umumiy o'rtacha foiz
     * - Eng qiyin / eng oson quizlar
     * - Savol darajasida (`answers` JSON ichidagi `question_id` va `correct` orqali) to'g'ri javob foizi
     */
    public function getQuizAnalytics(DateRange $range, bool $forceRefresh = false): array
    {
        $cacheKey = $range->cacheKey('content_quiz_analytics');
        $ttl = (int) config('analytics.cache_ttl', 300);

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, $ttl, function () use ($range) {
            $startStr = $range->start->format('Y-m-d H:i:s');
            $endStr = $range->end->format('Y-m-d H:i:s');
            $passingPercent = (int) (function_exists('setting') ? setting('quiz_passing_percent', 70) : 70);

            $gradedQuery = QuizAttempt::whereNotNull('completed_at')
                ->whereBetween('completed_at', [$startStr, $endStr])
                ->where(function ($q) {
                    $q->whereNull('review_status')
                      ->orWhere('review_status', '!=', 'pending');
                });

            $pendingReviewsCount = (int) QuizAttempt::where('review_status', 'pending')
                ->whereBetween('completed_at', [$startStr, $endStr])
                ->count();

            $totalGraded = (int) (clone $gradedQuery)->count();
            $overallAvgPercent = $totalGraded > 0
                ? round((float) (clone $gradedQuery)->avg('percent'), 1)
                : 0.0;
            $passedCount = (int) (clone $gradedQuery)->where('percent', '>=', $passingPercent)->count();
            $passRatePercent = $totalGraded > 0 ? round(($passedCount / $totalGraded) * 100, 1) : 0.0;

            // Quiz darajasida statistika
            $quizRows = QuizAttempt::join('quizzes', 'quiz_attempts.quiz_id', '=', 'quizzes.id')
                ->leftJoin('books', 'quizzes.book_id', '=', 'books.id')
                ->whereNotNull('quiz_attempts.completed_at')
                ->whereBetween('quiz_attempts.completed_at', [$startStr, $endStr])
                ->where(function ($q) {
                    $q->whereNull('quiz_attempts.review_status')
                      ->orWhere('quiz_attempts.review_status', '!=', 'pending');
                })
                ->select([
                    'quizzes.id as quiz_id',
                    'quizzes.title as quiz_title',
                    'books.title as book_title',
                    DB::raw('COUNT(quiz_attempts.id) as attempts_count'),
                    DB::raw('AVG(quiz_attempts.percent) as avg_percent'),
                ])
                ->groupBy('quizzes.id', 'quizzes.title', 'books.title')
                ->get();

            $byQuiz = [];
            foreach ($quizRows as $qr) {
                $byQuiz[] = [
                    'quiz_id'        => (int) $qr->quiz_id,
                    'quiz_title'     => $qr->quiz_title,
                    'book_title'     => $qr->book_title,
                    'attempts_count' => (int) $qr->attempts_count,
                    'avg_percent'    => round((float) $qr->avg_percent, 1),
                ];
            }

            $hardestQuizzes = $byQuiz;
            usort($hardestQuizzes, fn ($a, $b) => $a['avg_percent'] <=> $b['avg_percent']);

            $easiestQuizzes = $byQuiz;
            usort($easiestQuizzes, fn ($a, $b) => $b['avg_percent'] <=> $a['avg_percent']);

            // Savol darajasida tahlil (answers JSON tuzilmasi asosida)
            $questionMap = [];
            (clone $gradedQuery)
                ->select(['id', 'quiz_id', 'answers'])
                ->chunkById(500, function ($attempts) use (&$questionMap) {
                    foreach ($attempts as $attempt) {
                        $answers = is_array($attempt->answers)
                            ? $attempt->answers
                            : (is_string($attempt->answers) ? json_decode($attempt->answers, true) : []);

                        if (!is_array($answers)) {
                            continue;
                        }

                        foreach ($answers as $ans) {
                            if (!is_array($ans) || !isset($ans['question_id'])) {
                                continue;
                            }
                            if (!array_key_exists('correct', $ans) || $ans['correct'] === null) {
                                continue;
                            }

                            $qid = (int) $ans['question_id'];
                            if (!isset($questionMap[$qid])) {
                                $questionMap[$qid] = [
                                    'question_id'    => $qid,
                                    'quiz_id'        => (int) $attempt->quiz_id,
                                    'question_text'  => (string) ($ans['question_text'] ?? ("Savol #{$qid}")),
                                    'type'           => (string) ($ans['type'] ?? 'single'),
                                    'attempts_count' => 0,
                                    'correct_count'  => 0,
                                ];
                            }

                            $questionMap[$qid]['attempts_count']++;
                            if (!empty($ans['correct'])) {
                                $questionMap[$qid]['correct_count']++;
                            }
                        }
                    }
                });

            $questionsStats = [];
            foreach ($questionMap as $qItem) {
                $acc = $qItem['attempts_count'] > 0
                    ? round(($qItem['correct_count'] / $qItem['attempts_count']) * 100, 1)
                    : 0.0;
                $qItem['accuracy_percent'] = $acc;
                $questionsStats[] = $qItem;
            }

            usort($questionsStats, fn ($a, $b) => $a['accuracy_percent'] <=> $b['accuracy_percent']);

            return [
                'total_graded_attempts'  => $totalGraded,
                'pending_reviews_count'  => $pendingReviewsCount,
                'overall_avg_percent'    => $overallAvgPercent,
                'pass_rate_percent'      => $passRatePercent,
                'passing_threshold'      => $passingPercent,
                'hardest_quizzes'        => array_slice($hardestQuizzes, 0, 5),
                'easiest_quizzes'        => array_slice($easiestQuizzes, 0, 5),
                'question_level_enabled' => true,
                'hardest_questions'      => array_slice($questionsStats, 0, 10),
                'questions_stats'        => $questionsStats,
            ];
        });
    }
}
