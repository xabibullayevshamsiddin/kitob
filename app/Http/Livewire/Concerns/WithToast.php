<?php

namespace App\Http\Livewire\Concerns;

/**
 * Livewire komponentlaridan refreshsiz toast bildirishnoma chiqarish.
 *
 * Livewire AJAX amallarida session()->flash() ishlamaydi (sahifa qayta yuklanmaydi,
 * DOMContentLoaded otilmaydi), shuning uchun browser event orqali yuboriladi.
 * Agar metod redirect()->route(...) qaytarsa, session()->flash() ishlatilsin —
 * sahifa to'liq qayta yuklanadi va toast-container dagi session ko'prigi uni ko'rsatadi.
 */
trait WithToast
{
    protected function toast(string $type, string $message, ?string $title = null): void
    {
        $payload = ['type' => $type, 'message' => $message];

        if ($title !== null) {
            $payload['title'] = $title;
        }

        $this->dispatchBrowserEvent('toast', $payload);
    }

    protected function toastSuccess(string $message, ?string $title = null): void
    {
        $this->toast('success', $message, $title);
    }

    protected function toastError(string $message, ?string $title = null): void
    {
        $this->toast('error', $message, $title);
    }

    protected function toastWarning(string $message, ?string $title = null): void
    {
        $this->toast('warning', $message, $title);
    }

    protected function toastInfo(string $message, ?string $title = null): void
    {
        $this->toast('info', $message, $title);
    }
}
