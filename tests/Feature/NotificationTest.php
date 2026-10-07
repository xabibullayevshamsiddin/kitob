<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\NotifyUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_notify_user_creates_database_notification(): void
    {
        $user = User::create([
            'name'     => 'Notif User',
            'username' => 'notif_' . uniqid(),
            'email'    => 'notif_' . uniqid() . '@kitobxon.uz',
            'password' => bcrypt('password'),
        ]);

        NotifyUser::send($user, 'badge', 'Yangi nishon!', 'Test nishoni', '🏅', '/leaderboard');

        $this->assertEquals(1, $user->fresh()->unreadNotifications->count());

        $n = $user->unreadNotifications->first();
        $this->assertEquals('Yangi nishon!', $n->data['title']);
        $this->assertEquals('badge', $n->data['type']);
        $this->assertEquals('🏅', $n->data['icon']);
        $this->assertEquals('/leaderboard', $n->data['link']);

        $user->delete();
    }

    public function test_notifications_page_shows_notification(): void
    {
        $user = User::create([
            'name'     => 'Notif Page User',
            'username' => 'notifpage_' . uniqid(),
            'email'    => 'notifpage_' . uniqid() . '@kitobxon.uz',
            'password' => bcrypt('password'),
        ]);

        NotifyUser::send($user, 'live', '🔴 Jonli efir boshlandi!', 'Test efiri', '📺', '/live');

        $response = $this->actingAs($user)->get('/notifications');
        $response->assertOk();
        $response->assertSee('🔴 Jonli efir boshlandi!');
        $response->assertSee("ta o'qilmagan", false);

        $user->delete();
    }

    public function test_mark_read_and_open_notification(): void
    {
        $user = User::create([
            'name'     => 'Notif Open User',
            'username' => 'notifopen_' . uniqid(),
            'email'    => 'notifopen_' . uniqid() . '@kitobxon.uz',
            'password' => bcrypt('password'),
        ]);

        NotifyUser::send($user, 'quiz', 'Test yakunlandi', '10/10 ball', '📝', '/books/atom-odatlar');

        $notificationId = $user->unreadNotifications->first()->id;

        \Livewire\Livewire::actingAs($user)
            ->test(\App\Http\Livewire\Notifications\NotificationList::class)
            ->call('openNotification', $notificationId, '/books/atom-odatlar');

        $this->assertEquals(0, $user->fresh()->unreadNotifications->count());

        $user->delete();
    }

    public function test_user_cannot_claim_hourly_bonus_before_60_minutes(): void
    {
        $user = User::create([
            'name'     => 'Hourly Test User',
            'username' => 'hourly_' . uniqid(),
            'email'    => 'hourly_' . uniqid() . '@kitobxon.uz',
            'password' => bcrypt('password'),
            'total_points' => 0,
            'coin_balance' => 0,
        ]);

        $today = now('Asia/Tashkent')->toDateString();
        \App\Models\DailyActivity::create([
            'user_id' => $user->id,
            'activity_date' => $today,
            'minutes_read' => 45,
            'logged_in' => true,
            'points_earned' => 0,
            'hourly_bonus_claimed' => false,
        ]);

        \Livewire\Livewire::actingAs($user)
            ->test(\App\Http\Livewire\Notifications\NotificationList::class)
            ->call('claimHourlyBonus');

        $this->assertEquals(0, $user->fresh()->total_points);
        $this->assertEquals(0, $user->fresh()->coin_balance);
        $this->assertFalse((bool) \App\Models\DailyActivity::where('user_id', $user->id)->where('activity_date', $today)->value('hourly_bonus_claimed'));

        $user->delete();
    }

    public function test_user_can_claim_hourly_bonus_at_60_minutes_once_per_day(): void
    {
        $user = User::create([
            'name'     => 'Hourly Winner',
            'username' => 'hourlywin_' . uniqid(),
            'email'    => 'hourlywin_' . uniqid() . '@kitobxon.uz',
            'password' => bcrypt('password'),
            'total_points' => 50,
            'coin_balance' => 10,
        ]);

        $today = now('Asia/Tashkent')->toDateString();
        $activity = \App\Models\DailyActivity::create([
            'user_id' => $user->id,
            'activity_date' => $today,
            'minutes_read' => 65,
            'logged_in' => true,
            'points_earned' => 0,
            'hourly_bonus_claimed' => false,
        ]);

        // 1-marta olish
        \Livewire\Livewire::actingAs($user)
            ->test(\App\Http\Livewire\Notifications\NotificationList::class)
            ->call('claimHourlyBonus')
            ->assertDispatchedBrowserEvent('points-awarded')
            ->assertDispatchedBrowserEvent('coins-awarded')
            ->assertDispatchedBrowserEvent('bonus-claimed-animation');

        $user->refresh();
        $this->assertEquals(250, $user->total_points); // 50 + 200
        $this->assertEquals(50, $user->coin_balance);  // 10 + 40
        $this->assertTrue((bool) $activity->fresh()->hourly_bonus_claimed);

        // 2-marta olishga urinish (1 kunda faqat 1 marta berilishi shart)
        \Livewire\Livewire::actingAs($user)
            ->test(\App\Http\Livewire\Notifications\NotificationList::class)
            ->call('claimHourlyBonus');

        $user->refresh();
        $this->assertEquals(250, $user->total_points, 'Takroriy bonus berilmasligi kerak');
        $this->assertEquals(50, $user->coin_balance, 'Takroriy tanga berilmasligi kerak');

        $user->delete();
    }

    public function test_claim_hourly_bonus_api_endpoint(): void
    {
        $user = User::create([
            'name'     => 'API Hourly User',
            'username' => 'apihourly_' . uniqid(),
            'email'    => 'apihourly_' . uniqid() . '@kitobxon.uz',
            'password' => bcrypt('password'),
            'total_points' => 100,
            'coin_balance' => 20,
        ]);

        $today = now('Asia/Tashkent')->toDateString();
        $activity = \App\Models\DailyActivity::create([
            'user_id' => $user->id,
            'activity_date' => $today,
            'minutes_read' => 60,
            'logged_in' => true,
            'points_earned' => 0,
            'hourly_bonus_claimed' => false,
        ]);

        // API orqali olish
        $response = $this->actingAs($user)->postJson('/api/reading/claim-hourly-bonus');
        $response->assertOk()
            ->assertJson([
                'success' => true,
                'points_added' => 200,
                'coins_added' => 40,
            ]);

        $this->assertEquals(300, $user->fresh()->total_points);
        $this->assertEquals(60, $user->fresh()->coin_balance);
        $this->assertTrue((bool) $activity->fresh()->hourly_bonus_claimed);

        // Takroriy so'rov 422 qaytarishi kerak
        $retryResponse = $this->actingAs($user)->postJson('/api/reading/claim-hourly-bonus');
        $retryResponse->assertStatus(422)
            ->assertJson(['success' => false]);

        $user->delete();
    }

    public function test_notifications_automatically_pruned_to_twenty_when_new_one_arrives(): void
    {
        $user = User::create([
            'name'     => 'Prune Test User',
            'username' => 'prune_' . uniqid(),
            'email'    => 'prune_' . uniqid() . '@kitobxon.uz',
            'password' => bcrypt('password'),
        ]);

        // 20 ta bildirishnoma yuborish (vaqt oralig'i bilan)
        for ($i = 1; $i <= 20; $i++) {
            $this->travel(1)->seconds();
            NotifyUser::send($user, 'info', "Xabar #{$i}", "Matn #{$i}", '🔔');
        }

        $this->assertEquals(20, $user->notifications()->count());
        $firstNotification = $user->notifications()->reorder('created_at', 'asc')->first();
        $this->assertEquals('Xabar #1', $firstNotification->data['title']);

        // 21-chi bildirishnomani yuborish (eng yangisi)
        $this->travel(1)->seconds();
        NotifyUser::send($user, 'info', 'Xabar #21', 'Matn #21', '🔔');

        // Jami soni baribir 20 tadan oshmasligi kerak (eng eskisi o'chib ketadi)
        $this->assertEquals(20, $user->fresh()->notifications()->count());

        // Eng birinchi #1 xabar o'chirilgan bo'lishi kerak
        $this->assertDatabaseMissing('notifications', ['id' => $firstNotification->id]);

        // Yangi 21-chi xabar saqlangan bo'lishi kerak
        $latest = $user->notifications()->latest()->first();
        $this->assertEquals('Xabar #21', $latest->data['title']);

        $user->delete();
    }

    public function test_prune_notifications_artisan_command(): void
    {
        $user = User::create([
            'name'     => 'Artisan Prune User',
            'username' => 'artprune_' . uniqid(),
            'email'    => 'artprune_' . uniqid() . '@kitobxon.uz',
            'password' => bcrypt('password'),
        ]);

        // 25 ta bildirishnoma yaratamiz
        for ($i = 1; $i <= 25; $i++) {
            $user->notifications()->create([
                'id' => (string) \Illuminate\Support\Str::uuid(),
                'type' => 'App\\Notifications\\GenericNotification',
                'data' => json_encode(['title' => "Eski xabar #{$i}"]),
                'created_at' => now()->subMinutes(30 - $i),
                'updated_at' => now()->subMinutes(30 - $i),
            ]);
        }

        $this->assertEquals(25, $user->notifications()->count());

        // Artisan komandani ishga tushirish
        \Illuminate\Support\Facades\Artisan::call('notifications:prune', ['--keep' => 20]);

        $this->assertEquals(20, $user->fresh()->notifications()->count());

        $user->delete();
    }

    public function test_user_can_delete_single_notification_and_clear_all(): void
    {
        $user = User::create([
            'name'     => 'Delete Notif User',
            'username' => 'delnotif_' . uniqid(),
            'email'    => 'delnotif_' . uniqid() . '@kitobxon.uz',
            'password' => bcrypt('password'),
        ]);

        NotifyUser::send($user, 'info', 'Xabar 1', 'Matn 1');
        NotifyUser::send($user, 'info', 'Xabar 2', 'Matn 2');
        NotifyUser::send($user, 'info', 'Xabar 3', 'Matn 3');

        $this->assertEquals(3, $user->notifications()->count());

        $firstId = $user->notifications()->first()->id;

        // Bitta bildirishnomani o'chirish
        \Livewire\Livewire::actingAs($user)
            ->test(\App\Http\Livewire\Notifications\NotificationList::class)
            ->call('deleteNotification', $firstId);

        $this->assertEquals(2, $user->fresh()->notifications()->count());
        $this->assertDatabaseMissing('notifications', ['id' => $firstId]);

        // Barchasini tozalash
        \Livewire\Livewire::actingAs($user)
            ->test(\App\Http\Livewire\Notifications\NotificationList::class)
            ->call('clearAll');

        $this->assertEquals(0, $user->fresh()->notifications()->count());

        $user->delete();
    }
}

