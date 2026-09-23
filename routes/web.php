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
    return view('public.home');
})->name('home');

// Platforma haqida
Route::get('/about', function () {
    return view('public.about');
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
| AUTHENTICATED STUDENT ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'onboarding.complete'])->group(function () {

    // Dashboard — barcha login bo'lgan foydalanuvchilar (role-ga qarab redirect qiladi)
    Route::get('/dashboard', function () {
        $user = auth()->user();
        if ($user && $user->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        }
        if ($user && $user->hasRole('teacher')) {
            return redirect()->route('teacher.dashboard');
        }
        return app(\App\Http\Livewire\Dashboard::class)();
    })->name('dashboard');

    // Books & Reading
    Route::get('/catalog', CatalogPage::class)->name('books.catalog');

    Route::get('/books/{slug}', function ($slug) {
        $book = \App\Models\Book::where('slug', $slug)->firstOrFail();
        return view('pages.book-detail', compact('book'));
    })->name('books.show');

    Route::get('/books/{book}/read/{chapter}', function ($book, $chapter) {
        $book    = \App\Models\Book::findOrFail($book);
        $chapter = \App\Models\BookChapter::where('book_id', $book->id)->findOrFail($chapter);
        return view('pages.reader', compact('book', 'chapter'));
    })->name('reader.show');

    Route::get('/books/{book}/audio', function ($bookId) {
        $book = \App\Models\Book::with('audios')->findOrFail($bookId);
        return view('pages.audio', compact('book'));
    })->name('audio.show');

    Route::get('/books/{book}/videos', function ($bookId) {
        $book = \App\Models\Book::with('videos')->findOrFail($bookId);
        return view('pages.videos', compact('book'));
    })->name('videos.index');

    Route::get('/books/{book}/quiz', TakeQuiz::class)->name('quiz.show');

    // Gamification
    Route::get('/leaderboard', function () {
        $topUsers = \App\Models\User::orderByDesc('total_points')->take(20)->get();
        return view('pages.leaderboard', compact('topUsers'));
    })->name('leaderboard');

    // Community
    Route::get('/chat', GlobalChat::class)->name('chat');
    Route::get('/groups', GroupList::class)->name('groups.index');
    Route::get('/groups/{group}', GroupDetail::class)->name('groups.show');

    // Live events
    Route::get('/live', LiveIndex::class)->name('live.index');
    Route::get('/live/{event}', LiveDetail::class)->name('live.show');

    // AI Chatbot
    Route::get('/ai-chat', AiChat::class)->name('ai-chat');

    // User Profile & Settings
    Route::get('/profile/{username}', ProfilePage::class)->name('profile.show');
    Route::get('/settings', SettingsPage::class)->name('settings');
    Route::get('/notifications', NotificationList::class)->name('notifications');
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
        ]);
        \App\Models\Book::create([
            'title'        => $req->title,
            'author'       => $req->author,
            'description'  => $req->description,
            'genre'        => $req->genre,
            'slug'         => \Illuminate\Support\Str::slug($req->title),
            'week_number'  => \App\Models\Book::max('week_number') + 1,
            'published_at' => now(),
            'is_active'    => false,
        ]);
        return redirect()->route('teacher.books.index')->with('success', 'Kitob qo\'shildi!');
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
        $events = \App\Models\LiveEvent::latest()->paginate(10);
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
        ]);
        \App\Models\Book::create([
            'title'        => $req->title,
            'author'       => $req->author,
            'description'  => $req->description,
            'genre'        => $req->genre,
            'slug'         => \Illuminate\Support\Str::slug($req->title) . '-' . uniqid(),
            'week_number'  => $req->week_number ?? ((\App\Models\Book::max('week_number') ?? 0) + 1),
            'published_at' => now(),
            'is_active'    => $req->has('is_active'),
        ]);
        return redirect()->route('admin.books.index')->with('success', 'Yangi kitob muvaffaqiyatli qo\'shildi!');
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
        ]);
        $book->update([
            'title'       => $req->title,
            'author'      => $req->author,
            'description' => $req->description,
            'genre'       => $req->genre,
            'week_number' => $req->week_number ?? $book->week_number,
            'is_active'   => $req->has('is_active'),
        ]);
        return redirect()->route('admin.books.index')->with('success', 'Kitob ma\'lumotlari yangilandi!');
    })->name('books.update');

    Route::delete('/books/{book}', function (\App\Models\Book $book) {
        $book->delete();
        return redirect()->route('admin.books.index')->with('success', 'Kitob o\'chirildi!');
    })->name('books.destroy');
});

// Alias for profile.edit
Route::middleware(['auth'])->get('/profile-edit', function () {
    return redirect()->route('settings');
})->name('profile.edit');

