<?php

namespace App\Http\Livewire\Groups;

use App\Models\Group;
use App\Models\GroupMember;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class GroupList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';
    // Guruh ochish
    public string $name = '';
    public string $description = '';
    public bool $isPrivate = false;
    public string $password = '';
    public bool $showCreateModal = false;

    // Yopiq guruhga parol bilan qo'shilish
    public ?int $targetGroupId = null;
    public string $joinPassword = '';
    public bool $showJoinModal = false;

    // Guruhni o'chirish
    public ?int $groupToDeleteId = null;
    public bool $showDeleteModal = false;

    protected function rules(): array
    {
        return [
            'name'        => 'required|string|min:3|max:60',
            'description' => 'nullable|string|max:500',
            'password'    => $this->isPrivate ? 'required|string|min:3|max:50' : 'nullable|string|max:50',
        ];
    }

    protected $messages = [
        'name.required'         => 'Guruh nomini kiriting.',
        'name.min'              => 'Guruh nomi kamida 3 ta belgidan iborat bo\'lishi kerak.',
        'password.required'     => 'Yopiq guruh uchun parol o\'rnatish majburiy.',
        'password.min'          => 'Guruh paroli kamida 3 ta belgidan iborat bo\'lishi kerak.',
        'joinPassword.required' => 'Guruh parolini kiriting.',
    ];

    /**
     * Foydalanuvchi roliga qarab guruh ochish limiti:
     * Oddiy foydalanuvchilar: 1 ta
     * Teacher (o'qituvchi): 2 ta
     * Admin: 3 ta
     */
    public function getGroupLimit(User $user): int
    {
        if ($user->hasRole('admin') || $user->role === 'admin') {
            return 3;
        }

        if ($user->hasRole('teacher') || $user->role === 'teacher') {
            return 2;
        }

        return 1;
    }

    public function getTargetGroupProperty(): ?Group
    {
        return $this->targetGroupId ? Group::find($this->targetGroupId) : null;
    }

    public function getDeleteTargetGroupProperty(): ?Group
    {
        return $this->groupToDeleteId ? Group::find($this->groupToDeleteId) : null;
    }

    public function openCreateModal(): void
    {
        if (!Auth::check()) {
            redirect()->route('login');
            return;
        }

        $user = Auth::user();
        $limit = $this->getGroupLimit($user);
        $currentCount = Group::where('created_by', $user->id)->count();

        if ($currentCount >= $limit) {
            $roleLabel = ($user->hasRole('admin') || $user->role === 'admin')
                ? 'Admin'
                : (($user->hasRole('teacher') || $user->role === 'teacher') ? 'O\'qituvchi' : 'Foydalanuvchi');

            session()->flash('error', "Siz {$roleLabel} sifatida ko'pi bilan {$limit} ta guruh yarata olasiz. Hozirda sizda {$currentCount} ta guruh mavjud. Yangi guruh ochish uchun avvalgisini o'chirishingiz lozim.");
            return;
        }

        $this->resetValidation();
        $this->reset(['name', 'description', 'isPrivate', 'password']);
        $this->showCreateModal = true;
    }

    public function create()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $limit = $this->getGroupLimit($user);
        $currentCount = Group::where('created_by', $user->id)->count();

        if ($currentCount >= $limit) {
            $roleLabel = ($user->hasRole('admin') || $user->role === 'admin')
                ? 'Admin'
                : (($user->hasRole('teacher') || $user->role === 'teacher') ? 'O\'qituvchi' : 'Foydalanuvchi');

            $this->addError('name', "Guruh ochish limiti to'lgan ({$currentCount}/{$limit}). Siz {$roleLabel} sifatida ko'pi bilan {$limit} ta guruh ocha olasiz.");
            return;
        }

        $this->validate();

        $group = DB::transaction(function () use ($user) {
            $group = Group::create([
                'name'        => trim($this->name),
                'slug'        => Str::slug($this->name) . '-' . Str::lower(Str::random(5)),
                'description' => trim($this->description),
                'created_by'  => $user->id,
                'is_private'  => $this->isPrivate,
                'password'    => $this->isPrivate ? trim($this->password) : null,
            ]);

            GroupMember::create([
                'group_id'  => $group->id,
                'user_id'   => $user->id,
                'role'      => 'admin',
                'joined_at' => now(),
            ]);

            return $group;
        });

        $this->reset(['name', 'description', 'isPrivate', 'password', 'showCreateModal']);

        session()->flash('success', '«' . $group->name . '» guruhi yaratildi! 🎉');
        return redirect()->route('groups.show', $group->id);
    }

    public function openJoinModal(int $groupId): void
    {
        if (!Auth::check()) {
            redirect()->route('login');
            return;
        }

        $group = Group::findOrFail($groupId);

        if (!$group->is_private) {
            $this->join($groupId);
            return;
        }

        $this->resetValidation();
        $this->targetGroupId = $groupId;
        $this->joinPassword = '';
        $this->showJoinModal = true;
    }

    public function submitJoinPassword()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $this->validate([
            'joinPassword' => 'required|string',
        ]);

        $group = Group::findOrFail($this->targetGroupId);

        if ($group->password && trim($this->joinPassword) !== trim($group->password)) {
            $this->addError('joinPassword', 'Kiritilgan parol noto\'g\'ri! Qaytadan urinib ko\'ring.');
            return;
        }

        if ($group->max_members && $group->members()->count() >= $group->max_members) {
            $this->addError('joinPassword', 'Afsuski, guruh a\'zolari soni chegarasiga yetgan.');
            return;
        }

        GroupMember::firstOrCreate(
            ['group_id' => $group->id, 'user_id' => Auth::id()],
            ['role' => 'member', 'joined_at' => now()]
        );

        $this->reset(['showJoinModal', 'targetGroupId', 'joinPassword']);

        session()->flash('success', '«' . $group->name . '» guruhiga muvaffaqiyatli qo\'shildingiz! 🎉');
        return redirect()->route('groups.show', $group->id);
    }

    public function join(int $groupId)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $group = Group::findOrFail($groupId);

        if ($group->is_private) {
            $this->openJoinModal($groupId);
            return;
        }

        if ($group->max_members && $group->members()->count() >= $group->max_members) {
            session()->flash('error', 'Guruh to\'lgan.');
            return;
        }

        GroupMember::firstOrCreate(
            ['group_id' => $group->id, 'user_id' => Auth::id()],
            ['role' => 'member', 'joined_at' => now()]
        );

        session()->flash('success', '«' . $group->name . '» guruhiga qo\'shildingiz!');
        return redirect()->route('groups.show', $group->id);
    }

    public function leave(int $groupId): void
    {
        if (!Auth::check()) {
            return;
        }

        GroupMember::where('group_id', $groupId)
            ->where('user_id', Auth::id())
            ->delete();

        session()->flash('success', 'Guruhni tark etdingiz.');
    }

    public function openDeleteModal(int $groupId): void
    {
        if (!Auth::check()) {
            return;
        }

        $group = Group::findOrFail($groupId);
        $user = Auth::user();
        $isAdmin = $user->hasRole('admin') || $user->role === 'admin';

        if ($group->created_by !== $user->id && !$isAdmin) {
            session()->flash('error', 'Sizda bu guruhni o\'chirish huquqi yo\'q!');
            return;
        }

        $this->groupToDeleteId = $groupId;
        $this->showDeleteModal = true;
    }

    public function confirmDeleteGroup(): void
    {
        if (!Auth::check() || !$this->groupToDeleteId) {
            return;
        }

        $group = Group::findOrFail($this->groupToDeleteId);
        $user = Auth::user();
        $isAdmin = $user->hasRole('admin') || $user->role === 'admin';

        if ($group->created_by !== $user->id && !$isAdmin) {
            session()->flash('error', 'Sizda bu guruhni o\'chirish huquqi yo\'q!');
            $this->showDeleteModal = false;
            return;
        }

        $name = $group->name;

        DB::transaction(function () use ($group) {
            $group->messages()->delete();
            $group->members()->delete();
            $group->delete();
        });

        $this->showDeleteModal = false;
        $this->groupToDeleteId = null;

        session()->flash('success', "«{$name}» guruhi muvaffaqiyatli o'chirildi.");
    }

    public function render()
    {
        $userId = Auth::id();
        $currentUser = Auth::user();

        $myGroupCount = $userId ? Group::where('created_by', $userId)->count() : 0;
        $myGroupLimit = $currentUser ? $this->getGroupLimit($currentUser) : 1;
        $isAdmin = $currentUser ? ($currentUser->hasRole('admin') || $currentUser->role === 'admin') : false;

        $groups = Group::query()
            ->withCount('members')
            ->with(['creator:id,name', 'book:id,title,slug'])
            ->latest()
            ->paginate(9);

        $groups->getCollection()->transform(function ($g) use ($userId, $isAdmin) {
            $g->is_member   = $userId ? $g->members()->where('user_id', $userId)->exists() : false;
            $g->is_owner    = $userId ? ($g->created_by === $userId) : false;
            $g->can_delete  = $userId ? ($g->is_owner || $isAdmin) : false;
            return $g;
        });

        return view('livewire.groups.group-list', [
            'groups'          => $groups,
            'myGroupCount'    => $myGroupCount,
            'myGroupLimit'    => $myGroupLimit,
            'isAdmin'         => $isAdmin,
            'showCreateModal' => $this->showCreateModal,
            'showJoinModal'   => $this->showJoinModal,
            'showDeleteModal' => $this->showDeleteModal,
            'isPrivate'       => $this->isPrivate,
        ])->layout('layouts.app', ['title' => 'Kitobxon Guruhlari']);
    }
}
