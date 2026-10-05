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

    $topFiveUsers = app(\App\Services\LeaderboardService::class)->getTopUsers(5);

    $featuredReadersCount = $featuredBook ? max(18, (int) $featuredBook->readingSessions()->distinct('user_id')->count('user_id')) : 0;
    $featuredChaptersCount = $featuredBook ? max(1, (int) $featuredBook->chapters()->count()) : 0;
    $featuredAudioMinutes = $featuredBook ? (int) round($featuredBook->audios()->sum('duration') / 60) : 0;
    if ($featuredAudioMinutes === 0 && $featuredBook) {
        $featuredAudioMinutes = max(25, $featuredChaptersCount * 6);
    }

    return view('public.home', compact(
        'usersCount',
        'recentUsers',
        'booksCount',
        'featuredBook',
        'maxStreak',
        'totalMinutes',
        'audiosCount',
        'videosCount',
        'quizzesCount',
        'topFiveUsers',
        'featuredReadersCount',
        'featuredChaptersCount',
        'featuredAudioMinutes'
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
        'message' => 'required|string|max:4000',
    ]);

    if ($req->input('is_report') == '1' || $req->filled('reported_user_id')) {
        \App\Models\Report::create([
            'reporter_id'      => auth()->id(),
            'reported_user_id' => $req->input('reported_user_id') ?: null,
            'source'           => $req->input('source', 'Umumiy chat'),
            'message_content'  => $req->input('message'),
            'link'             => $req->input('link'),
            'reason'           => $req->input('subject', 'Haqorat / Nojo\'ya xatti-harakat'),
            'status'           => 'pending',
        ]);

        return redirect()->route('contact')->with('success', '🚩 Qoidabuzarlik bo\'yicha shikoyatingiz ma\'muriyatga yetkazildi! Moderatorlar tez orada tekshirib, tegishli chora (ban) ko\'rishadi.');
    }

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

// Public kitoblar katalogi -> Interaktiv katalog sahifasiga yo'naltirish
Route::get('/books', function (\Illuminate\Http\Request $request) {
    return redirect()->route('books.catalog', $request->all());
})->name('books.public');

// Kitoblar katalogi (login shart emas — hamma ko'ra oladi)
Route::get('/catalog', CatalogPage::class)->name('books.catalog');

// Reyting sahifasi (hamma ko'ra oladi)
Route::get('/leaderboard', LeaderboardPage::class)->name('leaderboard');

// Umumiy Chat (hamma ko'ra oladi, yozish uchun login kerak bo'ladi Livewire tomonida)
Route::get('/chat', GlobalChat::class)->name('chat');
Route::post('/chat/voice', [\App\Http\Controllers\ChatVoiceController::class, 'upload'])->middleware('auth')->name('chat.voice.upload');

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

    // AI Chatbot (faqat student — login kerak)
    Route::get('/ai-chat', AiChat::class)->name('ai-chat');
});

