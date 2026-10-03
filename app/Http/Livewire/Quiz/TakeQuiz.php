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

    /** Maximum reward points set for this quiz (max 50 for teachers, max 200 for admins) */
    public int $maxRewardPoints = 50;

    /** Currently displayed question index */
    public int $currentQuestion = 0;

    /** Selected option id for the current question */
    public $selectedOption = null;

    /** Number of questions answered correctly */
    public int $correctCount = 0;

    /** Set when the whole quiz is finished */
    public bool $finished = false;

    /** Earned score in points */
    public int $score = 0;

    /** Max possible score in points */
    public int $maxScore = 0;

    /** Percentage of correct answers (0 - 100%) */
    public float $percent = 0;

    /** Points actually added to the user's account in this attempt */
    public int $pointsAwarded = 0;

    /** True if the user has already taken this test before (prevent duplicate points) */
    public bool $alreadyHadFullPoints = false;

    /** Collected per-question feedback */
    public array $feedback = [];

    /** All quiz data for rendering */
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
            // Har bir kitobda bitta test bo'lgani sababli oxirgi faol testni olamiz
            $quiz = (clone $query)->active()->latest()->first()
                 ?? (clone $query)->latest()->first();
        }

        if (!$quiz) {
            $this->quizId = null;
            $this->quizTitle = null;
            $this->questions = [];
            $this->maxRewardPoints = 50;
            return;
        }

        $this->quizId = $quiz->id;
        $this->quizTitle = $quiz->title;
        $this->quizDescription = $quiz->description;
        $this->quizDifficulty = $quiz->difficulty ?? 'medium';
        $this->timeLimitMinutes = $quiz->time_limit_minutes;
        $this->maxRewardPoints = (int) ($quiz->reward_points ?: 50);

        if ($quiz->questions->isEmpty()) {
            $this->questions = [];
            $this->maxScore = $this->maxRewardPoints;
            return;
        }

        $this->maxScore = $this->maxRewardPoints;

        $this->questions = $quiz->questions->values()->map(function ($q) {
            return [
                'id'          => $q->id,
                'text'        => $q->question_text,
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
        $this->correctCount = 0;
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
            $this->correctCount++;
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

        $totalQuestions = count($this->questions);
        $this->percent = $totalQuestions > 0 ? round(($this->correctCount / $totalQuestions) * 100, 2) : 0;

        // O'quvchi testni qancha yechganiga (foiziga) qarab mutanosib ball beriladi
        $calculatedPoints = (int) round(($this->percent / 100) * $this->maxRewardPoints);

        if (!$quiz || !$user) {
            return;
        }

        // Qayta topshirilganda takroriy ball berilmasin (anti-farming)
        $alreadyAttempted = QuizAttempt::where('user_id', $user->id)
            ->where('quiz_id', $quiz->id)
            ->exists();

        if ($alreadyAttempted) {
            // Takroriy urinish: foydalanuvchiga 0 ball ko'rsatiladi va hisobiga ham 0 ball qo'shiladi!
            $pointsToAward = 0;
            $this->score = 0;
            $this->alreadyHadFullPoints = true;
        } else {
            // Birinchi urinish: hisoblangan ball beriladi
            $pointsToAward = $calculatedPoints;
            $this->score = $calculatedPoints;
            $this->alreadyHadFullPoints = false;
        }

        $this->maxScore = $this->maxRewardPoints;
        $this->pointsAwarded = $pointsToAward;

        DB::transaction(function () use ($user, $quiz, $pointsToAward, $alreadyAttempted) {
            QuizAttempt::create([
                'user_id'                => $user->id,
                'quiz_id'                => $quiz->id,
                'score'                  => $pointsToAward,
                'max_score'              => $this->maxRewardPoints,
                'percent'                => $this->percent,
                'answers'                => $this->feedback,
                'is_full_points_awarded' => !$alreadyAttempted && $pointsToAward === $this->maxRewardPoints && $pointsToAward > 0,
                'completed_at'           => now(),
            ]);

            if ($pointsToAward > 0) {
                app(PointsService::class)->awardPoints(
                    $user,
                    $pointsToAward,
                    'quiz',
                    '«' . $this->book->title . '» testi: ' . $pointsToAward . ' ball (' . $this->percent . '%)'
                );
            }
        });

        // Tepadagi navbar ball hisoblagichiga jonli animatsiya yuborish
        if ($pointsToAward > 0) {
            $user->refresh();
            $this->dispatchBrowserEvent('points-awarded', [
                'points'   => $pointsToAward,
                'newTotal' => (int) $user->total_points,
            ]);
        }

        // Bildirishnoma yuborish
        \App\Services\NotifyUser::send(
            $user,
            'quiz',
            $this->percent >= 80 ? 'Test a\'lo darajada o\'tdi! 🎯' : ($this->percent >= 50 ? 'Test muvaffaqiyatli yakunlandi' : 'Test yakunlandi'),
            '«' . $this->book->title . '» — ' . $quiz->title . ': ' . $this->score . '/' . $this->maxRewardPoints . ' ball (' . $this->percent . '%)',
            '📝',
            route('quiz.show', ['book' => $this->book->id])
        );
    }

    public function render()
    {
        $attemptCount = (Auth::check() && $this->quizId)
            ? QuizAttempt::where('user_id', Auth::id())->where('quiz_id', $this->quizId)->count()
            : 0;

        return view('livewire.quiz.take-quiz', [
            'book'                 => $this->book,
            'quizId'               => $this->quizId,
            'quizTitle'            => $this->quizTitle,
            'quizDescription'      => $this->quizDescription,
            'quizDifficulty'       => $this->quizDifficulty,
            'timeLimitMinutes'     => $this->timeLimitMinutes,
            'maxRewardPoints'      => $this->maxRewardPoints,
            'attemptCount'         => $attemptCount,
            'questions'            => $this->questions,
            'currentQuestion'      => $this->currentQuestion,
            'correctCount'         => $this->correctCount,
            'selectedOption'       => $this->selectedOption,
            'finished'             => $this->finished,
            'score'                => $this->score,
            'maxScore'             => $this->maxScore,
            'percent'              => $this->percent,
            'pointsAwarded'        => $this->pointsAwarded,
            'alreadyHadFullPoints' => $this->alreadyHadFullPoints,
            'feedback'             => $this->feedback,
        ])->layout('layouts.app', ['title' => 'Test – ' . $this->book->title]);
    }
}
