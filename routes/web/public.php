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

// Lightweight connectivity probe; deliberately excludes the web session and its middleware.
Route::get('/ping', function () {
    return response('', 204)->header('Cache-Control', 'no-store, max-age=0');
})->withoutMiddleware([
    \App\Http\Middleware\EncryptCookies::class,
    \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
    \Illuminate\Session\Middleware\StartSession::class,
    \Illuminate\View\Middleware\ShareErrorsFromSession::class,
    \App\Http\Middleware\VerifyCsrfToken::class,
    \Illuminate\Routing\Middleware\SubstituteBindings::class,
    \App\Http\Middleware\SetLocale::class,
    \App\Http\Middleware\TrackDailyStreak::class,
    \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
])->name('pwa.ping');

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
    $featuredBook = \App\Models\Book::with('audios')->where('is_active', true)->orderBy('week_number', 'desc')->first();
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
})->middleware('onboarding.complete')->name('home');

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
        'name'    => 'nullable|string|max:100',
        'phone'   => 'nullable|string|max:150',
        'email'   => 'nullable|string|max:150',
        'subject' => 'nullable|string|max:250',
        'message' => 'required|string|max:4000',
    ]);

    if ($req->input('is_report') == '1' || $req->filled('reported_user_id')) {
        $reportedUserId = $req->input('reported_user_id');
        if ($reportedUserId) {
            $reportedUser = \App\Models\User::find($reportedUserId);
            if ($reportedUser && $reportedUser->isAdmin()) {
                return redirect()->route('contact')->with('error', "Admin foydalanuvchilar ustidan shikoyat yuborish mumkin emas. 🛡️");
            }
        }

        \App\Models\Report::create([
            'reporter_id'      => auth()->id(),
            'reported_user_id' => $reportedUserId ?: null,
            'source'           => $req->input('source', 'Umumiy chat'),
            'message_content'  => $req->input('message'),
            'link'             => $req->input('link'),
            'reason'           => $req->input('subject', 'Haqorat / Nojo\'ya xatti-harakat'),
            'status'           => 'pending',
        ]);

        return redirect()->route('contact')->with('success', '🚩 Qoidabuzarlik bo\'yicha shikoyatingiz ma\'muriyatga yetkazildi! Moderatorlar tez orada tekshirib, tegishli chora (ban) ko\'rishadi.');
    }

    // Yuboruvchi foydalanuvchini aniqlash (avtorizatsiya qilingan bo'lsa, yoki appeal_user_id / telefon orqali)
    $senderUser = auth()->user();

    if (!$senderUser && $req->filled('appeal_user_id')) {
        $senderUser = \App\Models\User::find($req->input('appeal_user_id'));
    }

    $rawContact = trim((string) ($req->input('phone') ?? $req->input('email') ?? ''));

    if (!$senderUser && $rawContact !== '') {
        $cleanUsername = ltrim($rawContact, '@');
        $normalizedPhone = \App\Support\PhoneNumber::normalize($rawContact);

        $senderUser = \App\Models\User::where('username', strtolower($cleanUsername))
            ->when($normalizedPhone, fn ($q) => $q->orWhere('phone', $normalizedPhone))
            ->orWhere('email', $rawContact)
            ->first();
    }

    // Agar foydalanuvchi tizimda aniqlangan bo'lsa, ism va telefon raqamni o'zgartirib bo'lmaydigan qilib bazadan olamiz
    $finalName = $senderUser ? $senderUser->name : (trim((string) $req->input('name')) ?: 'Mehmon');
    $finalPhone = $senderUser
        ? ($senderUser->phone ?: ('@' . $senderUser->username))
        : ($rawContact ?: 'Ko\'rsatilmagan');

    $isBannedSender = ($senderUser && $senderUser->isBanned()) || $req->input('is_appeal') == '1';

    if ($isBannedSender) {
        $headerInfo = $senderUser
            ? "👤 Foydalanuvchi: {$finalName} (@{$senderUser->username}, ID: #{$senderUser->id})\n"
              . "📞 Telefon raqam: {$finalPhone}\n"
              . "🚫 Ban holati: " . ($senderUser->isBanned() ? $senderUser->ban_remaining : 'Faol emas') . "\n"
              . "⚠️ Ban sababi: " . ($senderUser->ban_reason ?? 'Ko\'rsatilmagan') . "\n"
            : "👤 Yuboruvchi: {$finalName}\n📞 Telefon raqam: {$finalPhone}\n";

        $fullContent = "[⚖️ BAN BO'YICHA APELLYATSIYA / MUROJAAT]\n"
            . $headerInfo
            . ($req->filled('subject') ? "📌 Mavzu: " . $req->input('subject') . "\n" : "")
            . "\n💬 Murojaat matni:\n" . $req->input('message');

        \App\Models\Report::create([
            'reporter_id'      => $senderUser?->id ?? auth()->id(),
            'reported_user_id' => $senderUser?->id ?? auth()->id(),
            'source'           => 'Ban Apellyatsiyasi',
            'message_content'  => $fullContent,
            'link'             => null,
            'reason'           => $req->input('subject') ?: '⚖️ Noto\'g\'ri ban bo\'yicha apellyatsiya',
            'status'           => 'pending',
        ]);

        return redirect()->route('contact')->with('success', '⚖️ Ban bo\'yicha apellyatsiyangiz (murojaatingiz) ma\'muriyatga yuborildi! Moderatorlar tez orada ko\'rib chiqib, natijasini ma\'lum qilishadi.');
    }

    // Oddiy aloqa xabarini ham ma'muriyat "Aloqa va shikoyatlar" bo'limiga saqlaymiz
    $contactContent = "[✉️ ALOQA BO'LIMIDAN XABAR]\n"
        . "👤 Yuboruvchi: {$finalName}\n"
        . "📞 Telefon raqam: {$finalPhone}\n"
        . ($req->filled('subject') ? "📌 Mavzu: " . $req->input('subject') . "\n" : "")
        . "\n💬 Xabar:\n" . $req->input('message');

    \App\Models\Report::create([
        'reporter_id'      => $senderUser?->id ?? auth()->id(),
        'reported_user_id' => $senderUser?->id ?? auth()->id(),
        'source'           => 'Aloqa bo\'limi',
        'message_content'  => $contactContent,
        'link'             => null,
        'reason'           => $req->input('subject') ?: '✉️ Aloqa xabari',
        'status'           => 'pending',
    ]);

    return back()->with('success', 'Xabaringiz qabul qilindi! Tez orada javob beramiz.');
})->name('contact.send');

