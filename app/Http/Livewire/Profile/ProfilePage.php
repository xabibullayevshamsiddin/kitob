<?php

namespace App\Http\Livewire\Profile;

use App\Models\User;
use App\Models\BookReadingProgress;
use App\Models\DailyActivity;
use App\Models\Follow;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ProfilePage extends Component
{
    public User $user;
    public string $activeTab = 'overview';
    public bool $isFollowing = false;

    public function mount(string $username)
    {
        $this->user = User::with(['profile', 'streak', 'badges'])
            ->where('username', $username)
            ->firstOrFail();

        if (Auth::check()) {
            $this->isFollowing = Follow::where('follower_id', Auth::id())
                ->where('following_id', $this->user->id)
                ->exists();
        }
    }

    public function toggleFollow()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $authId = Auth::id();
        if ($authId === $this->user->id) {
            return;
        }

        $follow = Follow::where('follower_id', $authId)
            ->where('following_id', $this->user->id)
            ->first();

        if ($follow) {
            $follow->delete();
            $this->isFollowing = false;
        } else {
            Follow::create([
                'follower_id' => $authId,
                'following_id' => $this->user->id,
            ]);
            $this->isFollowing = true;
        }
    }

    public function setTab(string $tab)
    {
        $this->activeTab = $tab;
    }

    public function render()
    {
        $readingProgresses = BookReadingProgress::with(['book', 'chapter'])
            ->where('user_id', $this->user->id)
            ->latest('updated_at')
            ->take(10)
            ->get();

        $activities = DailyActivity::where('user_id', $this->user->id)
            ->where('activity_date', '>=', now()->subDays(60)->toDateString())
            ->pluck('minutes_read', 'activity_date')
            ->toArray();

        $badges = $this->user->badges;

        $notes = $this->user->notes()->with(['book', 'chapter'])->latest()->take(10)->get();

        return view('livewire.profile.profile-page', [
            'readingProgresses' => $readingProgresses,
            'activities' => $activities,
            'badges' => $badges,
            'notes' => $notes,
            'isOwner' => Auth::id() === $this->user->id,
        ])->layout('layouts.app', ['title' => $this->user->name . ' (@' . $this->user->username . ')']);
    }
}
