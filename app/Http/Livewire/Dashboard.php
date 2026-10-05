<?php

namespace App\Http\Livewire;

use App\Http\Livewire\Concerns\WithToast;
use App\Models\Book;
use App\Models\DailyQuote;
use App\Models\LiveEvent;
use App\Models\User;
use App\Models\UserQuote;
use App\Models\BookReadingProgress;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Dashboard extends Component
{
    use WithToast;

    public $likedQuotes = [];
    public $savedQuotes = [];

    public function mount()
    {
        $user = Auth::user();
        if ($user) {
            $this->likedQuotes = UserQuote::where('user_id', $user->id)
                ->where('liked', true)
                ->pluck('quote_id')
                ->toArray();

            $this->savedQuotes = UserQuote::where('user_id', $user->id)
                ->where('saved', true)
                ->pluck('quote_id')
                ->toArray();
        }
    }

    public function toggleQuoteLike($quoteId)
    {
        $userId    = Auth::id();
        $userQuote = UserQuote::firstOrCreate(
            ['user_id' => $userId, 'quote_id' => $quoteId],
            ['liked'   => false, 'saved' => false]
        );

        $userQuote->liked = !$userQuote->liked;
        $userQuote->save();

        if ($userQuote->liked) {
            $this->likedQuotes[] = $quoteId;
            $this->toastSuccess("Hikmatli so'z yoqdi! ❤️");
        } else {
            $this->likedQuotes = array_diff($this->likedQuotes, [$quoteId]);
            $this->toastInfo("Yoqtirish bekor qilindi.");
        }
    }

    public function toggleQuoteSave($quoteId)
    {
        $userId    = Auth::id();
        $userQuote = UserQuote::firstOrCreate(
            ['user_id' => $userId, 'quote_id' => $quoteId],
            ['liked'   => false, 'saved' => false]
        );

        $userQuote->saved = !$userQuote->saved;
        $userQuote->save();

        if ($userQuote->saved) {
            $this->savedQuotes[] = $quoteId;
            $this->toastSuccess("Hikmatli so'z saqlandi! ✨");
        } else {
            $this->savedQuotes = array_diff($this->savedQuotes, [$quoteId]);
            $this->toastInfo("Hikmatli so'z saqlanganlardan olib tashlandi.");
        }
    }

    public function render()
    {
        $user = Auth::user();

        // Student dashboard data
        $featuredBook = Book::where('is_active', true)
            ->orderBy('week_number', 'desc')
            ->first();

        $userProgress = null;
        if ($user && $featuredBook) {
            $userProgress = BookReadingProgress::where('user_id', $user->id)
                ->where('book_id', $featuredBook->id)
                ->latest()
                ->first();
        }

        $todayQuote = DailyQuote::with('book')
            ->whereDate('send_date', '<=', today('Asia/Tashkent'))
            ->latest('send_date')
            ->first();

        $upcomingLive = null;
        if (class_exists(LiveEvent::class)) {
            // ENG YANGI efirni ko'rsatamiz (eski navbatdagi emas)
            $upcomingLive = LiveEvent::with('book')
                ->whereIn('status', ['scheduled', 'live'])
                // Eng yangi efir ustunlik bilan ko'rsatiladi
                ->orderByRaw("CASE WHEN status = 'live' THEN 0 ELSE 1 END")
                ->orderByDesc('created_at')
                ->first();
        }

        $topUsers = User::orderBy('total_points', 'desc')
            ->take(5)
            ->get();

        return view('livewire.dashboard', [
            'featuredBook' => $featuredBook,
            'userProgress' => $userProgress,
            'todayQuote'   => $todayQuote,
            'upcomingLive' => $upcomingLive,
            'topUsers'     => $topUsers,
            'user'         => $user,
        ])->layout('layouts.dashboard', ['title' => 'Boshqaruv paneli']);
    }
}