// FAQ
Route::get('/faq', function () {
    $customFaqs = collect();
    try {
        if (\Illuminate\Support\Facades\Schema::hasTable('faqs')) {
            $customFaqs = \App\Models\Faq::where('is_active', true)
                ->orderBy('sort_order')
                ->latest('id')
                ->get();
        }
    } catch (\Throwable $e) {
        $customFaqs = collect();
    }

    return view('public.faq', compact('customFaqs'));
})->name('faq');

// Shartlar va maxfiylik
Route::get('/privacy', function () {
    return view('public.privacy');
})->name('privacy');

Route::get('/terms', function () {
    return view('public.terms');
})->name('terms');

Route::redirect('/rules-and-penalties', '/terms');
Route::redirect('/rules', '/terms');

// Public kitoblar katalogi -> Interaktiv katalog sahifasiga yo'naltirish (SEO 301 Permanent Redirect)
Route::get('/books', function (\Illuminate\Http\Request $request) {
    return redirect()->route('books.catalog', $request->all(), 301);
})->name('books.public');

// Route alias for legacy or sitemap compatibility
Route::get('/books/{book}/audio-view', function ($book) {
    return redirect()->route('audio.show', $book);
})->name('books.audio');

// Kitob audio ro'yxati (Global Audio Player uchun ochiq JSON API)
Route::get('/books/{book}/audios-json', function ($bookId) {
    $book = \App\Models\Book::with(['audios.chapter'])->findOrFail($bookId);
    $audios = $book->audios->map(function ($audio) {
        return [
            'id'            => $audio->id,
            'title'         => $audio->title ?: 'Audio qism',
            'duration'      => (int) ($audio->duration ?? 0),
            'file_url'      => $audio->file_url,
            'chapter_id'    => $audio->chapter_id,
            'chapter_title' => $audio->chapter ? $audio->chapter->title : null,
        ];
    });

    return response()->json([
        'book' => [
            'id'          => $book->id,
            'title'       => $book->title,
            'author'      => $book->author,
            'cover_url'   => $book->cover_url,
            'slug'        => $book->slug,
            'share_url'   => route('books.show', $book->slug),
        ],
        'audios' => $audios,
    ]);
})->name('books.audios.json');

// Kitoblar katalogi (login shart emas — hamma ko'ra oladi)
Route::get('/catalog', CatalogPage::class)->name('books.catalog');

