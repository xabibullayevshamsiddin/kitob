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

        if (Auth::user()->isBanned()) {
            $this->addError('message', 'Siz bloklangansiz! Qolgan vaqt: ' . Auth::user()->ban_remaining . '. Sabab: ' . (Auth::user()->ban_reason ?? 'Qoidabuzarlik'));
            return;
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

        if (Auth::user()->isBanned()) {
            $this->addError('message', 'Siz bloklangansiz! Qolgan vaqt: ' . Auth::user()->ban_remaining . '. Sabab: ' . (Auth::user()->ban_reason ?? 'Qoidabuzarlik'));
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

    public function banUser(int $userId, string $duration = '1_day', ?string $reason = null): void
    {
        if (!Auth::check()) {
            return;
        }

        $admin = Auth::user();
        $isAdmin = (method_exists($admin, 'hasRole') && $admin->hasRole('admin')) || ($admin->role === 'admin');
        if (!$isAdmin) {
            return;
        }

        if ($userId === $admin->id) {
            return;
        }

        $targetUser = \App\Models\User::find($userId);
        if (!$targetUser) {
            return;
        }

        $validDurations = ['1_hour', '1_day', '1_week', '1_month', 'permanent'];
        if (!in_array($duration, $validDurations)) {
            $duration = '1_day';
        }

        $targetUser->ban($duration, $reason ?: 'Chatda nojo\'ya harakat / qoidabuzarlik');
    }

    public function unbanUser(int $userId): void
    {
        if (!Auth::check()) {
            return;
        }

        $admin = Auth::user();
        $isAdmin = (method_exists($admin, 'hasRole') && $admin->hasRole('admin')) || ($admin->role === 'admin');
        if (!$isAdmin) {
            return;
        }

        $targetUser = \App\Models\User::find($userId);
        if ($targetUser) {
            $targetUser->unban();
        }
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
