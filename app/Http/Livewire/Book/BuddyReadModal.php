<?php

namespace App\Http\Livewire\Book;

use App\Http\Livewire\Concerns\WithToast;
use App\Models\Book;
use App\Models\BuddyRead;
use App\Models\BuddyReadMember;
use App\Models\BuddyReadMessage;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class BuddyReadModal extends Component
{
    use WithToast;

    public Book $book;
    public ?int $activeBuddyReadId = null;
    public string $newTitle = '';
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

        $this->validate(['newTitle' => 'required|string|min:3|max:100']);

        $buddyRead = BuddyRead::create([
            'book_id'     => $this->book->id,
            'creator_id'  => Auth::id(),
            'title'       => trim($this->newTitle),
            'status'      => 'active',
            'max_members' => 5,
        ]);

        BuddyReadMember::create([
            'buddy_read_id' => $buddyRead->id,
            'user_id'       => Auth::id(),
            'current_page'  => 1,
        ]);

        $this->activeBuddyReadId = $buddyRead->id;
        $this->newTitle = '';
        $this->toastSuccess("Birgalikda mutolaa xonasi muvaffaqiyatli yaratildi! Do'stlaringizni taklif qiling.");
    }

    public function joinBuddyRead(int $id): void
    {
        if (!Auth::check()) {
            $this->toastWarning("A'zo bo'lish uchun tizimga kiring.");
            return;
        }

        $buddyRead = BuddyRead::withCount('members')->findOrFail($id);
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

    public function revealSpoiler(int $messageId): void
    {
        $this->revealedSpoilers[$messageId] = true;
    }

    public function selectBuddyRead(?int $id): void
    {
        $this->activeBuddyReadId = $id;
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

        return view('livewire.book.buddy-read-modal', [
            'allRooms'   => $allRooms,
            'activeRoom' => $activeRoom,
            'myMember'   => $myMember,
        ]);
    }
}
