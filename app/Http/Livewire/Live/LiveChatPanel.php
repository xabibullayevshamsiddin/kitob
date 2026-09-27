<?php

namespace App\Http\Livewire\Live;

use App\Models\LiveEvent;
use App\Models\LiveQuestion;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * Jonli efir chat paneli (alohida komponent).
 *
 * Nega alohida? Parent LiveDetail WebRTC skriptiga ega — uni har 2 sekundda
 * yangilash video ulanishini uzadi. Bu komponent esa faqat chat qismini
 * wire:poll orqali avtomatik yangilaydi (umumiy chat kabi, "obnova" shart emas).
 */
class LiveChatPanel extends Component
{
    /**
     * Event faqat ID sifatida saqlanadi (model emas!).
     * Agar host efirni tugatsa (event o'chiriladi), wire:poll yangilanishida
     * model hydration 500 xato bermasligi kerak — id bo'yicha topib, null bo'lsa
     * "efir tugadi" holatini ko'rsatamiz.
     */
    public int $eventId;

    public bool $isHost = false;

    public string $message = '';

    protected $rules = [
        'message' => 'required|string|min:2|max:300',
    ];

    protected $messages = [
        'message.required' => 'Xabaringizni yozing.',
        'message.min'      => 'Xabar kamida 2 belgidan iborat bo\'lsin.',
    ];

    protected $validationAttributes = [
        'message' => 'xabar',
    ];

    public function mount(LiveEvent $event, $isHost = false): void
    {
        $this->eventId = $event->id;
        $this->isHost  = (bool) $isHost;
    }

    /** Har safar DB'dan yangi o'qiladi — event o'chirilgan bo'lsa null */
    public function getEventProperty(): ?LiveEvent
    {
        return LiveEvent::find($this->eventId);
    }

    /** Yozma chatga ruxsat (server tomonda tekshiruv) */
    public function getCanWriteProperty(): bool
    {
        if ($this->isHost) {
            return true;
        }

        if (!$this->event) {
            return false;
        }

        if (!in_array($this->event->permission_mode, ['both', 'chat_only'], true)) {
            return false;
        }

        return Auth::check();
    }

    /** Ovozli savol so'rash mumkinmi */
    public function getCanRequestVoiceProperty(): bool
    {
        if ($this->isHost) {
            return false;
        }

        if (!$this->event) {
            return false;
        }

        if (!in_array($this->event->permission_mode, ['both', 'voice_only'], true)) {
            return false;
        }

        return Auth::check();
    }

    public function sendMessage(): void
    {
        if (!$this->event) {
            return;
        }

        if (!$this->canWrite) {
            session()->flash('error', 'Ushbu efirda yozma chat cheklangan.');
            return;
        }

        if (!Auth::check()) {
            session()->flash('error', 'Xabar yozish uchun avval tizimga kiring.');
            return;
        }

        $this->validate();

        LiveQuestion::create([
            'live_event_id' => $this->event->id,
            'user_id'       => Auth::id(),
            'question'      => trim($this->message),
            'is_selected'   => false,
            'is_answered'   => false,
        ]);

        $this->reset('message');

        $this->dispatchBrowserEvent('live-chat-scroll-bottom');
    }

    public function requestVoiceSpeech(): void
    {
        if (!$this->event) {
            return;
        }

        if (!$this->canRequestVoice) {
            session()->flash('error', 'Ushbu efirda ovozli savollar rejimi o\'chirilgan.');
            return;
        }

        if (!Auth::check()) {
            session()->flash('error', 'Ovozli savol so\'rash uchun avval tizimga kiring.');
            return;
        }

        LiveQuestion::create([
            'live_event_id' => $this->event->id,
            'user_id'       => Auth::id(),
            'question'      => '✋ [Ovozli savol]: Mikrofon orqali savol berishni so\'ramoqda',
            'is_selected'   => true,
            'is_answered'   => false,
        ]);

        session()->flash('success', 'Ovozli savol so\'rovingiz yuborildi! Ustoz navbatingiz kelganda mikrofon beradi. 🎙️');
    }

    /** HOST: xabarni pin qilish / bekor qilish */
    public function togglePin(int $questionId): void
    {
        if (!$this->isHost || !$this->event) {
            return;
        }

        $q = LiveQuestion::where('live_event_id', $this->event->id)->find($questionId);
        if ($q) {
            $q->update(['is_selected' => !$q->is_selected]);
        }
    }

    /** HOST: javob berildi deb belgilash */
    public function markAnswered(int $questionId): void
    {
        if (!$this->isHost || !$this->event) {
            return;
        }

        $q = LiveQuestion::where('live_event_id', $this->event->id)->find($questionId);
        if ($q) {
            $q->update(['is_answered' => !$q->is_answered]);
        }
    }

    public function render()
    {
        $event = $this->event;

        // Event o'chirilgan (host efirni tugatgan) — xatosiz bo'sh holat
        if (!$event) {
            return view('livewire.live.live-chat-panel', [
                'event'    => null,
                'pinned'   => collect(),
                'messages' => collect(),
            ]);
        }

        // Mustahkamlangan (pin) xabarlar — tepada oltin banner
        $pinned = collect();
        if ($event->status === LiveEvent::STATUS_LIVE) {
            $pinned = LiveQuestion::where('live_event_id', $event->id)
                ->selected()
                ->with('user:id,name,username,avatar,role')
                ->orderBy('updated_at')
                ->take(3)
                ->get();
        }

        $messages = LiveQuestion::where('live_event_id', $event->id)
            ->with('user:id,name,username,avatar,role')
            ->latest()
            ->take(60)
            ->get()
            ->reverse()
            ->values();

        return view('livewire.live.live-chat-panel', [
            'event'    => $event,
            'pinned'   => $pinned,
            'messages' => $messages,
        ]);
    }
}
