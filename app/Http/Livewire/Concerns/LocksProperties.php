<?php

namespace App\Http\Livewire\Concerns;

use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Livewire 2 da har bir public property brauzerdan (syncInput orqali) o'zgartirilishi mumkin.
 * Ruxsat/egalik uchun ishlatiladigan ID va bayroqlarni shu trait bilan qulflang:
 *
 *     protected array $lockedProperties = ['eventId', 'isHost'];
 *
 * Qulflangan property'larni faqat server tomoni (mount/action) o'zgartira oladi.
 */
trait LocksProperties
{
    public function updatingLocksProperties(string $name, $value): void
    {
        $property = Str::before($name, '.');

        if (in_array($property, $this->lockedProperties ?? [], true)) {
            throw new HttpException(403, "\"{$property}\" maydonini o'zgartirib bo'lmaydi.");
        }
    }
}
