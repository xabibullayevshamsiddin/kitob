<?php

namespace App\Http\Livewire\Live;

use App\Models\LiveEvent;
use App\Models\LiveQuestion;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class LiveIndex extends Component
{
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
        ]);

        $this->showStudioModal = false;

        return redirect()->route('live.show', $event)
            ->with('success', 'Jonli efir boshlandi! Mikrofon va kamerangizni sozlang.');
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
        $upcoming = LiveEvent::with(['book', 'hostUser'])
            ->whereIn('status', [LiveEvent::STATUS_SCHEDULED, LiveEvent::STATUS_LIVE])
            ->orderByRaw("CASE WHEN status = 'live' THEN 0 ELSE 1 END")
            ->orderBy('scheduled_at')
            ->get();

        $ended = LiveEvent::with(['book', 'hostUser'])
            ->ended()
            ->take(10)
            ->get();

        $myQuestions = LiveQuestion::where('user_id', Auth::id())
            ->latest()
            ->take(5)
            ->get();

        $books = \App\Models\Book::where('is_active', true)->orderBy('title')->get();

        return view('livewire.live.live-index', [
            'upcoming'    => $upcoming,
            'ended'       => $ended,
            'myQuestions' => $myQuestions,
            'books'       => $books,
        ])->layout('layouts.app', ['title' => 'Jonli efirlar']);
    }
}
