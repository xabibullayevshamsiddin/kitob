<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserProfile;
use App\Models\UserStreak;
use App\Models\Book;
use App\Models\BookChapter;
use App\Models\BookAudio;
use App\Models\BookVideo;
use App\Models\DailyQuote;
use App\Models\Badge;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ─── Roles ───────────────────────────────────────────────────────────
        $roleNames = ['admin', 'teacher', 'student', 'author', 'reader'];
        foreach ($roleNames as $r) {
            Role::firstOrCreate(['name' => $r, 'guard_name' => 'web']);
        }

        // ─── Permissions ─────────────────────────────────────────────────────
        $permissions = [
            // Book management
            'books.view', 'books.create', 'books.edit', 'books.delete',
            // Chapter management
            'chapters.view', 'chapters.create', 'chapters.edit', 'chapters.delete',
            // Quiz management
            'quizzes.view', 'quizzes.create', 'quizzes.edit', 'quizzes.delete',
            // User management
            'users.view', 'users.create', 'users.edit', 'users.delete',
            // Content moderation
            'chat.moderate', 'comments.moderate',
            // Stats
            'stats.view',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // ─── Assign permissions to roles ──────────────────────────────────────
        $adminRole = Role::findByName('admin');
        $adminRole->syncPermissions(Permission::all());

        $teacherRole = Role::findByName('teacher');
        $teacherRole->syncPermissions([
            'books.view', 'books.create', 'books.edit',
            'chapters.view', 'chapters.create', 'chapters.edit',
            'quizzes.view', 'quizzes.create', 'quizzes.edit',
            'users.view',
            'stats.view',
        ]);

        $studentRole = Role::findByName('student');
        $studentRole->syncPermissions([
            'books.view', 'chapters.view', 'quizzes.view',
        ]);

        // ─── Admin user ───────────────────────────────────────────────────────
        $admin = User::firstOrCreate(
            ['email' => 'admin@kitobxon.uz'],
            [
                'name'              => 'Kitobxon Admin',
                'username'          => 'admin',
                'password'          => Hash::make('password'),
                'total_points'      => 5000,
                'coin_balance'      => 500,
                'bio'               => 'Kitobxon platformasi asoschisi va bosh moderatori.',
                'email_verified_at' => now(),
            ]
        );
        $admin->syncRoles(['admin']);

        UserProfile::firstOrCreate(
            ['user_id' => $admin->id],
            [
                'reading_place'    => 'home',
                'reading_goal'     => 'personal_dev',
                'privacy_settings' => ['show_stats' => true, 'show_activity' => true],
            ]
        );

        UserStreak::firstOrCreate(
            ['user_id' => $admin->id],
            [
                'current_streak'   => 30,
                'longest_streak'   => 60,
                'last_active_date' => now('Asia/Tashkent')->toDateString(),
            ]
        );

        // ─── Teacher user ─────────────────────────────────────────────────────
        $teacher = User::firstOrCreate(
            ['email' => 'teacher@kitobxon.uz'],
            [
                'name'              => 'Dilnoza O\'qituvchi',
                'username'          => 'dilnoza_teacher',
                'password'          => Hash::make('password'),
                'total_points'      => 2500,
                'coin_balance'      => 200,
                'bio'               => 'Adabiyot o\'qituvchisi va kitob tahlilchisi.',
                'email_verified_at' => now(),
            ]
        );
        $teacher->syncRoles(['teacher']);

        UserProfile::firstOrCreate(
            ['user_id' => $teacher->id],
            [
                'reading_place'    => 'other',
                'reading_goal'     => 'knowledge',
                'privacy_settings' => ['show_stats' => true, 'show_activity' => true],
            ]
        );

        UserStreak::firstOrCreate(
            ['user_id' => $teacher->id],
            [
                'current_streak'   => 14,
                'longest_streak'   => 30,
                'last_active_date' => now('Asia/Tashkent')->toDateString(),
            ]
        );

        // ─── Student user ─────────────────────────────────────────────────────
        $student = User::firstOrCreate(
            ['email' => 'ali@misol.uz'],
            [
                'name'              => 'Ali Valiyev',
                'username'          => 'ali_valiyev',
                'password'          => Hash::make('password'),
                'total_points'      => 450,
                'coin_balance'      => 80,
                'bio'               => 'Kitob o\'qish va til o\'rganishga qiziqaman.',
                'email_verified_at' => now(),
            ]
        );
        $student->syncRoles(['student']);

        UserProfile::firstOrCreate(
            ['user_id' => $student->id],
            [
                'reading_place'    => 'library',
                'reading_goal'     => 'knowledge',
                'privacy_settings' => ['show_stats' => true, 'show_activity' => true],
            ]
        );

        UserStreak::firstOrCreate(
            ['user_id' => $student->id],
            [
                'current_streak'   => 5,
                'longest_streak'   => 12,
                'last_active_date' => now('Asia/Tashkent')->toDateString(),
            ]
        );

        // ─── Featured Book ────────────────────────────────────────────────────
        $book = Book::firstOrCreate(
            ['slug' => 'atom-odatlar'],
            [
                'title'        => 'Atom Odatlar (Atomic Habits)',
                'author'       => 'James Clear',
                'description'  => 'Kichik o\'zgarishlar qanday qilib ulkan natijalarga olib kelishi haqida amaliy qo\'llanma.',
                'genre'        => 'Shaxsiy rivojlanish',
                'week_number'  => 1,
                'published_at' => now(),
                'is_active'    => true,
            ]
        );

        BookChapter::firstOrCreate(
            ['book_id' => $book->id, 'chapter_number' => 1],
            [
                'title'            => '1-bob: Kichik odatlarning hayratlanarli kuchi',
                'duration_minutes' => 15,
                'is_published'     => true,
                'content'          => "Har kuni atigi bir foiz yaxshilanish uzoq muddatda aql bovar qilmas natijalarni beradi. Agar har kuni 1% yaxshilansangiz, bir yil oxirida 37 barobar kuchliroq bo'lasiz.\n\nOdatlar — bu o'z-o'zini rivojlantirishning murakkab foizlaridir.",
            ]
        );

        BookChapter::firstOrCreate(
            ['book_id' => $book->id, 'chapter_number' => 2],
            [
                'title'            => '2-bob: Sizning shaxsiyatingiz va odatlaringiz',
                'duration_minutes' => 18,
                'is_published'     => true,
                'content'          => "Haqiqiy xulq-atvor o'zgarishi bu — shaxsiyatning o'zgarishidir.",
            ]
        );

        DailyQuote::firstOrCreate(
            ['book_id' => $book->id, 'send_date' => now('Asia/Tashkent')->toDateString()],
            ['quote_text' => 'Siz o\'z maqsadlaringiz darajasiga ko\'tarilmaysiz, balki o\'z tizimlaringiz darajasiga qulaysiz.']
        );

        // ─── Badges ───────────────────────────────────────────────────────────
        $badgesData = [
            ['name' => 'Birinchi qadam',   'slug' => 'first-step',       'icon' => '🌱', 'description' => 'Birinchi kitob bobini o\'qidingiz',   'condition_type' => 'chapters_read',  'condition_value' => 1],
            ['name' => '3 kunlik olov',    'slug' => 'streak-3',         'icon' => '🔥', 'description' => 'Ketma-ket 3 kun faol bo\'ldingiz',     'condition_type' => 'streak_days',    'condition_value' => 3],
            ['name' => 'Haftalik chempion','slug' => 'weekly-champion',  'icon' => '🏆', 'description' => 'Haftalik kitobni to\'liq yakunladingiz','condition_type' => 'books_finished', 'condition_value' => 1],
            ['name' => 'Bilimdon',         'slug' => 'quiz-master',      'icon' => '🧠', 'description' => 'Testdan 100% natija qayd etdingiz',    'condition_type' => 'perfect_quiz',   'condition_value' => 1],
            ['name' => '7 kunlik olov',    'slug' => 'streak-7',         'icon' => '🔥', 'description' => 'Ketma-ket 7 kun faol bo\'ldingiz',     'condition_type' => 'streak_days',    'condition_value' => 7],
            ['name' => 'Kitobxon',         'slug' => 'bookworm',         'icon' => '📚', 'description' => '5 ta kitobni tugatdingiz',             'condition_type' => 'books_finished', 'condition_value' => 5],
        ];

        foreach ($badgesData as $b) {
            Badge::firstOrCreate(['slug' => $b['slug']], $b);
        }
    }
}
