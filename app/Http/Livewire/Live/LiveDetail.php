<?php

namespace App\Http\Livewire\Live;

use App\Models\LiveEvent;
use App\Models\LiveQuestion;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class LiveDetail extends Component
{
    public LiveEvent $event;

    public string $question = '';

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

        LiveQuestion::create([
            'live_event_id' => $this->event->id,
            'user_id'       => Auth::id(),
            'question'      => trim($this->question),
        ]);

        $this->reset('question');

        session()->flash('success', 'Savolingiz yuborildi! 🎤');
    }

    public function render()
    {
        $selectedQuestions = LiveQuestion::where('live_event_id', $this->event->id)
            ->selected()
            ->with('user:id,name,avatar')
            ->latest()
            ->take(10)
            ->get();

        $myQuestions = LiveQuestion::where('live_event_id', $this->event->id)
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        return view('livewire.live.live-detail', [
            'selectedQuestions' => $selectedQuestions,
            'myQuestions'       => $myQuestions,
        ])->layout('layouts.app', ['title' => $this->event->title]);
    }
}
