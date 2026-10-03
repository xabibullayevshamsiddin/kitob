<?php

namespace App\Http\Livewire\Quiz;

use App\Models\Book;
use App\Models\PointTransaction;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Services\Gamification\PointsService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class TakeQuiz extends Component
{
    public Book $book;

    /** Currently selected Quiz ID */
    public ?int $quizId = null;

    /** Quiz metadata */
    public ?string $quizTitle = null;
    public ?string $quizDescription = null;
    public ?string $quizDifficulty = null;
    public ?int $timeLimitMinutes = null;

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

    /** Percentage achieved */
    public float $percent = 0;

    /** Points actually added in this attempt (shown in result screen) */
    public int $pointsAwarded = 0;

    public bool $alreadyHadFullPoints = false;

    /** Collected per-question feedback: ['correct' => bool, 'explanation' => ?string, 'selected_text' => ?string] */
    public array $feedback = [];

    /** All quiz data for rendering (no correct answers leaked to the client) */
    public array $questions = [];

    protected $queryString = [
        'quizId' => ['except' => null, 'as' => 'quiz_id'],
    ];

    public function mount(Book $book, ?int $quiz_id = null)
    {
        $this->book = $book;

        $targetQuizId = $quiz_id ?: request()->query('quiz_id', $this->quizId);
        $this->loadQuiz($targetQuizId ? (int) $targetQuizId : null);
    }

    public function selectQuiz(int $id): void
    {
        $this->loadQuiz($id);
    }

    public function restartQuiz(): void
    {
        $this->loadQuiz($this->quizId);
    }

    public function loadQuiz(?int $targetQuizId = null): void
    {
        $this->resetQuizState();

        $query = $this->book->quizzes()->with(['questions.options']);

        if ($targetQuizId) {
            $quiz = (clone $query)->where('id', $targetQuizId)->first();
        } else {
            // Prefer active quiz that actually has questions, or latest quiz
            $quiz = (clone $query)->active()->whereHas('questions')->latest()->first()
                 ?? (clone $query)->whereHas('questions')->latest()->first()
                 ?? (clone $query)->active()->latest()->first()
                 ?? (clone $query)->latest()->first();
        }

        if (!$quiz) {
            $this->quizId = null;
            $this->quizTitle = null;
            $this->questions = [];
            $this->maxScore = 0;
            return;
        }

        $this->quizId = $quiz->id;
        $this->quizTitle = $quiz->title;
        $this->quizDescription = $quiz->description;
        $this->quizDifficulty = $quiz->difficulty ?? 'medium';
        $this->timeLimitMinutes = $quiz->time_limit_minutes;

        if ($quiz->questions->isEmpty()) {
            $this->questions = [];
            $this->maxScore = 0;
            return;
        }

        $this->maxScore = (int) $quiz->questions->sum('points');

        $this->questions = $quiz->questions->values()->map(function ($q) {
            return [
                'id'          => $q->id,
                'text'        => $q->question_text,
                'points'      => (int) ($q->points ?: 10),
                'explanation' => $q->explanation,
                'options'     => $q->options->values()->map(fn ($o) => [
                    'id'   => $o->id,
                    'text' => $o->option_text,
                ])->all(),
            ];
        })->all();
    }

    protected function resetQuizState(): void
    {
        $this->currentQuestion = 0;
        $this->selectedOption = null;
        $this->finished = false;
        $this->score = 0;
        $this->maxScore = 0;
        $this->percent = 0;
        $this->pointsAwarded = 0;
        $this->alreadyHadFullPoints = false;
        $this->feedback = [];
        $this->questions = [];
    }

    public function nextQuestion(): void
    {
        if ($this->finished) {
            return;
        }

        $question = $this->questions[$this->currentQuestion] ?? null;
        abort_if(!$question, 404);

        $correctOption = DB::table('quiz_options')
            ->where('question_id', $question['id'])
            ->where('is_correct', true)
            ->first();

        $correctOptionId = $correctOption ? $correctOption->id : null;

        $isCorrect = $this->selectedOption !== null
            && (int) $this->selectedOption === (int) $correctOptionId;

        if ($isCorrect) {
            $this->score += $question['points'];
        }

        $selectedOptionText = null;
        foreach ($question['options'] as $opt) {
            if ((int) $opt['id'] === (int) $this->selectedOption) {
                $selectedOptionText = $opt['text'];
                break;
            }
        }

        $this->feedback[$this->currentQuestion] = [
            'question_text' => $question['text'],
            'correct'       => $isCorrect,
            'selected_id'   => $this->selectedOption,
            'selected_text' => $selectedOptionText,
            'correct_text'  => $correctOption ? $correctOption->option_text : null,
            'explanation'   => $question['explanation'],
            'points'        => $isCorrect ? $question['points'] : 0,
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

        $user = Auth::user();
        $quiz = Quiz::find($this->quizId);

        $this->percent = $this->maxScore > 0 ? round(($this->score / $this->maxScore) * 100, 2) : 0;

        if (!$quiz || !$user) {
            return;
        }

        // Anti-farming: full points only on the very first attempt where max score is reached
        $alreadyPassed = QuizAttempt::where('user_id', $user->id)
            ->where('quiz_id', $quiz->id)
            ->where('score', '>=', $this->maxScore)
            ->exists();

        $pointsToAward = ($this->score > 0 && !$alreadyPassed) ? $this->score : 0;

        DB::transaction(function () use ($user, $quiz, $pointsToAward) {
            QuizAttempt::create([
                'user_id'                => $user->id,
                'quiz_id'                => $quiz->id,
                'score'                  => $this->score,
                'max_score'              => $this->maxScore,
                'percent'                => $this->percent,
                'answers'                => $this->feedback,
                'is_full_points_awarded' => $pointsToAward === $this->score && $this->score > 0,
                'completed_at'           => now(),
            ]);

            if ($pointsToAward > 0) {
                app(PointsService::class)->awardPoints(
                    $user,
                    $pointsToAward,
                    'quiz',
                    '«' . $this->book->title . '» (' . $quiz->title . ') testi: ' . $this->score . '/' . $this->maxScore . ' ball'
                );
            }
        });

        $this->pointsAwarded = $pointsToAward;
        $this->alreadyHadFullPoints = $alreadyPassed;

        // Bildirishnoma yuborish
        \App\Services\NotifyUser::send(
            $user,
            'quiz',
            $this->percent >= 80 ? 'Test a\'lo darajada o\'tdi! 🎯' : ($this->percent >= 50 ? 'Test muvaffaqiyatli yakunlandi' : 'Test yakunlandi'),
            '«' . $this->book->title . '» — ' . $quiz->title . ': ' . $this->score . '/' . $this->maxScore . ' ball (' . $this->percent . '%)',
            '📝',
            route('quiz.show', ['book' => $this->book->id, 'quiz_id' => $quiz->id])
        );
    }

    public function render()
    {
        $allQuizzes = $this->book->quizzes()
            ->withCount('questions')
            ->orderBy('id', 'desc')
            ->get();

        $attemptCount = (Auth::check() && $this->quizId)
            ? QuizAttempt::where('user_id', Auth::id())->where('quiz_id', $this->quizId)->count()
            : 0;

        return view('livewire.quiz.take-quiz', [
            'book'             => $this->book,
            'allQuizzes'       => $allQuizzes,
            'quizId'           => $this->quizId,
            'quizTitle'        => $this->quizTitle,
            'quizDescription'  => $this->quizDescription,
            'quizDifficulty'   => $this->quizDifficulty,
            'timeLimitMinutes' => $this->timeLimitMinutes,
            'attemptCount'     => $attemptCount,
            'questions'        => $this->questions,
            'currentQuestion'  => $this->currentQuestion,
            'selectedOption'   => $this->selectedOption,
            'finished'         => $this->finished,
            'score'            => $this->score,
            'maxScore'         => $this->maxScore,
            'percent'          => $this->percent,
            'pointsAwarded'    => $this->pointsAwarded,
            'alreadyHadFullPoints' => $this->alreadyHadFullPoints,
            'feedback'         => $this->feedback,
        ])->layout('layouts.app', ['title' => 'Test – ' . $this->book->title]);
    }
}
