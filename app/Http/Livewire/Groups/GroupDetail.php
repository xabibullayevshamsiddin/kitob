<?php

namespace App\Http\Livewire\Groups;

use App\Models\Group;
use App\Models\GroupMember;
use App\Models\GroupMessage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class GroupDetail extends Component
{
    public Group $group;

    public string $message = '';
    public bool $showDeleteModal = false;

    public function openDeleteModal(): void
    {
        $user = Auth::user();
        if (!$user) return;

        $isAdmin = $user->hasRole('admin') || $user->role === 'admin';
        if ($this->group->created_by !== $user->id && !$isAdmin) {
            session()->flash('error', 'Sizda bu guruhni o\'chirish huquqi yo\'q!');
            return;
        }

        $this->showDeleteModal = true;
    }

    public function confirmDeleteGroup()
    {
        $user = Auth::user();
        if (!$user) return;

        $isAdmin = $user->hasRole('admin') || $user->role === 'admin';
        if ($this->group->created_by !== $user->id && !$isAdmin) {
            session()->flash('error', 'Sizda bu guruhni o\'chirish huquqi yo\'q!');
            $this->showDeleteModal = false;
            return;
        }

        $name = $this->group->name;

        DB::transaction(function () {
            $this->group->messages()->delete();
            $this->group->members()->delete();
            $this->group->delete();
        });

        session()->flash('success', "«{$name}» guruhi muvaffaqiyatli o'chirildi.");
        return redirect()->route('groups.index');
    }

    public function mount(Group $group)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $isMember = GroupMember::where('group_id', $group->id)
            ->where('user_id', Auth::id())
            ->exists();

        if (!$isMember) {
            session()->flash('error', '«' . $group->name . '» guruhiga kirish uchun avval guruhga a\'zo bo\'ling.');
            return redirect()->route('groups.index');
        }
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

    public function deleteMessage(int $messageId): void
    {
        if (!Auth::check()) {
            return;
        }

        $message = GroupMessage::where('group_id', $this->group->id)->find($messageId);
        if (!$message) {
            return;
        }

        $user = Auth::user();
        $isAdmin = (method_exists($user, 'hasRole') && $user->hasRole('admin')) 
            || ($user->role === 'admin')
            || ($this->group->created_by === $user->id);

        // Foydalanuvchi o'z xabarini, guruh egasi yoki admin istalgan xabarni o'chira oladi
        if ($message->user_id !== $user->id && !$isAdmin) {
            return;
        }

        $message->update(['is_deleted' => true]);
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

        $user = Auth::user();
        $canDelete = $user && ($this->group->created_by === $user->id || $user->hasRole('admin') || $user->role === 'admin');

        return view('livewire.groups.group-detail', [
            'messages'  => $messages,
            'members'   => $members,
            'canDelete' => $canDelete,
        ])->layout('layouts.app', ['title' => $this->group->name]);
    }
}
