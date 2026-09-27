<?php

namespace App\Events;

use App\Models\GlobalChatMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewGlobalChatMessage implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public GlobalChatMessage $message;

    /**
     * Create a new event instance.
     *
     * @param  \App\Models\GlobalChatMessage  $message
     * @return void
     */
    public function __construct(GlobalChatMessage $message)
    {
        $this->message = $message;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('global-chat');
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'new-message';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        $user = $this->message->user;
        $isAdmin = false;
        if ($user) {
            $isAdmin = (method_exists($user, 'hasRole') && $user->hasRole('admin')) 
                || ($user->role === 'admin');
        }

        return [
            'id'              => $this->message->id,
            'message'         => $this->message->message,
            'created_at'      => $this->message->created_at ? $this->message->created_at->timezone('Asia/Tashkent')->format('H:i') : now()->timezone('Asia/Tashkent')->format('H:i'),
            'user_id'         => $this->message->user_id,
            'user_name'       => $user?->name ?? 'Foydalanuvchi',
            'user_avatar_url' => $user?->avatar_url ?? 'https://ui-avatars.com/api/?name=User&background=4f46e5&color=fff',
            'user_is_admin'   => $isAdmin,
        ];
    }
}
