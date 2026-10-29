<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| ADMIN PANEL — faqat admin kirishi mumkin
|--------------------------------------------------------------------------
*/
Route::prefix('admin')
    ->middleware(['auth', 'role.admin'])
    ->name('admin.')
    ->group(function () {

    Route::get('/', [\App\Http\Controllers\Admin\AnalyticsController::class, 'dashboard'])->name('dashboard');

    // To'laqonli Analitika bo'limi (controller orqali, closure YO'Q)
    Route::get('/analytics/data/{metric}', [\App\Http\Controllers\Admin\AnalyticsController::class, 'data'])
        ->name('analytics.data');
    Route::get('/analytics/export/{metric}', [\App\Http\Controllers\Admin\AnalyticsController::class, 'export'])
        ->middleware('throttle:10,1')
        ->name('analytics.export');
    Route::get('/analytics/{tab?}', [\App\Http\Controllers\Admin\AnalyticsController::class, 'index'])
        ->name('analytics');

    // Foydalanuvchilar
    Route::get('/users', function () {
        $query = \App\Models\User::with(['roles', 'profile', 'createdGroups'])->latest();
        if ($search = request('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        if ($role = request('role')) {
            $query->where('role', $role);
        }
        if (request('has_group') === 'yes') {
            $query->has('createdGroups');
        } elseif (request('has_group') === 'no') {
            $query->doesntHave('createdGroups');
        }
        if (request('has_theme') === 'yes') {
            $query->whereHas('profile', function ($q) {
                $q->whereNotNull('unlocked_items')->where('unlocked_items', '!=', '[]')->where('unlocked_items', '!=', 'null');
            });
        } elseif (request('has_theme') === 'no') {
            $query->where(function($q) {
                $q->whereDoesntHave('profile')
                  ->orWhereHas('profile', function($pq) {
                      $pq->whereNull('unlocked_items')->orWhere('unlocked_items', '[]')->orWhere('unlocked_items', 'null');
                  });
            });
        }
        $users = $query->paginate(20)->withQueryString();
        return view('admin.users.index', compact('users'));
    })->name('users.index');

    Route::get('/users/{user}/edit', function (\App\Models\User $user) {
        $roles = \Spatie\Permission\Models\Role::all();

        // 1. Leaderboard reytingi va o'rni
        $leaderboardService = app(\App\Services\LeaderboardService::class);
        $rank = $leaderboardService->getUserRank($user);
        $leaderboardRank = $rank ?? (\App\Models\User::where('total_points', '>', 0)->count() + 1);
        $totalUsersCount = \App\Models\User::count();

        // 2. O'qish statistikasi va o'rtacha mutolaa
        $totalReadingMinutes = (int) $user->total_reading_minutes;
        $readingDaysCount = (int) $user->dailyActivities()->where('minutes_read', '>', 0)->count();
        if ($readingDaysCount === 0) {
            $readingDaysCount = (int) $user->readingSessions()->distinct('session_date')->count('session_date');
        }
        $avgDailyMinutes = $readingDaysCount > 0 ? (int) round($totalReadingMinutes / $readingDaysCount) : $totalReadingMinutes;

        $totalSessionsCount = (int) $user->readingSessions()->count();
        $avgSessionMinutes = $totalSessionsCount > 0 ? (int) round($user->readingSessions()->avg('minutes_read') ?: 0) : 0;

        // Kitoblar mutolaa progressi
        $readingProgress = $user->readingProgress()
            ->with(['book' => function ($q) {
                $q->select('id', 'title', 'slug', 'cover_image', 'author');
            }])
            ->orderByDesc('updated_at')
            ->get();
        $booksFinishedCount = $readingProgress->filter(fn ($p) => (float) $p->percent_complete >= 100)->count();
        $booksInProgressCount = $readingProgress->filter(fn ($p) => (float) $p->percent_complete < 100 && (float) $p->percent_complete > 0)->count();

        // Streak ma'lumotlari
        $streak = $user->streak;
        $currentStreak = (int) $user->current_streak;
        $longestStreak = (int) ($streak?->longest_streak ?? 0);
        $freezeCount = (int) ($streak?->freeze_count ?? 0);

        // 3. Do'kon va Inventar
        $userItems = $user->userItems()->with('shopItem')->latest('acquired_at')->get();
        $shopPurchases = $user->shopPurchases()->with('shopItem')->latest('created_at')->take(20)->get();
        $equipment = $user->equipment()->with('shopItem')->get()->keyBy('slot');

        // 4. Moliya va Balans tarixi
        $coinTransactions = $user->coinTransactions()->latest()->take(20)->get();
        $pointTransactions = $user->pointTransactions()->latest()->take(20)->get();
        $totalEarnedCoins = (int) $user->coinTransactions()->where('coins', '>', 0)->sum('coins');
        $totalSpentCoins = abs((int) $user->coinTransactions()->where('coins', '<', 0)->sum('coins'));

        // 5. Testlar va boshqa faolliklar
        $quizAttempts = $user->quizAttempts()
            ->with(['quiz.book'])
            ->latest('completed_at')
            ->latest('id')
            ->take(15)
            ->get();
        $quizPassedCount = (int) $user->quizAttempts()->where('percent', '>=', 70)->count();
        $quizTotalCount = (int) $user->quizAttempts()->count();

        $bookComments = $user->bookComments()
            ->with('book:id,title,slug')
            ->latest()
            ->take(10)
            ->get();

        $aiQuestions = $user->aiChatHistories()
            ->with('book:id,title,slug')
            ->latest()
            ->take(10)
            ->get();

        $liveQuestions = $user->liveQuestions()
            ->with('liveEvent:id,title')
            ->latest()
            ->take(10)
            ->get();

        $userNotesCount = (int) $user->notes()->count();
        $userBookmarksCount = (int) $user->bookmarks()->count();
        $userQuotesCount = (int) $user->userQuotes()->count();

        return view('admin.users.edit', compact(
            'user',
            'roles',
            'leaderboardRank',
            'totalUsersCount',
            'totalReadingMinutes',
            'readingDaysCount',
            'avgDailyMinutes',
            'totalSessionsCount',
            'avgSessionMinutes',
            'readingProgress',
            'booksFinishedCount',
            'booksInProgressCount',
            'streak',
            'currentStreak',
            'longestStreak',
            'freezeCount',
            'userItems',
            'shopPurchases',
            'equipment',
            'coinTransactions',
            'pointTransactions',
            'totalEarnedCoins',
            'totalSpentCoins',
            'quizAttempts',
            'quizPassedCount',
            'quizTotalCount',
            'bookComments',
            'aiQuestions',
            'liveQuestions',
            'userNotesCount',
            'userBookmarksCount',
            'userQuotesCount'
        ));
    })->name('users.edit');

    // Admin tezkor balans to'ldirish (tekin tanga va ball)
    Route::post('/balance/topup', [\App\Http\Controllers\Admin\AdminBalanceController::class, 'topup'])
        ->name('balance.topup');

    Route::put('/users/{user}', function (\Illuminate\Http\Request $req, \App\Models\User $user) {
        $req->validate([
            'role'   => 'nullable|string|exists:roles,name',
            'points' => 'nullable|integer|min:0',
            'coins'  => 'nullable|integer|min:0',
        ]);

        // Faqat oddiy foydalanuvchilarning roli o'zgartiriladi, admin roli saqlanadi
        if (!$user->isAdmin() && !$user->hasRole('admin') && $req->filled('role')) {
            $user->syncRoles([$req->role]);
        }

        $pointsService = app(\App\Services\Gamification\PointsService::class);

        // Ball (total_points): forma hozirgi balansni yuboradi — farq (delta) qo'shiladi/ayiriladi
        $currentPoints = (int) $user->total_points;
        $newPoints     = (int) $req->input('points', $currentPoints);
        $pointsDelta   = $newPoints - $currentPoints;

        if ($pointsDelta > 0) {
            $pointsService->awardPoints($user, $pointsDelta, 'admin', "Admin tomonidan ball qo'shildi");
        } elseif ($pointsDelta < 0) {
            $user->decrement('total_points', abs($pointsDelta));
            \App\Models\PointTransaction::create([
                'user_id'     => $user->id,
                'points'      => $pointsDelta,
                'source'      => 'admin',
                'description' => 'Admin tomonidan ball ayirildi',
            ]);
        }

        // Tanga (coin_balance): xuddi shu delta logikasi
        $currentCoins = (int) $user->coin_balance;
        $newCoins     = (int) $req->input('coins', $currentCoins);
        $coinsDelta   = $newCoins - $currentCoins;

        if ($coinsDelta > 0) {
            $pointsService->awardCoins($user, $coinsDelta, 'admin', 'Admin tomonidan tanga berildi');
        } elseif ($coinsDelta < 0) {
            $user->decrement('coin_balance', abs($coinsDelta));
            \App\Models\CoinTransaction::create([
                'user_id'     => $user->id,
                'coins'       => $coinsDelta,
                'source'      => 'admin',
                'description' => 'Admin tomonidan tanga ayirildi',
            ]);
        }

        $summary = "Ma'lumotlar saqlandi!";
        if ($pointsDelta !== 0) {
            $summary .= ' Ball: ' . ($pointsDelta > 0 ? '+' : '') . $pointsDelta;
        }
        if ($coinsDelta !== 0) {
            $summary .= ' Tanga: ' . ($coinsDelta > 0 ? '+' : '') . $coinsDelta;
        }

        return redirect()->route('admin.users.edit', $user)->with('success', $summary);
    })->name('users.update');

    Route::delete('/users/{user}', function (\App\Models\User $user) {
        // Adminlar (jumladan o'zi) o'chirilmaydi
        if ($user->isAdmin() || $user->role === 'admin') {
            return redirect()->route('admin.users.index')->with('error', "Admin foydalanuvchini o'chirish mumkin emas. 🛡️");
        }
        $user->forceDelete();
        return redirect()->route('admin.users.index')->with('success', 'Foydalanuvchi butunlay o\'chirildi!');
    })->name('users.destroy');

    // Foydalanuvchini ban qilish
    Route::post('/users/{user}/ban', function (\Illuminate\Http\Request $req, \App\Models\User $user) {
        $req->validate([
            'duration' => 'required|in:1_hour,1_day,1_week,1_month,permanent',
            'reason'   => 'nullable|string|max:500',
        ]);

        if ($user->isAdmin() || $user->role === 'admin' || $user->id === auth()->id()) {
            return back()->with('error', "Admin foydalanuvchini (va o'zingizni) bloklash mumkin emas! 🛡️");
        }

        $user->ban($req->duration, $req->reason);

        return back()->with('success', "'{$user->name}' muvaffaqiyatli bloklandi (" . $user->ban_remaining . ")! 🚫");
    })->name('users.ban');

    // Foydalanuvchini bandan chiqarish
    Route::post('/users/{user}/unban', function (\App\Models\User $user) {
        $user->unban();
        return back()->with('success', "'{$user->name}' blokdan chiqarildi! ✅");
    })->name('users.unban');

    // Foydalanuvchiga Telegram Bot nomidan shaxsiy xabar / e'lon yuborish
    Route::post('/users/{user}/telegram-message', [\App\Http\Controllers\Admin\TelegramAdminController::class, 'sendToUser'])
        ->name('users.telegram-message');

    // Shikoyatlar (Reports) boshqaruvi
    Route::get('/reports', function (\Illuminate\Http\Request $request) {
        $filter = (string) $request->query('filter', 'all');

        $bannedAppealsQuery = fn ($q) => $q->where(function ($sub) {
            $sub->where('source', 'Ban Apellyatsiyasi')
                ->orWhereHas('reporter', fn ($u) => $u->where('is_banned', true))
                ->orWhere(function ($s2) {
                    $s2->where('source', 'Aloqa bo\'limi')
                        ->whereHas('reportedUser', fn ($u) => $u->where('is_banned', true));
                });
        });

        $bannedAppealsCount = \App\Models\Report::tap($bannedAppealsQuery)->count();

        $query = \App\Models\Report::with(['reporter', 'reportedUser']);

        if ($filter === 'banned') {
            $bannedAppealsQuery($query);
        } elseif ($filter === 'reports') {
            $query->whereNotIn('source', ['Ban Apellyatsiyasi', 'Aloqa bo\'limi']);
        } elseif ($filter === 'contact') {
            $query->where('source', 'Aloqa bo\'limi');
        }

        $reports = $query->latest()->paginate(20)->withQueryString();

        return view('admin.reports.index', compact('reports', 'filter', 'bannedAppealsCount'));
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
        $books = \App\Models\Book::withCount('chunks')->latest()->paginate(20);
        return view('admin.books.index', compact('books'));
    })->name('books.index');

    // Forma @method('PATCH') yuboradi (musiqa toggle'i bilan izchil) — shuning uchun PATCH.
    Route::patch('/books/{book}/toggle', function (\App\Models\Book $book) {
        $book->update(['is_active' => !$book->is_active]);
        return back()->with('success', 'Kitob holati yangilandi!');
    })->name('books.toggle');

    // Statistika
    Route::get('/stats', [\App\Http\Controllers\Admin\AnalyticsController::class, 'stats'])->name('stats');

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

        $book = \App\Models\Book::create([
            'title'        => $req->title,
            'author'       => $req->author,
            'description'  => $req->description,
            'genre'        => $req->genre,
            'slug'         => \Illuminate\Support\Str::slug($req->title) . '-' . uniqid(),
            'week_number'  => $req->week_number ?? ((\App\Models\Book::max('week_number') ?? 0) + 1),
            'pdf_path'     => $pdfPath,
            'cover_image'  => $coverPath,
            'created_by'   => auth()->id(),
            'published_at' => now(),
            'is_active'    => $req->has('is_active'),
        ]);

        // Telegram kanalga va bot foydalanuvchilariga avtomatik e'lon yuborish
        if ($book->is_active && $req->boolean('telegram_broadcast', true)) {
            try {
                $announcer = new \App\Services\Telegram\TelegramAnnouncementService();
                $announcer->broadcastNewBook($book);
            } catch (\Throwable $e) {}
        }

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

    Route::post('/books/{book}/telegram-broadcast', function (\App\Models\Book $book) {
        try {
            $announcer = new \App\Services\Telegram\TelegramAnnouncementService();
            $res = $announcer->broadcastNewBook($book);

            if (!empty($res['reason']) && $res['reason'] === 'bot_token_empty') {
                return back()->with('warning', "Telegram Bot Token sozlanmagan (.env faylida TELEGRAM_BOT_TOKEN kiritilishi lozim).");
            }

            $userMsg = ($res['users_count'] ?? 0) > 0 ? " ({$res['users_count']} ta bot foydalanuvchisiga yuborildi)" : '';
            $channelMsg = !empty($res['channel']) ? " va Telegram kanalga" : "";

            return back()->with('success', "«{$book->title}» kitobi haqida e'lon Telegramga muvaffaqiyatli yuborildi{$channelMsg}!{$userMsg}");
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Telegram broadcast xatosi: " . $e->getMessage(), [
                'book_id' => $book->id,
                'trace'   => $e->getTraceAsString(),
            ]);
            return back()->with('error', "Telegramga e'lon yuborishda xatolik yuz berdi: " . $e->getMessage());
        }
    })->name('books.telegram-broadcast');

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

        $newPdfUploaded = false;
        if ($req->hasFile('pdf_file')) {
            $updateData['pdf_path'] = $req->file('pdf_file')->store('books/pdf', 'public');
            $updateData['ai_index_status'] = 'stale';
            $newPdfUploaded = true;
        }

        if ($req->hasFile('cover_image')) {
            $updateData['cover_image'] = $req->file('cover_image')->store('books/covers', 'public');
        }

        $book->update($updateData);

        if ($newPdfUploaded) {
            app(\App\Services\AI\BookAssistant\BookIndexer::class)->clearBookIndex($book);
            $book->update([
                'ai_index_status' => 'none',
                'ai_text_quality' => 0.0,
                'ai_enabled'      => false,
            ]);
        }

        return redirect()->route('admin.books.index')->with('success', 'Kitob ma\'lumotlari va fayllari muvaffaqiyatli yangilandi!');
    })->name('books.update');

    Route::delete('/books/{book}', function (\App\Models\Book $book) {
        $book->delete();
        return redirect()->route('admin.books.index')->with('success', 'Kitob o\'chirildi!');
    })->name('books.destroy');

    // ── Kitob AI Yordamchi (RAG Indekslash va Sozlamalar) ──
    Route::post('/books/{book}/ai/settings', [\App\Http\Controllers\Admin\BookAiController::class, 'updateSettings'])->name('books.ai.settings');
    Route::post('/books/{book}/ai/index', [\App\Http\Controllers\Admin\BookAiController::class, 'indexBook'])->name('books.ai.index');
    Route::get('/books/{book}/ai/status', [\App\Http\Controllers\Admin\BookAiController::class, 'status'])->name('books.ai.status');
    Route::match(['post', 'delete'], '/books/{book}/ai/clear', [\App\Http\Controllers\Admin\BookAiController::class, 'clearIndex'])->name('books.ai.clear');

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
    Route::get('/quizzes/{quiz}/edit', [\App\Http\Controllers\Admin\QuizController::class, 'edit'])->name('quizzes.edit');
    Route::put('/quizzes/{quiz}', [\App\Http\Controllers\Admin\QuizController::class, 'update'])->name('quizzes.update');
    Route::get('/quizzes/{quiz}', [\App\Http\Controllers\Admin\QuizController::class, 'show'])->name('quizzes.show');
    Route::delete('/quizzes/{quiz}', [\App\Http\Controllers\Admin\QuizController::class, 'destroy'])->name('quizzes.destroy');
    Route::get('/quiz-reviews', [\App\Http\Controllers\QuizReviewController::class, 'index'])->name('quiz-reviews.index');
    Route::get('/quiz-reviews/{attempt}', [\App\Http\Controllers\QuizReviewController::class, 'show'])->name('quiz-reviews.show');
    Route::post('/quiz-reviews/{attempt}', [\App\Http\Controllers\QuizReviewController::class, 'update'])->name('quiz-reviews.update');

    // ── FAQ Savol-Javoblar Boshqaruvi ──
    Route::get('/faqs', [\App\Http\Controllers\Admin\FaqController::class, 'index'])->name('faqs.index');
    Route::get('/faqs/create', [\App\Http\Controllers\Admin\FaqController::class, 'create'])->name('faqs.create');
    Route::post('/faqs', [\App\Http\Controllers\Admin\FaqController::class, 'store'])->name('faqs.store');
    Route::get('/faqs/{faq}/edit', [\App\Http\Controllers\Admin\FaqController::class, 'edit'])->name('faqs.edit');
    Route::put('/faqs/{faq}', [\App\Http\Controllers\Admin\FaqController::class, 'update'])->name('faqs.update');
    Route::patch('/faqs/{faq}/toggle', [\App\Http\Controllers\Admin\FaqController::class, 'toggle'])->name('faqs.toggle');
    Route::delete('/faqs/{faq}', [\App\Http\Controllers\Admin\FaqController::class, 'destroy'])->name('faqs.destroy');

    // ── Do'kon Boshqaruvi (Admin Shop Management) ──
    Route::get('/shop', [\App\Http\Controllers\Admin\ShopController::class, 'index'])->name('shop.index');
    Route::get('/shop/items/create', [\App\Http\Controllers\Admin\ShopController::class, 'create'])->name('shop.items.create');
    Route::post('/shop/items', [\App\Http\Controllers\Admin\ShopController::class, 'store'])->name('shop.items.store');
    Route::get('/shop/items/{item}/edit', [\App\Http\Controllers\Admin\ShopController::class, 'edit'])->name('shop.items.edit');
    Route::put('/shop/items/{item}', [\App\Http\Controllers\Admin\ShopController::class, 'update'])->name('shop.items.update');
    Route::post('/shop/items/{item}/toggle', [\App\Http\Controllers\Admin\ShopController::class, 'toggleActive'])->name('shop.items.toggle');
    Route::post('/shop/settings', [\App\Http\Controllers\Admin\ShopController::class, 'updateSettings'])->name('shop.settings.update');
    Route::post('/shop/refund', [\App\Http\Controllers\Admin\ShopController::class, 'refundUserItem'])->name('shop.refund');

    // ── Telegram Bot & Stars Boshqaruvi (Admin Telegram Management) ──
    Route::get('/telegram', [\App\Http\Controllers\Admin\TelegramAdminController::class, 'index'])->name('telegram.index');
    Route::post('/telegram/broadcast', [\App\Http\Controllers\Admin\TelegramAdminController::class, 'broadcast'])->name('telegram.broadcast');
    Route::post('/telegram/reconcile', [\App\Http\Controllers\Admin\TelegramAdminController::class, 'reconcile'])->name('telegram.reconcile');
    Route::post('/telegram/payments/{payment}/refund', [\App\Http\Controllers\Admin\TelegramAdminController::class, 'refund'])->name('telegram.refund');
});
