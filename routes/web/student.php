<?php

use Illuminate\Support\Facades\Route;
use App\Http\Livewire\Dashboard;
use App\Http\Livewire\Auth\OnboardingWizard;
use App\Http\Controllers\AiChatRedirectController;
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
use App\Http\Livewire\Shop\ShopPage;

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
});

/*
|--------------------------------------------------------------------------
| SHARED AUTH ROUTES — barcha login bo'lgan foydalanuvchilar (student, teacher, admin)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    // Shop (Tanga Do'koni)
    Route::get('/shop', ShopPage::class)->name('shop.index');
    // Karta orqali tanga/ball sotib olish (chek + admin tasdig'i)
    if (class_exists(\App\Http\Livewire\Shop\TopupPage::class)) {
        Route::get('/shop/topup', \App\Http\Livewire\Shop\TopupPage::class)->name('topup.index');
    }

    // Kitobni "Keyinroq o'qiyman" ro'yxatiga qo'shish / o'chirish (Toggle)
    Route::post('/books/{book}/toggle-save', function (\App\Models\Book $book) {
        $user = auth()->user();
        $existing = \App\Models\UserSavedBook::where('user_id', $user->id)
            ->where('book_id', $book->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $isSaved = false;
            $message = "Kitob \"Keyinroq o'qiyman\" ro'yxatidan olib tashlandi.";
        } else {
            \App\Models\UserSavedBook::create([
                'user_id' => $user->id,
                'book_id' => $book->id,
            ]);
            $isSaved = true;
            $message = "Kitob \"Keyinroq o'qiyman\" ro'yxatiga saqlandi!";
        }

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success'     => true,
                'is_saved'    => $isSaved,
                'message'     => $message,
                'total_saved' => \App\Models\UserSavedBook::where('book_id', $book->id)->count(),
            ]);
        }

        return back()->with('success', $message);
    })->name('books.toggle-save');

    // Kitobga izoh qoldirish
    Route::post('/books/{book}/comments', [\App\Http\Controllers\BookCommentController::class, 'store'])
        ->name('books.comments.store');

    // Izohni o'chirish
    Route::delete('/book-comments/{comment}', [\App\Http\Controllers\BookCommentController::class, 'destroy'])
        ->name('books.comments.destroy');

    // Izohga like bosish / qaytarish
    Route::post('/book-comments/{comment}/like', [\App\Http\Controllers\BookCommentController::class, 'toggleLike'])
        ->name('books.comments.like');

    // Kitob muhokamasiga yangi fikr / taqriz qo'shish
    Route::post('/books/{book}/discussions', function (\Illuminate\Http\Request $request, \App\Models\Book $book) {
        $validated = $request->validate([
            'title' => 'required|string|max:160',
            'body'  => 'required|string|max:5000',
        ]);

        \App\Models\Discussion::create([
            'book_id' => $book->id,
            'user_id' => auth()->id(),
            'title'   => $validated['title'],
            'body'    => $validated['body'],
            'views'   => 0,
        ]);

        return back()->with('success', 'Fikr va tahlilingiz muvaffaqiyatli chop etildi!');
    })->name('books.discussions.store');

    // Kitobni BRAUZERDA onlayn o'qish (PDF embed)
    Route::get('/books/{book}/read-pdf', [\App\Http\Controllers\BookPdfController::class, 'read'])
        ->name('books.pdf.read');

    Route::get('/books/{book}/read/{chapter}', function ($book, $chapter) {
        $book    = \App\Models\Book::findOrFail($book);
        abort_unless($book->isViewableBy(request()->user()), 404);
        $chapter = \App\Models\BookChapter::where('book_id', $book->id)->findOrFail($chapter);
        $user = request()->user();
        $bookmarks = $user->bookmarks()
            ->where('book_id', $book->id)
            ->where('chapter_id', $chapter->id)
            ->orderBy('position')
            ->get(['id', 'position', 'note']);
        $notes = $user->notes()
            ->where('book_id', $book->id)
            ->where('chapter_id', $chapter->id)
            ->latest()
            ->take(20)
            ->get(['id', 'position', 'selected_text', 'color', 'note_text', 'created_at'])
            ->map(fn ($note) => [
                'id' => $note->id,
                'position' => $note->position,
                'selected_text' => $note->selected_text,
                'color' => $note->color ?? 'amber',
                'note_text' => $note->note_text,
                'created_at' => $note->created_at?->format('d.m.Y H:i'),
            ]);

        return view('pages.reader', compact('book', 'chapter', 'bookmarks', 'notes'));
    })->name('reader.show');

    Route::post('/reader/bookmarks', [\App\Http\Controllers\ReaderAnnotationController::class, 'storeBookmark'])
        ->name('reader.bookmarks.store');
    Route::delete('/reader/bookmarks/{bookmark}', [\App\Http\Controllers\ReaderAnnotationController::class, 'destroyBookmark'])
        ->name('reader.bookmarks.destroy');
    Route::post('/reader/notes', [\App\Http\Controllers\ReaderAnnotationController::class, 'storeNote'])
        ->name('reader.notes.store');

    Route::post('/books/{book}/pdf-annotations', [\App\Http\Controllers\PdfAnnotationController::class, 'store'])
        ->name('books.pdf.annotations.store');
    Route::delete('/pdf-annotations/{pdfAnnotation}', [\App\Http\Controllers\PdfAnnotationController::class, 'destroy'])
        ->name('books.pdf.annotations.destroy');

    // reader alias route
    Route::get('/reader/{book}', function ($bookId) {
        $book = \App\Models\Book::findOrFail($bookId);
        abort_unless($book->isViewableBy(request()->user()), 404);
        if ($book->pdf_path) {
            return redirect()->route('books.flipbook', $book->id);
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
        abort_unless($book->isViewableBy(request()->user()), 404);
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
    Route::post('/settings/sound-preferences', [\App\Http\Controllers\UserSoundPreferenceController::class, 'update'])->name('settings.sound.update');
    Route::get('/notifications', NotificationList::class)->name('notifications');

    // Kitobni PDF sifatida yuklab olish — barcha login bo'lgan foydalanuvchilar uchun
    Route::get('/books/{book}/pdf', [\App\Http\Controllers\BookPdfController::class, 'download'])
        ->name('books.pdf');

    Route::get('/books/{book}/pdf-stream', [\App\Http\Controllers\BookPdfController::class, 'stream'])
        ->name('books.pdf.stream');

    Route::get('/books/{book}/flipbook', function (\App\Models\Book $book) {
        abort_unless($book->pdf_path, 404);
        $pdfAnnotations = request()->user()->pdfAnnotations()
            ->where('book_id', $book->id)
            ->latest()
            ->take(100)
            ->get(['id', 'page_number', 'kind', 'label', 'note_text', 'color', 'created_at'])
            ->map(fn ($annotation) => [
                'id' => $annotation->id,
                'page_number' => $annotation->page_number,
                'kind' => $annotation->kind,
                'label' => $annotation->label,
                'note_text' => $annotation->note_text,
                'color' => $annotation->color,
                'created_at' => $annotation->created_at?->format('d.m.Y H:i'),
            ]);

        return view('pages.book-flipbook', compact('book', 'pdfAnnotations'));
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
        $minutes = 1; // Har bir 60 soniyalik faol heartbeat uchun qat'iy 1 daqiqa
        $today = now('Asia/Tashkent')->toDateString();

        // Anti-cheat: Bir nechta tab ochib yoki fokusdan tashqari tez-tez so'rov yuborishni bloklash (kamida 52 soniya oraliq)
        $cooldownKey = 'reading_heartbeat_lock_' . $user->id;
        // Cache::add atomar: bir vaqtda kelgan bir nechta so'rovdan faqat bittasi ball oladi
        if (!\Illuminate\Support\Facades\Cache::add($cooldownKey, true, now()->addSeconds(52))) {
            $activity = \App\Models\DailyActivity::getTodayActivity($user->id, $today);
            $streak = \App\Models\UserStreak::firstOrCreate(['user_id' => $user->id]);
            return response()->json([
                'success'             => true,
                'minutes_added'       => 0,
                'points_added'        => 0,
                'coins_added'         => 0,
                'total_minutes_today' => (int) $activity->minutes_read,
                'total_minutes_all'   => (int) $user->total_reading_minutes,
                'total_points'        => (int) $user->total_points,
                'coin_balance'        => (int) $user->coin_balance,
                'streak'              => (int) $streak->current_streak,
                'daily_goal_reached'  => ((int) $activity->minutes_read) >= 60,
                'daily_goal_claimed'  => (bool) ($activity->hourly_bonus_claimed ?? false),
            ]);
        }

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

        $activity = \App\Models\DailyActivity::getTodayActivity($user->id, $today);

        $prevMinutes = (int) $activity->minutes_read;
        $newMinutes = $prevMinutes + $minutes;
        $activity->increment('minutes_read', $minutes);

        $streakMinMinutes = (int) setting('streak_minimum_minutes', 1);
        if ($newMinutes >= $streakMinMinutes || $minutes >= 1) {
            $streakService = app(\App\Services\Gamification\StreakService::class);
            $streak = $streakService->recordActivity($user);
        } else {
            $streak = \App\Models\UserStreak::firstOrCreate(['user_id' => $user->id]);
        }

        // Har 1 daqiqa uchun: sozlamalardagi dinamik ball va tangalar beriladi
        $pointsPerMinute = (int) setting('reading_points_per_minute', 1);
        $coinsPerMinute  = (int) setting('reading_coins_per_minute', 1);
        $boosterMultiplier = app(\App\Services\Shop\ShopService::class)->getActiveCoinBoosterMultiplier($user);
        $pointsToAward   = $minutes * $pointsPerMinute;
        $coinsToAward    = (int) round($minutes * $coinsPerMinute * $boosterMultiplier);

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

        // Kunlik 1 soatlik (60 daqiqa) mutolaa super bonusi haqida xabardor qilish
        $dailyGoalReached = $newMinutes >= 60;
        $dailyGoalClaimed = (bool) ($activity->hourly_bonus_claimed ?? false);

        if ($dailyGoalReached && !$dailyGoalClaimed) {
            $alreadyNotified = $user->notifications()
                ->whereDate('created_at', $today)
                ->where('data->type', 'daily_reading_goal')
                ->exists();

            if (!$alreadyNotified) {
                \App\Services\NotifyUser::send(
                    $user,
                    'daily_reading_goal',
                    'Super Bonus tayyor! 🏆',
                    "Siz bugun 1 soat (60 daqiqa) mutolaa qildingiz! Bildirishnomalar bo'limida +200 ball va +40 tangani qabul qilib oling!",
                    '🎁',
                    '/notifications'
                );
            }
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
            'daily_goal_reached'   => $dailyGoalReached,
            'daily_goal_claimed'   => $dailyGoalClaimed,
        ]);
    })->name('reading.heartbeat');

    // Kunlik 1 soatlik super mutolaa bonusini olish API (200 ball + 40 tanga)
    Route::post('/api/reading/claim-hourly-bonus', function () {
        $user = auth()->user();
        $today = now('Asia/Tashkent')->toDateString();
        $activity = \App\Models\DailyActivity::getTodayActivity($user->id, $today);

        if ((int) $activity->minutes_read < 60) {
            $remaining = 60 - (int) $activity->minutes_read;
            return response()->json([
                'success' => false,
                'message' => "Kunlik super bonus uchun yana {$remaining} daqiqa mutolaa qilishingiz kerak!"
            ], 422);
        }

        if ($activity->hourly_bonus_claimed) {
            return response()->json([
                'success' => false,
                'message' => "Bugungi 1 soatlik super bonus allaqachon qabul qilingan!"
            ], 422);
        }

        // Atomar belgilash: parallel so'rovlardan faqat bittasi bonusni oladi
        $claimed = \App\Models\DailyActivity::whereKey($activity->getKey())
            ->where(fn ($q) => $q->where('hourly_bonus_claimed', false)->orWhereNull('hourly_bonus_claimed'))
            ->update(['hourly_bonus_claimed' => true]);

        if ($claimed !== 1) {
            return response()->json([
                'success' => false,
                'message' => "Bugungi 1 soatlik super bonus allaqachon qabul qilingan!"
            ], 422);
        }

        $pointsService = app(\App\Services\Gamification\PointsService::class);
        $pointsService->awardPoints($user, 200, 'bonus', "Kunlik 1 soatlik mutolaa super bonusi (+200 ball)");
        $pointsService->awardCoins($user, 40, 'bonus', "Kunlik 1 soatlik mutolaa super bonusi (+40 tanga 🪙)");

        $freshUser = $user->fresh();

        return response()->json([
            'success'      => true,
            'message'      => "Tabriklaymiz! +200 ball va +40 tanga hisobingizga muvaffaqiyatli qo'shildi! 🏆🪙",
            'points_added' => 200,
            'coins_added'  => 40,
            'total_points' => (int) $freshUser->total_points,
            'coin_balance' => (int) $freshUser->coin_balance,
        ]);
    })->name('reading.claim-hourly-bonus');

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


// Alias for profile.edit
Route::middleware(['auth'])->get('/profile-edit', function () {
    return redirect()->route('settings');
})->name('profile.edit');
