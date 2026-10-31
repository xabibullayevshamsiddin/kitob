@php
    $money = fn (int $v) => number_format($v, 0, '.', ' ');
    $statusClasses = [
        'awaiting_payment' => 'bg-amber-500/10 text-amber-700 dark:text-amber-300 border-amber-500/25',
        'pending_review'   => 'bg-sky-500/10 text-sky-700 dark:text-sky-300 border-sky-500/25',
        'approved'         => 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 border-emerald-500/25',
        'rejected'         => 'bg-rose-500/10 text-rose-700 dark:text-rose-300 border-rose-500/25',
    ];
@endphp

<div class="max-w-3xl mx-auto space-y-6 pb-20 px-3 sm:px-6">

    {{-- Sarlavha --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between pt-2">
        <div>
            <p class="font-mono text-xs uppercase tracking-widest text-amber-600 dark:text-amber-400">Hamyonni to'ldirish</p>
            <h1 class="mt-1 font-serif text-2xl sm:text-3xl font-bold text-paper">Tanga va ball sotib olish</h1>
            <p class="mt-1 text-sm text-stone-600 dark:text-mist">Kartaga o'tkazing, chekni yuklang — admin tekshirgach hisobingizga tushadi.</p>
        </div>
        <a href="{{ route('shop.index', ['tab' => 'wallet']) }}" class="ks-btn-ghost inline-flex min-h-11 items-center justify-center px-4 py-2 text-xs font-bold">← Do'konga qaytish</a>
    </div>

    @if(!$enabled)
        <div class="p-8 rounded-panel border border-ink-border bg-ink-900/80 text-center">
            <p class="text-3xl">🔒</p>
            <p class="mt-2 font-semibold text-paper">Karta orqali to'lov hozircha yopiq</p>
            <p class="mt-1 text-sm text-stone-600 dark:text-mist">Tez orada ochiladi. Hozircha tangalarni mutolaa va testlar orqali yig'ishingiz mumkin.</p>
            @if(auth()->user()->isAdmin())
                <a href="{{ route('admin.topups.settings') }}" class="mt-4 inline-flex min-h-11 items-center px-5 rounded-btn bg-amber-500 hover:bg-amber-400 text-slate-950 text-sm font-bold">
                    Admin: karta raqamini kiritib yoqish →
                </a>
            @endif
        </div>

    @elseif($open && $open->isAwaitingPayment())
        {{-- 2-bosqich: to'lov va chek --}}
        <div class="p-5 sm:p-6 rounded-panel border border-amber-500/30 bg-ink-900/80 space-y-5"
             x-data="{
                 copied: null,
                 copy(text, key, label) {
                     const doCopy = () => {
                         if (navigator.clipboard && window.isSecureContext) {
                             return navigator.clipboard.writeText(text);
                         }
                         try {
                             const ta = document.createElement('textarea');
                             ta.value = text;
                             ta.style.position = 'fixed';
                             ta.style.left = '-9999px';
                             ta.style.top = '-9999px';
                             ta.setAttribute('readonly', '');
                             document.body.appendChild(ta);
                             ta.focus();
                             ta.select();
                             document.execCommand('copy');
                             document.body.removeChild(ta);
                             return Promise.resolve();
                         } catch (e) {
                             return Promise.reject(e);
                         }
                     };

                     doCopy().then(() => {
                         this.copied = key;
                         if (window.showToast) {
                             window.showToast((label || 'Nusxa') + ' nusxalandi! 📋', 'success');
                         }
                         setTimeout(() => {
                             if (this.copied === key) this.copied = null;
                         }, 2200);
                     }).catch(() => {
                         if (window.showToast) {
                             window.showToast('Nusxa olishda xatolik yuz berdi', 'error');
                         }
                     });
                 }
             }">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div>
                    <p class="text-xs font-mono uppercase text-stone-600 dark:text-mist">So'rov #{{ $open->reference }}</p>
                    <p class="font-serif text-lg font-bold text-paper">{{ $open->package_title }} <span class="text-sm font-normal text-stone-600 dark:text-mist">→ {{ $open->reward_label }}</span></p>
                </div>
                <span class="text-xs font-mono text-stone-600 dark:text-mist">Amal qiladi: {{ $open->expires_at?->timezone('Asia/Tashkent')->format('d.m H:i') }} gacha</span>
            </div>

            <ol class="space-y-4">
                <li class="space-y-2">
                    <p class="text-sm font-semibold text-paper">1. Shu kartaga o'tkazing</p>
                    <div class="flex items-center justify-between gap-3 p-4 rounded-2xl bg-ink-950 border border-ink-border">
                        <div class="min-w-0">
                            <p class="font-mono text-lg sm:text-xl font-bold tracking-wider text-paper break-all">{{ $card }}</p>
                            @if($cardHolder || $cardBank)
                                <p class="text-xs text-stone-600 dark:text-mist">{{ $cardHolder }}{{ $cardHolder && $cardBank ? ' · ' : '' }}{{ $cardBank }}</p>
                            @endif
                        </div>
                        <button type="button"
                                @click="copy('{{ preg_replace('/\D/', '', $card) }}', 'card', 'Karta raqami')"
                                title="Karta raqamidan nusxa olish"
                                aria-label="Karta raqamidan nusxa olish"
                                :class="copied === 'card' ? 'border-emerald-500/50 bg-emerald-500/15 text-emerald-400' : 'border-ink-border bg-ink-800 hover:bg-ink-700 text-paper hover:text-amber-400 hover:border-amber-500/40'"
                                class="inline-flex items-center justify-center gap-2 px-3.5 py-2.5 rounded-btn text-xs font-bold shrink-0 min-h-11 border transition-all duration-200 select-none shadow-sm cursor-pointer">
                            <span x-show="copied !== 'card'" class="inline-flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-mist shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                                    <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                                </svg>
                                <span>Nusxa olish</span>
                            </span>
                            <span x-show="copied === 'card'" x-cloak class="inline-flex items-center gap-1.5 text-emerald-400 font-bold">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                                <span>Nusxalandi!</span>
                            </span>
                        </button>
                    </div>
                </li>

                <li class="space-y-2">
                    <p class="text-sm font-semibold text-paper">2. Aynan shu summani yuboring</p>
                    <div class="flex items-center justify-between gap-3 p-4 rounded-2xl bg-amber-500/10 border border-amber-500/30">
                        <p class="font-mono text-2xl font-bold text-amber-700 dark:text-amber-300">{{ $money($open->expected_amount) }} <span class="text-sm">so'm</span></p>
                        <button type="button"
                                @click="copy('{{ $open->expected_amount }}', 'amount', 'Summa')"
                                title="Summadan nusxa olish"
                                aria-label="Summadan nusxa olish"
                                :class="copied === 'amount' ? 'border-emerald-500/50 bg-emerald-500/15 text-emerald-400' : 'border-amber-500/30 bg-ink-800 hover:bg-ink-700 text-amber-300 hover:text-amber-200 hover:border-amber-500/60'"
                                class="inline-flex items-center justify-center gap-2 px-3.5 py-2.5 rounded-btn text-xs font-bold shrink-0 min-h-11 border transition-all duration-200 select-none shadow-sm cursor-pointer">
                            <span x-show="copied !== 'amount'" class="inline-flex items-center gap-1.5">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                                    <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                                </svg>
                                <span>Nusxa olish</span>
                            </span>
                            <span x-show="copied === 'amount'" x-cloak class="inline-flex items-center gap-1.5 text-emerald-400 font-bold">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                                <span>Nusxalandi!</span>
                            </span>
                        </button>
                    </div>
                    <p class="text-xs text-stone-600 dark:text-mist leading-relaxed">
                        ⚠️ Oxirgi <b>{{ $open->expected_amount - $open->base_amount }} so'm</b> — sizning to'lovingizni boshqalarnikidan ajratadigan belgi. Summani yaxlitlamang, aks holda tekshiruv kechikadi.
                        Izohga <b class="font-mono">{{ $open->reference }}</b> deb yozsangiz yanada yaxshi.
                    </p>
                </li>

                <li class="space-y-3">
                    <p class="text-sm font-semibold text-paper">3. To'lov chekini (skrinshot) yuklang</p>

                    <form wire:submit.prevent="submitReceipt" class="space-y-3">
                        <label class="flex flex-col items-center justify-center gap-2 p-5 rounded-2xl border-2 border-dashed border-ink-border hover:border-amber-500/40 bg-ink-950 cursor-pointer text-center transition-colors">
                            @if($receipt && method_exists($receipt, 'temporaryUrl'))
                                <img src="{{ $receipt->temporaryUrl() }}" alt="Chek ko'rinishi" class="max-h-64 rounded-xl border border-ink-border">
                                <span class="text-xs text-stone-600 dark:text-mist">Boshqa rasm tanlash uchun bosing</span>
                            @else
                                <span class="text-2xl">🧾</span>
                                <span class="text-sm font-semibold text-paper">Chek rasmini tanlang</span>
                                <span class="text-xs text-stone-600 dark:text-mist">JPG, PNG yoki WEBP · 6 MB gacha · ilovadagi asl skrinshot</span>
                            @endif
                            <input type="file" wire:model="receipt" accept="image/jpeg,image/png,image/webp" class="sr-only">
                        </label>
                        <div wire:loading wire:target="receipt" class="text-xs text-stone-600 dark:text-mist">Rasm yuklanmoqda…</div>
                        @error('receipt') <p class="text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror

                        <div class="grid gap-3 sm:grid-cols-2">
                            <label class="block space-y-1">
                                <span class="text-xs font-semibold text-paper">Siz to'lagan karta (oxirgi 4 raqam)</span>
                                <input type="text" inputmode="numeric" maxlength="4" wire:model.defer="payerCardLast4" placeholder="1234"
                                       class="w-full min-h-11 rounded-btn border border-ink-border bg-ink-950 px-3 font-mono text-paper focus:border-amber-500/50 focus:outline-none">
                                @error('payerCardLast4') <span class="text-xs text-rose-600 dark:text-rose-400">{{ $message }}</span> @enderror
                            </label>
                            <label class="block space-y-1">
                                <span class="text-xs font-semibold text-paper">Izoh (ixtiyoriy)</span>
                                <input type="text" maxlength="500" wire:model.defer="note" placeholder="Masalan: Click orqali to'ladim"
                                       class="w-full min-h-11 rounded-btn border border-ink-border bg-ink-950 px-3 text-paper focus:border-amber-500/50 focus:outline-none">
                            </label>
                        </div>

                        <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-between">
                            <button type="button" wire:click="cancelRequest"
                                    onclick="confirm('So\'rovni bekor qilasizmi? Agar pul o\'tkazgan bo\'lsangiz, bekor qilmang!') || event.stopImmediatePropagation()"
                                    class="ks-btn-ghost min-h-11 px-4 text-xs font-bold text-rose-600 dark:text-rose-300">Bekor qilish</button>
                            <button type="submit" wire:loading.attr="disabled" wire:target="submitReceipt,receipt"
                                    class="min-h-11 px-5 rounded-btn bg-amber-500 hover:bg-amber-400 text-ink-950 text-sm font-bold disabled:opacity-60 transition-colors">
                                <span wire:loading.remove wire:target="submitReceipt">Chekni yuborish</span>
                                <span wire:loading wire:target="submitReceipt">Tekshirilmoqda…</span>
                            </button>
                        </div>
                    </form>
                </li>
            </ol>

            <p class="text-[11px] text-stone-600 dark:text-mist leading-relaxed border-t border-ink-border pt-3">
                Chek avtomatik tekshiriladi: takroriy, tahrirlangan yoki AI yordamida yasalgan cheklar aniqlanadi. Soxta chek yuborgan hisob bloklanadi.
            </p>
        </div>

    @elseif($open && $open->isPendingReview())
        <div class="p-6 rounded-panel border border-sky-500/30 bg-ink-900/80 text-center space-y-2">
            <p class="text-3xl">⏳</p>
            <p class="font-serif text-lg font-bold text-paper">Chekingiz tekshirilmoqda</p>
            <p class="text-sm text-stone-600 dark:text-mist">
                #{{ $open->reference }} · {{ $money($open->expected_amount) }} so'm → {{ $open->reward_label }}.<br>
                Admin pulni tekshirgach, hisobingizga tushadi va sizga bildirishnoma keladi.
            </p>
        </div>

    @else
        {{-- 1-bosqich: paket tanlash --}}
        <div class="space-y-3">
            <div class="flex items-center justify-between text-xs text-stone-600 dark:text-mist">
                <span>Paketni tanlang</span>
                <span class="font-mono">Bugungi limit: {{ $money($spentToday) }} / {{ $money($dailyLimit) }} so'm</span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                @forelse($packages as $package)
                    <button type="button" wire:click="choosePackage('{{ $package['key'] }}')" wire:loading.attr="disabled"
                            class="text-left p-4 rounded-2xl bg-ink-900/80 border border-ink-border hover:border-amber-500/40 transition-colors flex items-center justify-between gap-3 disabled:opacity-60">
                        <span>
                            <span class="block font-serif text-base font-bold text-paper">{{ $package['title'] }}</span>
                            <span class="block text-xs text-stone-600 dark:text-mist">
                                @if($package['coins'] > 0) 🪙 {{ $money($package['coins']) }} tanga @endif
                                @if($package['coins'] > 0 && $package['points'] > 0) + @endif
                                @if($package['points'] > 0) ⭐ {{ $money($package['points']) }} ball @endif
                            </span>
                        </span>
                        <span class="shrink-0 font-mono text-sm font-bold text-amber-700 dark:text-amber-300">{{ $money($package['price']) }} so'm</span>
                    </button>
                @empty
                    <p class="text-sm text-stone-600 dark:text-mist">Hozircha paketlar yo'q.</p>
                @endforelse
            </div>
        </div>
    @endif

    {{-- Tarix --}}
    @if($history->isNotEmpty())
        <div class="rounded-panel border border-ink-border bg-ink-900/80 overflow-hidden">
            <p class="px-5 pt-4 pb-2 text-xs font-mono uppercase text-stone-600 dark:text-mist">So'rovlar tarixi</p>
            <ul class="divide-y divide-ink-border">
                @foreach($history as $item)
                    <li class="px-5 py-3 flex flex-wrap items-center justify-between gap-2 text-sm">
                        <span class="min-w-0">
                            <span class="font-mono text-xs text-stone-600 dark:text-mist">#{{ $item->reference }} · {{ $item->created_at->timezone('Asia/Tashkent')->format('d.m.Y H:i') }}</span>
                            <span class="block text-paper">{{ $item->reward_label }} — {{ $money($item->expected_amount) }} so'm</span>
                            @if($item->reject_reason)
                                <span class="block text-xs text-rose-600 dark:text-rose-400">Sabab: {{ $item->reject_reason }}</span>
                            @endif
                        </span>
                        <span class="px-2.5 py-1 rounded-full border text-xs font-semibold {{ $statusClasses[$item->status] ?? 'bg-slate-500/10 text-slate-600 dark:text-slate-400 border-slate-500/20' }}">{{ $item->status_label }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
