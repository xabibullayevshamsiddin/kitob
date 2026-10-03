<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Quiz;
use App\Models\QuizOption;
use App\Models\QuizQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuizController extends Controller
{
    public function index()
    {
        $quizzes = Quiz::with(['book:id,title,cover_image,slug,week_number,author'])
            ->withCount(['questions', 'attempts'])
            ->latest()
            ->paginate(15);

        $books = Book::withCount('quizzes')->orderBy('title')->get(['id', 'title', 'week_number', 'author']);

        return view('teacher.quizzes.index', compact('quizzes', 'books'));
    }

    public function create(Request $request)
    {
        $books = Book::withCount('quizzes')->orderBy('title')->get(['id', 'title', 'week_number', 'author']);
        $selectedBookId = $request->query('book_id');

        return view('teacher.quizzes.create', compact('books', 'selectedBookId'));
    }

    public function store(Request $request)
    {
        $isTeacher = !auth()->user()->isAdmin();
        $maxAllowedPoints = $isTeacher ? 50 : 200;

        $request->validate([
            'book_id'            => 'nullable|exists:books,id',
            'title'              => 'required|string|max:255',
            'reward_points'      => "required|integer|min:5|max:{$maxAllowedPoints}",
            'description'        => 'nullable|string|max:1000',
            'difficulty'         => 'required|in:easy,medium,hard',
            'time_limit_minutes' => 'nullable|integer|min:1|max:180',
            'questions'          => 'required|array|min:1',
            'questions.*.text'   => 'required|string|min:3',
            'questions.*.options'=> 'required|array|min:2',
            'questions.*.options.*' => 'required|string',
            'questions.*.correct'   => 'required',
        ], [
            'title.required'         => 'Test topshirig\'i nomini kiriting.',
            'reward_points.required' => 'Test uchun umumiy mukofot balini kiriting.',
            'reward_points.max'      => "Ustozlar uchun maksimal mukofot bali: {$maxAllowedPoints} ball.",
            'reward_points.min'      => 'Minimal mukofot bali: 5 ball.',
            'questions.required'     => 'Kamida 1 ta savol kiritilishi shart.',
            'questions.min'          => 'Kamida 1 ta savol kiritilishi shart.',
        ]);

        // Bir kitobga faqat 1 ta test biriktirish tekshiruvi
        if ($request->filled('book_id')) {
            $alreadyExists = Quiz::where('book_id', $request->book_id)->exists();
            if ($alreadyExists) {
                return back()->withInput()->withErrors([
                    'book_id' => 'Ushbu kitob uchun allaqachon test yaratilgan. Har bir kitob uchun faqat bitta test topshirig\'i biriktirilishi mumkin! (Mavjud testni tahrirlashingiz yoki o\'chirishingiz mumkin).'
                ]);
            }
        }

        $rewardPoints = (int) $request->input('reward_points', $isTeacher ? 50 : 100);
        $totalQuestions = count($request->questions);
        $perQuestionPoints = $totalQuestions > 0 ? max(1, (int) round($rewardPoints / $totalQuestions)) : 10;

        DB::transaction(function () use ($request, $rewardPoints, $perQuestionPoints) {
            $quiz = Quiz::create([
                'book_id'            => $request->book_id,
                'chapter_number'     => $request->chapter_number,
                'title'              => trim($request->title),
                'description'        => trim($request->description),
                'difficulty'         => $request->difficulty,
                'reward_points'      => $rewardPoints,
                'time_limit_minutes' => $request->time_limit_minutes ?? 15,
                'is_active'          => $request->has('is_active') || $request->input('is_active', 1) == 1,
            ]);

            foreach ($request->questions as $qIndex => $qData) {
                $question = QuizQuestion::create([
                    'quiz_id'       => $quiz->id,
                    'question_text' => trim($qData['text']),
                    'type'          => 'single',
                    'points'        => $perQuestionPoints,
                    'explanation'   => !empty($qData['explanation']) ? trim($qData['explanation']) : null,
                    'order'         => $qIndex + 1,
                ]);

                $correctIndex = (int) ($qData['correct'] ?? 0);

                foreach ($qData['options'] as $oIndex => $optText) {
                    if (trim($optText) === '') {
                        continue;
                    }

                    QuizOption::create([
                        'question_id' => $question->id,
                        'option_text' => trim($optText),
                        'is_correct'  => ($oIndex === $correctIndex),
                        'order'       => $oIndex + 1,
                    ]);
                }
            }
        });

        return redirect()->route('teacher.quizzes.index')->with('success', 'Test topshirig\'i muvaffaqiyatli yaratildi va kitobga biriktirildi! 📝🎯 (Mukofot bali: ' . $rewardPoints . ' ball)');
    }

    public function show(Quiz $quiz)
    {
        $quiz->load(['book', 'questions.options']);

        return view('teacher.quizzes.show', compact('quiz'));
    }

    public function destroy(Quiz $quiz)
    {
        DB::transaction(function () use ($quiz) {
            foreach ($quiz->questions as $question) {
                $question->options()->delete();
                $question->delete();
            }

            $quiz->attempts()->delete();
            $quiz->delete();
        });

        return redirect()->route('teacher.quizzes.index')->with('success', 'Test topshirig\'i va uning barcha savollari butunlay o\'chirildi. 🗑');
    }
}