// Kitob sahifasi (Public — SEO, OpenGraph va qidiruv botlari uchun ochiq)
Route::get('/books/{slug}', function ($slug) {
    $book = \App\Models\Book::withCount('chunks')
        ->where('slug', $slug)
        ->when(is_numeric($slug), function ($query) use ($slug) {
            $query->orWhere('id', (int) $slug);
        })
        ->first();

    if (!$book) {
        abort(404, 'Kitob topilmadi.');
    }

    $user = auth()->user();
    $isAdmin = $user && ($user->isAdmin() || (method_exists($user, 'hasRole') && $user->hasRole('admin')));

    if (!$book->is_active && !$isAdmin) {
        abort(404, 'Kitob topilmadi yoki vaqtincha nofaol.');
    }

    if ($book->published_at && $book->published_at->isFuture()) {
        $rank = $user ? app(\App\Services\LeaderboardService::class)->getUserRank($user) : null;
        $canEarlyAccess = ($isAdmin || ($rank !== null && $rank <= 5));
        if (!$canEarlyAccess) {
            abort(403, 'Ushbu kitob hali rasman e\'lon qilinmagan. Top 5 kitobxonlar uchun erta kirish imtiyozi mavjud.');
        }
    }

    // Agar raqamli ID orqali kirilgan bo'lsa va kitobda slug mavjud bo'lsa, toza slug URL'ga yo'naltirish
    if (is_numeric($slug) && !empty($book->slug) && (string) $slug !== (string) $book->slug) {
        return redirect()->route('books.show', $book->slug);
    }
    $pdfAnnotations = request()->user() ? request()->user()->pdfAnnotations()
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
        ]) : collect();

    $isSaved = request()->user() ? request()->user()->isBookSaved($book) : false;
    $savedCount = $book->savedByUsers()->count();

    $bookComments = $book->comments()
        ->with(['user', 'replies.user', 'replies.likes', 'likes'])
        ->get();
    $averageRating = $book->averageRating();
    $ratingsCount = $book->ratingsCount();
    $totalCommentsCount = $book->allComments()->count();

    return view('pages.book-detail', compact(
        'book',
        'pdfAnnotations',
        'isSaved',
        'savedCount',
        'bookComments',
        'averageRating',
        'ratingsCount',
        'totalCommentsCount'
    ));
})->name('books.show');

// 1 daqiqalik AI Kitob Konspekti (Public)
Route::get('/books/{book}/ai-summary', [\App\Http\Controllers\BookSummaryController::class, 'show'])
    ->name('books.ai-summary');

// Legacy AI Chat 301 Permanent Redirect -> Katalog sahifasiga yo'naltirish
Route::get('/ai-chat', AiChatRedirectController::class);

// Reyting sahifasi (hamma ko'ra oladi)
Route::get('/leaderboard', LeaderboardPage::class)->name('leaderboard');

// Umumiy Chat (hamma ko'ra oladi, yozish uchun login kerak bo'ladi Livewire tomonida)
Route::get('/chat', GlobalChat::class)->name('chat');
Route::post('/chat/voice', [\App\Http\Controllers\ChatVoiceController::class, 'upload'])->middleware('auth')->name('chat.voice.upload');
Route::post('/buddy-read/{id}/signal', [\App\Http\Controllers\BuddyReadVoiceController::class, 'sendSignal'])->middleware('auth')->name('buddy.voice.signal');
Route::get('/buddy-read/{id}/signals', [\App\Http\Controllers\BuddyReadVoiceController::class, 'pollSignals'])->middleware('auth')->name('buddy.voice.signals');

// Guruhlar (hamma ko'ra oladi)
Route::get('/groups', GroupList::class)->name('groups.index');
Route::get('/groups/{group}', GroupDetail::class)->name('groups.show');

// Jonli efirlar (hamma ko'ra oladi)
Route::get('/live', LiveIndex::class)->name('live.index');
Route::get('/live/{event}', LiveDetail::class)->name('live.show');
Route::post('/live/{event}/signal', [\App\Http\Controllers\LiveSignalController::class, 'send'])->name('live.signal.send');
Route::get('/live/{event}/signals', [\App\Http\Controllers\LiveSignalController::class, 'poll'])->name('live.signal.poll');

// Fallback storage route (Hostingda symlink bo'lmaganda yoki buzilganda rasmlar 404 bo'lmasligi uchun)
Route::get('/storage/{path}', function ($path) {
    $fullPath = storage_path('app/public/' . $path);
    if (! file_exists($fullPath) || is_dir($fullPath)) {
        abort(404);
    }
    return response()->file($fullPath);
})->where('path', '.*')->name('storage.fallback');

