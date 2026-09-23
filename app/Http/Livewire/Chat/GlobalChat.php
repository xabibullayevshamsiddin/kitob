<?php

namespace App\Http\Livewire\Chat;

use App\Models\GlobalChatMessage;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class GlobalChat extends Component
{
    public string $message = '';

    public int $perPage = 50;

    protected $rules = [
        'message' => 'required|string|min:1|max:500',
    ];

    protected $validationAttributes = [
        'message' => 'xabar',
    ];

    public function sendMessage(): void
    {
        if (!Auth::check()) {
            return;
        }

        $this->validate();

        GlobalChatMessage::create([
            'user_id'    => Auth::id(),
            'message'    => trim($this->message),
            'is_deleted' => false,
        ]);

        $this->reset('message');

        $this->dispatchBrowserEvent('chat-scroll-bottom');
    }

    public function render()
    {
        $messages = GlobalChatMessage::query()
            ->with(['user:id,name,username,avatar,role'])
            ->notDeleted()
            ->latest()
            ->take($this->perPage)
            ->get()
            ->reverse()
            ->values();

        return view('livewire.chat.global-chat', [
            'messages'   => $messages,
            'authUser'   => Auth::user(),
        ])->layout('layouts.app', ['title' => 'Umumiy chat']);
    }
}
