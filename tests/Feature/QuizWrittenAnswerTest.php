<?php

namespace Tests\Feature;

use App\Http\Livewire\Quiz\TakeQuiz;
use App\Models\Book;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizQuestion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class QuizWrittenAnswerTest extends TestCase
{
    use RefreshDatabase;

    private function makeWrittenQuiz(?User $creator = null): array
    {
        $book = Book::create([
            'title' => 'Yozma test kitobi',
            'slug' => 'yozma-test-' . uniqid(),
            'author' => 'Muallif',
            'description' => 'Test',
            'genre' => 'Ta’lim',
            'week_number' => random_int(1, 90000),
            'is_active' => true,
        ]);
        $quiz = Quiz::create([
            'book_id' => $book->id,
            'created_by' => $creator?->id,
            'title' => 'Yozma sinov',
            'difficulty' => 'medium',
            'reward_points' => 50,
            'is_active' => true,
        ]);
        QuizQuestion::create([
            'quiz_id' => $quiz->id,
            'question_text' => 'Asar mazmunini qisqacha yozing',
            'type' => 'text',
            'expected_answer' => 'Namunaviy yozma javob mezoni: asarning asosiy g‘oyasini tushuntirish.',
            'points' => 10,
            'order' => 1,
        ]);

        return [$book, $quiz];
    }

    private function makeStudent(): User
    {
        return User::create([
            'name' => 'Test O‘quvchi',
            'username' => 'writer_' . uniqid(),
            'email' => uniqid() . '@test.uz',
            'password' => bcrypt('password'),
        ]);
    }

    public function test_written_answer_is_saved_and_waits_for_review(): void
    {
        [$book, $quiz] = $this->makeWrittenQuiz();
        $this->actingAs($this->makeStudent());

        Livewire::test(TakeQuiz::class, ['book' => $book])
            ->assertSee('Yozma javob')
            ->set('writtenAnswer', 'Asar haqida mening javobim')
            ->call('nextQuestion')
            ->assertSet('finished', true)
            ->assertSet('pendingReview', true);

        $attempt = QuizAttempt::where('quiz_id', $quiz->id)->firstOrFail();
        $this->assertSame('pending', $attempt->review_status);
        $this->assertSame('Asar haqida mening javobim', $attempt->answers[0]['written_answer']);
        $this->assertSame(0, $attempt->score);
    }

    public function test_empty_written_question_can_be_skipped_and_is_saved_for_teacher_review(): void
    {
        [$book] = $this->makeWrittenQuiz();
        $this->actingAs($this->makeStudent());

        Livewire::test(TakeQuiz::class, ['book' => $book])
            ->call('nextQuestion')
            ->assertSet('finished', true)
            ->assertSet('pendingReview', true);

        $attempt = QuizAttempt::firstOrFail();
        $this->assertSame('pending', $attempt->review_status);
        $this->assertSame('text', $attempt->answers[0]['type']);
        $this->assertSame(0, $attempt->answers[0]['earned_points']);
        $this->assertArrayNotHasKey('written_answer', $attempt->answers[0]);
    }

    public function test_written_draft_is_preserved_when_moving_between_questions(): void
    {
        [$book, $quiz] = $this->makeWrittenQuiz();
        QuizQuestion::create([
            'quiz_id' => $quiz->id,
            'question_text' => 'Ikkinchi yozma savol',
            'type' => 'text',
            'points' => 10,
            'order' => 2,
        ]);
        $this->actingAs($this->makeStudent());

        $component = Livewire::test(TakeQuiz::class, ['book' => $book])
            ->set('writtenAnswer', 'Birinchi javob')
            ->call('nextQuestion')
            ->assertSet('currentQuestion', 1)
            ->set('writtenAnswer', 'Ikkinchi javob')
            ->call('previousQuestion')
            ->assertSet('currentQuestion', 0)
            ->assertSet('writtenAnswer', 'Birinchi javob')
            ->set('writtenAnswer', 'Tahrirlangan birinchi javob')
            ->call('nextQuestion')
            ->assertSet('currentQuestion', 1)
            ->assertSet('writtenAnswer', 'Ikkinchi javob')
            ->call('nextQuestion')
            ->assertSet('finished', true);

        $attempt = QuizAttempt::firstOrFail();
        $this->assertSame('Tahrirlangan birinchi javob', $attempt->answers[0]['written_answer']);
        $this->assertSame('Ikkinchi javob', $attempt->answers[1]['written_answer']);
    }

    public function test_teacher_review_sets_final_score_and_awards_points(): void
    {
        Role::firstOrCreate(['name' => 'teacher', 'guard_name' => 'web']);
        $teacher = $this->makeStudent();
        $teacher->syncRoles(['teacher']);
        [$book, $quiz] = $this->makeWrittenQuiz($teacher);
        $question = $quiz->questions()->firstOrFail();
        $student = $this->makeStudent();
        $attempt = QuizAttempt::create([
            'user_id' => $student->id,
            'quiz_id' => $quiz->id,
            'score' => 0,
            'max_score' => 50,
            'percent' => 0,
            'answers' => [[
                'question_id' => $question->id,
                'type' => 'text',
                'points' => 10,
                'earned_points' => null,
                'written_answer' => 'Yozma javob',
            ]],
            'review_status' => 'pending',
            'completed_at' => now(),
        ]);

        $this->actingAs($teacher)
            ->post(route('teacher.quiz-reviews.update', $attempt), ['scores' => [0 => 8]])
            ->assertRedirect(route('teacher.quiz-reviews.index'));

        $attempt->refresh();
        $this->assertSame('reviewed', $attempt->review_status);
        $this->assertSame(80.0, (float) $attempt->percent);
        $this->assertSame(40, $attempt->score);
        $this->assertSame(40, (int) $student->fresh()->total_points);

        $this->actingAs($teacher)
            ->get(route('teacher.quiz-reviews.show', $attempt))
            ->assertOk()
            ->assertSee('Tekshirilgan')
            ->assertSee('Yozma javob')
            ->assertDontSee('Baholash va yakunlash');
    }

    public function test_teacher_can_find_pending_submissions_and_preview_the_answer(): void
    {
        Role::firstOrCreate(['name' => 'teacher', 'guard_name' => 'web']);
        $teacher = $this->makeStudent();
        $teacher->syncRoles(['teacher']);
        [$book, $quiz] = $this->makeWrittenQuiz($teacher);
        $student = $this->makeStudent();

        $attempt = QuizAttempt::create([
            'user_id' => $student->id,
            'quiz_id' => $quiz->id,
            'score' => 0,
            'max_score' => 50,
            'percent' => 0,
            'answers' => [[
                'question_id' => $quiz->questions()->firstOrFail()->id,
                'type' => 'text',
                'written_answer' => 'O‘quvchining topshirgan yozma javobi',
            ]],
            'review_status' => 'pending',
            'completed_at' => now(),
        ]);

        $this->actingAs($teacher)
            ->get(route('teacher.quiz-reviews.index'))
            ->assertOk()
            ->assertSee('Yozma javoblar')
            ->assertSee($student->name)
            ->assertSee($quiz->title)
            ->assertSee($book->title)
            ->assertSee('O‘quvchining topshirgan yozma javobi')
            ->assertSee(route('teacher.quiz-reviews.show', $attempt), false);

        $this->get(route('teacher.quizzes.show', $quiz))
            ->assertOk()
            ->assertSee('Topshirilgan ishlar')
            ->assertSee($student->name)
            ->assertSee('Tekshirilmagan')
            ->assertSee(route('teacher.quiz-reviews.show', $attempt), false);

        $this->get(route('teacher.quizzes.index'))
            ->assertOk()
            ->assertSee('1 ta yozma javob kutilmoqda')
            ->assertSee('Natijalar')
            ->assertSee('Savollar')
            ->assertSee('Tahrirlash');

        $this->get(route('teacher.quiz-reviews.show', $attempt))
            ->assertOk()
            ->assertSee('O‘quvchining yozma javobi')
            ->assertSee('Namunaviy yozma javob mezoni: asarning asosiy g‘oyasini tushuntirish.')
            ->assertSee('name="scores[0]"', false)
            ->assertSee('Baholash va yakunlash');
    }

    public function test_admin_can_open_a_quiz_submission_from_the_test_details(): void
    {
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'teacher', 'guard_name' => 'web']);
        $teacher = $this->makeStudent();
        $teacher->syncRoles(['teacher']);
        [, $quiz] = $this->makeWrittenQuiz($teacher);
        $student = $this->makeStudent();
        $admin = $this->makeStudent();
        $admin->syncRoles(['admin']);
        $attempt = QuizAttempt::create([
            'user_id' => $student->id,
            'quiz_id' => $quiz->id,
            'score' => 0,
            'max_score' => 50,
            'percent' => 0,
            'answers' => [[
                'question_id' => $quiz->questions()->firstOrFail()->id,
                'type' => 'text',
                'written_answer' => 'Admin tekshirishi kerak bo‘lgan javob',
            ]],
            'review_status' => 'pending',
            'completed_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.quizzes.index'))
            ->assertOk()
            ->assertSee($quiz->title)
            ->assertSee($teacher->name)
            ->assertSee('Yaratgan')
            ->assertSee('Natijalar (1)');

        $this->get(route('admin.quizzes.show', $quiz))
            ->assertOk()
            ->assertSee($student->name)
            ->assertSee(route('admin.quiz-reviews.show', $attempt), false);
    }

    public function test_teacher_only_sees_and_opens_tests_they_created(): void
    {
        Role::firstOrCreate(['name' => 'teacher', 'guard_name' => 'web']);
        $teacher = $this->makeStudent();
        $teacher->syncRoles(['teacher']);
        $anotherTeacher = $this->makeStudent();
        $anotherTeacher->syncRoles(['teacher']);
        [, $ownQuiz] = $this->makeWrittenQuiz($teacher);
        [, $otherQuiz] = $this->makeWrittenQuiz($anotherTeacher);
        $ownQuiz->update(['title' => 'Ustozning shaxsiy testi']);
        $otherQuiz->update(['title' => 'Boshqa ustozning testi']);
        $this->assertSame($teacher->id, (int) $ownQuiz->fresh()->created_by);
        $student = $this->makeStudent();
        $otherAttempt = QuizAttempt::create([
            'user_id' => $student->id,
            'quiz_id' => $otherQuiz->id,
            'score' => 0,
            'max_score' => 50,
            'percent' => 0,
            'answers' => [],
            'review_status' => 'pending',
            'completed_at' => now(),
        ]);

        $this->actingAs($teacher)
            ->get(route('teacher.quizzes.index'))
            ->assertOk()
            ->assertSee($ownQuiz->title)
            ->assertDontSee($otherQuiz->title);

        $this->get(route('teacher.quizzes.show', $otherQuiz))->assertForbidden();
        $this->get(route('teacher.quiz-reviews.show', $otherAttempt))->assertForbidden();
    }

    public function test_admin_can_create_a_written_question(): void
    {
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $book = Book::create([
            'title' => 'Rasmli test kitobi',
            'slug' => 'rasmli-test-' . uniqid(),
            'author' => 'Muallif',
            'description' => 'Test',
            'genre' => 'Ta’lim',
            'week_number' => random_int(90001, 99999),
            'is_active' => true,
        ]);
        $admin = $this->makeStudent();
        $admin->syncRoles(['admin']);

        $this->actingAs($admin)->post(route('admin.quizzes.store'), [
            'book_id' => $book->id,
            'title' => 'Rasm va yozma savol',
            'reward_points' => 50,
            'difficulty' => 'medium',
            'questions' => [[
                'text' => 'Savolni yozma javob bilan bajaring',
                'type' => 'text',
                'expected_answer' => 'Admin kiritgan namunaviy javob',
                'image_shape' => 'circle',
            ]],
        ])->assertRedirect();

        $question = QuizQuestion::where('question_text', 'Savolni yozma javob bilan bajaring')->firstOrFail();
        $this->assertSame('text', $question->type);
        $this->assertSame('circle', $question->image_shape);
        $this->assertNull($question->image_path);
        $this->assertSame('Admin kiritgan namunaviy javob', $question->expected_answer);
        $this->assertSame($admin->id, (int) $question->quiz->created_by);
    }

    public function test_teacher_cannot_see_or_add_quiz_to_books_they_did_not_create_while_admin_can(): void
    {
        Role::firstOrCreate(['name' => 'teacher', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        $teacher1 = $this->makeStudent();
        $teacher1->syncRoles(['teacher']);

        $teacher2 = $this->makeStudent();
        $teacher2->syncRoles(['teacher']);

        $admin = $this->makeStudent();
        $admin->syncRoles(['admin']);

        // Book created by teacher1
        $book1 = Book::create([
            'title' => 'O‘qituvchi 1 kitobi',
            'slug' => 'teacher1-book-' . uniqid(),
            'author' => 'Muallif 1',
            'description' => 'Tavsif 1',
            'genre' => 'Badiiy',
            'week_number' => random_int(80001, 85000),
            'created_by' => $teacher1->id,
            'is_active' => true,
        ]);

        // Book created by teacher2
        $book2 = Book::create([
            'title' => 'O‘qituvchi 2 kitobi',
            'slug' => 'teacher2-book-' . uniqid(),
            'author' => 'Muallif 2',
            'description' => 'Tavsif 2',
            'genre' => 'Badiiy',
            'week_number' => random_int(85001, 89999),
            'created_by' => $teacher2->id,
            'is_active' => true,
        ]);

        // 1. Teacher 1 viewing book 1 (own book): CAN see the button and open create page
        $this->actingAs($teacher1)
            ->get(route('books.show', $book1->slug))
            ->assertOk()
            ->assertSee('Ushbu kitobga test qo\'shish');

        $this->actingAs($teacher1)
            ->get(route('teacher.quizzes.create', ['book_id' => $book1->id]))
            ->assertOk();

        // 2. Teacher 1 viewing book 2 (other teacher's book): MUST NOT see the button and CANNOT open create page
        $this->actingAs($teacher1)
            ->get(route('books.show', $book2->slug))
            ->assertOk()
            ->assertDontSee('Ushbu kitobga test qo\'shish');

        $this->actingAs($teacher1)
            ->get(route('teacher.quizzes.create', ['book_id' => $book2->id]))
            ->assertForbidden();

        $this->actingAs($teacher1)
            ->post(route('teacher.quizzes.store'), [
                'book_id' => $book2->id,
                'title' => 'Ruxsatsiz test',
                'difficulty' => 'easy',
                'questions' => [[
                    'text' => 'Savol',
                    'type' => 'text',
                    'expected_answer' => 'Javob',
                ]],
            ])
            ->assertForbidden();

        // 3. Admin viewing both books: CAN see the button on both books (admin is exempt)
        $this->actingAs($admin)
            ->get(route('books.show', $book1->slug))
            ->assertOk()
            ->assertSee('Ushbu kitobga test qo\'shish');

        $this->actingAs($admin)
            ->get(route('books.show', $book2->slug))
            ->assertOk()
            ->assertSee('Ushbu kitobga test qo\'shish');
    }
}

