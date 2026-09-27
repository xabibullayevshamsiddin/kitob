<?php

use App\Http\Livewire\Dashboard;
use App\Http\Livewire\Auth\OnboardingWizard;
use App\Http\Livewire\Ai\AiChat;
use App\Http\Livewire\Catalog\CatalogPage;
use App\Http\Livewire\Groups\GroupDetail;
use App\Http\Livewire\Groups\GroupList;
use App\Http\Livewire\Live\LiveDetail;
use App\Http\Livewire\Live\LiveIndex;
use App\Http\Livewire\Chat\GlobalChat;
use App\Http\Livewire\Leaderboard\LeaderboardPage;
use App\Http\Livewire\Quiz\TakeQuiz;
use App\Http\Livewire\Profile\ProfilePage;
use App\Http\Livewire\Notifications\NotificationList;
use App\Http\Livewire\Settings\SettingsPage;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES — hech kim login bo'lmasdan kira oladi
|--------------------------------------------------------------------------
*/

// Bosh sahifa (public landing page)
Route::get('/', function () {
    $usersCount = \App\Models\User::count();
    $recentUsers = \App\Models\User::latest()->take(4)->get();
    $booksCount = \App\Models\Book::where('is_active', true)->count();
    $featuredBook = \App\Models\Book::where('is_active', true)->orderBy('week_number', 'desc')->first();
    $maxStreak = \App\Models\UserStreak::max('current_streak') ?? 0;
    $totalMinutes = (int) \App\Models\ReadingSession::sum('minutes_read');
    $audiosCount = \App\Models\BookAudio::count();
    $videosCount = \App\Models\BookVideo::count();
    $quizzesCount = \App\Models\Quiz::count();

    return view('public.home', compact(
        'usersCount',
        'recentUsers',
        'booksCount',
        'featuredBook',
        'maxStreak',
        'totalMinutes',
        'audiosCount',
        'videosCount',
        'quizzesCount'
    ));
})->name('home');

// Platforma haqida
Route::get('/about', function () {
    $usersCount = \App\Models\User::count();
    $booksCount = \App\Models\Book::where('is_active', true)->count();
    $totalMinutes = (int) \App\Models\ReadingSession::sum('minutes_read');
    $maxStreak = \App\Models\UserStreak::max('current_streak') ?? 0;

    return view('public.about', compact(
        'usersCount',
        'booksCount',
        'totalMinutes',
        'maxStreak'
    ));
})->name('about');

// Aloqa
Route::get('/contact', function () {
    return view('public.contact');
})->name('contact');

Route::post('/contact', function (\Illuminate\Http\Request $req) {
    $req->validate([
        'name'    => 'required|string|max:100',
        'email'   => 'required|email',
        'message' => 'required|string|max:2000',
    ]);
    // TODO: mail yoki DB ga yozish
    return back()->with('success', 'Xabaringiz qabul qilindi! Tez orada javob beramiz.');
})->name('contact.send');

// FAQ
Route::get('/faq', function () {
    return view('public.faq');
})->name('faq');

// Shartlar va maxfiylik
Route::get('/privacy', function () {
    return view('public.privacy');
})->name('privacy');

Route::get('/terms', function () {
    return view('public.terms');
})->name('terms');

// Public kitoblar katalogi (faqat ko'rish, o'qish uchun login kerak)
Route::get('/books', function () {
    $books = \App\Models\Book::where('is_active', true)->latest()->paginate(12);
    return view('public.books', compact('books'));
})->name('books.public');

// Kitoblar katalogi (login shart emas — hamma ko'ra oladi)
Route::get('/catalog', CatalogPage::class)->name('books.catalog');

// Reyting sahifasi (hamma ko'ra oladi)
Route::get('/leaderboard', LeaderboardPage::class)->name('leaderboard');

// Umumiy Chat (hamma ko'ra oladi, yozish uchun login kerak bo'ladi Livewire tomonida)
Route::get('/chat', GlobalChat::class)->name('chat');

// Guruhlar (hamma ko'ra oladi)
Route::get('/groups', GroupList::class)->name('groups.index');
Route::get('/groups/{group}', GroupDetail::class)->name('groups.show');

// Jonli efirlar (hamma ko'ra oladi)
Route::get('/live', LiveIndex::class)->name('live.index');
Route::get('/live/{event}', LiveDetail::class)->name('live.show');
Route::post('/live/{event}/signal', [\App\Http\Controllers\LiveSignalController::class, 'send'])->name('live.signal.send');
Route::get('/live/{event}/signals', [\App\Http\Controllers\LiveSignalController::class, 'poll'])->name('live.signal.poll');

