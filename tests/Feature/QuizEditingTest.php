<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Quiz;
use App\Models\QuizOption;
use App\Models\QuizQuestion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class QuizEditingTest extends TestCase
{
    use RefreshDatabase;

    private function makeTeacherAndQuiz(): array
    {
        Role::firstOrCreate(['name' => 'teacher', 'guard_name' => 'web']);
        $teacher = User::create([
            'name' => 'Test Ustoz',
            'username' => 'teacher_' . uniqid(),
            'email' => uniqid() . '@test.uz',
            'password' => bcrypt('password'),
        ]);
        $teacher->syncRoles(['teacher']);

        $book = Book::create([
            'title' => 'Tahrirlash kitobi',
            'slug' => 'tahrirlash-' . uniqid(),
            'author' => 'Muallif',
            'description' => 'Test kitobi',
            'genre' => 'Ta’lim',
            'week_number' => random_int(1, 90000),
            'is_active' => true,
        ]);
        $quiz = Quiz::create([
            'book_id' => $book->id,
            'created_by' => $teacher->id,
            'title' => 'Tahrir qilinadigan test',
            'difficulty' => 'medium',
            'reward_points' => 50,
            'time_limit_minutes' => 15,
            'is_active' => true,
        ]);

        return [$teacher, $quiz];
    }

    public function test_teacher_can_open_separate_sections_and_edit_questions(): void
    {
        [$teacher, $quiz] = $this->makeTeacherAndQuiz();
        $firstQuestion = QuizQuestion::create([
            'quiz_id' => $quiz->id,
            'question_text' => 'Eski birinchi savol',
            'type' => 'text',
            'expected_answer' => 'Eski mezon',
            'points' => 25,
            'order' => 1,
        ]);
        $deletedQuestion = QuizQuestion::create([
            'quiz_id' => $quiz->id,
            'question_text' => 'O‘chiriladigan savol',
            'type' => 'text',
            'points' => 25,
            'order' => 2,
        ]);

        $this->actingAs($teacher)
            ->get(route('teacher.quizzes.show', ['quiz' => $quiz, 'section' => 'results']))
            ->assertOk()
            ->assertSee('Natijalar')
            ->assertSee('Topshirilgan ishlar')
            ->assertDontSee('Eski birinchi savol');

        $this->get(route('teacher.quizzes.show', ['quiz' => $quiz, 'section' => 'questions']))
            ->assertOk()
            ->assertSee('Eski birinchi savol')
            ->assertDontSee('Topshirilgan ishlar');

        $this->get(route('teacher.quizzes.edit', $quiz))
            ->assertOk()
            ->assertSee('Savolni o‘chirish')
            ->assertSee('O‘zgarishlarni saqlash');

        $this->put(route('teacher.quizzes.update', $quiz), [
            'title' => 'Yangilangan test',
            'description' => 'Yangilangan tavsif',
            'difficulty' => 'hard',
            'reward_points' => 40,
            'time_limit_minutes' => 20,
            'questions' => [
                [
                    'id' => $firstQuestion->id,
                    'text' => 'Yangilangan variantli savol',
                    'type' => 'single',
                    'options' => ['Birinchi javob', 'To‘g‘ri javob'],
                    'correct' => 1,
                    'image_shape' => 'square',
                    'explanation' => 'Savol izohi',
                ],
                [
                    'text' => 'Yangi yozma savol',
                    'type' => 'text',
                    'expected_answer' => 'Yangi baholash mezoni',
                    'image_shape' => 'rectangle',
                ],
            ],
        ])->assertRedirect(route('teacher.quizzes.show', ['quiz' => $quiz, 'section' => 'questions']));

        $this->assertSame('Yangilangan test', $quiz->fresh()->title);
        $this->assertSame('hard', $quiz->fresh()->difficulty);
        $this->assertDatabaseHas('quiz_questions', [
            'id' => $firstQuestion->id,
            'question_text' => 'Yangilangan variantli savol',
            'type' => 'single',
            'image_shape' => 'square',
        ]);
        $this->assertDatabaseMissing('quiz_questions', ['id' => $deletedQuestion->id]);
        $this->assertDatabaseHas('quiz_questions', [
            'quiz_id' => $quiz->id,
            'question_text' => 'Yangi yozma savol',
            'expected_answer' => 'Yangi baholash mezoni',
        ]);
        $this->assertSame(2, QuizQuestion::where('quiz_id', $quiz->id)->count());
        $this->assertSame(1, QuizOption::where('question_id', $firstQuestion->id)->where('is_correct', true)->count());

        $this->get(route('teacher.quizzes.show', ['quiz' => $quiz, 'section' => 'questions']))
            ->assertOk()
            ->assertSee('To‘g‘ri')
            ->assertSee('Noto‘g‘ri');
    }

    public function test_teacher_cannot_edit_another_teachers_quiz(): void
    {
        [$owner, $quiz] = $this->makeTeacherAndQuiz();
        $otherTeacher = User::create([
            'name' => 'Boshqa ustoz',
            'username' => 'other_' . uniqid(),
            'email' => uniqid() . '@test.uz',
            'password' => bcrypt('password'),
        ]);
        $otherTeacher->syncRoles(['teacher']);

        $this->actingAs($otherTeacher)
            ->get(route('teacher.quizzes.edit', $quiz))
            ->assertForbidden();
    }

    public function test_admin_can_manage_a_quiz_created_by_a_teacher(): void
    {
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        [$teacher, $quiz] = $this->makeTeacherAndQuiz();
        $question = QuizQuestion::create([
            'quiz_id' => $quiz->id,
            'question_text' => 'Ustoz yaratgan savol',
            'type' => 'text',
            'points' => 50,
            'order' => 1,
        ]);
        $admin = User::create([
            'name' => 'Test Admin',
            'username' => 'admin_' . uniqid(),
            'email' => uniqid() . '@test.uz',
            'password' => bcrypt('password'),
        ]);
        $admin->syncRoles(['admin']);

        $this->actingAs($admin)
            ->get(route('admin.quizzes.index'))
            ->assertOk()
            ->assertSee($quiz->title)
            ->assertSee($teacher->name);

        $this->get(route('admin.quizzes.show', ['quiz' => $quiz, 'section' => 'questions']))
            ->assertOk()
            ->assertSee('Ustoz yaratgan savol');

        $this->put(route('admin.quizzes.update', $quiz), [
            'title' => $quiz->title,
            'difficulty' => 'medium',
            'reward_points' => 60,
            'time_limit_minutes' => 15,
            'questions' => [[
                'id' => $question->id,
                'text' => 'Admin tahrirlagan savol',
                'type' => 'text',
                'expected_answer' => 'Mezon',
            ]],
        ])->assertRedirect(route('admin.quizzes.show', ['quiz' => $quiz, 'section' => 'questions']));

        $this->assertDatabaseHas('quiz_questions', [
            'id' => $question->id,
            'question_text' => 'Admin tahrirlagan savol',
        ]);
    }
}