// Error sahifalari dizaynini ko'rish (Preview routes)
Route::prefix('errors')->group(function () {
    Route::get('/404', fn() => response()->view('errors.404', [], 404))->name('error.404');
    Route::get('/403', fn() => response()->view('errors.403', [], 403))->name('error.403');
    Route::get('/500', fn() => response()->view('errors.500', [], 500))->name('error.500');
    Route::get('/502', fn() => response()->view('errors.502', [], 502))->name('error.502');
    Route::get('/504', fn() => response()->view('errors.504', [], 504))->name('error.504');
    Route::get('/419', fn() => response()->view('errors.419', [], 419))->name('error.419');
    Route::get('/429', fn() => response()->view('errors.429', [], 429))->name('error.429');
    Route::get('/503', fn() => response()->view('errors.503', [], 503))->name('error.503');
});

// Server / Deploy Setup Endpoint (Terminal / SSH ruxsati bo'lmagan hostinglar uchun)
Route::get('/system/setup', function (\Illuminate\Http\Request $request) {
    $token = (string) $request->query('token', '');
    $secretToken = env('SETUP_TOKEN', 'kitobxon2026');
    $appKey = (string) config('app.key', '');

    if (empty($token) || ($token !== $secretToken && $token !== $appKey)) {
        abort(403, 'Maxsus ruxsat belgisi (token) xato. URL oxiriga ?token=kitobxon2026 qo\'shing.');
    }

    $results = [
        'status'    => 'active',
        'timestamp' => now()->toIso8601String(),
        'php'       => PHP_VERSION,
        'app_key'   => !empty($appKey) ? 'Mavjud (O\'rnatilgan)' : 'BO\'SH',
    ];

    // 1. Directory permissions
    $directories = [
        'storage_views'    => storage_path('framework/views'),
        'storage_cache'    => storage_path('framework/cache'),
        'storage_sessions' => storage_path('framework/sessions'),
        'storage_logs'     => storage_path('logs'),
        'bootstrap_cache'  => base_path('bootstrap/cache'),
    ];
    $dirStatus = [];
    foreach ($directories as $name => $path) {
        if (!is_dir($path)) {
            @mkdir($path, 0777, true);
        }
        $dirStatus[$name] = is_writable($path) ? 'Ruxsat berilgan (OK)' : 'Ruxsat yo\'q (Not writable)';
    }
    $results['storage_directories'] = $dirStatus;

    // 2. Database connection
    try {
        \Illuminate\Support\Facades\DB::connection()->getPdo();
        $dbName = \Illuminate\Support\Facades\DB::connection()->getDatabaseName();
        $results['database'] = [
            'connected'     => true,
            'database_name' => $dbName,
            'driver'        => \Illuminate\Support\Facades\DB::connection()->getDriverName(),
        ];

        // Migrations
        if ($request->has('migrate')) {
            \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
            $results['database']['migration_output'] = \Illuminate\Support\Facades\Artisan::output();
        }

        $tables = \Illuminate\Support\Facades\DB::select('SHOW TABLES');
        $results['database']['tables_count'] = count($tables);

    } catch (\Throwable $e) {
        $results['database'] = [
            'connected' => false,
            'error'     => $e->getMessage(),
            'hint'      => '.env faylida DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD parametrlarini tekshiring.',
        ];
    }

    // 3. Cache clearing
    if ($request->has('clear')) {
        \Illuminate\Support\Facades\Artisan::call('optimize:clear');
        $results['optimize_clear'] = \Illuminate\Support\Facades\Artisan::output();
    }

    // 4. Storage link
    if ($request->has('link')) {
        \Illuminate\Support\Facades\Artisan::call('storage:link');
        $results['storage_link'] = \Illuminate\Support\Facades\Artisan::output();
    }

    // 5. Unpack public_tar.zip
    if ($request->has('unzip_public')) {
        $zipPath = public_path('public_tar.zip');
        if (file_exists($zipPath)) {
            $zip = new \ZipArchive();
            if ($zip->open($zipPath) === true) {
                $zip->extractTo(public_path());
                $zip->close();
                @unlink($zipPath);
                $results['unzip_public'] = 'SUCCESS_EXTRACTED';
            } else {
                $results['unzip_public'] = 'FAILED_OPEN_ZIP';
            }
        } else {
            $results['unzip_public'] = 'ZIP_NOT_FOUND';
        }
    }

    return response()->json($results, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
})->name('system.setup');


// ── SEO: sitemap.xml va robots.txt ──
Route::get('/robots.txt', function () {
    $content = file_exists(public_path('robots.txt'))
        ? file_get_contents(public_path('robots.txt'))
        : "User-agent: *\nAllow: /\nDisallow: /admin\nDisallow: /teacher\nDisallow: /settings\nDisallow: /livewire\nDisallow: /livewire/*\n\nSitemap: " . url('/sitemap.xml');
    return response($content, 200)->header('Content-Type', 'text/plain; charset=UTF-8');
});

Route::get('/sitemap.xml', [\App\Http\Controllers\SitemapController::class, 'index'])->name('sitemap');

