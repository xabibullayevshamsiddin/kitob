@extends('admin.layouts.app')
@section('title', 'To\'lov #' . $topup->reference)

@section('content')
@php
    $money = fn ($v) => $v === null ? '—' : number_format((int) $v, 0, '.', ' ') . " so'm";
    $levelStyles = [
        'danger' => ['border-rose-500/30 bg-rose-500/10 text-rose-300', '⛔'],
        'warn'   => ['border-amber-500/25 bg-amber-500/10 text-amber-300', '⚠️'],
        'info'   => ['border-ink-border bg-ink-800 text-mist', 'ℹ️'],
        'ok'     => ['border-emerald-500/25 bg-emerald-500/10 text-emerald-300', '✅'],
    ];
    $ai = $topup->ai_result;
    $riskText = ['low' => 'Past', 'medium' => 'O\'rta', 'high' => 'Yuqori'][$topup->risk_level];
    $riskClass = ['low' => 'text-emerald-300', 'medium' => 'text-amber-300', 'high' => 'text-rose-300'][$topup->risk_level];
    $match = fn ($actual, $expected) => $actual === null ? 'text-mist' : ((string) $actual === (string) $expected ? 'text-emerald-300' : 'text-rose-300');
@endphp
<div class="mx-auto max-w-5xl space-y-5">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div class="min-w-0">
            <a href="{{ route('admin.topups.index') }}" class="inline-flex min-h-10 items-center text-sm font-semibold text-amber-400 hover:text-amber-300">← Barcha to'lovlar</a>
            <h1 class="mt-1 font-serif text-2xl font-bold text-paper">#{{ $topup->reference }} · {{ $money($topup->expected_amount) }}</h1>
            <p class="mt-1 text-sm text-mist">
                <a href="{{ route('admin.users.edit', $topup->user_id) }}" class="font-semibold text-paper hover:text-amber-300">{{ $topup->user?->name }}</a>
                {{ '@' . $topup->user?->username }} · {{ $topup->package_title }} → {{ $topup->reward_label }}
            </p>
        </div>
        <span class="shrink-0 rounded-pill border border-ink-border bg-ink-800 px-3 py-2 text-xs font-bold text-paper">{{ $topup->status_label }}</span>
    </div>

    @include('admin.topups.partials.flash')

    <div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_minmax(0,1.1fr)]">
        {{-- Chek rasmi --}}
        <div class="ks-panel p-3">
            @if($topup->receipt_path)
                <a href="{{ route('admin.topups.receipt', $topup) }}" target="_blank" rel="noopener" title="To'liq o'lchamda ochish">
                    <img src="{{ route('admin.topups.receipt', $topup) }}" alt="To'lov cheki" class="w-full rounded-lg border border-ink-border bg-ink-950 object-contain max-h-[75vh]">
                </a>
                <p class="mt-2 text-center font-mono text-[11px] text-mist">
                    {{ number_format(($topup->receipt_size ?? 0) / 1024, 0) }} KB · {{ $topup->receipt_mime }} · yuklangan {{ $topup->submitted_at?->timezone('Asia/Tashkent')->format('d.m.Y H:i:s') }}
                </p>
            @else
                <div class="p-10 text-center text-sm text-mist">Chek hali yuklanmagan.</div>
            @endif
        </div>

        <div class="space-y-5">
            {{-- Kutilgan va chekdagi ma'lumot --}}
            <div class="ks-panel p-4 space-y-3">
                <div class="flex items-center justify-between">
                    <p class="font-semibold text-paper">Solishtirish</p>
                    <p class="text-sm">Xavf: <span class="font-mono font-bold {{ $riskClass }}">{{ $topup->risk_score }}/100 · {{ $riskText }}</span></p>
                </div>
                <dl class="grid grid-cols-[auto_1fr_1fr] gap-x-4 gap-y-2 text-sm">
                    <dt></dt><dt class="text-xs font-mono uppercase text-mist">Kutilgan</dt><dt class="text-xs font-mono uppercase text-mist">Chekda (AI)</dt>
                    <dt class="text-mist">Summa</dt>
                    <dd class="font-mono font-bold text-paper">{{ $money($topup->expected_amount) }}</dd>
                    <dd class="font-mono font-bold {{ $match($ai['amount'] ?? null, $topup->expected_amount) }}">{{ $money($ai['amount'] ?? null) }}</dd>
                    <dt class="text-mist">Karta</dt>
                    <dd class="font-mono text-paper">•••• {{ $topup->card_last4 }}</dd>
                    <dd class="font-mono {{ $match($ai['recipient_card_last4'] ?? null, $topup->card_last4) }}">{{ isset($ai['recipient_card_last4']) ? '•••• ' . $ai['recipient_card_last4'] : '—' }}</dd>
                    <dt class="text-mist">Vaqt</dt>
                    <dd class="font-mono text-paper">{{ $topup->created_at->timezone('Asia/Tashkent')->format('d.m H:i') }} dan keyin</dd>
                    <dd class="font-mono text-paper">{{ $ai['datetime'] ?? '—' }}</dd>
                    <dt class="text-mist">To'lovchi</dt>
                    <dd class="font-mono text-paper">{{ $topup->payer_card_last4 ? '•••• ' . $topup->payer_card_last4 : '—' }}</dd>
                    <dd class="font-mono text-paper">{{ isset($ai['sender_card_last4']) ? '•••• ' . $ai['sender_card_last4'] : '—' }}</dd>
                </dl>
                @if($ai)
                    <p class="text-xs text-mist">
                        Tranzaksiya: <span class="font-mono text-paper">{{ $ai['transaction_id'] ?? '—' }}</span>
                        · Ilova: {{ $ai['app_name'] ?? '—' }} · Qabul qiluvchi: {{ $ai['recipient_name'] ?? '—' }}
                        · Ishonch: {{ round(($ai['confidence'] ?? 0) * 100) }}%
                    </p>
                @endif
                @if($topup->user_note)
                    <p class="text-sm text-mist">Foydalanuvchi izohi: <span class="text-paper">{{ $topup->user_note }}</span></p>
                @endif
            </div>

            {{-- Avtomatik tekshiruvlar --}}
            <div class="ks-panel p-4 space-y-2">
                <div class="flex items-center justify-between gap-2">
                    <p class="font-semibold text-paper">Avtomatik tekshiruvlar</p>
                    @if($topup->isPendingReview())
                        <form method="POST" action="{{ route('admin.topups.reanalyze', $topup) }}">
                            @csrf
                            <button class="ks-btn-ghost min-h-9 px-3 text-xs font-bold">Qayta tekshirish</button>
                        </form>
                    @endif
                </div>
                @forelse($topup->checks ?? [] as $check)
                    @php [$cls, $icon] = $levelStyles[$check['level']] ?? $levelStyles['info']; @endphp
                    <p class="rounded-lg border px-3 py-2 text-sm {{ $cls }}">{{ $icon }} {{ $check['message'] }}</p>
                @empty
                    <p class="text-sm text-mist">Tekshiruv natijasi yo'q.</p>
                @endforelse
                <p class="pt-1 text-[11px] text-mist leading-relaxed">Avtomatik tekshiruv faqat yordamchi. Soxta chekni 100% aniqlab bo'lmaydi — yagona ishonchli dalil: pul kartangizga haqiqatan tushgani.</p>
            </div>

            {{-- Qaror --}}
            @if($topup->isPendingReview())
                <div class="ks-panel p-4 space-y-4">
                    <form method="POST" action="{{ route('admin.topups.approve', $topup) }}" class="space-y-3">
                        @csrf
                        <label class="flex items-start gap-3 rounded-lg border border-amber-500/30 bg-amber-500/10 p-3 text-sm text-paper cursor-pointer">
                            <input type="checkbox" name="money_confirmed" value="1" required class="mt-1 h-4 w-4">
                            <span>Bank ilovamda / SMS'da <b class="font-mono">{{ $money($topup->expected_amount) }}</b> kartamga <b>haqiqatan tushganini</b> tekshirdim.</span>
                        </label>
                        <input type="text" name="admin_note" maxlength="2000" placeholder="Ichki izoh (ixtiyoriy)"
                               class="w-full min-h-11 rounded-btn border border-ink-border bg-ink-900 px-3 text-sm text-paper focus:border-amber-500/50 focus:outline-none">
                        <button type="submit" class="w-full min-h-11 rounded-btn bg-emerald-500 hover:bg-emerald-400 text-ink-950 text-sm font-bold">
                            Tasdiqlash va {{ $topup->reward_label }} berish
                        </button>
                    </form>

                    <form method="POST" action="{{ route('admin.topups.reject', $topup) }}" class="space-y-2 border-t border-ink-border pt-4"
                          x-data="{ reason: '' }">
                        @csrf
                        <div class="flex flex-wrap gap-1.5">
                            @foreach(['Pul kartaga tushmadi', 'Summa mos kelmadi', 'Chek takroriy yoki eski', 'Chek tahrirlangan / soxta'] as $preset)
                                <button type="button" @click="reason = @js($preset)" class="rounded-btn border border-ink-border px-2.5 py-1 text-xs text-mist hover:text-paper">{{ $preset }}</button>
                            @endforeach
                        </div>
                        <input type="text" name="reject_reason" x-model="reason" required maxlength="500" placeholder="Rad etish sababi (foydalanuvchiga ko'rinadi)"
                               class="w-full min-h-11 rounded-btn border border-ink-border bg-ink-900 px-3 text-sm text-paper focus:border-rose-500/50 focus:outline-none">
                        <button type="submit" class="w-full min-h-11 rounded-btn border border-rose-500/40 bg-rose-500/10 hover:bg-rose-500/20 text-rose-300 text-sm font-bold">Rad etish</button>
                    </form>
                </div>
            @elseif($topup->reviewed_at)
                <div class="ks-panel p-4 text-sm text-mist space-y-1">
                    <p>Qaror: <span class="font-semibold text-paper">{{ $topup->status_label }}</span> · {{ $topup->reviewer?->name }} · {{ $topup->reviewed_at->timezone('Asia/Tashkent')->format('d.m.Y H:i') }}</p>
                    @if($topup->reject_reason)<p>Sabab: <span class="text-paper">{{ $topup->reject_reason }}</span></p>@endif
                    @if($topup->admin_note)<p>Izoh: <span class="text-paper">{{ $topup->admin_note }}</span></p>@endif
                </div>
            @endif

            {{-- Foydalanuvchi tarixi --}}
            @if($userHistory->isNotEmpty())
                <div class="ks-panel overflow-hidden">
                    <p class="px-4 pt-3 pb-2 text-xs font-mono uppercase text-mist">Foydalanuvchining boshqa so'rovlari</p>
                    <ul class="divide-y divide-ink-border text-sm">
                        @foreach($userHistory as $item)
                            <li><a href="{{ route('admin.topups.show', $item) }}" class="flex justify-between gap-2 px-4 py-2 hover:bg-ink-800/60">
                                <span class="font-mono text-mist">#{{ $item->reference }} · {{ $money($item->expected_amount) }}</span>
                                <span class="text-paper">{{ $item->status_label }}</span>
                            </a></li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
