<?php

namespace Tests\Feature;

use App\Http\Livewire\Quiz\TakeQuiz;
use App\Models\Book;
use App\Models\Quiz;
use App\Models\QuizOption;
use App\Models\QuizQuestion;
use App\Models\QuizAttemptDraft;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class QuizAttemptDraftTest extends TestCase
{
    use RefreshDatabase;

    private function setupQuiz(bool $written = false): array
    {
        $user = User::create([
            'name' => 'Test Kitobxon',
            'username' => 'draft_' . uniqid(),
            'email' => uniqid() . '@example.test',
            'password' => bcrypt('password'),
        ]);
        $book = Book::create([
            'title' => 'Draft kitobi',
            'slug' => 'draft-kitobi-' . uniqid(),
            'author' => 'Muallif',
            'description' => 'Test',
            'genre' => 'Ta’lim',
            'week_number' => 1,
            'is_active' => true,
        ]);
        $quiz = Quiz::create([
            'book_id' => $book->id,
            'title' => 'Draft testi',
            'difficulty' => 'medium',
            'reward_points' => 50,
            'time_limit_minutes' => 15,
            'is_active' => true,
        ]);
        $question = QuizQuestion::create([
            'quiz_id' => $quiz->id,
            'question_text' => 'Birinchi savol',
            'type' => $written ? 'text' : 'single',
            'points' => 10,
            'order' => 1,
        ]);
        $option = $written ? null : QuizOption::create([
            'question_id' => $question->id,
            'option_text' => 'To‘g‘ri javob',
            'is_correct' => true,
            'order' => 1,
        ]);
        QuizQuestion::create([
            'quiz_id' => $quiz->id,
            'question_text' => 'Ikkinchi savol',
            'type' => 'text',
            'points' => 10,
            'order' => 2,
        ]);

        return [$user, $book, $quiz, $option];
    }

    public function test_choice_and_question_position_are_saved_and_restored_after_reload(): void
    {
        [$user, $book, $quiz, $option] = $this->setupQuiz();
        $this->actingAs($user);

        Livewire::test(TakeQuiz::class, ['book' => $book])
            ->call('chooseOption', $option->id)
            ->call('nextQuestion')
            ->assertSet('currentQuestion', 1);

        $draft = QuizAttemptDraft::where('user_id', $user->id)->where('quiz_id', $quiz->id)->firstOrFail();
        $this->assertSame(1, $draft->current_question);
        $this->assertSame($option->id, $draft->answers[0]['selected_option']);

        Livewire::test(TakeQuiz::class, ['book' => $book])
            ->assertSet('currentQuestion', 1)
            ->assertSet('resumedSavedDraft', true)
            ->call('previousQuestion')
            ->assertSet('currentQuestion', 0)
            ->assertSet('selectedOption', $option->id);
    }

    public function test_written_answer_and_original_timer_are_restored(): void
    {
        [$user, $book, $quiz] = $this->setupQuiz(true);
        $this->actingAs($user);

        Livewire::test(TakeQuiz::class, ['book' => $book])
            ->set('writtenAnswer', 'Avval yozilgan javob')
            ->assertSet('writtenAnswer', 'Avval yozilgan javob');

        $draft = QuizAttemptDraft::where('user_id', $user->id)->where('quiz_id', $quiz->id)->firstOrFail();
        $draft->update(['started_at' => now()->subMinutes(2)]);

        Livewire::test(TakeQuiz::class, ['book' => $book])
            ->assertSet('writtenAnswer', 'Avval yozilgan javob')
            ->assertSet('resumedSavedDraft', true)
            ->assertViewHas('timerRemainingSeconds', fn ($seconds) => $seconds <= 13 * 60 && $seconds >= 12 * 60);
    }

    public function test_finishing_quiz_removes_its_draft_without_creating_a_draft_attempt(): void
    {
        [$user, $book, $quiz, $option] = $this->setupQuiz();
        $this->actingAs($user);

        Livewire::test(TakeQuiz::class, ['book' => $book])
            ->call('chooseOption', $option->id)
            ->call('nextQuestion')
            ->set('writtenAnswer', 'Yakuniy javob')
            ->call('nextQuestion')
            ->assertSet('finished', true);

        $this->assertDatabaseMissing('quiz_attempt_drafts', [
            'user_id' => $user->id,
            'quiz_id' => $quiz->id,
        ]);
        $this->assertDatabaseHas('quiz_attempts', [
            'user_id' => $user->id,
            'quiz_id' => $quiz->id,
        ]);
    }
}
