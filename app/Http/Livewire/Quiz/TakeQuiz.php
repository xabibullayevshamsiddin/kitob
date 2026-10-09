<?php

namespace App\Http\Livewire\Quiz;

use App\Http\Livewire\Concerns\WithToast;
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
    use WithToast;

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
    public string $writtenAnswer = '';
    public array $draftAnswers = [];

    /** Number of questions answered correctly */
    public int $correctCount = 0;

    /** Set when the whole quiz is finished */
    public bool $finished = false;
    public bool $confirmFinishOpen = false;

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

    /** Timestamp when the current attempt started (server-side, for countdown) */
    public ?int $startedAt = null;

    /** True when the attempt was auto-finished because time ran out */
    public bool $timedOut = false;
    public bool $pendingReview = false;

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
                'image'       => $q->image_path ? asset('storage/' . $q->image_path) : null,
                'image_shape' => $q->image_shape ?? 'rectangle',
                'type'        => $q->type,
                'points'      => (int) $q->points,
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
        $this->writtenAnswer = '';
        $this->draftAnswers = [];
        $this->correctCount = 0;
        $this->finished = false;
        $this->confirmFinishOpen = false;
        $this->score = 0;
        $this->maxScore = 0;
        $this->percent = 0;
        $this->pointsAwarded = 0;
        $this->alreadyHadFullPoints = false;
        $this->feedback = [];
        $this->questions = [];
        $this->startedAt = time();
        $this->timedOut = false;
        $this->pendingReview = false;
    }

    /**
     * Qolgan vaqt (soniyalarda). Cheklov bo'lmasa null.
     */
    public function getTimerRemainingProperty(): ?int
    {
        if (!$this->timeLimitMinutes || !$this->startedAt) {
            return null;
        }

        return max(0, (int) $this->startedAt + ((int) $this->timeLimitMinutes * 60) - time());
    }

    protected function saveCurrentDraft(): void
    {
        $question = $this->questions[$this->currentQuestion] ?? null;
        if (!$question) {
            return;
        }

        if (($question['type'] ?? 'single') === 'text') {
            $this->draftAnswers[$this->currentQuestion] = [
                'type' => 'text',
                'written_answer' => mb_substr($this->writtenAnswer, 0, 5000),
            ];
            return;
        }

        if ($this->selectedOption !== null) {
            $belongsToQuestion = DB::table('quiz_options')
                ->where('id', $this->selectedOption)
                ->where('question_id', $question['id'])
                ->exists();

            if ($belongsToQuestion) {
                $this->draftAnswers[$this->currentQuestion] = [
                    'type' => 'single',
                    'selected_option' => (int) $this->selectedOption,
                ];
                return;
            }
        }

        unset($this->draftAnswers[$this->currentQuestion]);
    }

    protected function restoreQuestionDraft(int $index): void
    {
        $draft = $this->draftAnswers[$index] ?? [];
        $this->selectedOption = ($draft['type'] ?? null) === 'single'
            ? ($draft['selected_option'] ?? null)
            : null;
        $this->writtenAnswer = ($draft['type'] ?? null) === 'text'
            ? (string) ($draft['written_answer'] ?? '')
            : '';
        $this->resetErrorBag();
    }

    protected function gradeDraftAnswers(): void
    {
        foreach ($this->questions as $index => $question) {
            if (isset($this->feedback[$index])) {
                continue;
            }

            $draft = $this->draftAnswers[$index] ?? [];
            if (($question['type'] ?? 'single') === 'text') {
                $answer = trim((string) ($draft['written_answer'] ?? ''));
                if ($answer !== '') {
                    $this->gradeWrittenQuestion($index, $answer);
                }
                continue;
            }

            $selectedOption = $draft['selected_option'] ?? null;
            if ($selectedOption !== null) {
                $this->gradeQuestion($index, $selectedOption);
            }
        }
    }

    /**
     * Vaqt tugaganda Alpine tomonidan chaqiriladi:
     * javoblangan savollar baholanadi, javoblanmaganlarga 0 ball,
     * test natija sahifasi ochilib, foydalanuvchi testdan chiqariladi.
     */
    public function timeUp(): void
    {
        if ($this->finished || empty($this->questions)) {
            return;
        }

        $this->saveCurrentDraft();
        $this->timedOut = true;
        $this->finishQuiz();
    }

    /**
     * Bitta savolni baholash va feedback yozish.
     */
    protected function gradeQuestion(int $index, $selectedId): array
    {
        $question = $this->questions[$index];

        $correctOption = DB::table('quiz_options')
            ->where('question_id', $question['id'])
            ->where('is_correct', true)
            ->first();

        $correctOptionId = $correctOption ? $correctOption->id : null;

        $isCorrect = $selectedId !== null
            && (int) $selectedId === (int) $correctOptionId;

        if ($isCorrect) {
            $this->correctCount++;
        }

        $selectedOptionText = null;
        foreach ($question['options'] as $opt) {
            if ((int) $opt['id'] === (int) $selectedId) {
                $selectedOptionText = $opt['text'];
                break;
            }
        }

        $this->feedback[$index] = [
            'question_id'   => $question['id'],
            'type'          => 'single',
            'points'        => $question['points'],
            'earned_points' => $isCorrect ? $question['points'] : 0,
            'question_text' => $question['text'],
            'correct'       => $isCorrect,
            'selected_id'   => $selectedId,
            'selected_text' => $selectedOptionText,
            'correct_text'  => $correctOption ? $correctOption->option_text : null,
            'explanation'   => $question['explanation'],
        ];

        return $this->feedback[$index];
    }

    protected function gradeWrittenQuestion(int $index, string $answer): void
    {
        $question = $this->questions[$index];
        $this->feedback[$index] = [
            'question_id' => $question['id'],
            'type' => 'text',
            'points' => $question['points'],
            'earned_points' => null,
            'question_text' => $question['text'],
            'written_answer' => trim($answer),
            'correct' => null,
            'explanation' => $question['explanation'],
        ];
    }

    /**
     * Javoblanmagan savollarni natijaga qo'shish (0 ball, tahlilda ko'rinadi).
     */
    protected function fillUnansweredFeedback(): void
    {
        foreach ($this->questions as $index => $question) {
            if (isset($this->feedback[$index])) {
                continue;
            }

            $correctOption = ($question['type'] ?? 'single') === 'single'
                ? DB::table('quiz_options')->where('question_id', $question['id'])->where('is_correct', true)->first()
                : null;

            $this->feedback[$index] = [
                'question_id'   => $question['id'],
                'type'          => $question['type'] ?? 'single',
                'points'        => $question['points'],
                'earned_points' => 0,
                'question_text' => $question['text'],
                'correct'       => false,
                'selected_id'   => null,
                'selected_text' => null,
                'correct_text'  => $correctOption ? $correctOption->option_text : null,
                'explanation'   => $question['explanation'],
            ];
        }

        ksort($this->feedback);
    }

    public function nextQuestion(): void
    {
        if ($this->finished) {
            return;
        }

        // Vaqt tugagan bo'lsa — javobni emas, avtomatik yakunlashni bajar
        if ($this->timerRemaining !== null && $this->timerRemaining <= 0) {
            $this->timeUp();
            return;
        }

        abort_if(!isset($this->questions[$this->currentQuestion]), 404);
        $this->saveCurrentDraft();

        if ($this->currentQuestion < count($this->questions) - 1) {
            $this->currentQuestion++;
            $this->restoreQuestionDraft($this->currentQuestion);
        } else {
            $this->finishQuiz();
        }
    }

    public function requestFinish(): void
    {
        if ($this->finished) {
            return;
        }

        if ($this->timerRemaining !== null && $this->timerRemaining <= 0) {
            $this->timeUp();
            return;
        }

        if ($this->currentQuestion === count($this->questions) - 1) {
            $this->confirmFinishOpen = true;
        }
    }

    public function cancelFinish(): void
    {
        $this->confirmFinishOpen = false;
    }

    public function confirmFinish(): void
    {
        if (!$this->confirmFinishOpen || $this->finished) {
            return;
        }

        $this->confirmFinishOpen = false;
        $this->nextQuestion();
    }

    public function previousQuestion(): void
    {
        if ($this->finished) {
            return;
        }

        if ($this->timerRemaining !== null && $this->timerRemaining <= 0) {
            $this->timeUp();
            return;
        }

        if ($this->currentQuestion <= 0) {
            return;
        }

        $this->saveCurrentDraft();
        $this->currentQuestion--;
        $this->restoreQuestionDraft($this->currentQuestion);
    }

    protected function finishQuiz(): void
    {
        $this->finished = true;

        $this->gradeDraftAnswers();

        // Javobsiz qoldirilgan savollar tahlilda ham ko'rinadi (0 ball)
        $this->fillUnansweredFeedback();

        $user = Auth::user();
        $quiz = Quiz::find($this->quizId);

        $totalQuestions = count($this->questions);
        $this->percent = $totalQuestions > 0 ? round(($this->correctCount / $totalQuestions) * 100, 2) : 0;

        // O'quvchi testni qancha yechganiga (foiziga) qarab mutanosib ball beriladi
        $calculatedPoints = (int) round(($this->percent / 100) * $this->maxRewardPoints);

        if (!$quiz || !$user) {
            return;
        }

        $hasWrittenQuestions = collect($this->questions)->contains(fn ($question) => ($question['type'] ?? 'single') === 'text');
        if ($hasWrittenQuestions) {
            QuizAttempt::create([
                'user_id' => $user->id,
                'quiz_id' => $quiz->id,
                'score' => 0,
                'max_score' => $this->maxRewardPoints,
                'percent' => 0,
                'answers' => $this->feedback,
                'review_status' => 'pending',
                'completed_at' => now(),
            ]);
            $this->score = 0;
            $this->percent = 0;
            $this->pointsAwarded = 0;
            $this->maxScore = $this->maxRewardPoints;
            $this->pendingReview = true;
            $this->toastInfo('Yozma javoblar tekshirilgach natija va ball ko‘rinadi.', 'Javoblar qabul qilindi');

            return;
        }

        $passingPercent = (int) setting('quiz_passing_percent', 70);
        $isPassed = $this->percent >= $passingPercent;

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
            // Birinchi urinish: foizga mutanosib hisoblangan ball beriladi
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

        // Toast bildirishnoma chiqarish
        if ($pointsToAward > 0) {
            if ($isPassed) {
                $this->toastSuccess("Test muvaffaqiyatli topshirildi! +{$pointsToAward} ball hisobingizga qo'shildi! 🎯", 'Ajoyib natija!');
            } else {
                $this->toastInfo("Test yakunlandi ({$this->percent}% to'g'ri). +{$pointsToAward} ball hisobingizga qo'shildi.", 'Yaxshi harakat');
            }
        } elseif ($alreadyAttempted) {
            $this->toastInfo("Test yakunlandi ({$this->percent}% to'g'ri). Takroriy urinish bo'lgani uchun qo'shimcha ball berilmadi.", 'Test yakunlandi');
        } else {
            $this->toastWarning("Test yakunlandi (0% to'g'ri). Ball berilmadi.", 'Sinov yakunlandi');
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
        $latestAttempt = (Auth::check() && $this->quizId)
            ? QuizAttempt::where('user_id', Auth::id())->where('quiz_id', $this->quizId)->latest('completed_at')->first()
            : null;

        return view('livewire.quiz.take-quiz', [
            'book'                 => $this->book,
            'quizId'               => $this->quizId,
            'quizTitle'            => $this->quizTitle,
            'quizDescription'      => $this->quizDescription,
            'quizDifficulty'       => $this->quizDifficulty,
            'timeLimitMinutes'     => $this->timeLimitMinutes,
            'maxRewardPoints'      => $this->maxRewardPoints,
            'attemptCount'         => $attemptCount,
            'latestAttempt'        => $latestAttempt,
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
            'timerRemainingSeconds' => $this->timerRemaining,
            'timedOut'             => $this->timedOut,
            'pendingReview'        => $this->pendingReview,
            'passingPercent'       => (int) setting('quiz_passing_percent', 70),
        ])->layout('layouts.app', ['title' => 'Test – ' . $this->book->title]);
    }
}
