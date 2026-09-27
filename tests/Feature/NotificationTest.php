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
}
