<?php

namespace App\Http\Livewire\Notifications;

use App\Models\DailyActivity;
use App\Models\LiveEvent;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class NotificationList extends Component
{
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

    public function render()
    {
        $user = Auth::user();

        $notifications = $user->notifications()
            ->take(30)
            ->get()
            ->map(function ($n) {
                return [
                    'id'        => $n->id,
                    'type'      => data_get($n->data, 'type', 'info'),
                    'title'     => data_get($n->data, 'title', 'Bildirishnoma'),
                    'body'      => data_get($n->data, 'body', ''),
                    'icon'      => data_get($n->data, 'icon', '🔔'),
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

        // Upcoming live event (real data)
        $upcomingLive = LiveEvent::whereIn('status', ['scheduled', 'live'])
            ->orderBy('scheduled_at')
            ->first();

        return view('livewire.notifications.notification-list', [
            'notifications' => $notifications,
            'unreadCount'   => $user->unreadNotifications->count(),
            'streakAlert'   => $streakAlert,
            'upcomingLive'  => $upcomingLive,
        ])->layout('layouts.app', ['title' => 'Bildirishnomalar']);
    }
}
