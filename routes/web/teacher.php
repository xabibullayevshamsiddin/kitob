<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| TEACHER PANEL — admin yoki teacher kirishi mumkin
|--------------------------------------------------------------------------
*/
Route::prefix('teacher')
    ->middleware(['auth', 'role.teacher'])
    ->name('teacher.')
    ->group(function () {

    Route::get('/', function () {
        return view('teacher.dashboard');
    })->name('dashboard');

    // Kitob boshqarish
    Route::get('/books', function () {
        $books = \App\Models\Book::when(!auth()->user()->isAdmin(), fn ($q) => $q->where('created_by', auth()->id()))
            ->latest()
            ->paginate(15);
        return view('teacher.books.index', compact('books'));
    })->name('books.index');

    Route::get('/books/create', function () {
        return view('teacher.books.create');
    })->name('books.create');

    Route::post('/books', function (\Illuminate\Http\Request $req) {
        $req->validate([
            'title'       => 'required|string|max:255',
            'author'      => 'required|string|max:255',
            'description' => 'required|string',
            'genre'       => 'required|string|max:100',
            'pdf_file'    => 'nullable|file|mimes:pdf|max:20480',
        ]);

        $pdfPath = null;
        if ($req->hasFile('pdf_file')) {
            $pdfPath = $req->file('pdf_file')->store('books/pdf', 'public');
        }

        \App\Models\Book::create([
            'title'        => $req->title,
            'author'       => $req->author,
            'description'  => $req->description,
            'genre'        => $req->genre,
            'slug'         => \Illuminate\Support\Str::slug($req->title),
            'week_number'  => \App\Models\Book::max('week_number') + 1,
            'pdf_path'     => $pdfPath,
            'created_by'   => auth()->id(),
            'published_at' => now(),
            'is_active'    => false,
        ]);
        return redirect()->route('teacher.books.index')->with('success', "Kitob qo'shildi!" . ($pdfPath ? ' PDF fayl yuklandi. 📕' : ''));
    })->name('books.store');

    // Quiz boshqarish (Kitobga test qo'shish va boshqarish)
    Route::get('/quizzes', [\App\Http\Controllers\Teacher\QuizController::class, 'index'])->name('quizzes.index');
    Route::get('/quizzes/create', [\App\Http\Controllers\Teacher\QuizController::class, 'create'])->name('quizzes.create');
    Route::post('/quizzes', [\App\Http\Controllers\Teacher\QuizController::class, 'store'])->name('quizzes.store');
    Route::get('/quizzes/{quiz}/edit', [\App\Http\Controllers\Teacher\QuizController::class, 'edit'])->name('quizzes.edit');
    Route::put('/quizzes/{quiz}', [\App\Http\Controllers\Teacher\QuizController::class, 'update'])->name('quizzes.update');
    Route::get('/quizzes/{quiz}', [\App\Http\Controllers\Teacher\QuizController::class, 'show'])->name('quizzes.show');
    Route::delete('/quizzes/{quiz}', [\App\Http\Controllers\Teacher\QuizController::class, 'destroy'])->name('quizzes.destroy');
    Route::get('/quiz-reviews', [\App\Http\Controllers\QuizReviewController::class, 'index'])->name('quiz-reviews.index');
    Route::get('/quiz-reviews/{attempt}', [\App\Http\Controllers\QuizReviewController::class, 'show'])->name('quiz-reviews.show');
    Route::post('/quiz-reviews/{attempt}', [\App\Http\Controllers\QuizReviewController::class, 'update'])->name('quiz-reviews.update');

    // O'quvchilar statistikasi
    Route::get('/students', function () {
        $students = \App\Models\User::role('student')->with('streak')->paginate(20);
        return view('teacher.students.index', compact('students'));
    })->name('students.index');

    // Jonli efirlar
    Route::get('/live', function () {
        $events = \App\Models\LiveEvent::whereIn('status', ['scheduled', 'live'])->latest()->paginate(10);
        return view('teacher.live.index', compact('events'));
    })->name('live.index');
});

