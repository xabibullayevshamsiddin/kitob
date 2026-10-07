<?php

namespace Tests\Feature;

use App\Http\Livewire\Notifications\NotificationList;
use App\Models\CoinTransaction;
use App\Models\PointTransaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class EarlyBirdBonusTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name'         => 'Early Bird User',
            'username'     => 'earlybird_' . uniqid(),
            'email'        => 'earlybird_' . uniqid() . '@kitobxon.uz',
            'password'     => bcrypt('password'),
            'total_points' => 0,
            'coin_balance' => 0,
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow(); // Vaqt testini bekor qilish
        parent::tearDown();
    }

    /** @test */
    public function claim_is_rejected_outside_morning_window(): void
    {
        // 10:00 — oyna yopiq
        Carbon::setTestNow(Carbon::create(2026, 10, 7, 10, 0, 0, 'Asia/Tashkent'));

        Livewire::actingAs($this->user)
            ->test(NotificationList::class)
            ->call('claimEarlyBird')
            ->assertHasNoErrors();

        $this->user->refresh();
        $this->assertEquals(0, $this->user->total_points);
        $this->assertEquals(0, $this->user->coin_balance);
        $this->assertEquals(0, PointTransaction::where('user_id', $this->user->id)->count());
    }

    /** @test */
    public function claim_works_at_5am_and_6am_within_window(): void
    {
        // 05:00 — oyna ochiq
        Carbon::setTestNow(Carbon::create(2026, 10, 7, 5, 0, 0, 'Asia/Tashkent'));

        Livewire::actingAs($this->user)
            ->test(NotificationList::class)
            ->call('claimEarlyBird')
            ->assertHasNoErrors();

        $this->user->refresh();
        $this->assertEquals(70, $this->user->total_points);
        $this->assertEquals(10, $this->user->coin_balance);

        $this->assertDatabaseHas('point_transactions', [
            'user_id' => $this->user->id,
            'points'  => 70,
            'source'  => 'bonus',
        ]);
        $this->assertDatabaseHas('coin_transactions', [
            'user_id' => $this->user->id,
            'coins'   => 10,
            'source'  => 'bonus',
        ]);
        $this->assertDatabaseHas('daily_activities', [
            'user_id'             => $this->user->id,
            'early_bird_claimed'  => true,
        ]);

        // 06:30 — baribir oyna ichida, ikkinchi marta berilmasligi kerak
        Carbon::setTestNow(Carbon::create(2026, 10, 7, 6, 30, 0, 'Asia/Tashkent'));

        Livewire::actingAs($this->user)
            ->test(NotificationList::class)
            ->call('claimEarlyBird')
            ->assertHasNoErrors();

        $this->user->refresh();
        $this->assertEquals(70, $this->user->total_points); // o'zgarmadi
        $this->assertEquals(10, $this->user->coin_balance);  // o'zgarmadi
    }

    /** @test */
    public function claim_is_rejected_at_7am_sharp(): void
    {
        // Birinchi marta 06:59 da olamiz
        Carbon::setTestNow(Carbon::create(2026, 10, 7, 6, 59, 0, 'Asia/Tashkent'));

        Livewire::actingAs($this->user)
            ->test(NotificationList::class)
            ->call('claimEarlyBird');

        $this->user->refresh();
        $this->assertEquals(70, $this->user->total_points);

        // Ertaga 07:00 aniq — oyna yopiq (07:00 kirmaydi)
        Carbon::setTestNow(Carbon::create(2026, 10, 8, 7, 0, 0, 'Asia/Tashkent'));

        Livewire::actingAs($this->user)
            ->test(NotificationList::class)
            ->call('claimEarlyBird')
            ->assertHasNoErrors();

        $this->user->refresh();
        $this->assertEquals(70, $this->user->total_points); // o'zgarmadi
        $this->assertEquals(0, CoinTransaction::where('user_id', $this->user->id)->whereDate('created_at', '2026-10-08')->count());
    }

    /** @test */
    public function fresh_claim_next_day_is_allowed(): void
    {
        // Bugun olamiz
        Carbon::setTestNow(Carbon::create(2026, 10, 7, 5, 30, 0, 'Asia/Tashkent'));

        Livewire::actingAs($this->user)
            ->test(NotificationList::class)
            ->call('claimEarlyBird');

        $this->user->refresh();
        $this->assertEquals(70, $this->user->total_points);

        // Ertaga yana olish mumkin
        Carbon::setTestNow(Carbon::create(2026, 10, 8, 5, 30, 0, 'Asia/Tashkent'));

        Livewire::actingAs($this->user)
            ->test(NotificationList::class)
            ->call('claimEarlyBird');

        $this->user->refresh();
        $this->assertEquals(140, $this->user->total_points);
        $this->assertEquals(20, $this->user->coin_balance);
    }
}
