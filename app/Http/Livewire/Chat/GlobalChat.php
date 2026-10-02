<?php

namespace App\Http\Livewire\Chat;

use App\Events\NewGlobalChatMessage;
use App\Models\GlobalChatMessage;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class GlobalChat extends Component
{
    public string $message = '';

    public int $perPage = 50;

    protected function getListeners()
    {
        return [
            'echo:global-chat,new-message' => 'onNewMessage',
        ];
    }

    public function onNewMessage($payload = null)
    {
        // Livewire re-renders automatically
        $this->dispatchBrowserEvent('chat-scroll-bottom');
    }

    protected $rules = [
        'message' => 'required|string|min:1|max:250',
    ];

    protected $messages = [
        'message.required' => 'Xabar matnini kiriting.',
        'message.max'      => 'Xabar 250 ta belgidan oshmasligi kerak.',
    ];

    protected $validationAttributes = [
        'message' => 'xabar',
    ];

    public function sendMessage()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $this->validate();

        $chatMessage = GlobalChatMessage::create([
            'user_id'    => Auth::id(),
            'message'    => trim($this->message),
            'is_deleted' => false,
        ]);

        $chatMessage->load('user');

        try {
            broadcast(new NewGlobalChatMessage($chatMessage))->toOthers();
        } catch (\Throwable $e) {
            // Agar Pusher kalitlari hali to'ldirilmagan bo'lsa yoki ulanishda xatolik bo'lsa
            // xabar DB ga yozilgan holda qoladi, Livewire odatdagidek ishlayveradi
            report($e);
        }

        $this->reset('message');

        $this->dispatchBrowserEvent('chat-scroll-bottom');
    }

    public function sendVoiceMessage(string $path, int $duration = 0): void
    {
        if (!Auth::check()) {
            return;
        }

        $cleanPath = trim($path);
        if (empty($cleanPath)) {
            return;
        }

        $chatMessage = GlobalChatMessage::create([
            'user_id'        => Auth::id(),
            'message'        => '🎤 Ovozli xabar',
            'audio_path'     => $cleanPath,
            'audio_duration' => max(1, $duration),
            'is_deleted'     => false,
        ]);

        $chatMessage->load('user');

        try {
            broadcast(new NewGlobalChatMessage($chatMessage))->toOthers();
        } catch (\Throwable $e) {
            report($e);
        }

        $this->dispatchBrowserEvent('chat-scroll-bottom');
    }

    public function deleteMessage(int $messageId): void
    {
        if (!Auth::check()) {
            return;
        }

        $message = GlobalChatMessage::find($messageId);
        if (!$message) {
            return;
        }

        $user = Auth::user();
        $isAdmin = (method_exists($user, 'hasRole') && $user->hasRole('admin')) 
            || ($user->role === 'admin');

        // Foydalanuvchi faqat o'zining xabarini, admin esa har qanday xabarni o'chira oladi
        if ($message->user_id !== $user->id && !$isAdmin) {
            return;
        }

        $message->update(['is_deleted' => true]);
    }

    public function render()
    {
        $messages = GlobalChatMessage::query()
            ->has('user')
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
