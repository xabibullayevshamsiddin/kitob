<?php

namespace App\Http\Livewire\Quiz;

use App\Models\Book;
use App\Models\PointTransaction;
use App\Models\QuizAttempt;
use App\Services\Gamification\PointsService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class TakeQuiz extends Component
{
    public Book $book;

    /** Currently displayed question index */
    public int $currentQuestion = 0;

    /** Selected option id for the current question */
    public $selectedOption = null;

    /** Set when the whole quiz is finished */
    public bool $finished = false;

    /** Accumulated server-side score */
    public int $score = 0;

    /** Total possible points */
    public int $maxScore = 0;

    /** Collected per-question feedback: ['correct' => bool, 'explanation' => ?string] */
    public array $feedback = [];

    /** All quiz data for rendering (no correct answers leaked to the client) */
    public array $questions = [];

    public function mount(Book $book)
    {
        $quiz = $book->quizzes()->active()->with(['questions.options'])->first();

        abort_unless($quiz, 404, 'Bu kitob uchun hozircha test mavjud emas.');

        $this->maxScore = (int) $quiz->questions->sum('points');

        $this->questions = $quiz->questions->map(function ($q) {
            return [
                'id'          => $q->id,
                'text'        => $q->question_text,
                'points'      => (int) $q->points,
                'explanation' => $q->explanation,
                'options'     => $q->options->map(fn ($o) => [
                    'id'   => $o->id,
                    'text' => $o->option_text,
                ])->all(),
            ];
        })->all();
    }

    public function nextQuestion(): void
    {
        if ($this->finished) {
            return;
        }

        $question = $this->questions[$this->currentQuestion] ?? null;
        abort_if(!$question, 404);

        $correctOptionId = DB::table('quiz_options')
            ->where('question_id', $question['id'])
            ->where('is_correct', true)
            ->value('id');

        $isCorrect = $this->selectedOption !== null
            && (int) $this->selectedOption === (int) $correctOptionId;

        if ($isCorrect) {
            $this->score += $question['points'];
        }

        $this->feedback[$this->currentQuestion] = [
            'correct'     => $isCorrect,
            'explanation' => $question['explanation'],
        ];

        $this->selectedOption = null;

        if ($this->currentQuestion < count($this->questions) - 1) {
            $this->currentQuestion++;
        } else {
            $this->finishQuiz();
        }
    }

    protected function finishQuiz(): void
    {
        $this->finished = true;

        $user  = Auth::user();
        $quiz  = $this->book->quizzes()->active()->first();
        $percent = $this->maxScore > 0 ? round(($this->score / $this->maxScore) * 100, 2) : 0;

        // Anti-farming: full points only on the very first attempt
        $alreadyPassed = QuizAttempt::where('user_id', $user->id)
            ->where('quiz_id', $quiz->id)
            ->where('score', '>=', $this->maxScore)
            ->exists();

        $pointsToAward = ($this->score > 0 && !$alreadyPassed) ? $this->score : 0;

        DB::transaction(function () use ($user, $quiz, $percent, $pointsToAward) {
            QuizAttempt::create([
                'user_id'                 => $user->id,
                'quiz_id'                 => $quiz->id,
                'score'                   => $this->score,
                'max_score'               => $this->maxScore,
                'percent'                 => $percent,
                'answers'                 => $this->feedback,
                'is_full_points_awarded'  => $pointsToAward === $this->score && $this->score > 0,
                'completed_at'            => now(),
            ]);

            if ($pointsToAward > 0) {
                app(PointsService::class)->awardPoints(
                    $user,
                    $pointsToAward,
                    'quiz',
                    '«' . $this->book->title . '» testi: ' . $this->score . '/' . $this->maxScore . ' ball'
                );
            }
        });

        $this->pointsAwarded = $pointsToAward;
        $this->alreadyHadFullPoints = $alreadyPassed;
    }

    /** Points actually added in this attempt (shown in result screen) */
    public int $pointsAwarded = 0;

    public bool $alreadyHadFullPoints = false;

    public function render()
    {
        return view('livewire.quiz.take-quiz', [
            'attemptCount' => Auth::check()
                ? QuizAttempt::where('user_id', Auth::id())->where('quiz_id', $this->book->quizzes()->active()->value('id'))->count()
                : 0,
        ])->layout('layouts.app', ['title' => 'Test – ' . $this->book->title]);
    }
}
