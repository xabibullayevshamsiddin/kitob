<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Quiz;
use App\Services\QuizAuthoringService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuizController extends Controller
{
    public function index()
    {
        $quizzes = Quiz::with(['book:id,title,cover_image,slug', 'creator:id,name'])
            ->withCount([
                'questions',
                'attempts',
                'attempts as pending_attempts_count' => fn ($query) => $query->where('review_status', 'pending'),
            ])->latest()->paginate(15);

        return view('admin.quizzes.index', compact('quizzes'));
    }

    public function create(Request $request)
    {
        $books = Book::withCount('quizzes')->orderBy('title')->get(['id', 'title', 'week_number', 'author']);
        $selectedBookId = $request->query('book_id');

        return view('admin.quizzes.create', compact('books', 'selectedBookId'));
    }

    public function store(Request $request, QuizAuthoringService $authoring)
    {
        if ($request->filled('book_id') && Quiz::where('book_id', $request->book_id)->exists()) {
            return back()->withInput()->withErrors(['book_id' => 'Ushbu kitob uchun test allaqachon mavjud.']);
        }

        $quiz = $authoring->create($request, 200, (int) auth()->id());

        return redirect()->route('admin.quizzes.show', $quiz)->with('success', 'Test savollari saqlandi.');
    }

    public function show(Quiz $quiz)
    {
        $quiz->load(['book', 'creator:id,name', 'questions.options']);
        $section = request()->query('section', 'results');
        abort_unless(in_array($section, ['results', 'questions'], true), 404);
        $attempts = $quiz->attempts()
            ->with('user:id,name,email')
            ->latest('completed_at')
            ->paginate(20);

        return view('admin.quizzes.show', compact('quiz', 'attempts', 'section'));
    }

    public function edit(Quiz $quiz)
    {
        $quiz->load(['questions.options']);

        return view('teacher.quizzes.edit', compact('quiz'));
    }

    public function update(Request $request, Quiz $quiz, QuizAuthoringService $authoring)
    {
        $authoring->update($request, $quiz, 200);

        return redirect()->route('admin.quizzes.show', ['quiz' => $quiz, 'section' => 'questions'])
            ->with('success', 'Test va savollar yangilandi.');
    }

    public function destroy(Quiz $quiz)
    {
        DB::transaction(function () use ($quiz) {
            $quiz->questions()->each(function ($question) {
                if ($question->image_path) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($question->image_path);
                }
                $question->delete();
            });
            $quiz->attempts()->delete();
            $quiz->delete();
        });

        return redirect()->route('admin.quizzes.index')->with('success', 'Test o‘chirildi.');
    }
}
