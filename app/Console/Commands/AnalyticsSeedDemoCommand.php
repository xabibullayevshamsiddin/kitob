<?php

namespace App\Console\Commands;

use App\Models\AiChatHistory;
use App\Models\Book;
use App\Models\BookChapter;
use App\Models\BookComment;
use App\Models\BookReadingProgress;
use App\Models\CoinTransaction;
use App\Models\DailyActivity;
use App\Models\GlobalChatMessage;
use App\Models\Group;
use App\Models\GroupMessage;
use App\Models\LiveEvent;
use App\Models\PointTransaction;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\ReadingSession;
use App\Models\Report;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\UserStreak;
use App\Services\Analytics\DateRange;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AnalyticsSeedDemoCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'analytics:seed-demo
                            {--days=30 : Necha kunlik tarixiy demo ma\'lumot yaratish}
                            {--users=45 : Yaratiladigan demo foydalanuvchilar soni}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Lokal/test muhiti uchun realistik analitika demo ma\'lumotlarini yaratish (production\'da ishlamaydi)';

    public function handle(): int
    {
        if (app()->environment('production')) {
            $this->error("XAVFSIZLIK: analytics:seed-demo buyrug'ini production muhitida ishga tushirish qat'iyan man etilgan!");
            return self::FAILURE;
        }

        $tz = config('analytics.timezone', DateRange::TIMEZONE);
        $now = Carbon::now($tz);
        $days = max(7, min(90, (int) $this->option('days')));
        $targetUsers = max(15, min(500, (int) $this->option('users')));

        $this->info("Demo analitika ma'lumotlari tayyorlanmoqda ({$days} kun, {$targetUsers} ta demo foydalanuvchi)...");

        // 1. Kitoblar va boblar mavjudligini ta'minlash
        $demoBooksData = [
            ['title' => "O'tkan kunlar",          'author' => 'Abdulla Qodiriy',  'genre' => 'Tarixiy roman'],
            ['title' => 'Atom odatlar',           'author' => 'James Clear',      'genre' => 'Shaxsiy rivojlanish'],
            ['title' => 'Yulduzli tunlar',        'author' => 'Pirimqul Qodirov', 'genre' => 'Tarixiy roman'],
            ['title' => 'Chuqur ish (Deep Work)', 'author' => 'Cal Newport',      'genre' => 'Produktivlik'],
            ['title' => 'Sariq devni minib',      'author' => "Xudoyberdi To'xtaboyev", 'genre' => 'Bolalar adabiyoti'],
        ];

        $books = [];
        foreach ($demoBooksData as $idx => $bData) {
            $book = Book::firstOrCreate(
                ['slug' => Str::slug($bData['title'])],
                [
                    'title'        => $bData['title'],
                    'author'       => $bData['author'],
                    'description'  => $bData['title'] . ' — demo analitika uchun namuna kitob.',
                    'genre'        => $bData['genre'],
                    'week_number'  => $idx + 1,
                    'published_at' => $now->copy()->subDays($days),
                    'is_active'    => true,
                ]
            );

            // Har kitobga 5 tadan bob
            for ($chNum = 1; $chNum <= 5; $chNum++) {
                BookChapter::firstOrCreate(
                    ['book_id' => $book->id, 'chapter_number' => $chNum],
                    [
                        'title'             => "{$chNum}-bob. Asosiy qism",
                        'content'           => str_repeat("Demo matn mazmuni. ", 30),
                        'estimated_minutes' => 15,
                    ]
                );
            }

            // Har kitobga 1 tadan Quiz
            Quiz::firstOrCreate(
                ['book_id' => $book->id, 'title' => $book->title . ' — Yakuniy test'],
                [
                    'description'        => 'Kitob bo\'yicha bilimlarni sinash testi',
                    'difficulty'         => 'medium',
                    'time_limit_minutes' => 15,
                    'reward_points'      => 100,
                    'is_active'          => true,
                ]
            );

            $books[] = $book->load(['chapters', 'quizzes']);
        }

        // 2. Demo foydalanuvchilar yaratish (oxirgi $days kun davomida taqsimlangan)
        $places = ['home', 'library', 'travel', 'university'];
        $goals = ['knowledge', 'personal_dev', 'exam_prep', 'career'];

        $demoUsers = [];
        $passwordHash = Hash::make('password');

        for ($i = 1; $i <= $targetUsers; $i++) {
            $daysAgo = (int) floor((($targetUsers - $i) / max(1, $targetUsers - 1)) * ($days - 1));
            $regTime = $now->copy()->subDays($daysAgo)->setTime(rand(8, 21), rand(0, 59), 0);

            $username = 'demo_reader_' . str_pad((string) $i, 3, '0', STR_PAD_LEFT);
            $user = User::updateOrCreate(
                ['username' => $username],
                [
                    'name'         => 'Demo Kitobxon #' . $i,
                    'email'        => $username . '@example.test',
                    'password'     => $passwordHash,
                    'role'         => 'student',
                    'total_points' => 0,
                    'coin_balance' => 0,
                ]
            );

            // created_at ni kohorta sanasiga moslash
            User::whereKey($user->id)->update([
                'created_at' => $regTime->format('Y-m-d H:i:s'),
                'updated_at' => $regTime->format('Y-m-d H:i:s'),
            ]);
            $user->created_at = $regTime;

            // 85% foydalanuvchi onboarding tugatgan, shuningdek <5 foydalanuvchili kichik segment ('other') ham bo'ladi
            if ($i <= (int) round($targetUsers * 0.85)) {
                $place = $i <= 2 ? 'other' : $places[$i % count($places)];
                $goal = $i <= 3 ? 'other' : $goals[$i % count($goals)];

                UserProfile::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'reading_place' => $place,
                        'reading_goal'  => $goal,
                    ]
                );
                UserProfile::where('user_id', $user->id)->update([
                    'created_at' => $regTime->copy()->addMinutes(5)->format('Y-m-d H:i:s'),
                ]);
            }

            $demoUsers[] = $user;
        }

        // 3. Kunlik faollik, o'qish sessiyalari, kitob progressi, ball va tangalar
        foreach ($demoUsers as $idx => $user) {
            $regDate = Carbon::parse($user->created_at)->setTimezone($tz)->startOfDay();
            $totalUserPoints = 0;
            $totalUserCoins = 0;

            $isChurnRiskUser = ($idx < 4); // Birinchi 4 ta foydalanuvchi: streak >= 7, lekin oxirgi 3 kunda kirmagan
            if ($idx >= 34 && $idx < 38) {
                // Onboarding tugatgan, ammo hali birinchi o'qishni boshlamagan 4 ta foydalanuvchi (voronka uchun)
                continue;
            }

            $cursor = $regDate->copy();
            while ($cursor->lte($now->copy()->startOfDay())) {
                $dateStr = $cursor->toDateString();
                $daysFromToday = $cursor->diffInDays($now->copy()->startOfDay());

                if ($isChurnRiskUser && $daysFromToday < 3) {
                    $cursor->addDay();
                    continue;
                }

                // Faollik ehtimoli
                $isActiveDay = $isChurnRiskUser || (($idx + $cursor->dayOfYear) % 3 !== 0);
                if (!$isActiveDay) {
                    $cursor->addDay();
                    continue;
                }

                $minutes = (($idx + $cursor->day) % 5 === 0) ? 3 : (10 + (($idx * 7 + $cursor->day * 3) % 45));
                $pts = $minutes * 2;
                $coins = (int) round($minutes * 0.5);

                DailyActivity::updateOrCreate(
                    ['user_id' => $user->id, 'activity_date' => $dateStr],
                    [
                        'minutes_read'  => $minutes,
                        'logged_in'     => true,
                        'points_earned' => $pts,
                    ]
                );

                $book = $books[$idx % count($books)];
                $chapter = $book->chapters[min(($cursor->day % 5), 4)] ?? null;
                $sessionHour = 18 + (($idx + $cursor->day) % 5); // 18:00..22:00 oralig'ida pik faollik
                $sessionTime = $cursor->copy()->setTime($sessionHour, rand(5, 50), 0);

                $session = ReadingSession::updateOrCreate(
                    [
                        'user_id'      => $user->id,
                        'book_id'      => $book->id,
                        'chapter_id'   => $chapter ? $chapter->id : null,
                        'session_date' => $dateStr,
                    ],
                    [
                        'minutes_read' => $minutes,
                    ]
                );
                ReadingSession::whereKey($session->id)->update([
                    'created_at' => $sessionTime->format('Y-m-d H:i:s'),
                    'updated_at' => $sessionTime->format('Y-m-d H:i:s'),
                ]);

                // Point & Coin transactions
                $pt = PointTransaction::create([
                    'user_id'     => $user->id,
                    'points'      => $pts,
                    'source'      => 'reading',
                    'description' => "Demo mutolaa: {$minutes} daqiqa",
                ]);
                PointTransaction::whereKey($pt->id)->update([
                    'created_at' => $sessionTime->format('Y-m-d H:i:s'),
                ]);

                $ct = CoinTransaction::create([
                    'user_id'     => $user->id,
                    'coins'       => $coins,
                    'source'      => 'reading',
                    'description' => "Demo mutolaa tangasi",
                ]);
                CoinTransaction::whereKey($ct->id)->update([
                    'created_at' => $sessionTime->format('Y-m-d H:i:s'),
                ]);

                $totalUserPoints += $pts;
                $totalUserCoins += $coins;

                $cursor->addDay();
            }

            // Kitob progressi va tugatilgan kitoblar (bob bo'yicha tushib ketish grafigi uchun)
            $assignedBook = $books[$idx % count($books)];
            $maxChapterReached = max(1, 5 - ($idx % 5)); // 1-bobda hamma, 5-bobgacha kamayib boradi
            foreach ($assignedBook->chapters as $ch) {
                if ($ch->chapter_number <= $maxChapterReached) {
                    $pct = ($ch->chapter_number < $maxChapterReached || $maxChapterReached === 5) ? 100.0 : 55.0;
                    $brp = BookReadingProgress::updateOrCreate(
                        [
                            'user_id'    => $user->id,
                            'book_id'    => $assignedBook->id,
                            'chapter_id' => $ch->id,
                        ],
                        [
                            'last_position'    => 10,
                            'percent_complete' => $pct,
                        ]
                    );
                    BookReadingProgress::whereKey($brp->id)->update([
                        'updated_at' => $now->copy()->subDays($idx % 10)->format('Y-m-d H:i:s'),
                    ]);
                }
            }

            // Sharh va reyting
            if ($idx % 2 === 0) {
                BookComment::firstOrCreate(
                    ['book_id' => $assignedBook->id, 'user_id' => $user->id],
                    [
                        'comment' => 'Juda foydali va mazmunli asar ekan!',
                        'rating'  => ($idx % 5 === 0) ? 3 : (4 + ($idx % 2)),
                    ]
                );
            }

            // Quiz urinishlari (savol darajasida answers JSON bilan)
            $quiz = $assignedBook->quizzes->first();
            if ($quiz && $idx % 2 === 0) {
                $q1Correct = ($idx % 3 !== 0);
                $q2Correct = ($idx % 4 !== 0);
                $q3Correct = ($idx % 2 === 0);
                $correctCount = (int) $q1Correct + (int) $q2Correct + (int) $q3Correct;
                $pct = round(($correctCount / 3) * 100, 2);

                QuizAttempt::updateOrCreate(
                    ['user_id' => $user->id, 'quiz_id' => $quiz->id],
                    [
                        'score'         => (int) $pct,
                        'max_score'     => 100,
                        'percent'       => $pct,
                        'review_status' => 'reviewed',
                        'completed_at'  => $now->copy()->subDays($idx % 12),
                        'answers'       => [
                            [
                                'question_id'   => $assignedBook->id * 100 + 1,
                                'question_text' => "{$assignedBook->title}: Asosiy g'oya nimadan iborat?",
                                'type'          => 'single',
                                'points'        => 10,
                                'earned_points' => $q1Correct ? 10 : 0,
                                'correct'       => $q1Correct,
                            ],
                            [
                                'question_id'   => $assignedBook->id * 100 + 2,
                                'question_text' => "{$assignedBook->title}: Bosh qahramonning ilk qarori qanday edi?",
                                'type'          => 'single',
                                'points'        => 10,
                                'earned_points' => $q2Correct ? 10 : 0,
                                'correct'       => $q2Correct,
                            ],
                            [
                                'question_id'   => $assignedBook->id * 100 + 3,
                                'question_text' => "{$assignedBook->title}: Xulosa qismidagi muhim saboq?",
                                'type'          => 'single',
                                'points'        => 10,
                                'earned_points' => $q3Correct ? 10 : 0,
                                'correct'       => $q3Correct,
                            ],
                        ],
                    ]
                );
            }

            // Streaklar
            if ($isChurnRiskUser) {
                UserStreak::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'current_streak'   => 9 + $idx,
                        'longest_streak'   => 14 + $idx,
                        'last_active_date' => $now->copy()->subDays(3)->toDateString(),
                    ]
                );
            } else {
                $curStreak = ($idx % 10 === 0) ? 32 : (($idx % 4 === 0) ? 11 : (($idx % 2 === 0) ? 4 : 2));
                UserStreak::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'current_streak'   => $curStreak,
                        'longest_streak'   => $curStreak + 2,
                        'last_active_date' => $now->toDateString(),
                    ]
                );
            }

            User::whereKey($user->id)->update([
                'total_points' => $totalUserPoints,
                'coin_balance' => $totalUserCoins,
            ]);
        }

        // 4. Suiiste'mol (z-score >= 3) anomaliyasi uchun bitta keskin kunlik ball yozuvi
        if (!empty($demoUsers)) {
            $anomalyUser = $demoUsers[0];
            $anomalyTx = PointTransaction::create([
                'user_id'     => $anomalyUser->id,
                'points'      => 15000,
                'source'      => 'quiz',
                'description' => 'Anomal yuqori kunlik ball (demo z-score tekshiruvi)',
            ]);
            PointTransaction::whereKey($anomalyTx->id)->update([
                'created_at' => $now->copy()->subDay()->setTime(14, 0)->format('Y-m-d H:i:s'),
            ]);
        }

        // 5. Jamiyat (Chat, Guruhlar, Shikoyatlar, Jonli efir) va AI yozuvlari
        $firstUser = $demoUsers[0];
        $group = Group::firstOrCreate(
            ['slug' => 'kitobxonlar-klubi-demo'],
            [
                'name'        => 'Kitobxonlar Klubi (Demo)',
                'description' => 'Haftalik kitob muhokamasi guruhi',
                'created_by'  => $firstUser->id,
                'book_id'     => $books[0]->id,
                'is_private'  => false,
                'invite_code' => 'DEMO2026',
            ]
        );

        for ($d = 0; $d < min(14, $days); $d++) {
            $msgTime = $now->copy()->subDays($d)->setTime(19, 30);
            $u = $demoUsers[$d % count($demoUsers)];

            $gm = GlobalChatMessage::create([
                'user_id' => $u->id,
                'message' => "Bugungi mutolaa juda maroqli o'tdi! (#{$d})",
            ]);
            GlobalChatMessage::whereKey($gm->id)->update(['created_at' => $msgTime->format('Y-m-d H:i:s')]);

            $grm = GroupMessage::create([
                'group_id' => $group->id,
                'user_id'  => $u->id,
                'message'  => "Bob bo'yicha fikrlaringiz qanday? (#{$d})",
            ]);
            GroupMessage::whereKey($grm->id)->update(['created_at' => $msgTime->format('Y-m-d H:i:s')]);

            $ai = AiChatHistory::create([
                'user_id'     => $u->id,
                'book_id'     => $books[$d % count($books)]->id,
                'message'     => 'Ushbu bobning asosiy xulosasini tushuntirib bering.',
                'response'    => 'Ushbu bobda intizom va kichik odatlarning uzoq muddatli ta\'siri yoritilgan.',
                'provider'    => 'gemini',
                'model'       => 'gemini-1.5-flash',
                'tokens_used' => 450 + ($d * 35),
            ]);
            AiChatHistory::whereKey($ai->id)->update(['created_at' => $msgTime->format('Y-m-d H:i:s')]);
        }

        // Ochiq shikoyat (24 soatdan oshgan — alert'da ko'rinishi uchun)
        if (count($demoUsers) >= 2) {
            $rep = Report::firstOrCreate(
                [
                    'reporter_id'      => $demoUsers[1]->id,
                    'reported_user_id' => $demoUsers[2]->id,
                    'source'           => 'global_chat',
                ],
                [
                    'message_content' => 'Demo spam xabar namunasi',
                    'reason'          => 'Spam yoki reklama',
                    'status'          => 'pending',
                ]
            );
            Report::whereKey($rep->id)->update([
                'created_at' => $now->copy()->subHours(30)->format('Y-m-d H:i:s'),
            ]);
        }

        // Jonli efir namunasi
        LiveEvent::firstOrCreate(
            ['title' => "Haftalik kitob tahlili: O'tkan kunlar (Demo)"],
            [
                'book_id'      => $books[0]->id,
                'host_user_id' => $firstUser->id,
                'description'  => 'Asar qahramonlari va tarixiy davr muhokamasi',
                'scheduled_at' => $now->copy()->subDays(2)->setTime(20, 0),
                'started_at'   => $now->copy()->subDays(2)->setTime(20, 0),
                'status'       => LiveEvent::STATUS_ENDED,
                'likes_count'  => 42,
            ]
        );

        // 6. Tarixiy kunlar uchun daily_metrics snapshot yaratish
        $fromDate = $now->copy()->subDays($days)->toDateString();
        $toDate = $now->copy()->subDay()->toDateString();
        Artisan::call('analytics:snapshot', [
            '--from' => $fromDate,
            '--to'   => $toDate,
        ]);

        $this->info("Demo ma'lumotlar va `daily_metrics` snapshot muvaffaqiyatli yaratildi!");

        return self::SUCCESS;
    }
}
