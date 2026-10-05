<?php

namespace App\Http\Livewire\Groups;

use App\Http\Livewire\Concerns\WithToast;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\GroupMessage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class GroupDetail extends Component
{
    use WithToast;
    use WithFileUploads;

    public Group $group;

    public string $message = '';
    public bool $showDeleteModal = false;

    // Guruh rasmi
    public $newCoverImage = null;

    // Guruh sozlamalari modal holati
    public bool $showSettingsModal = false;
    public string $editName = '';
    public string $editDescription = '';
    public bool $editChatEnabled = true;
    public bool $editVoiceEnabled = true;
    public bool $editIsPrivate = false;
    public string $editPassword = '';
    public int $editMaxMembers = 100;

    // A'zoni guruhdan chiqarish (Kick) modal holati
    public bool $showRemoveMemberModal = false;
    public ?int $memberToRemoveId = null;
    public string $memberToRemoveName = '';

    public function openDeleteModal(): void
    {
        $user = Auth::user();
        if (!$user) return;

        $isAdmin = $user->hasRole('admin') || $user->role === 'admin';
        if ($this->group->created_by !== $user->id && !$isAdmin) {
            $this->toast('error', 'Sizda bu guruhni o\'chirish huquqi yo\'q!');
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
            $this->toast('error', 'Sizda bu guruhni o\'chirish huquqi yo\'q!');
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

    public function openSettingsModal(): void
    {
        $user = Auth::user();
        if (!$user || !$this->group->isManagedBy($user)) {
            $this->toast('error', 'Sizda guruh sozlamalarini o\'zgartirish huquqi yo\'q!');
            return;
        }

        $this->editName = $this->group->name;
        $this->editDescription = $this->group->description ?? '';
        $this->editChatEnabled = (bool) ($this->group->chat_enabled ?? true);
        $this->editVoiceEnabled = (bool) ($this->group->voice_enabled ?? true);
        $this->editIsPrivate = (bool) $this->group->is_private;
        $this->editPassword = $this->group->password ?? '';
        $this->editMaxMembers = (int) ($this->group->max_members ?? 100);
        $this->newCoverImage = null;

        $this->resetValidation();
        $this->showSettingsModal = true;
    }

    public function updatedNewCoverImage(): void
    {
        $this->validate([
            'newCoverImage' => 'nullable|image|max:5120|mimes:jpeg,png,jpg,webp,gif',
        ], [
            'newCoverImage.image' => "Faqat rasm fayllarini yuklash mumkin.",
            'newCoverImage.max'   => "Rasm hajmi 5MB dan oshmasligi kerak.",
        ]);
    }

    public function updateCoverImage(): void
    {
        $user = Auth::user();
        if (!$user || !$this->group->isManagedBy($user)) {
            $this->toast('error', 'Sizda guruh rasmini o\'zgartirish huquqi yo\'q!');
            return;
        }

        $this->validate([
            'newCoverImage' => 'required|image|max:5120|mimes:jpeg,png,jpg,webp,gif',
        ], [
            'newCoverImage.required' => 'Rasm tanlang.',
            'newCoverImage.image'    => 'Faqat rasm yuklash mumkin.',
            'newCoverImage.max'      => 'Rasm hajmi 5MB dan oshmasligi kerak.',
        ]);

        if ($this->group->cover_image && Storage::disk('public')->exists($this->group->cover_image)) {
            Storage::disk('public')->delete($this->group->cover_image);
        }

        $path = $this->newCoverImage->store('groups/covers', 'public');
        $this->group->update(['cover_image' => $path]);
        $this->group->refresh();
        $this->newCoverImage = null;

        $this->toast('success', 'Guruh rasmi muvaffaqiyatli yangilandi! 🖼️');
    }

    public function removeCoverImage(): void
    {
        $user = Auth::user();
        if (!$user || !$this->group->isManagedBy($user)) {
            $this->toast('error', 'Sizda ruxsat yo\'q!');
            return;
        }

        if ($this->group->cover_image && Storage::disk('public')->exists($this->group->cover_image)) {
            Storage::disk('public')->delete($this->group->cover_image);
        }

        $this->group->update(['cover_image' => null]);
        $this->group->refresh();
        $this->newCoverImage = null;

        $this->toast('info', 'Guruh rasmi olib tashlandi.');
    }

    protected function settingsRules(): array
    {
        return [
            'editName'         => 'required|string|min:3|max:60',
            'editDescription'  => 'nullable|string|max:500',
            'editChatEnabled'  => 'boolean',
            'editVoiceEnabled' => 'boolean',
            'editIsPrivate'    => 'boolean',
            'editPassword'     => $this->editIsPrivate ? 'required|string|min:3|max:50' : 'nullable|string|max:50',
            'editMaxMembers'   => 'required|integer|min:2|max:1000',
            'newCoverImage'    => 'nullable|image|max:5120|mimes:jpeg,png,jpg,webp,gif',
        ];
    }

    public function saveSettings(): void
    {
        $user = Auth::user();
        if (!$user || !$this->group->isManagedBy($user)) {
            $this->toast('error', 'Sizda guruh sozlamalarini o\'zgartirish huquqi yo\'q!');
            return;
        }

        $this->validate($this->settingsRules(), [
            'editName.required'     => 'Guruh nomini kiriting.',
            'editName.min'          => 'Guruh nomi kamida 3 ta belgidan iborat bo\'lishi kerak.',
            'editPassword.required' => 'Yopiq guruh uchun parol kiriting.',
            'editPassword.min'      => 'Parol kamida 3 ta belgidan iborat bo\'lishi kerak.',
            'editMaxMembers.min'    => 'A\'zolar soni kamida 2 bo\'lishi kerak.',
            'newCoverImage.image'   => 'Faqat rasm fayllarini yuklash mumkin.',
            'newCoverImage.max'     => 'Rasm hajmi 5MB dan oshmasligi kerak.',
        ]);

        $updateData = [
            'name'          => trim($this->editName),
            'description'   => trim($this->editDescription),
            'chat_enabled'  => $this->editChatEnabled,
            'voice_enabled' => $this->editVoiceEnabled,
            'is_private'    => $this->editIsPrivate,
            'password'      => $this->editIsPrivate ? trim($this->editPassword) : null,
            'max_members'   => $this->editMaxMembers,
        ];

        if ($this->newCoverImage) {
            if ($this->group->cover_image && Storage::disk('public')->exists($this->group->cover_image)) {
                Storage::disk('public')->delete($this->group->cover_image);
            }
            $updateData['cover_image'] = $this->newCoverImage->store('groups/covers', 'public');
            $this->newCoverImage = null;
        }

        $this->group->update($updateData);

        $this->group->refresh();
        $this->showSettingsModal = false;
        $this->toast('success', 'Guruh sozlamalari muvaffaqiyatli saqlandi! ✨');
    }

    public function openRemoveMemberModal(int $userId): void
    {
        $user = Auth::user();
        if (!$user || !$this->group->isManagedBy($user)) {
            $this->toast('error', 'Sizda a\'zoni chiqarish huquqi yo\'q!');
            return;
        }

        if ($userId === $this->group->created_by) {
            $this->toast('error', 'Guruh asoschisini guruhdan chiqarib bo\'lmaydi!');
            return;
        }

        if ($userId === $user->id) {
            $this->toast('error', 'O\'zingizni guruhdan bu tarzda chiqara olmaysiz.');
            return;
        }

        $targetMember = GroupMember::where('group_id', $this->group->id)
            ->where('user_id', $userId)
            ->with('user')
            ->first();

        if (!$targetMember) {
            return;
        }

        $this->memberToRemoveId = $userId;
        $this->memberToRemoveName = $targetMember->user->name ?? 'Foydalanuvchi';
        $this->showRemoveMemberModal = true;
    }

    public function confirmRemoveMember(): void
    {
        $user = Auth::user();
        if (!$user || !$this->group->isManagedBy($user) || !$this->memberToRemoveId) {
            return;
        }

        if ($this->memberToRemoveId === $this->group->created_by) {
            $this->toast('error', 'Guruh asoschisini chiqarib bo\'lmaydi!');
            $this->showRemoveMemberModal = false;
            return;
        }

        GroupMember::where('group_id', $this->group->id)
            ->where('user_id', $this->memberToRemoveId)
            ->delete();

        $name = $this->memberToRemoveName;
        $this->showRemoveMemberModal = false;
        $this->memberToRemoveId = null;
        $this->memberToRemoveName = '';

        $this->toast('success', "«{$name}» guruhdan muvaffaqiyatli chiqarildi.");
    }

    public function toggleModerator(int $userId): void
    {
        $user = Auth::user();
        if (!$user || !$this->group->isManagedBy($user)) {
            return;
        }

        if ($userId === $this->group->created_by) {
            return;
        }

        $member = GroupMember::where('group_id', $this->group->id)->where('user_id', $userId)->first();
        if (!$member) {
            return;
        }

        $newRole = $member->role === 'moderator' ? 'member' : 'moderator';
        $member->update(['role' => $newRole]);

        $roleText = $newRole === 'moderator' ? 'moderator etib tayinlandi' : 'oddiy a\'zo qilindi';
        $this->toast('success', "Foydalanuvchi {$roleText}.");
    }

    public function leaveGroup()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        if ($this->group->created_by === $user->id) {
            $this->toast('error', 'Guruh asoschisi guruhdan chiqa olmaydi. Kerak bo\'lsa guruhni o\'chirishingiz mumkin.');
            return;
        }

        GroupMember::where('group_id', $this->group->id)
            ->where('user_id', $user->id)
            ->delete();

        session()->flash('success', "«{$this->group->name}» guruhidan chiqdingiz.");
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
        if (!Auth::check()) {
            return;
        }

        $user = Auth::user();

        if ($user->isBanned()) {
            $this->addError('message', 'Siz bloklangansiz! Qolgan vaqt: ' . $user->ban_remaining . '. Sabab: ' . ($user->ban_reason ?? 'Qoidabuzarlik'));
            return;
        }

        if (!$this->group->canUserChat($user)) {
            $this->addError('message', 'Guruhda xabar yozish guruh egasi tomonidan vaqtincha to\'xtatilgan.');
            return;
        }

        $this->validate();

        GroupMessage::create([
            'group_id' => $this->group->id,
            'user_id'  => $user->id,
            'message'  => trim($this->message),
        ]);

        $this->reset('message');
        $this->dispatchBrowserEvent('chat-scroll-bottom');
    }

    public function sendVoiceMessage(string $path, int $duration = 0): void
    {
        if (!Auth::check()) {
            return;
        }

        $user = Auth::user();

        if ($user->isBanned()) {
            $this->addError('message', 'Siz bloklangansiz! Qolgan vaqt: ' . $user->ban_remaining . '. Sabab: ' . ($user->ban_reason ?? 'Qoidabuzarlik'));
            return;
        }

        if (!$this->group->canUserSendVoice($user)) {
            $this->addError('message', 'Guruhda ovozli xabarlar yuborish o\'chirilgan.');
            return;
        }

        $cleanPath = trim($path);
        if (empty($cleanPath)) {
            return;
        }

        GroupMessage::create([
            'group_id'       => $this->group->id,
            'user_id'        => $user->id,
            'message'        => '🎤 Ovozli xabar',
            'audio_path'     => $cleanPath,
            'audio_duration' => max(1, $duration),
        ]);

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

        $targetUser->ban($duration, $reason ?: 'Guruhda nojo\'ya harakat / qoidabuzarlik');
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
        $canManage = $this->group->isManagedBy($user);
        $canChat = $this->group->canUserChat($user);
        $canSendVoice = $this->group->canUserSendVoice($user);
        $isOwner = $user && ($this->group->created_by === $user->id);

        return view('livewire.groups.group-detail', [
            'messages'     => $messages,
            'members'      => $members,
            'canDelete'    => $canManage,
            'canManage'    => $canManage,
            'canChat'      => $canChat,
            'canSendVoice' => $canSendVoice,
            'isOwner'      => $isOwner,
        ])->layout('layouts.app', ['title' => $this->group->name]);
    }
}
