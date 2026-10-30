<?php

namespace App\Http\Livewire\Book;

use App\Http\Livewire\Concerns\LocksProperties;
use App\Http\Livewire\Concerns\WithToast;
use App\Models\Book;
use App\Models\BuddyRead;
use App\Models\BuddyReadMember;
use App\Models\BuddyReadMessage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class BuddyReadModal extends Component
{
    use WithToast;
    use LocksProperties;

    /** Xona ID faqat serverda (parol/a'zolik tekshiruvidan keyin) o'rnatiladi */
    protected array $lockedProperties = ['activeBuddyReadId'];

    public Book $book;
    public ?int $activeBuddyReadId = null;
    public string $newTitle = '';
    public bool $isPrivate = false;
    public string $roomPassword = '';
    public ?int $passwordPromptRoomId = null;
    public string $joinPassword = '';
    public string $newMessage = '';
    public int $messagePage = 1;
    public bool $isSpoiler = false;
    public array $revealedSpoilers = [];

    protected $rules = [
        'newTitle' => 'required|string|min:3|max:100',
    ];

    public function mount(Book $book)
    {
        $this->book = $book;
        if (Auth::check()) {
            $existing = BuddyRead::where('book_id', $this->book->id)
                ->whereHas('members', fn($q) => $q->where('user_id', Auth::id()))
                ->latest()
                ->first();

            if ($existing) {
                $this->activeBuddyReadId = $existing->id;
            }
        }
    }

    public function createBuddyRead(): void
    {
        if (!Auth::check()) {
            $this->toastWarning("Birgalikda mutolaa yaratish uchun profilingizga kiring.");
            return;
        }

        $rules = [
            'newTitle' => 'required|string|min:3|max:100',
        ];
        if ($this->isPrivate) {
            $rules['roomPassword'] = 'required|string|min:4|max:50';
        }

        $this->validate($rules, [
            'newTitle.required' => 'Guruh nomini kiriting.',
            'newTitle.min' => 'Guruh nomi kamida 3 ta belgidan iborat bo\'lsin.',
            'roomPassword.required' => 'Xona parolini kiriting.',
            'roomPassword.min' => 'Parol kamida 4 ta belgidan iborat bo\'lsin.',
        ]);

        $buddyRead = BuddyRead::create([
            'book_id'     => $this->book->id,
            'creator_id'  => Auth::id(),
            'title'       => trim($this->newTitle),
            'status'      => 'active',
            'max_members' => 5,
            'is_private'  => $this->isPrivate,
            'password'    => $this->isPrivate ? Hash::make(trim($this->roomPassword)) : null,
        ]);

        BuddyReadMember::create([
            'buddy_read_id' => $buddyRead->id,
            'user_id'       => Auth::id(),
            'current_page'  => 1,
        ]);

        $this->activeBuddyReadId = $buddyRead->id;
        $this->newTitle = '';
        $this->roomPassword = '';
        $this->isPrivate = false;
        $this->toastSuccess("Birgalikda mutolaa xonasi muvaffaqiyatli yaratildi! Do'stlaringizni taklif qiling.");
    }

    public function joinBuddyRead(int $id): void
    {
        if (!Auth::check()) {
            $this->toastWarning("A'zo bo'lish uchun tizimga kiring.");
            return;
        }

        $buddyRead = BuddyRead::withCount('members')->findOrFail($id);

        $isMember = BuddyReadMember::where('buddy_read_id', $buddyRead->id)->where('user_id', Auth::id())->exists();
        if ($buddyRead->is_private && !$isMember && $buddyRead->creator_id !== Auth::id()) {
            $this->passwordPromptRoomId = $buddyRead->id;
            $this->joinPassword = '';
            return;
        }

        if ($buddyRead->members_count >= $buddyRead->max_members) {
            $this->toastError("Ushbu xonada a'zolar soni to'lgan (maksimum {$buddyRead->max_members} kishi).");
            return;
        }

        BuddyReadMember::firstOrCreate([
            'buddy_read_id' => $buddyRead->id,
            'user_id'       => Auth::id(),
        ], [
            'current_page'  => 1,
        ]);

        $this->activeBuddyReadId = $buddyRead->id;
        $this->toastSuccess("Siz birgalikda mutolaaga qo'shildingiz!");
    }

    public function sendMessage(): void
    {
        if (!Auth::check() || !$this->activeBuddyReadId) return;

        $msg = trim($this->newMessage);
        if (empty($msg)) return;

        BuddyReadMessage::create([
            'buddy_read_id' => $this->activeBuddyReadId,
            'user_id'       => Auth::id(),
            'message'       => $msg,
            'page_number'   => max(1, (int) $this->messagePage),
            'is_spoiler'    => $this->isSpoiler,
        ]);

        // Foydalanuvchining a'zolikdagi progressini yangilash
        BuddyReadMember::where('buddy_read_id', $this->activeBuddyReadId)
            ->where('user_id', Auth::id())
            ->update([
                'current_page' => max(1, (int) $this->messagePage),
            ]);

        $this->newMessage = '';
        $this->isSpoiler = false;
        $this->emit('buddy-message-sent');
        $this->dispatchBrowserEvent('buddy-chat-scroll');
    }

    public function sendVoiceNote(string $audioPath, int $duration = 0): void
    {
        if (!Auth::check() || !$this->activeBuddyReadId) return;
        if (empty($audioPath)) return;

        BuddyReadMessage::create([
            'buddy_read_id'   => $this->activeBuddyReadId,
            'user_id'         => Auth::id(),
            'message'         => null,
            'audio_path'      => $audioPath,
            'audio_duration'  => max(1, $duration),
            'page_number'     => max(1, (int) $this->messagePage),
            'is_spoiler'      => $this->isSpoiler,
        ]);

        BuddyReadMember::where('buddy_read_id', $this->activeBuddyReadId)
            ->where('user_id', Auth::id())
            ->update([
                'current_page'  => max(1, (int) $this->messagePage),
                'last_voice_at' => now(),
            ]);

        $this->isSpoiler = false;
        $this->emit('buddy-message-sent');
        $this->dispatchBrowserEvent('buddy-chat-scroll');
    }

    public function toggleVoiceActive(bool $active): void
    {
        if (!Auth::check() || !$this->activeBuddyReadId) return;

        BuddyReadMember::where('buddy_read_id', $this->activeBuddyReadId)
            ->where('user_id', Auth::id())
            ->update([
                'voice_active'  => $active,
                'last_voice_at' => now(),
            ]);
    }

    public function updateCurrentPage(int $page): void
    {
        if (!Auth::check() || !$this->activeBuddyReadId) return;

        BuddyReadMember::where('buddy_read_id', $this->activeBuddyReadId)
            ->where('user_id', Auth::id())
            ->update([
                'current_page' => max(1, $page),
            ]);
    }

    public function revealSpoiler(int $messageId): void
    {
        $this->revealedSpoilers[$messageId] = true;
    }

    public function backToRooms(): void
    {
        $this->activeBuddyReadId = null;
        $this->passwordPromptRoomId = null;
        $this->joinPassword = '';
    }

    public function selectBuddyRead($id = null): void
    {
        if ($id === null || $id === 'null' || $id === '' || $id === 0) {
            $this->activeBuddyReadId = null;
            $this->passwordPromptRoomId = null;
            $this->joinPassword = '';
            return;
        }

        $id = (int) $id;
        $room = BuddyRead::find($id);
        if (!$room) {
            $this->activeBuddyReadId = null;
            return;
        }

        // Agar yopiq (parolli) guruh bo'lsa va foydalanuvchi hali a'zo/muallif bo'lmasa:
        if ($room->is_private) {
            if (!Auth::check()) {
                $this->toastWarning("Yopiq guruhga kirish uchun avval tizimga kiring.");
                return;
            }

            $isMemberOrCreator = ($room->creator_id === Auth::id()) ||
                BuddyReadMember::where('buddy_read_id', $room->id)->where('user_id', Auth::id())->exists();

            if (!$isMemberOrCreator) {
                $this->passwordPromptRoomId = $room->id;
                $this->joinPassword = '';
                $this->resetValidation();
                return;
            }
        }

        $this->passwordPromptRoomId = null;
        $this->activeBuddyReadId = $id;
    }

    public function submitRoomPassword(): void
    {
        if (!Auth::check()) {
            $this->toastWarning("Guruhga kirish uchun tizimga kiring.");
            return;
        }

        $this->validate([
            'joinPassword' => 'required|string',
        ], [
            'joinPassword.required' => 'Xona parolini kiriting.',
        ]);

        if (!$this->passwordPromptRoomId) return;

        $room = BuddyRead::withCount('members')->find($this->passwordPromptRoomId);
        if (!$room) {
            $this->passwordPromptRoomId = null;
            $this->toastError("Xona topilmadi.");
            return;
        }

        if (!$room->checkPassword($this->joinPassword)) {
            $this->addError('joinPassword', 'Kiritilgan parol noto\'g\'ri! Qaytadan urinib ko\'ring.');
            return;
        }

        if ($room->members_count >= $room->max_members) {
            $this->toastError("Ushbu xonada a'zolar soni to'lgan (maksimum {$room->max_members} kishi).");
            $this->passwordPromptRoomId = null;
            return;
        }

        BuddyReadMember::firstOrCreate([
            'buddy_read_id' => $room->id,
            'user_id'       => Auth::id(),
        ], [
            'current_page'  => 1,
        ]);

        $this->activeBuddyReadId = $room->id;
        $this->passwordPromptRoomId = null;
        $this->joinPassword = '';
        $this->toastSuccess("Parol to'g'ri! Siz guruhga muvaffaqiyatli qo'shildingiz.");
    }

    public function cancelPasswordPrompt(): void
    {
        $this->passwordPromptRoomId = null;
        $this->joinPassword = '';
        $this->resetValidation();
    }

    public function render()
    {
        $allRooms = BuddyRead::where('book_id', $this->book->id)
            ->where('status', 'active')
            ->with(['creator', 'members.user'])
            ->withCount('members')
            ->latest()
            ->take(10)
            ->get();

        $activeRoom = $this->activeBuddyReadId
            ? BuddyRead::with(['members.user', 'messages.user'])->find($this->activeBuddyReadId)
            : null;

        $myMember = null;
        if ($activeRoom && Auth::check()) {
            $myMember = $activeRoom->members->firstWhere('user_id', Auth::id());
        }

        $promptRoom = $this->passwordPromptRoomId
            ? BuddyRead::with('creator')->find($this->passwordPromptRoomId)
            : null;

        return view('livewire.book.buddy-read-modal', [
            'allRooms'   => $allRooms,
            'activeRoom' => $activeRoom,
            'myMember'   => $myMember,
            'promptRoom' => $promptRoom,
        ]);
    }
}