// Error sahifalari dizaynini ko'rish (Preview routes)
Route::prefix('errors')->group(function () {
    Route::get('/404', fn() => response()->view('errors.404', [], 404))->name('error.404');
    Route::get('/403', fn() => response()->view('errors.403', [], 403))->name('error.403');
    Route::get('/500', fn() => response()->view('errors.500', [], 500))->name('error.500');
    Route::get('/419', fn() => response()->view('errors.419', [], 419))->name('error.419');
    Route::get('/429', fn() => response()->view('errors.429', [], 429))->name('error.429');
    Route::get('/503', fn() => response()->view('errors.503', [], 503))->name('error.503');
});

/*
|--------------------------------------------------------------------------
| ONBOARDING
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::get('/onboarding', OnboardingWizard::class)->name('onboarding');
});

/*
|--------------------------------------------------------------------------
| AUTHENTICATED STUDENT ROUTES — faqat student va admin kirishi mumkin
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'onboarding.complete', 'role.student'])->group(function () {

    // Dashboard — studentlar uchun Livewire to'liq sahifa komponenti.
    // Admin 'staff.redirect' middleware orqali o'z paneliga yo'naltiriladi.
    Route::get('/dashboard', Dashboard::class)
        ->middleware('staff.redirect')
        ->name('dashboard');

    // Books & Reading (O'quvchilar uchun — login kerak)
    Route::get('/books/{slug}', function ($slug) {
        $book = \App\Models\Book::where('slug', $slug)->firstOrFail();
        return view('pages.book-detail', compact('book'));
    })->name('books.show');

    // Kitobni BRAUZERDA onlayn o'qish (PDF embed)
    Route::get('/books/{book}/read-pdf', [\App\Http\Controllers\BookPdfController::class, 'read'])
        ->name('books.pdf.read');

    // Kitobni PDF sifatida yuklab olish
    // (endi SHARED AUTH guruhida — barcha rollar uchun)

    Route::get('/books/{book}/read/{chapter}', function ($book, $chapter) {
        $book    = \App\Models\Book::findOrFail($book);
        $chapter = \App\Models\BookChapter::where('book_id', $book->id)->findOrFail($chapter);
        return view('pages.reader', compact('book', 'chapter'));
    })->name('reader.show');

    // reader alias route
    Route::get('/reader/{book}', function ($bookId) {
        $book = \App\Models\Book::findOrFail($bookId);
        if ($book->pdf_path) {
            return redirect()->route('books.pdf.read', $book->id);
        }
        $firstChapter = $book->chapters()->first();
        if ($firstChapter) {
            return redirect()->route('reader.show', ['book' => $book->id, 'chapter' => $firstChapter->id]);
        }
        return redirect()->route('books.show', $book->slug);
    })->name('reader');

    Route::get('/books/{book}/audio', function ($bookId) {
        $book = \App\Models\Book::with('audios')->findOrFail($bookId);
        return view('pages.audio', compact('book'));
    })->name('audio.show');

    Route::get('/books/{book}/videos', function ($bookId) {
        $book = \App\Models\Book::with('videos')->findOrFail($bookId);
        return view('pages.videos', compact('book'));
    })->name('videos.index');

    Route::get('/books/{book}/quiz', TakeQuiz::class)->name('quiz.show');

    // AI Chatbot (faqat student — login kerak)
    Route::get('/ai-chat', AiChat::class)->name('ai-chat');
});

/*
|--------------------------------------------------------------------------
| SHARED AUTH ROUTES — barcha login bo'lgan foydalanuvchilar (student, teacher, admin)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    // Profil, Sozlamalar, Bildirishnomalar — barcha rollar uchun
    Route::get('/profile/{username}', ProfilePage::class)->name('profile.show');
    Route::get('/settings', SettingsPage::class)->name('settings');
    Route::get('/notifications', NotificationList::class)->name('notifications');

    // Kitobni PDF sifatida yuklab olish — barcha login bo'lgan foydalanuvchilar uchun
    Route::get('/books/{book}/pdf', [\App\Http\Controllers\BookPdfController::class, 'download'])
        ->name('books.pdf');

    Route::get('/books/{book}/pdf-stream', [\App\Http\Controllers\BookPdfController::class, 'stream'])
        ->name('books.pdf.stream');

    Route::get('/books/{book}/flipbook', function (\App\Models\Book $book) {
        abort_unless($book->pdf_path, 404);
        return view('pages.book-flipbook', compact('book'));
    })->name('books.flipbook');

    // Mutolaa sessiyasi va progressini real-time hisoblash
    Route::post('/api/reading/heartbeat', function (\Illuminate\Http\Request $request) {
        $request->validate([
            'book_id'    => 'required|exists:books,id',
            'chapter_id' => 'nullable|exists:book_chapters,id',
            'minutes'    => 'required|numeric|min:0.1|max:60',
        ]);

        $user = auth()->user();
        $minutes = ceil($request->minutes);
        $today = now('Asia/Tashkent')->toDateString();

        \App\Models\ReadingSession::create([
            'user_id'      => $user->id,
            'book_id'      => $request->book_id,
            'chapter_id'   => $request->chapter_id,
            'minutes_read' => $minutes,
            'session_date' => $today,
        ]);

        $activity = \App\Models\DailyActivity::firstOrCreate(
            ['user_id' => $user->id, 'activity_date' => $today],
            ['minutes_read' => 0, 'logged_in' => true, 'points_earned' => 0]
        );
        $activity->increment('minutes_read', $minutes);

        $streakService = app(\App\Services\Gamification\StreakService::class);
        $streak = $streakService->recordActivity($user);

        $pointsPer10Min = (int) config('app.reading_points_per_10min', 10);
        $pointsToAward = max(1, (int) round(($minutes / 10) * $pointsPer10Min));

        $pointsService = app(\App\Services\Gamification\PointsService::class);
        $pointsService->awardPoints(
            $user,
            $pointsToAward,
            'reading',
            "{$minutes} daqiqa kitob mutolaasi uchun rag'bat",
            $request->book_id,
            \App\Models\Book::class
        );
        $pointsService->awardCoins(
            $user,
            max(1, (int) round($pointsToAward / 2)),
            'reading',
            "{$minutes} daqiqa kitob mutolaasi tangasi"
        );

        return response()->json([
            'success'             => true,
            'total_minutes_today' => $activity->minutes_read,
            'total_points'        => $user->fresh()->total_points,
            'streak'              => $streak->current_streak,
        ]);
    })->name('reading.heartbeat');

    Route::post('/api/reading/progress', function (\Illuminate\Http\Request $request) {
        $request->validate([
            'book_id'          => 'required|exists:books,id',
            'chapter_id'       => 'nullable|exists:book_chapters,id',
            'last_position'    => 'required|integer',
            'percent_complete' => 'required|numeric|min:0|max:100',
        ]);

        $user = auth()->user();
        $progress = \App\Models\BookReadingProgress::updateOrCreate(
            [
                'user_id'    => $user->id,
                'book_id'    => $request->book_id,
                'chapter_id' => $request->chapter_id,
            ],
            [
                'last_position'    => $request->last_position,
                'percent_complete' => $request->percent_complete,
            ]
        );

        return response()->json(['success' => true, 'progress' => $progress]);
    })->name('reading.progress');
});

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
        $books = \App\Models\Book::latest()->paginate(15);
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
            'published_at' => now(),
            'is_active'    => false,
        ]);
        return redirect()->route('teacher.books.index')->with('success', "Kitob qo'shildi!" . ($pdfPath ? ' PDF fayl yuklandi. 📕' : ''));
    })->name('books.store');

    // Quiz boshqarish
    Route::get('/quizzes', function () {
        $books = \App\Models\Book::with('quizzes')->get();
        return view('teacher.quizzes.index', compact('books'));
    })->name('quizzes.index');

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

/*
|--------------------------------------------------------------------------
| ADMIN PANEL — faqat admin kirishi mumkin
|--------------------------------------------------------------------------
*/
Route::prefix('admin')
    ->middleware(['auth', 'role.admin'])
    ->name('admin.')
    ->group(function () {

    Route::get('/', function () {
        $stats = [
            'total_users'    => \App\Models\User::count(),
            'total_students' => \App\Models\User::role('student')->count(),
            'total_teachers' => \App\Models\User::role('teacher')->count(),
            'total_books'    => \App\Models\Book::count(),
            'active_books'   => \App\Models\Book::where('is_active', true)->count(),
        ];
        $recentUsers = \App\Models\User::with('roles')->latest()->take(5)->get();
        return view('admin.dashboard', compact('stats', 'recentUsers'));
    })->name('dashboard');

    // Foydalanuvchilar
    Route::get('/users', function () {
        $users = \App\Models\User::with('roles')->latest()->paginate(20);
        return view('admin.users.index', compact('users'));
    })->name('users.index');

    Route::get('/users/{user}/edit', function (\App\Models\User $user) {
        $roles = \Spatie\Permission\Models\Role::all();
        return view('admin.users.edit', compact('user', 'roles'));
    })->name('users.edit');

    Route::put('/users/{user}', function (\Illuminate\Http\Request $req, \App\Models\User $user) {
        $req->validate(['role' => 'required|string|exists:roles,name']);
        $user->syncRoles([$req->role]);
        return redirect()->route('admin.users.index')->with('success', 'Rol yangilandi!');
    })->name('users.update');

    Route::delete('/users/{user}', function (\App\Models\User $user) {
        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'Foydalanuvchi o\'chirildi!');
    })->name('users.destroy');

    // Kitoblar
    Route::get('/books', function () {
        $books = \App\Models\Book::latest()->paginate(20);
        return view('admin.books.index', compact('books'));
    })->name('books.index');

    Route::put('/books/{book}/toggle', function (\App\Models\Book $book) {
        $book->update(['is_active' => !$book->is_active]);
        return back()->with('success', 'Kitob holati yangilandi!');
    })->name('books.toggle');

    // Statistika
    Route::get('/stats', function () {
        $stats = [
            'total_users'    => \App\Models\User::count(),
            'total_students' => \App\Models\User::role('student')->count(),
            'total_teachers' => \App\Models\User::role('teacher')->count(),
            'total_books'    => \App\Models\Book::count(),
            'active_books'   => \App\Models\Book::where('is_active', true)->count(),
        ];
        return view('admin.stats', compact('stats'));
    })->name('stats');

    // Sozlamalar
    Route::get('/settings', function () {
        return view('admin.settings');
    })->name('settings');

    Route::post('/settings/update', function (\Illuminate\Http\Request $req) {
        // TODO: update .env or config table
        return back()->with('success', 'Sozlamalar saqlandi!');
    })->name('settings.update');

    Route::post('/settings/cache', function (\Illuminate\Http\Request $req) {
        $type = $req->input('type', 'all');
        match($type) {
            'config' => \Illuminate\Support\Facades\Artisan::call('config:clear'),
            'route'  => \Illuminate\Support\Facades\Artisan::call('route:clear'),
            'view'   => \Illuminate\Support\Facades\Artisan::call('view:clear'),
            default  => \Illuminate\Support\Facades\Artisan::call('cache:clear'),
        };
        return back()->with('success', 'Cache tozalandi!');
    })->name('settings.cache');

    Route::post('/settings/maintenance', function (\Illuminate\Http\Request $req) {
        // TODO: maintenance mode toggle
        return back()->with('success', 'Texnik xizmat rejimi yangilandi!');
    })->name('settings.maintenance');

    // Admin books create/edit routes
    Route::get('/books/create', function () {
        return view('admin.books.create');
    })->name('books.create');

    Route::post('/books', function (\Illuminate\Http\Request $req) {
        $req->validate([
            'title'       => 'required|string|max:255',
            'author'      => 'required|string|max:255',
            'description' => 'required|string',
            'genre'       => 'required|string|max:100',
            'pdf_file'    => 'nullable|file|mimes:pdf|max:204800',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:15360',
        ], [
            'pdf_file.max'        => "PDF fayl hajmi 200 MB dan oshmasligi kerak.",
            'pdf_file.mimes'      => "Faqat PDF formatdagi (.pdf) faylni yuklash mumkin.",
            'cover_image.max'     => "Muqova rasmi hajmi 15 MB dan oshmasligi kerak.",
            'cover_image.image'   => "Faqat rasm formatidagi (JPG, PNG, WEBP) faylni yuklash mumkin.",
        ]);

        $pdfPath = null;
        if ($req->hasFile('pdf_file')) {
            $pdfPath = $req->file('pdf_file')->store('books/pdf', 'public');
        }

        $coverPath = null;
        if ($req->hasFile('cover_image')) {
            $coverPath = $req->file('cover_image')->store('books/covers', 'public');
        }

        \App\Models\Book::create([
            'title'        => $req->title,
            'author'       => $req->author,
            'description'  => $req->description,
            'genre'        => $req->genre,
            'slug'         => \Illuminate\Support\Str::slug($req->title) . '-' . uniqid(),
            'week_number'  => $req->week_number ?? ((\App\Models\Book::max('week_number') ?? 0) + 1),
            'pdf_path'     => $pdfPath,
            'cover_image'  => $coverPath,
            'published_at' => now(),
            'is_active'    => $req->has('is_active'),
        ]);

        $msg = "Yangi kitob muvaffaqiyatli qo'shildi!";
        if ($pdfPath && $coverPath) {
            $msg = "Kitob, muqova rasmi va PDF fayl muvaffaqiyatli yuklandi! 📚✨";
        } elseif ($pdfPath) {
            $msg = "Kitob qo'shildi va 200MB gacha bo'lgan PDF fayl yuklandi! 📕";
        } elseif ($coverPath) {
            $msg = "Kitob va muqova rasmi muvaffaqiyatli qo'shildi! 🖼️";
        }

        return redirect()->route('admin.books.index')->with('success', $msg);
    })->name('books.store');

    Route::get('/books/{book}/edit', function (\App\Models\Book $book) {
        return view('admin.books.edit', compact('book'));
    })->name('books.edit');

    Route::put('/books/{book}', function (\Illuminate\Http\Request $req, \App\Models\Book $book) {
        $req->validate([
            'title'       => 'required|string|max:255',
            'author'      => 'required|string|max:255',
            'description' => 'required|string',
            'genre'       => 'required|string|max:100',
            'pdf_file'    => 'nullable|file|mimes:pdf|max:204800',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:15360',
        ], [
            'pdf_file.max'        => "PDF fayl hajmi 200 MB dan oshmasligi kerak.",
            'pdf_file.mimes'      => "Faqat PDF formatdagi (.pdf) faylni yuklash mumkin.",
            'cover_image.max'     => "Muqova rasmi hajmi 15 MB dan oshmasligi kerak.",
            'cover_image.image'   => "Faqat rasm formatidagi (JPG, PNG, WEBP) faylni yuklash mumkin.",
        ]);

        $updateData = [
            'title'       => $req->title,
            'author'      => $req->author,
            'description' => $req->description,
            'genre'       => $req->genre,
            'week_number' => $req->week_number ?? $book->week_number,
            'is_active'   => $req->has('is_active'),
        ];

        if ($req->hasFile('pdf_file')) {
            $updateData['pdf_path'] = $req->file('pdf_file')->store('books/pdf', 'public');
        }

        if ($req->hasFile('cover_image')) {
            $updateData['cover_image'] = $req->file('cover_image')->store('books/covers', 'public');
        }

        $book->update($updateData);

        return redirect()->route('admin.books.index')->with('success', 'Kitob ma\'lumotlari va fayllari muvaffaqiyatli yangilandi!');
    })->name('books.update');

    Route::delete('/books/{book}', function (\App\Models\Book $book) {
        $book->delete();
        return redirect()->route('admin.books.index')->with('success', 'Kitob o\'chirildi!');
    })->name('books.destroy');

    // ── Kitob Audiolari ──
    Route::get('/audios', [\App\Http\Controllers\Admin\AudioController::class, 'index'])->name('audios.index');
    Route::get('/audios/create', [\App\Http\Controllers\Admin\AudioController::class, 'create'])->name('audios.create');
    Route::post('/audios', [\App\Http\Controllers\Admin\AudioController::class, 'store'])->name('audios.store');
    Route::delete('/audios/{audio}', [\App\Http\Controllers\Admin\AudioController::class, 'destroy'])->name('audios.destroy');

    // ── Kitob Videolari ──
    Route::get('/videos', [\App\Http\Controllers\Admin\VideoController::class, 'index'])->name('videos.index');
    Route::get('/videos/create', [\App\Http\Controllers\Admin\VideoController::class, 'create'])->name('videos.create');
    Route::post('/videos', [\App\Http\Controllers\Admin\VideoController::class, 'store'])->name('videos.store');
    Route::delete('/videos/{video}', [\App\Http\Controllers\Admin\VideoController::class, 'destroy'])->name('videos.destroy');

    // ── Kitob Test Topshiriqlari (Quizzes) ──
    Route::get('/quizzes', [\App\Http\Controllers\Admin\QuizController::class, 'index'])->name('quizzes.index');
    Route::get('/quizzes/create', [\App\Http\Controllers\Admin\QuizController::class, 'create'])->name('quizzes.create');
    Route::post('/quizzes', [\App\Http\Controllers\Admin\QuizController::class, 'store'])->name('quizzes.store');
    Route::get('/quizzes/{quiz}', [\App\Http\Controllers\Admin\QuizController::class, 'show'])->name('quizzes.show');
    Route::delete('/quizzes/{quiz}', [\App\Http\Controllers\Admin\QuizController::class, 'destroy'])->name('quizzes.destroy');
});

// Alias for profile.edit
Route::middleware(['auth'])->get('/profile-edit', function () {
    return redirect()->route('settings');
})->name('profile.edit');

