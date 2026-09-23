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

    protected $rules = [
        'question' => 'required|string|min:5|max:300',
    ];

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
        $upcoming = LiveEvent::with('book')
            ->whereIn('status', [LiveEvent::STATUS_SCHEDULED, LiveEvent::STATUS_LIVE])
            ->orderByRaw("CASE WHEN status = 'live' THEN 0 ELSE 1 END")
            ->orderBy('scheduled_at')
            ->get();

        $ended = LiveEvent::with('book')
            ->ended()
            ->take(10)
            ->get();

        $myQuestions = LiveQuestion::where('user_id', Auth::id())
            ->latest()
            ->take(5)
            ->get();

        return view('livewire.live.live-index', [
            'upcoming'    => $upcoming,
            'ended'       => $ended,
            'myQuestions' => $myQuestions,
        ])->layout('layouts.app', ['title' => 'Jonli efirlar']);
    }
}
