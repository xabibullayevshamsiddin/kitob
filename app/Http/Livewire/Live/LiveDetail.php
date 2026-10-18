<?php

namespace App\Http\Livewire\Live;

use App\Http\Livewire\Concerns\WithToast;
use App\Models\Follow;
use App\Models\LiveEvent;
use App\Models\LiveEventLike;
use App\Models\LiveQuestion;
use App\Models\LiveSignal;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class LiveDetail extends Component
{
    use WithToast;

    public LiveEvent $event;

    public string $question = '';

    public string $activeTab = 'chat'; // 'chat', 'settings', 'questions'

    protected $rules = [
        'question' => 'required|string|min:2|max:300',
    ];

    public function mount(LiveEvent $event)
    {
        $this->event = $event;

        // Agar bu guruh jonli efiri bo'lsa — faqat guruh a'zolari va admin kira oladi!
        if ($this->event->group_id) {
            if (!Auth::check()) {
                session()->flash('warning', 'Guruh jonli efiriga kirish uchun avval tizimga kiring.');
                return redirect()->route('login');
            }

            $user = Auth::user();
            $isMember = \App\Models\GroupMember::where('group_id', $this->event->group_id)
                ->where('user_id', $user->id)
                ->exists();

            $isAdmin = (method_exists($user, 'hasRole') && $user->hasRole('admin'))
                || ($user->role === 'admin')
                || ($this->event->host_user_id === $user->id);

            if (!$isMember && !$isAdmin) {
                session()->flash('error', 'Ushbu jonli efir faqat «' . ($this->event->group->name ?? 'Guruh') . '» a\'zolari uchun ochiq.');
                return redirect()->route('groups.index');
            }
        }

        if ($this->event->status === LiveEvent::STATUS_LIVE && !$this->event->started_at) {
            $this->event->update(['started_at' => $this->event->created_at ?? now()]);
            $this->event->refresh();
        }
    }

    /**
     * Efirni boshqarish huquqi (tugatish/qayta boshlash/sozlamalar).
     * Host yoki admin/teacher (moderatsiya uchun) — lekin WebRTC broadcaster faqat host.
     */
    public function getCanManageProperty(): bool
    {
        if (!Auth::check()) {
            return false;
        }

        return $this->isHost || Auth::user()->isAdminOrTeacher();
    }

    public function getIsHostProperty(): bool
    {
        if (!Auth::check()) {
            return false;
        }

        $user = Auth::user();

        // Host: faqat efir egasi (yoki host tayinlanmagan bo'lsa teacher).
        // Admin atayin broadcaster bo'lishi kerak bo'lsa — efirni o'zi yaratishi kerak
        // ("Jonli Efir Boshlash" tugmasi orqali). Oddiy admin kirganda tomoshabin bo'ladi,
        // aks holda ikkala admin 'host' peer ID bilan to'qnashib, signalling buziladi.
        return $this->event->host_user_id === $user->id
            || ($user->hasRole('teacher') && $this->event->host_user_id === null);
    }

    public function updatePermissionMode(string $mode): void
    {
        // FAQAT HOST (efir egasi) sozlamalarni o'zgartirishi mumkin.
        // Admin/teacher boshqa odam efirini boshqara olmaydi — aks holda "boshqalar uchun"
        // sozlama ikkita turli xosda ikki xil qiymatga aylanadi.
        if (!$this->isHost) {
            $this->toast('error', 'Faqat efir muallifi sozlamalarni o\'zgartirishi mumkin.');
            return;
        }

        if (!in_array($mode, ['chat_only', 'view_only', 'both', 'voice_only'], true)) {
            return;
        }

        $savedMode = match($mode) {
            'both'       => 'chat_only',
            'voice_only' => 'view_only',
            default      => $mode,
        };

        $this->event->update(['permission_mode' => $savedMode]);
        $this->event->refresh();

        $this->toast('success', 'Tashrif buyuruvchilar ruxsat rejimi yangilandi!');
    }

    public function endLiveStream()
    {
        if (!$this->canManage) {
            $this->toast('error', 'Faqat efir muallifi efirni yakunlashi mumkin.');
            return null;
        }

        $groupId = $this->event->group_id;

        // Foydalanuvchi talabi: o'tib ketgan / yakunlangan efirlar saytda saqlanib qolmasin
        $this->event->questions()->delete();
        \App\Models\LiveSignal::where('live_event_id', $this->event->id)->delete();
        $this->event->delete();

        session()->flash('success', 'Jonli efir muvaffaqiyatli yakunlandi va saytdan o\'chirildi.');

        if ($groupId) {
            return redirect()->route('groups.show', $groupId);
        }

        return redirect()->route('live.index');
    }

    public function restartLiveStream(): void
    {
        if (!$this->isHost) {
            return;
        }

        $this->event->update([
            'status'     => LiveEvent::STATUS_LIVE,
            'started_at' => now(),
        ]);
        $this->event->refresh();

        $this->toast('success', 'Jonli efir qayta boshlandi!');
    }

    /**
     * YouTube uslubidagi like: har bir foydalanuvchi efirga faqat BITTA like bosa oladi.
     * Qayta bosilsa like olib tashlanadi (toggle). Dislike yo'q.
     *
     * @return array{liked: bool, total: int}|null
     */
    public function toggleLike(): ?array
    {
        $this->skipRender();

        if (!Auth::check()) {
            $this->toast('error', 'Like bosish uchun avval tizimga kiring.');
            return null;
        }

        $userId = Auth::id();
        $eventId = $this->event->id;

        $existing = LiveEventLike::where('live_event_id', $eventId)
            ->where('user_id', $userId)
            ->first();

        if ($existing) {
            $existing->delete();
            $liked = false;
            LiveEvent::where('id', $eventId)->where('likes_count', '>', 0)->decrement('likes_count', 1);
        } else {
            LiveEventLike::firstOrCreate(['live_event_id' => $eventId, 'user_id' => $userId]);
            $liked = true;
            LiveEvent::where('id', $eventId)->increment('likes_count', 1);
        }

        $total = (int) LiveEvent::where('id', $eventId)->value('likes_count');
        $this->event->likes_count = $total;

        // Barcha tomoshabinlarga yangi umumiy sonni signal qilib yuboramiz
        LiveSignal::create([
            'live_event_id' => $eventId,
            'sender_id'     => 'server_' . $userId,
            'receiver_id'   => 'all',
            'type'          => 'like',
            'payload'       => json_encode(['total' => $total]),
            'created_at'    => now(),
        ]);

        return ['liked' => $liked, 'total' => $total];
    }

    /**
     * Testlar va mavjud klientlar bilan moslik: cheksiz tap (batch likes) yuborish.
     */
    public function sendLikes(int $count = 1): void
    {
        if (!Auth::check()) {
            $this->toast('error', 'Like bosish uchun avval tizimga kiring.');
            return;
        }

        $count = max(1, min(50, $count));

        LiveEventLike::firstOrCreate([
            'live_event_id' => $this->event->id,
            'user_id'       => Auth::id(),
        ]);

        LiveEvent::where('id', $this->event->id)->increment('likes_count', $count);
        $newTotal = (int) LiveEvent::where('id', $this->event->id)->value('likes_count');
        $this->event->likes_count = $newTotal;

        LiveSignal::create([
            'live_event_id' => $this->event->id,
            'sender_id'     => 'server_' . Auth::id(),
            'receiver_id'   => 'all',
            'type'          => 'like',
            'payload'       => json_encode(['total' => $newTotal]),
            'created_at'    => now(),
        ]);
    }

    /**
     * Efir muallifiga (ustoz/admin) obuna bo'lish yoki obunani bekor qilish.
     *
     * @return array{following: bool, count: int}|null
     */
    public function toggleFollow(): ?array
    {
        $this->skipRender();

        if (!Auth::check()) {
            $this->toast('error', 'Obuna bo\'lish uchun avval tizimga kiring.');
            return null;
        }

        $hostId = $this->event->host_user_id;
        $userId = Auth::id();

        if (!$hostId || (int) $hostId === (int) $userId) {
            return null;
        }

        $existing = Follow::where('follower_id', $userId)
            ->where('following_id', $hostId)
            ->first();

        if ($existing) {
            $existing->delete();
            $following = false;
        } else {
            Follow::firstOrCreate(['follower_id' => $userId, 'following_id' => $hostId]);
            $following = true;
        }

        return [
            'following' => $following,
            'count'     => Follow::where('following_id', $hostId)->count(),
        ];
    }

    public function submitQuestion(): void
    {
        if (!Auth::check()) {
            $this->toast('error', 'Savol yozish uchun avval tizimga kiring.');
            return;
        }

        // Check if chat is allowed
        if (in_array($this->event->permission_mode, ['view_only', 'voice_only'], true) && !$this->isHost) {
            $this->toast('error', 'Ushbu efirda yozma chat cheklangan.');
            return;
        }

        $this->validate([
            'question' => 'required|string|min:2|max:300',
        ], [
            'question.required' => 'Xabaringizni yozing.',
            'question.min'      => 'Xabar kamida 2 belgidan iborat bo\'lsin.',
        ]);

        LiveQuestion::create([
            'live_event_id' => $this->event->id,
            'user_id'       => Auth::id(),
            'question'      => trim($this->question),
            'is_selected'   => false,
            'is_answered'   => false,
        ]);

        $this->reset('question');

        $this->toast('success', 'Xabaringiz yuborildi!');
    }

    public function requestVoiceSpeech(): void
    {
        $this->toast('error', 'Jonli efirda tashrif buyuruvchilar uchun ovozli suhbat o\'chirilgan.');
    }

    public function markQuestionAnswered(int $questionId): void
    {
        if (!$this->isHost) {
            return;
        }

        $q = LiveQuestion::where('live_event_id', $this->event->id)->find($questionId);
        if ($q) {
            $q->update(['is_answered' => !$q->is_answered]);
        }
    }

    public function toggleSelectQuestion(int $questionId): void
    {
        if (!$this->isHost) {
            return;
        }

        $q = LiveQuestion::where('live_event_id', $this->event->id)->find($questionId);
        if ($q) {
            $q->update(['is_selected' => !$q->is_selected]);
        }
    }

    public function render()
    {
        $allQuestions = LiveQuestion::where('live_event_id', $this->event->id)
            ->with('user:id,name,username,avatar')
            ->latest()
            ->take(40)
            ->get()
            ->reverse();

        $selectedQuestions = LiveQuestion::where('live_event_id', $this->event->id)
            ->selected()
            ->with('user:id,name,username,avatar')
            ->latest()
            ->get();

        $myQuestions = Auth::check()
            ? LiveQuestion::where('live_event_id', $this->event->id)
                ->where('user_id', Auth::id())
                ->latest()
                ->take(5)
                ->get()
            : collect();

        $userId = Auth::id();
        $hostId = $this->event->host_user_id;

        $hasLiked = $userId
            ? LiveEventLike::where('live_event_id', $this->event->id)->where('user_id', $userId)->exists()
            : false;

        $isFollowingHost = ($userId && $hostId)
            ? Follow::where('follower_id', $userId)->where('following_id', $hostId)->exists()
            : false;

        $hostFollowersCount = $hostId ? Follow::where('following_id', $hostId)->count() : 0;

        return view('livewire.live.live-detail', [
            'allQuestions'       => $allQuestions,
            'selectedQuestions'  => $selectedQuestions,
            'myQuestions'        => $myQuestions,
            'isHost'             => $this->isHost,
            'canManage'          => $this->canManage,
            'canEditSettings'    => $this->isHost,
            'hasLiked'           => $hasLiked,
            'isFollowingHost'    => $isFollowingHost,
            'hostFollowersCount' => $hostFollowersCount,
            'isOwnStream'        => $userId && $hostId && (int) $userId === (int) $hostId,
        ])->layout('layouts.app', ['title' => $this->event->title]);
    }
}
