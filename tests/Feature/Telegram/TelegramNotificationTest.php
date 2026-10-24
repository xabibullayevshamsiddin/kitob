<?php

namespace Tests\Feature\Telegram;

use App\Console\Commands\Telegram\SendDailyQuoteCommand;
use App\Console\Commands\Telegram\SendStreakRemindersCommand;
use App\Jobs\SendTelegramNotificationJob;
use App\Models\Book;
use App\Models\DailyQuote;
use App\Models\User;
use App\Models\UserStreak;
use App\Services\Telegram\Exceptions\TelegramBlockedException;
use App\Services\Telegram\TelegramClient;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class TelegramNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('telegram.bot_token', '123456789:ABCdefGHIjklMNOpqrsTUVwxyz');
        Config::set('telegram.bot_username', 'ziyonur_test_bot');
    }

    public function test_notification_job_sends_message_if_preference_enabled(): void
    {
        Http::fake([
            'https://api.telegram.org/bot*' => Http::response(['ok' => true, 'result' => ['message_id' => 99]], 200),
        ]);

        $user = User::factory()->create([
            'telegram_id'        => 555001,
            'notification_prefs' => ['streak_reminder' => true],
        ]);

        $job = new SendTelegramNotificationJob($user->id, 'Sinov xabari', [], 'streak_reminder');
        $job->handle(new TelegramClient());

        Http::assertSent(function ($request) {
            return $request['chat_id'] === 555001 && $request['text'] === 'Sinov xabari';
        });
    }

    public function test_notification_job_does_not_send_if_preference_disabled(): void
    {
        Http::fake([
            'https://api.telegram.org/bot*' => Http::response(['ok' => true], 200),
        ]);

        $user = User::factory()->create([
            'telegram_id'        => 555002,
            'notification_prefs' => ['streak_reminder' => false],
        ]);

        $job = new SendTelegramNotificationJob($user->id, 'Sinov xabari', [], 'streak_reminder');
        $job->handle(new TelegramClient());

        Http::assertNothingSent();
    }

    public function test_notification_job_marks_user_blocked_on_403(): void
    {
        Http::fake([
            'https://api.telegram.org/bot*' => Http::response([
                'ok'          => false,
                'error_code'  => 403,
                'description' => 'Forbidden: bot was blocked by the user',
            ], 403),
        ]);

        $user = User::factory()->create([
            'telegram_id'         => 555003,
            'telegram_blocked_at' => null,
        ]);

        $job = new SendTelegramNotificationJob($user->id, 'Sinov xabari');
        $job->handle(new TelegramClient());

        $this->assertNotNull($user->fresh()->telegram_blocked_at);
        $this->assertTrue($user->fresh()->isTelegramBlocked());
    }

    public function test_streak_reminders_command_dispatches_jobs_for_inactive_streaks(): void
    {
        Queue::fake();

        $activeUser = User::factory()->create(['telegram_id' => 101]);
        UserStreak::create([
            'user_id'          => $activeUser->id,
            'current_streak'   => 5,
            'last_active_date' => Carbon::now('Asia/Tashkent')->toDateString(),
        ]);

        $inActiveUser = User::factory()->create(['telegram_id' => 102]);
        UserStreak::create([
            'user_id'          => $inActiveUser->id,
            'current_streak'   => 7,
            'last_active_date' => Carbon::now('Asia/Tashkent')->subDay()->toDateString(),
        ]);

        $this->artisan('telegram:send-streak-reminders')
            ->assertExitCode(0);

        Queue::assertPushed(SendTelegramNotificationJob::class, function ($job) use ($inActiveUser) {
            return $job->userId === $inActiveUser->id && $job->type === 'streak_reminder';
        });

        Queue::assertNotPushed(SendTelegramNotificationJob::class, function ($job) use ($activeUser) {
            return $job->userId === $activeUser->id;
        });
    }

    public function test_daily_quote_command_dispatches_quote(): void
    {
        Queue::fake();

        $book = Book::create([
            'title'       => 'Alkimyogar',
            'slug'        => 'alkimyogar-' . time(),
            'author'      => 'Paulo Koelo',
            'description' => 'Ajoyib asar',
            'genre'       => 'Falsafa',
            'week_number' => 888,
        ]);

        DailyQuote::create([
            'book_id'    => $book->id,
            'quote_text' => 'Biror narsani chin dildan istasang, butun koinot yordam beradi.',
            'send_date'  => Carbon::now('Asia/Tashkent')->toDateString(),
        ]);

        $user = User::factory()->create(['telegram_id' => 201]);

        $this->artisan('telegram:send-daily-quote')
            ->assertExitCode(0);

        Queue::assertPushed(SendTelegramNotificationJob::class, function ($job) use ($user) {
            return $job->userId === $user->id
                && $job->type === 'daily_quote'
                && str_contains($job->message, 'Alkimyogar');
        });
    }

    public function test_notification_job_respects_quiet_hours_for_non_security_messages(): void
    {
        Http::fake([
            'https://api.telegram.org/bot*' => Http::response(['ok' => true, 'result' => ['message_id' => 100]], 200),
        ]);

        // Toshkent vaqti bilan 23:30 (jim soatlar ichida)
        Carbon::setTestNow(Carbon::create(2026, 10, 24, 23, 30, 0, 'Asia/Tashkent'));

        $user = User::factory()->create([
            'telegram_id'        => 555009,
            'notification_prefs' => [
                'streak_reminder' => true,
                'quiet_hours'     => true,
            ],
        ]);

        // Oddiy bildirishnoma jim soatlarda yuborilmasligi kerak
        $job = new SendTelegramNotificationJob($user->id, 'Tungi eslatma', [], 'streak_reminder');
        $job->handle(new TelegramClient());
        Http::assertNothingSent();

        // Xavfsizlik (security) xabari esa jim soatlarda ham yuborilishi shart
        $secJob = new SendTelegramNotificationJob($user->id, 'Xavfsizlik kodi: 1234', [], 'security');
        $secJob->handle(new TelegramClient());
        Http::assertSentCount(1);

        Carbon::setTestNow();
    }
}
