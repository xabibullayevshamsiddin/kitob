<?php

namespace App\Http\Livewire\Groups;

use App\Models\Group;
use App\Models\GroupMember;
use App\Models\GroupMessage;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class GroupDetail extends Component
{
    public Group $group;

    public string $message = '';

    public function mount(Group $group)
    {
        $isMember = GroupMember::where('group_id', $group->id)
            ->where('user_id', Auth::id())
            ->exists();

        abort_unless($isMember, 403, 'Bu guruh a\'zosi emassiz. Avval guruhga qo\'shiling.');
    }

    protected $rules = [
        'message' => 'required|string|min:1|max:500',
    ];

    public function sendMessage(): void
    {
        $this->validate();

        GroupMessage::create([
            'group_id' => $this->group->id,
            'user_id'  => Auth::id(),
            'message'  => trim($this->message),
        ]);

        $this->reset('message');
        $this->dispatchBrowserEvent('chat-scroll-bottom');
    }

    public function render()
    {
        $messages = GroupMessage::where('group_id', $this->group->id)
            ->with('user:id,name,username,avatar')
            ->notDeleted()
            ->latest()
            ->take(50)
            ->get()
            ->reverse()
            ->values();

        $members = GroupMember::where('group_id', $this->group->id)
            ->with('user:id,name,username,avatar,total_points')
            ->orderBy('joined_at')
            ->get();

        return view('livewire.groups.group-detail', [
            'messages' => $messages,
            'members'  => $members,
        ])->layout('layouts.app', ['title' => $this->group->name]);
    }
}
