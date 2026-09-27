<?php

namespace App\Http\Controllers\Admin;

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
        $quizzes = Quiz::with(['book:id,title,cover_image,slug'])
            ->withCount(['questions', 'attempts'])
            ->latest()
            ->paginate(15);

        return view('admin.quizzes.index', compact('quizzes'));
    }

    public function create(Request $request)
    {
        $books = Book::orderBy('title')->get(['id', 'title', 'week_number', 'author']);
        $selectedBookId = $request->query('book_id');

        return view('admin.quizzes.create', compact('books', 'selectedBookId'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'book_id'            => 'required|exists:books,id',
            'title'              => 'required|string|max:255',
            'description'        => 'nullable|string|max:1000',
            'difficulty'         => 'required|in:easy,medium,hard',
            'time_limit_minutes' => 'nullable|integer|min:1|max:180',
            'questions'          => 'required|array|min:1',
            'questions.*.text'   => 'required|string|min:3',
            'questions.*.points' => 'nullable|integer|min:1',
            'questions.*.options'=> 'required|array|min:2',
            'questions.*.options.*' => 'required|string',
            'questions.*.correct'   => 'required',
        ], [
            'book_id.required'   => 'Kitobni tanlang.',
            'title.required'     => 'Test topshirig\'i nomini kiriting.',
            'questions.required' => 'Kamida 1 ta savol kiritilishi shart.',
            'questions.min'      => 'Kamida 1 ta savol kiritilishi shart.',
        ]);

        DB::transaction(function () use ($request) {
            $quiz = Quiz::create([
                'book_id'            => $request->book_id,
                'chapter_number'     => $request->chapter_number,
                'title'              => trim($request->title),
                'description'        => trim($request->description),
                'difficulty'         => $request->difficulty,
                'time_limit_minutes' => $request->time_limit_minutes ?? 15,
                'is_active'          => $request->has('is_active'),
            ]);

            foreach ($request->questions as $qIndex => $qData) {
                $question = QuizQuestion::create([
                    'quiz_id'       => $quiz->id,
                    'question_text' => trim($qData['text']),
                    'type'          => 'single',
                    'points'        => !empty($qData['points']) ? (int) $qData['points'] : 10,
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

        return redirect()->route('admin.quizzes.index')->with('success', 'Test topshirig\'i muvaffaqiyatli yaratildi va savollar kitobga biriktirildi! 📝🎯');
    }

    public function show(Quiz $quiz)
    {
        $quiz->load(['book', 'questions.options']);

        return view('admin.quizzes.show', compact('quiz'));
    }

    public function destroy(Quiz $quiz)
    {
        DB::transaction(function () use ($quiz) {
            foreach ($quiz->questions as $q) {
                $q->options()->delete();
            }
            $quiz->questions()->delete();
            $quiz->attempts()->delete();
            $quiz->delete();
        });

        return redirect()->route('admin.quizzes.index')->with('success', 'Test topshirig\'i va uning savollari o\'chirildi.');
    }
}
