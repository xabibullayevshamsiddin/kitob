<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\BookChapter;
use App\Models\BookReadingProgress;
use App\Models\DailyActivity;
use App\Models\DailyMetric;
use App\Models\PointTransaction;
use App\Models\User;
use App\Models\UserProfile;
use App\Services\Analytics\DateRange;
use App\Services\Analytics\EconomyMetrics;
use App\Services\Analytics\OverviewMetrics;
use App\Services\Analytics\RetentionMetrics;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    protected static int $userSeq = 1;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['admin', 'teacher', 'student', 'reader'] as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        }
    }

    protected function makeUser(array $overrides = []): User
    {
        $seq = self::$userSeq++;
        $defaults = [
            'name'     => "Test Foydalanuvchi {$seq}",
            'username' => "user_{$seq}_" . uniqid(),
            'email'    => "user_{$seq}_" . uniqid() . '@example.test',
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
            'role'     => 'student',
        ];

        return User::create(array_merge($defaults, $overrides));
    }

    protected function createAdmin(): User
    {
        $admin = $this->makeUser(['role' => 'admin']);
        $admin->assignRole('admin');
        return $admin;
    }

    /** @test */
    public function dau_wau_mau_and_stickiness_are_calculated_accurately(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 10, 17, 14, 0, 0, 'Asia/Tashkent'));

        $u1 = $this->makeUser();
        $u2 = $this->makeUser();
        $u3 = $this->makeUser();
        $u4 = $this->makeUser();

        // Bugun (2026-10-17): u1 (o'qigan 10 daq), u2 (faqat kirgan, 0 daq) -> DAU = 2, active_readers = 1
        DailyActivity::create([
            'user_id'       => $u1->id,
            'activity_date' => '2026-10-17',
            'minutes_read'  => 10,
            'logged_in'     => true,
        ]);
        DailyActivity::create([
            'user_id'       => $u2->id,
            'activity_date' => '2026-10-17',
            'minutes_read'  => 0,
            'logged_in'     => true,
        ]);

        // 4 kun oldin (2026-10-13): u3 (o'qigan 6 daq) -> WAU ichida (u1, u2, u3 = 3)
        DailyActivity::create([
            'user_id'       => $u3->id,
            'activity_date' => '2026-10-13',
            'minutes_read'  => 6,
            'logged_in'     => false,
        ]);

        // 20 kun oldin (2026-09-27): u4 (o'qigan 15 daq) -> MAU ichida (u1, u2, u3, u4 = 4)
        DailyActivity::create([
            'user_id'       => $u4->id,
            'activity_date' => '2026-09-27',
            'minutes_read'  => 15,
            'logged_in'     => true,
        ]);

        $range = DateRange::fromPreset('30');
        $kpis = app(OverviewMetrics::class)->getKpis($range, true);

        $this->assertSame(2, $kpis['dau']['value']);
        $this->assertSame(2, $kpis['dau']['logged_in_users']);
        $this->assertSame(1, $kpis['dau']['reading_users']);
        $this->assertSame(3, $kpis['wau']['value']);
        $this->assertSame(4, $kpis['mau']['value']);
        // Stickiness = 2 / 4 * 100 = 50.0%
        $this->assertSame(50.0, $kpis['stickiness']['value']);
        // Active readers (>= 5 daq): u1, u3, u4 = 3
        $this->assertSame(3, $kpis['active_readers']['value']);
        $this->assertSame(1, $kpis['active_readers']['today_value']);

        Carbon::setTestNow();
    }

    /** @test */
    public function tashkent_midnight_boundary_separates_days_accurately(): void
    {
        $u1 = $this->makeUser();
        $u2 = $this->makeUser();

        // 2026-10-16 23:59:59 Asia/Tashkent -> 2026-10-16 ga tushadi
        User::whereKey($u1->id)->update(['created_at' => '2026-10-16 23:59:59']);
        // 2026-10-17 00:00:01 Asia/Tashkent -> 2026-10-17 ga tushadi
        User::whereKey($u2->id)->update(['created_at' => '2026-10-17 00:00:01']);

        $overview = app(OverviewMetrics::class);
        $row16 = $overview->computeDailyRow('2026-10-16');
        $row17 = $overview->computeDailyRow('2026-10-17');

        $this->assertSame(1, $row16['new_users']);
        $this->assertSame(1, $row17['new_users']);
    }

    /** @test */
    public function retention_returns_null_for_immature_cohorts_not_zero(): void
    {
        $today = Carbon::create(2026, 10, 17, 12, 0, 0, 'Asia/Tashkent');
        Carbon::setTestNow($today);

        // Bugun (2026-10-17) ro'yxatdan o'tgan foydalanuvchi: D1, D7, D30 hali yetilmagan -> null bo'lishi shart
        $userToday = $this->makeUser();
        User::whereKey($userToday->id)->update(['created_at' => '2026-10-17 10:00:00']);

        // 3 kun oldin (2026-10-14) ro'yxatdan o'tgan foydalanuvchi: D1 yetilgan (0.0%), D7 va D30 hali yetilmagan -> null
        $user3DaysAgo = $this->makeUser();
        User::whereKey($user3DaysAgo->id)->update(['created_at' => '2026-10-14 10:00:00']);

        $range = DateRange::fromDates('2026-10-14', '2026-10-17');
        $cohorts = app(RetentionMetrics::class)->getDailyCohorts($range, $today);
        $byDate = collect($cohorts)->keyBy('cohort_date');

        $cToday = $byDate->get('2026-10-17');
        $this->assertNull($cToday['d1']);
        $this->assertNull($cToday['d7']);
        $this->assertNull($cToday['d30']);

        $c14 = $byDate->get('2026-10-14');
        $this->assertSame(0.0, $c14['d1']);
        $this->assertNull($c14['d7']);
        $this->assertNull($c14['d30']);

        Carbon::setTestNow();
    }

    /** @test */
    public function delta_with_zero_previous_returns_yangi_instead_of_infinity(): void
    {
        $deltaNew = DateRange::formatDelta(15, 0);
        $this->assertNull($deltaNew['percent']);
        $this->assertSame('yangi', $deltaNew['label']);
        $this->assertSame('new', $deltaNew['direction']);
        $this->assertTrue($deltaNew['is_positive']);

        $deltaZero = DateRange::formatDelta(0, 0);
        $this->assertSame(0.0, $deltaZero['percent']);
        $this->assertSame('0%', $deltaZero['label']);
        $this->assertSame('flat', $deltaZero['direction']);

        // Teskari rangli metrika (shikoyat o'sishi yomon)
        $deltaInverted = DateRange::formatDelta(10, 5, true);
        $this->assertSame(100.0, $deltaInverted['percent']);
        $this->assertFalse($deltaInverted['is_positive']);
    }

    /** @test */
    public function segments_hide_groups_with_fewer_than_five_users(): void
    {
        // 6 ta foydalanuvchi "home" (Uyda) o'qiydi (ko'rinishi kerak)
        for ($i = 0; $i < 6; $i++) {
            $u = $this->makeUser();
            UserProfile::create([
                'user_id'       => $u->id,
                'reading_place' => 'home',
                'reading_goal'  => 'knowledge',
            ]);
        }

        // 3 ta foydalanuvchi "other" o'qiydi (<5 -> yashirilishi kerak)
        for ($i = 0; $i < 3; $i++) {
            $u = $this->makeUser();
            UserProfile::create([
                'user_id'       => $u->id,
                'reading_place' => 'other',
                'reading_goal'  => 'other',
            ]);
        }

        $segments = app(RetentionMetrics::class)->getSegments(true);
        $placeItems = $segments['by_reading_place']['items'];

        $this->assertCount(1, $placeItems);
        $this->assertSame('home', $placeItems[0]['segment']);
        $this->assertSame('Uyda', $placeItems[0]['label']);
        $this->assertSame(6, $placeItems[0]['count']);

        $this->assertTrue($segments['by_reading_place']['insufficient']['has_hidden']);
        $this->assertSame("kam ma'lumot", $segments['by_reading_place']['insufficient']['label']);
        $this->assertSame(1, $segments['by_reading_place']['insufficient']['hidden_groups_count']);
        $this->assertSame(3, $segments['by_reading_place']['insufficient']['hidden_users_count']);
    }

    /** @test */
    public function daily_metrics_snapshot_is_idempotent(): void
    {
        $user = $this->makeUser();
        DailyActivity::create([
            'user_id'       => $user->id,
            'activity_date' => '2026-10-15',
            'minutes_read'  => 25,
            'logged_in'     => true,
        ]);

        Artisan::call('analytics:snapshot', ['--from' => '2026-10-15', '--to' => '2026-10-15']);
        $this->assertSame(1, DailyMetric::where('date', '2026-10-15')->count());
        $this->assertSame(25, DailyMetric::where('date', '2026-10-15')->first()->minutes_read);

        // Ma'lumot yangilangandan keyin qayta ishga tushiramiz — dublikat bo'lmasligi, ustiga yozilishi kerak
        DailyActivity::where('user_id', $user->id)
            ->where('activity_date', '2026-10-15')
            ->update(['minutes_read' => 45]);

        Artisan::call('analytics:snapshot', ['--from' => '2026-10-15', '--to' => '2026-10-15']);
        $this->assertSame(1, DailyMetric::where('date', '2026-10-15')->count());
        $this->assertSame(45, DailyMetric::where('date', '2026-10-15')->first()->minutes_read);
    }

    /** @test */
    public function non_admins_including_teachers_receive_403_on_analytics_routes(): void
    {
        $student = $this->makeUser(['role' => 'student']);
        $student->assignRole('student');

        $teacher = $this->makeUser(['role' => 'teacher']);
        $teacher->assignRole('teacher');

        $admin = $this->createAdmin();

        $this->actingAs($student)->get(route('admin.analytics'))->assertStatus(403);
        $this->actingAs($teacher)->get(route('admin.analytics'))->assertStatus(403);
        $this->actingAs($teacher)->get(route('admin.analytics.data', ['metric' => 'overview']))->assertStatus(403);
        $this->actingAs($teacher)->get(route('admin.analytics.export', ['metric' => 'overview']))->assertStatus(403);

        $this->actingAs($admin)->get(route('admin.analytics'))->assertStatus(200);
        $this->actingAs($admin)->get(route('admin.analytics.data', ['metric' => 'overview']))->assertStatus(200);
    }

    /** @test */
    public function export_returns_aggregate_csv_only_without_pii(): void
    {
        $admin = $this->createAdmin();
        $secretUser = $this->makeUser([
            'name'     => 'Maxfiy Ism Familiya',
            'username' => 'secret_user_99',
            'email'    => 'maxfiy_shaxsiy@example.com',
        ]);

        DailyActivity::create([
            'user_id'       => $secretUser->id,
            'activity_date' => Carbon::now('Asia/Tashkent')->toDateString(),
            'minutes_read'  => 30,
            'logged_in'     => true,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.analytics.export', ['metric' => 'overview', 'period' => '7']));
        $response->assertStatus(200);
        $csvContent = $response->streamedContent();

        $this->assertStringContainsString('Sana,DAU', $csvContent);
        $this->assertStringNotContainsString('maxfiy_shaxsiy@example.com', $csvContent);
        $this->assertStringNotContainsString('Maxfiy Ism Familiya', $csvContent);
        $this->assertStringNotContainsString('secret_user_99', $csvContent);
    }

    /** @test */
    public function cache_key_includes_period_and_admin_dashboard_fixes_online_today_zero(): void
    {
        $r7 = DateRange::fromPreset('7');
        $r30 = DateRange::fromPreset('30');
        $rCustom = DateRange::fromDates('2026-10-01', '2026-10-10');

        $this->assertNotSame($r7->cacheKey('overview_kpis'), $r30->cacheKey('overview_kpis'));
        $this->assertNotSame($r30->cacheKey('overview_kpis'), $rCustom->cacheKey('overview_kpis'));
        $this->assertStringContainsString(':7:', $r7->cacheKey('overview_kpis'));
        $this->assertStringContainsString(':30:', $r30->cacheKey('overview_kpis'));
        $this->assertStringContainsString(':custom:2026-10-01:2026-10-10', $rCustom->cacheKey('overview_kpis'));

        // Bugun hech kim faol bo'lmaganda /admin sahifasida online_today = 0 bo'lishi kerak (eski 1 emas!)
        $admin = $this->createAdmin();
        $dashboardData = app(OverviewMetrics::class)->getAdminDashboardData(true);
        $this->assertSame(0, $dashboardData['stats']['online_today']);

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertStatus(200);
    }

    /** @test */
    public function books_finished_counts_distinct_user_book_pairs_not_chapters(): void
    {
        $user = $this->makeUser();
        $book = Book::create([
            'title'        => 'Test Kitob',
            'slug'         => 'test-kitob',
            'author'       => 'Muallif',
            'description'  => 'Tavsif',
            'genre'        => 'Ilmiy',
            'week_number'  => 1,
            'published_at' => now(),
            'is_active'    => true,
        ]);

        // Bitta kitobning 3 ta bobida >= 90% progress
        for ($i = 1; $i <= 3; $i++) {
            $ch = BookChapter::create([
                'book_id'        => $book->id,
                'chapter_number' => $i,
                'title'          => "{$i}-bob",
                'content'        => 'Matn',
            ]);
            BookReadingProgress::create([
                'user_id'          => $user->id,
                'book_id'          => $book->id,
                'chapter_id'       => $ch->id,
                'last_position'    => 100,
                'percent_complete' => 95.0,
            ]);
        }

        $overview = app(OverviewMetrics::class);
        // 3 ta bob qatori bo'lsa ham, 1 ta foydalanuvchi 1 ta kitobni tugatgan = 1
        $this->assertSame(1, $overview->countDistinctFinishedBookPairs());
    }

    /** @test */
    public function abuse_signals_detect_z_score_outliers_without_auto_banning(): void
    {
        // 12 ta oddiy yozuv (har biri ~20 ball) va 1 ta anomal yozuv (5000 ball, z-score > 3)
        for ($i = 1; $i <= 12; $i++) {
            $u = $this->makeUser();
            PointTransaction::create([
                'user_id'     => $u->id,
                'points'      => 20,
                'source'      => 'reading',
                'description' => 'Oddiy mutolaa',
            ]);
        }

        $outlierUser = $this->makeUser(['username' => 'outlier_reader']);
        PointTransaction::create([
            'user_id'     => $outlierUser->id,
            'points'      => 5000,
            'source'      => 'quiz',
            'description' => 'Anomal ball',
        ]);

        $range = DateRange::fromPreset('7');
        $signals = app(EconomyMetrics::class)->getAbuseSignals($range, true);

        $this->assertSame(1, $signals['count']);
        $this->assertSame('outlier_reader', $signals['items'][0]['username']);
        $this->assertGreaterThanOrEqual(3.0, $signals['items'][0]['z_score']);
        $this->assertSame('tekshirilsin', $signals['items'][0]['status']);
        // Avtomatik ban QILINMAGAN bo'lishi shart
        $this->assertFalse((bool) $outlierUser->fresh()->is_banned);
    }

    /** @test */
    public function stage2_overview_tab_renders_kpis_dau_split_alerts_and_accessible_tables(): void
    {
        $admin = $this->createAdmin();
        $today = Carbon::now('Asia/Tashkent')->toDateString();

        $u1 = $this->makeUser();
        $u2 = $this->makeUser();

        DailyActivity::create([
            'user_id'       => $u1->id,
            'activity_date' => $today,
            'minutes_read'  => 0,
            'logged_in'     => true,
        ]);
        DailyActivity::create([
            'user_id'       => $u2->id,
            'activity_date' => $today,
            'minutes_read'  => 25,
            'logged_in'     => true,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.analytics', ['tab' => 'overview', 'period' => '30', 'refresh' => 1]));

        $response->assertStatus(200);
        $response->assertSee('Asia/Tashkent');
        $response->assertSee('Diqqat talab qiladi');
        $response->assertSee('DAU (Kunlik faol)');
        $response->assertSee('Kirgan:');
        $response->assertSee("O'qigan:", false);
        $response->assertSee('WAU (7 kunlik faol)');
        $response->assertSee('MAU (30 kunlik faol)');
        $response->assertSee('Yopishqoqlik (DAU/MAU)');
        $response->assertSee("Jadval ko'rinishi", false);
        $response->assertSee('grid-cols-2', false);
        $response->assertSee('chart-overview-activity', false);
        $response->assertSee('chart-overview-reading', false);
    }

    /** @test */
    public function stage2_users_tab_renders_funnel_cohorts_na_segments_churn_privacy_and_heatmap(): void
    {
        $admin = $this->createAdmin();
        $now = Carbon::now('Asia/Tashkent');

        // Churn risk ostidagi foydalanuvchi (streak >= 7, 3 kun oldin faol bo'lgan)
        $churnUser = $this->makeUser([
            'name'       => 'Sirli Ism Familiya',
            'username'   => 'kitobxon_streak9',
            'email'      => 'sirli_pochta@example.com',
            'created_at' => $now->copy()->subDays(15),
        ]);
        \App\Models\UserStreak::create([
            'user_id'          => $churnUser->id,
            'current_streak'   => 9,
            'longest_streak'   => 12,
            'last_active_date' => $now->copy()->subDays(3)->toDateString(),
        ]);

        // Bugungi kohorta (D1/D7/D30 -> N/A bo'lishi shart)
        $todayCohortUser = $this->makeUser([
            'created_at' => $now->copy(),
        ]);
        UserProfile::create([
            'user_id'       => $todayCohortUser->id,
            'reading_place' => 'other',
            'reading_goal'  => 'other',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.analytics', ['tab' => 'users', 'period' => '30', 'refresh' => 1]));

        $response->assertStatus(200);
        // 1. Voronka
        $response->assertSee("Ro'yxatdan o'tishdan 7 kunlik streakkacha konversiya voronkasi", false);
        $response->assertSee('Onboarding tugadi');
        // 2. Kohorta N/A
        $response->assertSee('N/A');
        // 3. Segmentatsiya (< 5 -> Kam ma'lumot)
        $response->assertSee("Kam ma'lumot", false);
        // 4. Yo'qolish xavfi: faqat @username va admin profil havolasi bor, email va ism YO'Q
        $response->assertSee('@kitobxon_streak9');
        $response->assertSee(route('admin.users.edit', $churnUser->id), false);
        $response->assertDontSee('sirli_pochta@example.com');
        $response->assertDontSee('Sirli Ism Familiya');
        // 5. 7x24 Issiqlik xaritasi
        $response->assertSee('7 × 24 ISSIQLIK XARITASI');
        $response->assertSee('Tavsiya etilgan efir vaqti:');
    }
}

