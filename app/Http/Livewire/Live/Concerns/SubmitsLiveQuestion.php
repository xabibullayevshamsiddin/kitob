<?php

namespace App\Http\Livewire\Live\Concerns;

use App\Models\LiveQuestion;
use App\Models\User;
use App\Services\Shop\Exceptions\ShopException;
use App\Services\Shop\ShopService;
use Illuminate\Support\Facades\Auth;

trait SubmitsLiveQuestion
{
    public bool $isPriority = false;

    /**
     * Foydalanuvchida ustuvor savol chiptalari soni.
     */
    public function getPriorityTicketsCountProperty(): int
    {
        if (!Auth::check()) {
            return 0;
        }

        return app(ShopService::class)->getLivePriorityTicketCount(Auth::user());
    }

    /**
     * Ustuvor savol tanlovi ko'rinishi va tanlanishi mumkinmi?
     */
    public function getCanMakePriorityProperty(): bool
    {
        if (!Auth::check() || $this->priorityTicketsCount <= 0) {
            return false;
        }

        $eventId = $this->resolveEventIdForSubmission();
        if (!$eventId) {
            return false;
        }

        return app(ShopService::class)->canSubmitLivePriorityQuestion(Auth::user(), $eventId);
    }

    /**
     * Efir ID sini aniqlash (zarurat bo'lsa komponent tomonidan qayta yozilishi mumkin).
     */
    protected function resolveEventIdForSubmission(): ?int
    {
        if (isset($this->eventId) && $this->eventId) {
            return (int) $this->eventId;
        }

        if (isset($this->event) && $this->event) {
            return (int) $this->event->id;
        }

        return null;
    }

    /**
     * Savol yaratish va agar ustuvor bo'lsa chiptani atomik sarflash.
     *
     * @throws ShopException
     */
    protected function handleQuestionSubmission(User $user, int $liveEventId, string $questionText): LiveQuestion
    {
        $question = app(ShopService::class)->createLiveQuestion(
            $user,
            $liveEventId,
            $questionText,
            $this->isPriority
        );

        $this->isPriority = false;

        return $question;
    }
}
