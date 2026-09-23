<?php

namespace App\Http\Livewire\Groups;

use App\Models\Group;
use App\Models\GroupMember;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;

class GroupList extends Component
{
    public string $name = '';

    public string $description = '';

    public bool $isPrivate = false;

    public bool $showCreateModal = false;

    protected $rules = [
        'name'        => 'required|string|min:3|max:60',
        'description' => 'nullable|string|max:500',
    ];

    public function create(): void
    {
        $this->validate();

        $user = Auth::user();

        $group = DB::transaction(function () use ($user) {
            $group = Group::create([
                'name'        => $this->name,
                'slug'        => Str::slug($this->name) . '-' . Str::lower(Str::random(5)),
                'description' => $this->description,
                'created_by'  => $user->id,
                'is_private'  => $this->isPrivate,
            ]);

            GroupMember::create([
                'group_id'  => $group->id,
                'user_id'   => $user->id,
                'role'      => 'admin',
                'joined_at' => now(),
            ]);

            return $group;
        });

        $this->reset(['name', 'description', 'isPrivate', 'showCreateModal']);

        session()->flash('success', '«' . $group->name . '» guruhi yaratildi! 🎉');
    }

    public function join(int $groupId): void
    {
        $group = Group::findOrFail($groupId);

        if ($group->is_private) {
            session()->flash('error', 'Bu yopiq guruh — faqat taklif kodi orqali qo\'shilish mumkin.');
            return;
        }

        if ($group->max_members && $group->members_count >= $group->max_members) {
            session()->flash('error', 'Guruh to\'lgan.');
            return;
        }

        GroupMember::firstOrCreate(
            ['group_id' => $group->id, 'user_id' => Auth::id()],
            ['role' => 'member', 'joined_at' => now()]
        );

        session()->flash('success', '«' . $group->name . '» guruhiga qo\'shildingiz!');
    }

    public function leave(int $groupId): void
    {
        GroupMember::where('group_id', $groupId)
            ->where('user_id', Auth::id())
            ->delete();

        session()->flash('success', 'Guruhni tark etdingiz.');
    }

    public function render()
    {
        $userId = Auth::id();

        $groups = Group::query()
            ->withCount('members')
            ->with(['creator:id,name', 'book:id,title,slug'])
            ->latest()
            ->get()
            ->map(function ($g) use ($userId) {
                $g->is_member = $g->members()->where('user_id', $userId)->exists();
                $g->is_owner  = $g->created_by === $userId;
                return $g;
            });

        return view('livewire.groups.group-list', [
            'groups' => $groups,
        ])->layout('layouts.app', ['title' => 'Guruhlar']);
    }
}
