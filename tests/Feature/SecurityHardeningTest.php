<?php

namespace Tests\Feature;

use App\Http\Livewire\Live\LiveChatPanel;
use App\Http\Livewire\Quiz\TakeQuiz;
use App\Models\Book;
use App\Models\BookChapter;
use App\Models\LiveEvent;
use App\Models\Quiz;
use App\Models\QuizOption;
use App\Models\QuizQuestion;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $prefix = 'sec'): User
    {
        return User::create([
            'name'     => 'Sec User',
            'username' => $prefix . '_' . uniqid(),
            'email'    => $prefix . '_' . uniqid() . '@test.uz',
            'password' => bcrypt('password'),
        ]);
    }

    private function makeBook(array $attributes = []): Book
    {
        return Book::create(array_merge([
            'title'       => 'Xavfsizlik kitobi',
            'slug'        => 'xavfsizlik-' . uniqid(),
            'author'      => 'Muallif',
            'description' => 'Test',
            'genre'       => 'Adabiyot',
            'week_number' => random_int(1, 99999),
            'is_active'   => true,
        ], $attributes));
    }

    private function makeLiveEvent(User $host): LiveEvent
    {
        return LiveEvent::create([
            'title'           => 'Xavfsiz efir',
            'host_user_id'    => $host->id,
            'status'          => LiveEvent::STATUS_LIVE,
            'permission_mode' => 'both',
            'scheduled_at'    => now(),
            'started_at'      => now(),
        ]);
    }

    public function test_storage_fallback_blocks_path_traversal(): void
    {
        $this->get('/storage/..%2F..%2F..%2F.env')->assertNotFound();
        $this->get('/storage/../../../composer.json')->assertNotFound();
    }

    public function test_setup_endpoint_is_disabled_without_configured_token(): void
    {
        config(['app.setup_token' => null]);

        $this->get('/system/setup?token=kitobxon2026')->assertNotFound();
        $this->get('/system/setup?token=' . config('app.key'))->assertNotFound();
    }

    public function test_setup_endpoint_rejects_wrong_token(): void
    {
        config(['app.setup_token' => 'a-very-long-setup-token-123']);

        $this->get('/system/setup?token=wrong')->assertNotFound();
    }

    public function test_expired_ban_cron_does_not_leak_user_data(): void
    {
        $banned = $this->makeUser('banned');
        $banned->forceFill(['is_banned' => true, 'banned_until' => now()->addDay()])->save();

        $response = $this->getJson('/cron/process-expired-bans')->assertOk();

        $response->assertJsonMissing(['username' => $banned->username]);
        $this->assertSame(1, $response->json('remaining_count'));
    }

    public function test_plain_contact_message_is_saved_without_error(): void
    {
        $this->post('/contact', [
            'name'    => 'Mehmon',
            'phone'   => '+998901234567',
            'subject' => 'Savol',
            'message' => 'Salom, saytingiz haqida savolim bor.',
        ])->assertRedirect();

        $this->assertDatabaseHas('reports', ['source' => 'Aloqa bo\'limi']);
        $this->assertStringContainsString('Mehmon', Report::latest('id')->first()->message_content);
    }

    public function test_viewer_cannot_impersonate_host_in_live_signals(): void
    {
        $host = $this->makeUser('host');
        $viewer = $this->makeUser('viewer');
        $event = $this->makeLiveEvent($host);

        // Anonim yoki oddiy tomoshabin host nomidan offer yubora olmaydi
        $this->postJson(route('live.signal.send', $event), [
            'sender_id' => 'host_' . $host->id . '_abc', 'receiver_id' => 'all', 'type' => 'stream-status', 'payload' => [],
        ])->assertForbidden();

        $this->actingAs($viewer)->postJson(route('live.signal.send', $event), [
            'sender_id' => 'viewer_' . $viewer->id . '_abc', 'receiver_id' => 'viewer_x', 'type' => 'offer', 'payload' => [],
        ])->assertForbidden();

        // Host peer sifatida poll qilib, tomoshabinlar signallarini o'qiy olmaydi
        $this->actingAs($viewer)->getJson(route('live.signal.poll', [
            'event' => $event, 'peer_id' => 'host_' . $viewer->id . '_abc',
        ]))->assertForbidden();

        // Tomoshabinning oddiy join signali ishlaydi
        $this->actingAs($viewer)->postJson(route('live.signal.send', $event), [
            'sender_id' => 'viewer_' . $viewer->id . '_abc', 'receiver_id' => 'host:*', 'type' => 'join', 'payload' => [],
        ])->assertOk();
    }

    public function test_viewer_cannot_become_live_chat_host(): void
    {
        $host = $this->makeUser('host');
        $viewer = $this->makeUser('viewer');
        $event = $this->makeLiveEvent($host);

        $this->actingAs($viewer);

        Livewire::test(LiveChatPanel::class, ['event' => $event, 'isHost' => true])
            ->assertSet('isHost', false)
            ->set('isHost', true)
            ->assertForbidden();
    }

    public function test_quiz_score_fields_cannot_be_tampered(): void
    {
        $book = $this->makeBook();
        $quiz = Quiz::create([
            'book_id' => $book->id, 'title' => 'Test', 'difficulty' => 'medium',
            'reward_points' => 50, 'time_limit_minutes' => 15, 'is_active' => true,
        ]);
        $question = QuizQuestion::create(['quiz_id' => $quiz->id, 'question_text' => 'Savol', 'type' => 'single', 'order' => 1]);
        QuizOption::create(['question_id' => $question->id, 'option_text' => 'Javob', 'is_correct' => true, 'order' => 1]);

        $this->actingAs($this->makeUser());
        $component = Livewire::test(TakeQuiz::class, ['book' => $book]);

        foreach (['correctCount' => 999, 'maxRewardPoints' => 100000, 'startedAt' => now()->addDay()->timestamp] as $field => $value) {
            $component->set($field, $value)->assertForbidden();
        }

        $this->assertSame(0, $component->get('correctCount'));
        $this->assertSame(50, $component->get('maxRewardPoints'));
    }

    public function test_inactive_book_chapters_are_hidden_from_students(): void
    {
        $book = $this->makeBook(['is_active' => false]);
        $chapter = BookChapter::create([
            'book_id' => $book->id, 'chapter_number' => 1, 'title' => 'Bob', 'content' => 'Matn', 'is_published' => true,
        ]);

        $this->actingAs($this->makeUser())
            ->get(route('reader.show', ['book' => $book->id, 'chapter' => $chapter->id]))
            ->assertNotFound();

        $this->getJson(route('books.audios.json', $book->id))->assertNotFound();
    }

    public function test_hourly_reading_bonus_can_only_be_claimed_once(): void
    {
        $user = $this->makeUser();
        $activity = \App\Models\DailyActivity::getTodayActivity($user->id);
        $activity->update(['minutes_read' => 61]);

        $this->actingAs($user)->postJson(route('reading.claim-hourly-bonus'))->assertOk();
        $this->actingAs($user)->postJson(route('reading.claim-hourly-bonus'))->assertStatus(422);

        $this->assertSame(40, (int) $user->fresh()->coin_balance);
    }
}
