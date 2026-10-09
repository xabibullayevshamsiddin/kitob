<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Quiz;
use App\Services\QuizAuthoringService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class QuizController extends Controller
{
    public function index()
    {
        $quizzes = Quiz::with(['book:id,title,cover_image,slug,week_number,author'])
            ->when(!auth()->user()->isAdmin(), fn ($query) => $query->where('created_by', auth()->id()))
            ->withCount([
                'questions',
                'attempts',
                'attempts as pending_attempts_count' => fn ($query) => $query->where('review_status', 'pending'),
            ])->latest()->paginate(15);
        return view('teacher.quizzes.index', compact('quizzes'));
    }

    public function create(Request $request)
    {
        $books = Book::withCount('quizzes')
            ->when(!auth()->user()->isAdmin(), fn ($q) => $q->where('created_by', auth()->id()))
            ->orderBy('title')
            ->get(['id', 'title', 'week_number', 'author']);

        $selectedBookId = $request->query('book_id');
        if ($selectedBookId && !auth()->user()->isAdmin()) {
            $selectedBook = Book::find($selectedBookId);
            abort_unless($selectedBook && (int) $selectedBook->created_by === (int) auth()->id(), 403, "Siz faqat o'zingiz qo'shgan kitobga test qo'sha olasiz.");
        }

        return view('teacher.quizzes.create', compact('books', 'selectedBookId'));
    }

    public function store(Request $request, QuizAuthoringService $authoring)
    {
        if ($request->filled('book_id')) {
            $book = Book::findOrFail($request->book_id);
            abort_unless(auth()->user()->isAdmin() || (int) $book->created_by === (int) auth()->id(), 403, "Siz faqat o'zingiz qo'shgan kitobga test qo'sha olasiz.");

            if (Quiz::where('book_id', $request->book_id)->exists()) {
                return back()->withInput()->withErrors(['book_id' => 'Ushbu kitob uchun test allaqachon mavjud.']);
            }
        }

        $quiz = $authoring->create($request, auth()->user()->isAdmin() ? 200 : 50, (int) auth()->id());

        return redirect()->route('teacher.quizzes.show', $quiz)->with('success', 'Test savollari saqlandi.');
    }

    public function show(Quiz $quiz)
    {
        abort_unless(auth()->user()->isAdmin() || (int) $quiz->created_by === (int) auth()->id(), 403);

        $quiz->load(['book', 'questions.options']);
        $section = request()->query('section', 'results');
        abort_unless(in_array($section, ['results', 'questions'], true), 404);
        $attempts = $quiz->attempts()
            ->with('user:id,name,email')
            ->latest('completed_at')
            ->paginate(20);

        return view('teacher.quizzes.show', compact('quiz', 'attempts', 'section'));
    }

    public function edit(Quiz $quiz)
    {
        abort_unless(auth()->user()->isAdmin() || (int) $quiz->created_by === (int) auth()->id(), 403);

        $quiz->load(['questions.options']);

        return view('teacher.quizzes.edit', compact('quiz'));
    }

    public function update(Request $request, Quiz $quiz, QuizAuthoringService $authoring)
    {
        abort_unless(auth()->user()->isAdmin() || (int) $quiz->created_by === (int) auth()->id(), 403);

        $authoring->update($request, $quiz, auth()->user()->isAdmin() ? 200 : 50);

        return redirect()->route('teacher.quizzes.show', ['quiz' => $quiz, 'section' => 'questions'])
            ->with('success', 'Test va savollar yangilandi.');
    }

    public function destroy(Quiz $quiz)
    {
        abort_unless(auth()->user()->isAdmin() || (int) $quiz->created_by === (int) auth()->id(), 403);

        DB::transaction(function () use ($quiz) {
            $quiz->questions()->each(function ($question) {
                if ($question->image_path) {
                    Storage::disk('public')->delete($question->image_path);
                }
                $question->delete();
            });
            $quiz->attempts()->delete();
            $quiz->delete();
        });

        return redirect()->route('teacher.quizzes.index')->with('success', 'Test o‘chirildi.');
    }
}
