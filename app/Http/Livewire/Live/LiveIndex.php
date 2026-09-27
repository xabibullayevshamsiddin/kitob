<?php

namespace App\Http\Livewire\Live;

use App\Models\LiveEvent;
use App\Models\LiveQuestion;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class LiveIndex extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public string $question = '';
    public ?int $questionEventId = null;

    // Stream Studio Modal
    public bool $showStudioModal = false;
    public string $newTitle = '';
    public string $newDescription = '';
    public ?int $newBookId = null;
    public string $newPermissionMode = 'both'; // both, chat_only, voice_only, view_only

    protected $rules = [
        'question' => 'required|string|min:5|max:300',
    ];

    public function openStudioModal(): void
    {
        if (!Auth::check()) {
            redirect()->route('login')->with('warning', 'Jonli efir boshlash uchun tizimga kiring.');
            return;
        }

        $this->newTitle = '';
        $this->newDescription = '';
        $this->newBookId = null;
        $this->newPermissionMode = 'both';
        $this->showStudioModal = true;
    }

    public function closeStudioModal(): void
    {
        $this->showStudioModal = false;
    }

    public function startLiveStream()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $this->validate([
            'newTitle'          => 'required|string|min:3|max:150',
            'newDescription'    => 'nullable|string|max:1000',
            'newBookId'         => 'nullable|exists:books,id',
            'newPermissionMode' => 'required|in:both,chat_only,voice_only,view_only',
        ], [
            'newTitle.required' => 'Efir mavzusini kiriting.',
            'newTitle.min'      => 'Efir mavzusi kamida 3 belgidan iborat bo\'lsin.',
        ]);

        $event = LiveEvent::create([
            'title'           => trim($this->newTitle),
            'description'     => trim($this->newDescription),
            'book_id'         => $this->newBookId,
            'host_user_id'    => Auth::id(),
            'permission_mode' => $this->newPermissionMode,
            'status'          => LiveEvent::STATUS_LIVE,
            'scheduled_at'    => now(),
            'started_at'      => now(),
        ]);

        $this->showStudioModal = false;

        // Yangi efir — eski signallarni tozalash (eski offer/answer yangi ulanishni buzmasin)
        \App\Models\LiveSignal::where('live_event_id', '!=', $event->id)->delete();

        // Barcha boshqa foydalanuvchilarga "jonli efir boshlandi" bildirishnomasi
        \App\Models\User::where('id', '!=', Auth::id())
            ->whereNull('deleted_at')
            ->get()
            ->each(function (\App\Models\User $u) use ($event) {
                \App\Services\NotifyUser::send(
                    $u,
                    'live',
                    '🔴 Jonli efir boshlandi!',
                    '"' . $event->title . '" — ustoz ' . Auth::user()->name . ' efirda. Hoziroq qoshiling!',
                    '📺',
                    '/live/' . $event->id
                );
            });

        return redirect()->route('live.show', $event)
            ->with('success', 'Jonli efir boshlandi! Mikrofon va kamerangizni sozlang.');
    }

    /**
     * Admin/teacher: ro'yxatdan to'g'ridan-to'g'ri efirni tugatish.
     */
    public function endEvent(int $eventId): void
    {
        if (!Auth::check() || !Auth::user()->isAdminOrTeacher()) {
            session()->flash('error', "Faqat admin yoki ustoz efirni tugatishi mumkin.");
            return;
        }

        $event = LiveEvent::find($eventId);

        if (!$event) {
            session()->flash('error', 'Efir topilmadi.');
            return;
        }

        $title = $event->title;
        $event->questions()->delete();
        $event->delete();

        session()->flash('success', '"' . $title . '" efiri tugatildi va saytdan o\'chirildi.');
    }

    /**
     * Admin/teacher: HAMMA jonli (va navbatdagi) efilrlarni birdan tugatish.
     */
    public function endAllLiveStreams(): void
    {
        if (!Auth::check() || !Auth::user()->isAdminOrTeacher()) {
            session()->flash('error', "Faqat admin yoki ustoz efilrlarni tugatishi mumkin.");
            return;
        }

        $events = LiveEvent::whereIn('status', [LiveEvent::STATUS_LIVE, LiveEvent::STATUS_SCHEDULED])->get();

        if ($events->isEmpty()) {
            session()->flash('error', 'Tugatish uchun faol efir yo\'q.');
            return;
        }

        $count = $events->count();

        foreach ($events as $event) {
            $event->questions()->delete();
            $event->signals()->delete();
            $event->delete();
        }

        session()->flash('success', $count . ' ta jonli efir birdan tugatildi va saytdan o\'chirildi.');
    }

    public function submitQuestion(): void
    {
        $this->validate([
            'question' => 'required|string|min:5|max:300',
        ], [
            'question.required' => 'Savolingizni yozing.',
            'question.min'      => 'Savol kamida 5 belgidan iborat bo\'lsin.',
        ]);

        $event = LiveEvent::whereIn('status', [LiveEvent::STATUS_SCHEDULED, LiveEvent::STATUS_LIVE])
            ->orderByRaw("CASE WHEN status = 'live' THEN 0 ELSE 1 END")
            ->orderBy('scheduled_at')
            ->first();

        if (!$event) {
            session()->flash('error', 'Hozircha rejalashtirilgan efir yo\'q — savol yuborib bo\'lmaydi.');
            $this->reset('question');
            return;
        }

        LiveQuestion::create([
            'live_event_id' => $event->id,
            'user_id'       => Auth::id(),
            'question'      => trim($this->question),
        ]);

        $this->reset('question');

        session()->flash('success', 'Savolingiz yuborildi! Muallif efirda javob berishi mumkin. 🎤');
    }

    public function render()
    {
        // Foydalanuvchi talabi: o'tib ketgan / yakunlangan efirlar saytda saqlanib qolmasin
        LiveEvent::where('status', LiveEvent::STATUS_ENDED)
            ->orWhere(function ($q) {
                $q->where('status', LiveEvent::STATUS_SCHEDULED)
                  ->where('scheduled_at', '<', now()->subHours(6));
            })
            ->delete();

        // Bir kundan ortiq "live" holatda qotib qolgan (tugatilmagan) efirlarni ham tozalaymiz
        LiveEvent::where('status', LiveEvent::STATUS_LIVE)
            ->where('started_at', '<', now()->subDay())
            ->get()
            ->each(function ($e) {
                // HasMany::delete() bool qaytaradi, shuning uchun zanjirlab chaqirib bo'lmaydi
                $e->questions()->delete();
                $e->signals()->delete();
                $e->delete();
            });

        $upcoming = LiveEvent::with(['book', 'hostUser'])
            ->whereIn('status', [LiveEvent::STATUS_SCHEDULED, LiveEvent::STATUS_LIVE])
            ->orderByRaw("CASE WHEN status = 'live' THEN 0 ELSE 1 END")
            // ENG YANGI efir birinchi (tepada) ko'rinadi — foydalanuvchi talabi
            ->orderByDesc('created_at')
            ->paginate(4);

        $myQuestions = LiveQuestion::where('user_id', Auth::id())
            ->latest()
            ->take(5)
            ->get();

        $books = \App\Models\Book::where('is_active', true)->orderBy('title')->get();

        return view('livewire.live.live-index', [
            'upcoming'    => $upcoming,
            'myQuestions' => $myQuestions,
            'books'       => $books,
        ])->layout('layouts.app', ['title' => 'Jonli efirlar']);
    }
}
