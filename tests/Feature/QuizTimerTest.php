<?php

namespace Tests\Feature;

use App\Http\Livewire\Quiz\TakeQuiz;
use App\Models\Book;
use App\Models\Quiz;
use App\Models\QuizOption;
use App\Models\QuizQuestion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class QuizTimerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Kitob + test yaratish. Har bir savolga 1 to'g'ri va 1 xato variant.
     *
     * @return array{0: Book, 1: Quiz, 2: array} [book, quiz, correctOptionIds]
     */
    private function setupQuiz(int $questionCount = 3, ?int $timeLimit = 15): array
    {
        $book = Book::create([
            'title'       => 'Sinov kitobi',
            'slug'        => 'sinov-kitobi-' . uniqid(),
            'author'      => 'Muallif',
            'description' => 'Test uchun',
            'genre'       => 'Ta\'lim',
            'week_number' => random_int(1, 99999),
            'is_active'   => true,
        ]);

        $quiz = Quiz::create([
            'book_id'           => $book->id,
            'title'             => 'Sinov testi',
            'difficulty'        => 'medium',
            'reward_points'     => 50,
            'time_limit_minutes'=> $timeLimit,
            'is_active'         => true,
        ]);

        $correctOptionIds = [];

        for ($i = 1; $i <= $questionCount; $i++) {
            $q = QuizQuestion::create([
                'quiz_id'       => $quiz->id,
                'question_text' => "Savol {$i}",
                'type'          => 'single',
                'order'         => $i,
            ]);

            QuizOption::create([
                'question_id' => $q->id,
                'option_text' => "Xato javob {$i}",
                'is_correct'  => false,
                'order'       => 1,
            ]);

            $correct = QuizOption::create([
                'question_id' => $q->id,
                'option_text' => "To'g'ri javob {$i}",
                'is_correct'  => true,
                'order'       => 2,
            ]);

            $correctOptionIds[] = $correct->id;
        }

        return [$book, $quiz, $correctOptionIds];
    }

    private function makeUser(): User
    {
        return User::create([
            'name'     => 'Test O\'quvchi',
            'username' => 'test_student_' . uniqid(),
            'email'    => uniqid() . '@test.uz',
            'password' => bcrypt('password'),
        ]);
    }

    public function test_timer_remaining_counts_down_from_time_limit(): void
    {
        [$book] = $this->setupQuiz(3, 15);
        $user = $this->makeUser();
        $this->actingAs($user);

        Livewire::test(TakeQuiz::class, ['book' => $book])
            ->assertViewHas('timerRemainingSeconds', function ($seconds) {
                return $seconds !== null && $seconds <= 900 && $seconds >= 895;
            });
    }

    public function test_no_timer_when_quiz_has_no_time_limit(): void
    {
        [$book] = $this->setupQuiz(3, null);
        $this->actingAs($this->makeUser());

        Livewire::test(TakeQuiz::class, ['book' => $book])
            ->assertViewHas('timerRemainingSeconds', null);
    }

    public function test_time_up_auto_finishes_with_all_unanswered_marked_wrong(): void
    {
        [$book] = $this->setupQuiz(3, 15);
        $user = $this->makeUser();
        $this->actingAs($user);

        $component = Livewire::test(TakeQuiz::class, ['book' => $book])
            ->assertViewHas('timerRemainingSeconds');

        // Hech qanday javob bermasdan vaqt tugaydi
        $component->call('timeUp')
            ->assertSet('finished', true)
            ->assertSet('timedOut', true);

        $feedback = $component->get('feedback');
        $this->assertCount(3, $feedback, 'Barcha savollar tahlilda bo\'lishi kerak');

        foreach ($feedback as $fb) {
            $this->assertFalse($fb['correct'], 'Javoblanmagan savol noto\'g\'ri bo\'lishi kerak');
            $this->assertNull($fb['selected_id'], 'Javoblanmagan savol tanloviga ega bo\'lmasligi kerak');
            $this->assertNotNull($fb['correct_text'], 'To\'g\'ri javob ko\'rsatilishi kerak');
        }

        $this->assertSame(0, $component->get('correctCount'));
        $this->assertSame(0.0, $component->get('percent'));
        $this->assertSame(0, $component->get('pointsAwarded'));
    }

    public function test_time_up_grades_pending_answer_and_scores_proportionally(): void
    {
        [$book, $quiz, $correctIds] = $this->setupQuiz(5, 15);
        $user = $this->makeUser();
        $this->actingAs($user);

        $component = Livewire::test(TakeQuiz::class, ['book' => $book]);

        // 1-savolga to'g'ri javob berilgan (keyingi savolga o'tgan — ya'ni tasdiqlangan)
        $component->set('selectedOption', $correctIds[0])->call('nextQuestion');
        $this->assertSame(1, $component->get('currentQuestion'));
        $this->assertSame(0, $component->get('correctCount'), 'Javoblar faqat test yakunlanganda baholanishi kerak');

        // 2-savolda javob tanlangan, lekin tasdiqlanmagan — vaqt tugaydi
        $component->set('selectedOption', $correctIds[1]);

        $component->call('timeUp')
            ->assertSet('finished', true)
            ->assertSet('timedOut', true);

        // 2 ta to'g'ri javob = 5 savoldan = 40% => 50 balldan 20 ball
        $this->assertSame(2, $component->get('correctCount'));
        $this->assertSame(40.0, $component->get('percent'));
        $this->assertSame(20, $component->get('pointsAwarded'));

        $feedback = $component->get('feedback');
        $this->assertCount(5, $feedback, 'Javoblanmagan 3 savol ham tahlilda bo\'lishi kerak');
        $this->assertTrue($feedback[0]['correct']);
        $this->assertTrue($feedback[1]['correct']);
        $this->assertFalse($feedback[2]['correct']);
        $this->assertFalse($feedback[3]['correct']);
        $this->assertFalse($feedback[4]['correct']);
    }

    public function test_student_can_skip_navigate_back_and_change_an_answer_before_finishing(): void
    {
        [$book, $quiz, $correctIds] = $this->setupQuiz(3, 15);
        $this->actingAs($this->makeUser());
        $wrongOptionId = QuizOption::where('question_id', $quiz->questions()->first()->id)
            ->where('is_correct', false)
            ->value('id');

        $component = Livewire::test(TakeQuiz::class, ['book' => $book])
            ->set('selectedOption', $correctIds[0])
            ->call('nextQuestion')
            ->assertSet('currentQuestion', 1)
            ->assertSet('correctCount', 0)
            ->call('nextQuestion')
            ->assertSet('currentQuestion', 2)
            ->call('previousQuestion')
            ->assertSet('currentQuestion', 1)
            ->call('previousQuestion')
            ->assertSet('currentQuestion', 0)
            ->assertSet('selectedOption', $correctIds[0])
            ->set('selectedOption', $wrongOptionId)
            ->call('nextQuestion')
            ->call('nextQuestion')
            ->call('nextQuestion')
            ->assertSet('finished', true);

        $this->assertSame(0, $component->get('correctCount'));
        $feedback = $component->get('feedback');
        $this->assertFalse($feedback[0]['correct'], 'Qaytib o‘zgartirilgan javob yangicha baholanishi kerak');
        $this->assertFalse($feedback[1]['correct'], 'Javobsiz savol xato deb saqlanishi kerak');
        $this->assertFalse($feedback[2]['correct'], 'Javobsiz savol xato deb saqlanishi kerak');
    }

    public function test_finish_requires_confirmation_before_submitting_quiz(): void
    {
        [$book] = $this->setupQuiz(1, 15);
        $this->actingAs($this->makeUser());

        Livewire::test(TakeQuiz::class, ['book' => $book])
            ->call('requestFinish')
            ->assertSet('confirmFinishOpen', true)
            ->assertSet('finished', false)
            ->call('cancelFinish')
            ->assertSet('confirmFinishOpen', false)
            ->assertSet('finished', false)
            ->call('requestFinish')
            ->call('confirmFinish')
            ->assertSet('confirmFinishOpen', false)
            ->assertSet('finished', true);
    }

    public function test_second_time_up_call_is_ignored(): void
    {
        [$book] = $this->setupQuiz(2, 15);
        $this->actingAs($this->makeUser());

        $component = Livewire::test(TakeQuiz::class, ['book' => $book]);

        $component->call('timeUp')->assertSet('finished', true);
        $component->call('timeUp'); // takroriy chaqiruv xato bermasin

        $this->assertCount(2, $component->get('feedback'));
        $this->assertSame(
            1,
            \App\Models\QuizAttempt::count(),
            'Bitta urinish yozilishi kerak (takroriy emas)'
        );
    }

    public function test_next_question_after_expiry_routes_to_auto_finish(): void
    {
        [$book, $quiz, $correctIds] = $this->setupQuiz(3, 15);
        $this->actingAs($this->makeUser());

        $component = Livewire::test(TakeQuiz::class, ['book' => $book]);

        // startedAt'ni o'tmishga surib, vaqt tugaganini simulyatsiya qilamiz
        $component->set('startedAt', time() - (16 * 60));

        $component->set('selectedOption', $correctIds[0])->call('nextQuestion')
            ->assertSet('finished', true)
            ->assertSet('timedOut', true);

        $this->assertSame(1, $component->get('correctCount'));
        $this->assertCount(3, $component->get('feedback'));
    }
}