/*
|--------------------------------------------------------------------------
| SHARED AUTH ROUTES — barcha login bo'lgan foydalanuvchilar (student, teacher, admin)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    // Kitoblar va Mutolaa (Barcha login bo'lgan foydalanuvchilar uchun)
    Route::get('/books/{slug}', function ($slug) {
        $book = \App\Models\Book::where('slug', $slug)->firstOrFail();
        if ($book->published_at && $book->published_at->isFuture()) {
            $user = auth()->user();
            $rank = $user ? app(\App\Services\LeaderboardService::class)->getUserRank($user) : null;
            $canEarlyAccess = ($user && ($user->isAdmin() || (method_exists($user, 'hasRole') && $user->hasRole('admin')) || ($rank !== null && $rank <= 5)));
            if (!$canEarlyAccess) {
                abort(403, 'Ushbu kitob hali rasman e\'lon qilinmagan. Top 5 kitobxonlar uchun erta kirish imtiyozi mavjud.');
            }
        }
        return view('pages.book-detail', compact('book'));
    })->name('books.show');

    // Kitobni BRAUZERDA onlayn o'qish (PDF embed)
    Route::get('/books/{book}/read-pdf', [\App\Http\Controllers\BookPdfController::class, 'read'])
        ->name('books.pdf.read');

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

    // Audio darslar va tahlillar
    Route::get('/books/{book}/audio', function ($bookId) {
        $book = \App\Models\Book::with('audios')->findOrFail($bookId);
        return view('pages.audio', compact('book'));
    })->name('audio.show');

    // Barcha video darslar yoki tanlangan kitob videolari
    Route::get('/videos/{bookId?}', function ($bookId = null) {
        if ($bookId) {
            $book = \App\Models\Book::with('videos')->find($bookId);
            if ($book) {
                return view('pages.videos', compact('book'));
            }
        }
        $book = null;
        $videos = \App\Models\BookVideo::with('book')->latest()->get();
        return view('pages.videos', compact('book', 'videos'));
    })->name('videos.index');

    Route::get('/books/{book}/videos', function ($bookId) {
        return redirect()->route('videos.index', $bookId);
    });

    // Test topshirig'i
    Route::get('/books/{book}/quiz', TakeQuiz::class)->name('quiz.show');

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

    // Mutolaa va video sessiyasi va progressini real-time hisoblash
    Route::post('/api/reading/heartbeat', function (\Illuminate\Http\Request $request) {
        $request->validate([
            'book_id'    => 'nullable|exists:books,id',
            'chapter_id' => 'nullable|exists:book_chapters,id',
            'page_type'  => 'nullable|string|in:book,flipbook,chapter,audio,quiz,video',
            'minutes'    => 'required|numeric|min:0.1|max:60',
        ]);

        $user = auth()->user();
        $minutes = max(1, (int) round($request->minutes));
        $today = now('Asia/Tashkent')->toDateString();

        // Oxirgi 30 daqiqa ichidagi sessiyani davom ettirish yoki yangi yaratish
        $sessionQuery = \App\Models\ReadingSession::where('user_id', $user->id)
            ->where('session_date', $today)
            ->where('created_at', '>=', now()->subMinutes(30));

        if ($request->book_id) {
            $sessionQuery->where('book_id', $request->book_id);
        } else {
            $sessionQuery->whereNull('book_id');
        }

        $session = $sessionQuery->latest()->first();

        if ($session) {
            $session->increment('minutes_read', $minutes);
        } else {
            \App\Models\ReadingSession::create([
                'user_id'      => $user->id,
                'book_id'      => $request->book_id ?: null,
                'chapter_id'   => $request->chapter_id ?: null,
                'minutes_read' => $minutes,
                'session_date' => $today,
            ]);
        }

        $activity = \App\Models\DailyActivity::firstOrCreate(
            ['user_id' => $user->id, 'activity_date' => $today],
            ['minutes_read' => 0, 'logged_in' => true, 'points_earned' => 0]
        );

        $prevMinutes = (int) $activity->minutes_read;
        $newMinutes = $prevMinutes + $minutes;
        $activity->increment('minutes_read', $minutes);

        $streakMinMinutes = (int) setting('streak_minimum_minutes', 15);
        if ($newMinutes >= $streakMinMinutes) {
            $streakService = app(\App\Services\Gamification\StreakService::class);
            $streak = $streakService->recordActivity($user);
        } else {
            $streak = \App\Models\UserStreak::firstOrCreate(['user_id' => $user->id]);
        }

        // Har 1 daqiqa uchun: sozlamalardagi dinamik ball va tangalar beriladi
        $pointsPerMinute = (int) setting('reading_points_per_minute', 1);
        $coinsPerMinute  = (int) setting('reading_coins_per_minute', 1);
        $pointsToAward   = $minutes * $pointsPerMinute;
        $coinsToAward    = $minutes * $coinsPerMinute;

        $pageTypeLabels = [
            'video'    => 'video dars tomoshasi',
            'audio'    => 'audio dars tinglashi',
            'quiz'     => 'test topshirig\'i',
            'flipbook' => 'interaktiv mutolaa',
            'chapter'  => 'bob mutolaasi',
            'book'     => 'kitob mutolaasi',
        ];
        $typeLabel = $pageTypeLabels[$request->page_type] ?? 'mutolaa va o\'rganish';

        $pointsService = app(\App\Services\Gamification\PointsService::class);

        if ($pointsToAward > 0) {
            $pointsService->awardPoints(
                $user,
                $pointsToAward,
                'reading',
                "{$minutes} daqiqa faol {$typeLabel} uchun {$pointsToAward} ball",
                $request->book_id,
                $request->book_id ? \App\Models\Book::class : null
            );
        }

        if ($coinsToAward > 0) {
            $pointsService->awardCoins(
                $user,
                $coinsToAward,
                'reading',
                "{$minutes} daqiqa faol {$typeLabel} uchun {$coinsToAward} tanga 🪙"
            );
        }

        $freshUser = $user->fresh();

        return response()->json([
            'success'              => true,
            'minutes_added'        => $minutes,
            'points_added'         => $pointsToAward,
            'coins_added'          => $coinsToAward,
            'total_minutes_today'  => $newMinutes,
            'total_minutes_all'    => (int) $freshUser->total_reading_minutes,
            'total_points'         => (int) $freshUser->total_points,
            'coin_balance'         => (int) $freshUser->coin_balance,
            'streak'               => (int) $streak->current_streak,
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

    // Quiz boshqarish (Kitobga test qo'shish va boshqarish)
    Route::get('/quizzes', [\App\Http\Controllers\Teacher\QuizController::class, 'index'])->name('quizzes.index');
    Route::get('/quizzes/create', [\App\Http\Controllers\Teacher\QuizController::class, 'create'])->name('quizzes.create');
    Route::post('/quizzes', [\App\Http\Controllers\Teacher\QuizController::class, 'store'])->name('quizzes.store');
    Route::get('/quizzes/{quiz}', [\App\Http\Controllers\Teacher\QuizController::class, 'show'])->name('quizzes.show');
    Route::delete('/quizzes/{quiz}', [\App\Http\Controllers\Teacher\QuizController::class, 'destroy'])->name('quizzes.destroy');

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
            'total_students' => \App\Models\User::where('role', 'student')->count(),
            'total_teachers' => \App\Models\User::where('role', 'teacher')->count(),
            'total_books'    => \App\Models\Book::count(),
            'active_books'   => \App\Models\Book::where('is_active', true)->count(),
            // Real dinamika (bu oy vs o'tgan oy)
            'new_users_this_month' => \App\Models\User::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count(),
            'new_users_prev_month' => \App\Models\User::whereMonth('created_at', now()->subMonth()->month)->whereYear('created_at', now()->subMonth()->year)->count(),
            'total_reading_minutes' => (int) (\App\Models\DailyActivity::sum('minutes_read')),
            'total_quizzes'   => \App\Models\Quiz::count(),
            'total_groups'    => \App\Models\Group::count(),
            'total_points'    => (int) \App\Models\User::sum('total_points'),
            'online_today'    => \App\Models\DailyActivity::whereDate('activity_date', today())->count(),
        ];

        // Foizli o'sish (0 ga bo'linishdan himoya)
        $stats['user_growth_pct'] = $stats['new_users_prev_month'] > 0
            ? round((($stats['new_users_this_month'] - $stats['new_users_prev_month']) / $stats['new_users_prev_month']) * 100)
            : ($stats['new_users_this_month'] > 0 ? 100 : 0);

        $recentUsers = \App\Models\User::with('roles')->latest()->take(5)->get();

        // ── GRAFIK MA'LUMOTLARI ──
        // 1. 30 kunlik ro'yxatdan o'tish trendi
        $signupsRaw = \App\Models\User::selectRaw('DATE(created_at) as day, COUNT(*) as cnt')
            ->where('created_at', '>=', now()->subDays(29)->startOfDay())
            ->groupBy('day')->pluck('cnt', 'day');
        $signupLabels = [];
        $signupData = [];
        $cumulative = max(0, \App\Models\User::where('created_at', '<', now()->subDays(29)->startOfDay())->count());
        foreach (range(29, 0) as $i) {
            $day = now()->subDays($i);
            $key = $day->toDateString();
            $signupLabels[] = $day->format('d.m');
            $cumulative += (int) ($signupsRaw[$key] ?? 0);
            $signupData[] = $cumulative;
        }

        // 2. 14 kunlik o'qilgan daqiqalar (DailyActivity)
        $minutesRaw = \App\Models\DailyActivity::selectRaw('DATE(activity_date) as day, SUM(minutes_read) as m')
            ->where('activity_date', '>=', today()->subDays(13))
            ->groupBy('day')->pluck('m', 'day');
        $minutesLabels = [];
        $minutesData = [];
        foreach (range(13, 0) as $i) {
            $day = today()->subDays($i);
            $minutesLabels[] = $day->format('d.m');
            $minutesData[] = (int) ($minutesRaw[$day->toDateString()] ?? 0);
        }

        // 3. Kontent formatlari taqsimoti (kitob + media)
        $formatData = [
            \App\Models\BookChapter::count(),
            \App\Models\BookAudio::count(),
            \App\Models\BookVideo::count(),
            \App\Models\Quiz::count(),
        ];

        return view('admin.dashboard', compact(
            'stats', 'recentUsers',
            'signupLabels', 'signupData',
            'minutesLabels', 'minutesData',
            'formatData'
        ));
    })->name('dashboard');

    // Foydalanuvchilar
    Route::get('/users', function () {
        $query = \App\Models\User::with('roles')->latest();
        if ($search = request('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        if ($role = request('role')) {
            $query->where('role', $role);
        }
        $users = $query->paginate(20)->withQueryString();
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

    // Foydalanuvchini ban qilish
    Route::post('/users/{user}/ban', function (\Illuminate\Http\Request $req, \App\Models\User $user) {
        $req->validate([
            'duration' => 'required|in:1_hour,1_day,1_week,1_month,permanent',
            'reason'   => 'nullable|string|max:500',
        ]);

        if ($user->id === auth()->id()) {
            return back()->with('error', 'O\'zingizni bloklab bo\'lmaydi!');
        }

        $user->ban($req->duration, $req->reason);

        return back()->with('success', "'{$user->name}' muvaffaqiyatli bloklandi (" . $user->ban_remaining . ")! 🚫");
    })->name('users.ban');

    // Foydalanuvchini bandan chiqarish
    Route::post('/users/{user}/unban', function (\App\Models\User $user) {
        $user->unban();
        return back()->with('success', "'{$user->name}' blokdan chiqarildi! ✅");
    })->name('users.unban');

    // Shikoyatlar (Reports) boshqaruvi
    Route::get('/reports', function () {
        $reports = \App\Models\Report::with(['reporter', 'reportedUser'])->latest()->paginate(20);
        return view('admin.reports.index', compact('reports'));
    })->name('reports.index');

    Route::post('/reports/{report}/resolve', function (\App\Models\Report $report) {
        $report->update(['status' => 'resolved']);
        return back()->with('success', 'Shikoyat ko\'rib chiqildi deb belgilandi. ✅');
    })->name('reports.resolve');

    Route::delete('/reports/{report}', function (\App\Models\Report $report) {
        $report->delete();
        return back()->with('success', 'Shikoyat o\'chirildi.');
    })->name('reports.destroy');

    // Kitoblar
    Route::get('/books', function () {
        $books = \App\Models\Book::latest()->paginate(20);
        return view('admin.books.index', compact('books'));
    })->name('books.index');

    // Forma @method('PATCH') yuboradi (musiqa toggle'i bilan izchil) — shuning uchun PATCH.
    Route::patch('/books/{book}/toggle', function (\App\Models\Book $book) {
        $book->update(['is_active' => !$book->is_active]);
        return back()->with('success', 'Kitob holati yangilandi!');
    })->name('books.toggle');

    // Statistika
    Route::get('/stats', function () {
        $stats = [
            'total_users'    => \App\Models\User::count(),
            'total_students' => \App\Models\User::where('role', 'student')->count(),
            'total_teachers' => \App\Models\User::where('role', 'teacher')->count(),
            'total_books'    => \App\Models\Book::count(),
            'active_books'   => \App\Models\Book::where('is_active', true)->count(),
        ];
        return view('admin.stats', compact('stats'));
    })->name('stats');

    // Sozlamalar va Tizim diagnostikasi
    Route::get('/settings', [\App\Http\Controllers\Admin\SettingsController::class, 'index'])->name('settings');
    Route::match(['post', 'put'], '/settings/update', [\App\Http\Controllers\Admin\SettingsController::class, 'update'])->name('settings.update');
    Route::post('/settings/cache', [\App\Http\Controllers\Admin\SettingsController::class, 'cache'])->name('settings.cache');
    Route::post('/settings/maintenance', [\App\Http\Controllers\Admin\SettingsController::class, 'maintenance'])->name('settings.maintenance');
    Route::post('/settings/storage-link', [\App\Http\Controllers\Admin\SettingsController::class, 'storageLink'])->name('settings.storage-link');
    Route::post('/settings/test-email', [\App\Http\Controllers\Admin\SettingsController::class, 'testEmail'])->name('settings.test-email');

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

    // ── Kitob Fon Musiqalari (Ambient Audio) ──
    Route::get('/books/{book}/music', [\App\Http\Controllers\Admin\BookMusicController::class, 'index'])->name('books.music.index');
    Route::post('/books/{book}/music', [\App\Http\Controllers\Admin\BookMusicController::class, 'store'])->name('books.music.store');
    Route::patch('/books/{book}/music/{music}/toggle', [\App\Http\Controllers\Admin\BookMusicController::class, 'toggle'])->name('books.music.toggle');
    Route::delete('/books/{book}/music/{music}', [\App\Http\Controllers\Admin\BookMusicController::class, 'destroy'])->name('books.music.destroy');

    // ── Kitob Audiolari ──
    Route::get('/audios', [\App\Http\Controllers\Admin\AudioController::class, 'index'])->name('audios.index');
    Route::get('/audios/create', [\App\Http\Controllers\Admin\AudioController::class, 'create'])->name('audios.create');
    Route::post('/audios', [\App\Http\Controllers\Admin\AudioController::class, 'store'])->name('audios.store');
    Route::delete('/audios/{audio}', [\App\Http\Controllers\Admin\AudioController::class, 'destroy'])->name('audios.destroy');

    // ── Kitob Videolari ──
    Route::get('/videos', [\App\Http\Controllers\Admin\VideoController::class, 'index'])->name('videos.index');
    Route::get('/videos/create', [\App\Http\Controllers\Admin\VideoController::class, 'create'])->name('videos.create');
    Route::post('/videos', [\App\Http\Controllers\Admin\VideoController::class, 'store'])->name('videos.store');
    Route::get('/videos/{video}/edit', [\App\Http\Controllers\Admin\VideoController::class, 'edit'])->name('videos.edit');
    Route::put('/videos/{video}', [\App\Http\Controllers\Admin\VideoController::class, 'update'])->name('videos.update');
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

