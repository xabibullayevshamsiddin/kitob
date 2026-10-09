<?php

namespace App\Http\Controllers;

use App\Models\QuizAttempt;
use App\Services\Gamification\PointsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class QuizReviewController extends Controller
{
    public function index()
    {
        $query = QuizAttempt::with([
            'user:id,name,email',
            'quiz:id,title,book_id',
            'quiz.book:id,title',
            'quiz.questions:id,quiz_id,type',
        ])
            ->where('review_status', 'pending');

        if (!auth()->user()->isAdmin()) {
            $query->whereHas('quiz', fn ($quizQuery) => $quizQuery->where('created_by', auth()->id()));
        }

        $attempts = $query->latest('completed_at')->paginate(20);

        return view('teacher.quizzes.reviews.index', compact('attempts'));
    }

    public function show(QuizAttempt $attempt)
    {
        $this->authorizeQuizAttempt($attempt);
        $attempt->load([
            'user:id,name,email',
            'reviewer:id,name',
            'quiz.book:id,title',
            'quiz.questions.options',
        ]);

        return view('teacher.quizzes.reviews.show', compact('attempt'));
    }

    public function update(Request $request, QuizAttempt $attempt)
    {
        $this->authorizeQuizAttempt($attempt);
        abort_unless($attempt->review_status === 'pending', 404);

        $rules = [];
        foreach ($attempt->answers ?? [] as $index => $answer) {
            if (($answer['type'] ?? null) === 'text') {
                $points = (int) ($answer['points'] ?? 10);
                $rules["scores.{$index}"] = ['required', 'integer', 'min:0', 'max:' . $points];
            }
        }
        $data = $request->validate(['scores' => ['required', 'array'], ...$rules]);

        DB::transaction(function () use ($attempt, $data) {
            $lockedAttempt = QuizAttempt::whereKey($attempt->id)->lockForUpdate()->firstOrFail();
            if ($lockedAttempt->review_status !== 'pending') {
                throw ValidationException::withMessages(['review' => 'Bu javoblar allaqachon tekshirilgan.']);
            }

            $answers = $lockedAttempt->answers ?? [];
            $earned = 0;
            $possible = 0;

            foreach ($answers as $index => $answer) {
                $points = (int) ($answer['points'] ?? 10);
                $possible += $points;

                if (($answer['type'] ?? null) === 'text') {
                    $score = (int) $data['scores'][$index];
                    $answer['earned_points'] = $score;
                    $answer['correct'] = $score >= $points;
                    $answer['reviewed_by'] = auth()->id();
                    $earned += $score;
                } else {
                    $isCorrect = (bool) ($answer['correct'] ?? false);
                    $answer['earned_points'] = $isCorrect ? $points : 0;
                    $earned += $answer['earned_points'];
                }
                $answers[$index] = $answer;
            }

            $percent = $possible > 0 ? round(($earned / $possible) * 100, 2) : 0;
            $calculatedPoints = (int) round(($percent / 100) * (int) $lockedAttempt->quiz->reward_points);
            $hasEarlierAttempt = QuizAttempt::where('user_id', $lockedAttempt->user_id)
                ->where('quiz_id', $lockedAttempt->quiz_id)
                ->where('id', '<', $lockedAttempt->id)
                ->exists();
            $awardedPoints = $hasEarlierAttempt ? 0 : $calculatedPoints;

            $lockedAttempt->update([
                'answers' => $answers,
                'score' => $awardedPoints,
                'max_score' => (int) $lockedAttempt->quiz->reward_points,
                'percent' => $percent,
                'review_status' => 'reviewed',
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
                'is_full_points_awarded' => !$hasEarlierAttempt && $awardedPoints === (int) $lockedAttempt->quiz->reward_points && $awardedPoints > 0,
            ]);

            if ($awardedPoints > 0) {
                app(PointsService::class)->awardPoints(
                    $lockedAttempt->user,
                    $awardedPoints,
                    'quiz',
                    'Yozma test javoblari tekshirildi: +' . $awardedPoints . ' ball (' . $percent . '%)'
                );
            }

            \App\Services\NotifyUser::send(
                $lockedAttempt->user,
                'quiz',
                'Yozma test javoblari tekshirildi',
                'Yakuniy natijangiz: ' . $percent . '% (' . $awardedPoints . ' ball).',
                '📝',
                $lockedAttempt->quiz->book_id
                    ? route('quiz.show', ['book' => $lockedAttempt->quiz->book_id, 'quiz_id' => $lockedAttempt->quiz_id])
                    : ''
            );
        });

        $route = auth()->user()->isAdmin() ? 'admin.quiz-reviews.index' : 'teacher.quiz-reviews.index';
        return redirect()->route($route)->with('success', 'Javoblar baholandi, natija va ball yangilandi.');
    }

    private function authorizeQuizAttempt(QuizAttempt $attempt): void
    {
        if (auth()->user()->isAdmin()) {
            return;
        }

        abort_unless(
            $attempt->quiz()->where('created_by', auth()->id())->exists(),
            403,
            'Bu test boshqa ustozga tegishli.'
        );
    }
}
