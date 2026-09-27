<?php

namespace App\Http\Livewire\Notifications;

use App\Models\DailyActivity;
use App\Models\LiveEvent;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class NotificationList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';
    public function markAllRead(): void
    {
        Auth::user()->unreadNotifications->markAsRead();
    }

    public function markRead(string $id): void
    {
        $notification = Auth::user()->notifications()->where('id', $id)->first();
        if ($notification) {
            $notification->markAsRead();
        }
    }

    /** Bildirishnoma havolasiga o'tish (o'qilgan deb belgilab) */
    public function openNotification(string $id, string $link): void
    {
        $this->markRead($id);

        if ($link) {
            // url() — loyiha base path'ini (/Kitob/public) hisobga oladi
            $this->redirect(url($link));
        }
    }

    public function render()
    {
        $user = Auth::user();

        $notifications = $user->notifications()
            ->latest()
            ->paginate(10)
            ->through(function ($n) {
                return [
                    'id'        => $n->id,
                    'type'      => data_get($n->data, 'type', 'info'),
                    'title'     => data_get($n->data, 'title', 'Bildirishnoma'),
                    'body'      => data_get($n->data, 'body', ''),
                    'icon'      => data_get($n->data, 'icon', '🔔'),
                    'link'      => data_get($n->data, 'link', ''),
                    'read_at'   => $n->read_at,
                    'time'      => $n->created_at->timezone('Asia/Tashkent')->diffForHumans(),
                ];
            });

        // Smart streak reminder (real data): streak at risk today
        $todayMinutes = (int) DailyActivity::where('user_id', $user->id)
            ->where('activity_date', now('Asia/Tashkent')->toDateString())
            ->value('minutes_read');

        $streak = $user->streak;

        $streakAlert = ($streak && $streak->current_streak > 0 && $todayMinutes < 10)
            ? [
                'title'  => 'Streak xavf ostida! 🔥',
                'body'   => 'Bugun hali ' . max(0, 10 - $todayMinutes) . ' daqiqa o\'qish kerak. ' . $streak->current_streak . ' kunlik ketma-ketlikni saqlab qoling!',
            ]
            : null;

        // Upcoming live event (real data) — eng yangisi ustunlik bilan
        $upcomingLive = LiveEvent::whereIn('status', ['scheduled', 'live'])
            ->orderByRaw("CASE WHEN status = 'live' THEN 0 ELSE 1 END")
            ->orderByDesc('created_at')
            ->first();

        return view('livewire.notifications.notification-list', [
            'notifications' => $notifications,
            'unreadCount'   => $user->unreadNotifications->count(),
            'streakAlert'   => $streakAlert,
            'upcomingLive'  => $upcomingLive,
        ])->layout('layouts.app', ['title' => 'Bildirishnomalar']);
    }
}
