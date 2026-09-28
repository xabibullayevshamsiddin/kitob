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

    // Filtrlash va qidiruv
    public string $search = '';
    public string $privacyFilter = 'all'; // 'all', 'public', 'private'
    public string $membershipFilter = 'all'; // 'all', 'my_groups', 'joined'
    public string $sortBy = 'latest'; // 'latest', 'popular', 'name'

    protected $queryString = [
        'search'           => ['except' => ''],
        'privacyFilter'    => ['except' => 'all'],
        'membershipFilter' => ['except' => 'all'],
        'sortBy'           => ['except' => 'latest'],
    ];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedPrivacyFilter(): void
    {
        $this->resetPage();
    }

    public function updatedMembershipFilter(): void
    {
        $this->resetPage();
    }

    public function updatedSortBy(): void
    {
        $this->resetPage();
    }

    public function setPrivacyFilter(string $privacy): void
    {
        $this->privacyFilter = $privacy;
        $this->membershipFilter = 'all';
        $this->resetPage();
    }

    public function setMembershipFilter(string $membership): void
    {
        $this->membershipFilter = $membership;
        $this->privacyFilter = 'all';
        $this->resetPage();
    }

    public function setAllFilters(): void
    {
        $this->privacyFilter = 'all';
        $this->membershipFilter = 'all';
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->privacyFilter = 'all';
        $this->membershipFilter = 'all';
        $this->sortBy = 'latest';
        $this->resetPage();
    }

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
    /**
     * Foydalanuvchi roliga qarab guruhlarga a'zo bo'lish limiti:
     * - Oddiy foydalanuvchilar: max 2 ta
     * - Teacher (o'qituvchi): max 5 ta
     * - Admin: cheksiz (null)
     */
    public function getMembershipLimit(User $user): ?int
    {
        if ($user->hasRole('admin') || $user->role === 'admin') {
            return null; // Cheksiz
        }

        if ($user->hasRole('teacher') || $user->role === 'teacher') {
            return 5;
        }

        return 2;
    }

    /**
     * Foydalanuvchi roliga qarab guruh ochish (yaratish) limiti:
     * - Oddiy foydalanuvchilar: 2 ta
     * - Teacher (o'qituvchi): 5 ta
     * - Admin: cheksiz (null)
     */
    public function getGroupLimit(User $user): ?int
    {
        return $this->getMembershipLimit($user);
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
        $membershipLimit = $this->getMembershipLimit($user);
        $currentMembershipCount = GroupMember::where('user_id', $user->id)->count();

        if ($limit !== null && $currentCount >= $limit) {
            $roleLabel = ($user->hasRole('teacher') || $user->role === 'teacher') ? 'O\'qituvchi' : 'Foydalanuvchi';
            session()->flash('error', "Siz {$roleLabel} sifatida ko'pi bilan {$limit} ta guruh yarata olasiz. Hozirda sizda {$currentCount} ta guruh mavjud. Yangi guruh ochish uchun avvalgisini o'chirishingiz lozim.");
            return;
        }

        if ($membershipLimit !== null && $currentMembershipCount >= $membershipLimit) {
            $roleLabel = ($user->hasRole('teacher') || $user->role === 'teacher') ? 'O\'qituvchi' : 'Foydalanuvchi';
            session()->flash('error', "Siz {$roleLabel} sifatida ko'pi bilan {$membershipLimit} ta guruhga a'zo bo'la olasiz (hozirda {$currentMembershipCount} ta guruhdasiz). Yangi guruh ochish uchun avval boshqa guruhlardan birini tark eting.");
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
        $membershipLimit = $this->getMembershipLimit($user);
        $currentMembershipCount = GroupMember::where('user_id', $user->id)->count();

        if ($limit !== null && $currentCount >= $limit) {
            $roleLabel = ($user->hasRole('teacher') || $user->role === 'teacher') ? 'O\'qituvchi' : 'Foydalanuvchi';
            $this->addError('name', "Guruh ochish limiti to'lgan ({$currentCount}/{$limit}). Siz {$roleLabel} sifatida ko'pi bilan {$limit} ta guruh ocha olasiz.");
            return;
        }

        if ($membershipLimit !== null && $currentMembershipCount >= $membershipLimit) {
            $roleLabel = ($user->hasRole('teacher') || $user->role === 'teacher') ? 'O\'qituvchi' : 'Foydalanuvchi';
            $this->addError('name', "Guruhga a'zolik limiti to'lgan ({$currentMembershipCount}/{$membershipLimit}). Siz {$roleLabel} sifatida ko'pi bilan {$membershipLimit} ta guruhga a'zo bo'la olasiz.");
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

        $user = Auth::user();

        // Agar allaqachon a'zo bo'lsa, guruhga yo'naltirish
        if (GroupMember::where('group_id', $groupId)->where('user_id', $user->id)->exists()) {
            redirect()->route('groups.show', $groupId);
            return;
        }

        // A'zolik limiti tekshiruvi: oddiy foydalanuvchi max 2, teacher 5, admin cheksiz
        $limit = $this->getMembershipLimit($user);
        $currentMembershipCount = GroupMember::where('user_id', $user->id)->count();

        if ($limit !== null && $currentMembershipCount >= $limit) {
            $roleLabel = ($user->hasRole('teacher') || $user->role === 'teacher') ? 'O\'qituvchi' : 'Oddiy foydalanuvchi';
            session()->flash('error', "Siz {$roleLabel} sifatida ko'pi bilan {$limit} ta guruhga a'zo bo'la olasiz (hozirda {$currentMembershipCount} ta guruhdasiz). Yangi guruhga qo'shilish uchun avval a'zo bo'lgan guruhlaringizdan birini tark eting.");
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

        $user = Auth::user();

        if (GroupMember::where('group_id', $this->targetGroupId)->where('user_id', $user->id)->exists()) {
            return redirect()->route('groups.show', $this->targetGroupId);
        }

        $limit = $this->getMembershipLimit($user);
        $currentMembershipCount = GroupMember::where('user_id', $user->id)->count();

        if ($limit !== null && $currentMembershipCount >= $limit) {
            $roleLabel = ($user->hasRole('teacher') || $user->role === 'teacher') ? 'O\'qituvchi' : 'Oddiy foydalanuvchi';
            $this->addError('joinPassword', "Siz {$roleLabel} sifatida ko'pi bilan {$limit} ta guruhga a'zo bo'la olasiz (hozirda {$currentMembershipCount} ta guruhdasiz). Yangi guruhga qo'shilish uchun avval boshqasidan chiqing.");
            return;
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
            ['group_id' => $group->id, 'user_id' => $user->id],
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

        $user = Auth::user();

        if (GroupMember::where('group_id', $groupId)->where('user_id', $user->id)->exists()) {
            return redirect()->route('groups.show', $groupId);
        }

        $limit = $this->getMembershipLimit($user);
        $currentMembershipCount = GroupMember::where('user_id', $user->id)->count();

        if ($limit !== null && $currentMembershipCount >= $limit) {
            $roleLabel = ($user->hasRole('teacher') || $user->role === 'teacher') ? 'O\'qituvchi' : 'Oddiy foydalanuvchi';
            session()->flash('error', "Siz {$roleLabel} sifatida ko'pi bilan {$limit} ta guruhga a'zo bo'la olasiz (hozirda {$currentMembershipCount} ta guruhdasiz). Yangi guruhga a'zo bo'lish uchun avval a'zo bo'lgan guruhlaringizdan birini tark eting.");
            return;
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
            ['group_id' => $group->id, 'user_id' => $user->id],
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
        $myGroupLimit = $currentUser ? $this->getGroupLimit($currentUser) : 2;
        $myMembershipLimit = $currentUser ? $this->getMembershipLimit($currentUser) : 2;
        $isAdmin = $currentUser ? ($currentUser->hasRole('admin') || $currentUser->role === 'admin') : false;

        // Hisoblagichlar
        $totalGroupsCount = Group::count();
        $publicGroupsCount = Group::where('is_private', false)->count();
        $privateGroupsCount = Group::where('is_private', true)->count();
        $myJoinedCount = $userId ? GroupMember::where('user_id', $userId)->count() : 0;

        $query = Group::query()
            ->withCount('members')
            ->with(['creator:id,name', 'book:id,title,slug']);

        // Maxfiylik filtri
        if ($this->privacyFilter === 'public') {
            $query->where('is_private', false);
        } elseif ($this->privacyFilter === 'private') {
            $query->where('is_private', true);
        }

        // A'zolik filtri
        if ($userId) {
            if ($this->membershipFilter === 'my_groups') {
                $query->where('created_by', $userId);
            } elseif ($this->membershipFilter === 'joined') {
                $query->whereHas('members', fn ($m) => $m->where('user_id', $userId));
            }
        }

        // Qidiruv
        if (!empty($this->search)) {
            $s = '%' . trim($this->search) . '%';
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', $s)
                  ->orWhere('description', 'like', $s)
                  ->orWhereHas('creator', fn ($u) => $u->where('name', 'like', $s))
                  ->orWhereHas('book', fn ($b) => $b->where('title', 'like', $s));
            });
        }

        // Saralash
        if ($this->sortBy === 'popular') {
            $query->orderByDesc('members_count')->latest();
        } elseif ($this->sortBy === 'name') {
            $query->orderBy('name');
        } else {
            $query->latest();
        }

        $groups = $query->paginate(9);

        $groups->getCollection()->transform(function ($g) use ($userId, $isAdmin) {
            $g->is_member   = $userId ? $g->members()->where('user_id', $userId)->exists() : false;
            $g->is_owner    = $userId ? ($g->created_by === $userId) : false;
            $g->can_delete  = $userId ? ($g->is_owner || $isAdmin) : false;
            return $g;
        });

        return view('livewire.groups.group-list', [
            'groups'              => $groups,
            'myGroupCount'        => $myGroupCount,
            'myGroupLimit'        => $myGroupLimit,
            'myMembershipLimit'   => $myMembershipLimit,
            'isAdmin'             => $isAdmin,
            'showCreateModal'     => $this->showCreateModal,
            'showJoinModal'       => $this->showJoinModal,
            'showDeleteModal'     => $this->showDeleteModal,
            'isPrivate'           => $this->isPrivate,
            'totalGroupsCount'    => $totalGroupsCount,
            'publicGroupsCount'   => $publicGroupsCount,
            'privateGroupsCount'  => $privateGroupsCount,
            'myJoinedCount'       => $myJoinedCount,
            'hasActiveFilters'    => ($this->search !== '' || $this->privacyFilter !== 'all' || $this->membershipFilter !== 'all' || $this->sortBy !== 'latest'),
        ])->layout('layouts.app', ['title' => 'Kitobxon Guruhlari']);
    }
}
