@extends('admin.layouts.app')

@section('title', 'Telegram Boshqaruvi')
@section('breadcrumb', 'Telegram')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold font-serif text-paper flex items-center gap-2">
                <span>✈️ Telegram Integratsiyasi & Bot Boshqaruvi</span>
            </h1>
            <p class="text-xs text-mist font-mono mt-0.5">
                Bot holati, ommaviy xabar yuborish, Telegram Stars to'lovlari va telefon tasdiqlash nazorati
            </p>
        </div>
        <div class="flex items-center gap-2">
            <form action="{{ route('admin.telegram.reconcile') }}" method="POST">
                @csrf
                <button type="submit"
                        class="px-3.5 py-2 rounded-btn bg-amber-500/15 hover:bg-amber-500/25 border border-amber-500/30 text-amber-300 text-xs font-mono font-bold transition-colors">
                    🔄 Stars to'lovlarini solishtirish (Reconcile)
                </button>
            </form>
        </div>
    </div>

    {{-- 1. Bot Holati & Statistika Kartalari --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="p-4 rounded-panel bg-ink-900 border border-ink-border space-y-1.5">
            <div class="flex items-center justify-between text-xs font-mono text-mist">
                <span>Bot holati</span>
                @if($botConfigured)
                    <span class="px-2 py-0.5 rounded-badge bg-emerald-500/15 text-emerald-400 font-bold">Faol</span>
                @else
                    <span class="px-2 py-0.5 rounded-badge bg-rose-500/15 text-rose-400 font-bold">Token yo'q</span>
                @endif
            </div>
            <div class="text-lg font-bold text-paper font-mono">&#64;{{ $botUsername }}</div>
            <p class="text-[11px] text-mist font-mono">
                Webhook Secret: {{ $webhookConfigured ? '✅ Sozlangan' : '⚠️ Sozlanmagan' }}
            </p>
        </div>

        <div class="p-4 rounded-panel bg-ink-900 border border-ink-border space-y-1.5">
            <div class="flex items-center justify-between text-xs font-mono text-mist">
                <span>Telegram ulanganlar</span>
                <span class="text-amber-400">👥</span>
            </div>
            <div class="text-2xl font-bold text-paper font-mono">{{ number_format($stats['linked_users']) }}</div>
            <p class="text-[11px] text-mist font-mono">
                Faol: <span class="text-emerald-400">{{ $stats['active_users'] }}</span> • Bloklagan: <span class="text-rose-400">{{ $stats['blocked_users'] }}</span>
            </p>
        </div>

        <div class="p-4 rounded-panel bg-ink-900 border border-ink-border space-y-1.5">
            <div class="flex items-center justify-between text-xs font-mono text-mist">
                <span>Telegram Stars</span>
                <span class="px-2 py-0.5 rounded-badge {{ $paymentsEnabled ? 'bg-emerald-500/15 text-emerald-400' : 'bg-ink-800 text-mist' }}">
                    {{ $paymentsEnabled ? 'Yoqilgan' : 'O\'chiq' }}
                </span>
            </div>
            <div class="text-2xl font-bold text-amber-400 font-mono">{{ number_format($stats['stars_completed']) }} ⭐️</div>
            <p class="text-[11px] text-mist font-mono">
                Pending: {{ $stats['stars_pending'] }} • Qaytarilgan: {{ $stats['stars_refunded'] }}
            </p>
        </div>

        <div class="p-4 rounded-panel bg-ink-900 border border-ink-border space-y-1.5">
            <div class="flex items-center justify-between text-xs font-mono text-mist">
                <span>Telefon holati</span>
                <span>📱</span>
            </div>
            <div class="text-2xl font-bold text-paper font-mono">{{ number_format($stats['unverified_phone']) }}</div>
            <p class="text-[11px] text-mist font-mono">
                Telefoni tasdiqlanmagan TG hisoblar • To'qnashuvlar: {{ count($phoneCollisions) }}
            </p>
        </div>
    </div>

    {{-- 2. Ommaviy E'lon yoki Bitta Foydalanuvchiga Bot Xabari va Telefon To'qnashuvlari --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        {{-- Telegram Bot nomidan e'lon / xabar yuborish --}}
        <div class="lg:col-span-6 p-5 rounded-panel bg-ink-900 border border-ink-border space-y-4"
             x-data="{ targetType: '{{ old('target_type', 'all') }}', userSearch: '' }">
            <div class="flex items-start justify-between gap-2">
                <div>
                    <h2 class="text-base font-bold font-serif text-paper">📣 Telegram Botdan E'lon / Xabar Yuborish</h2>
                    <p class="text-xs text-mist font-mono mt-0.5">
                        Shaxsiy lichkadan emas, rasmiy <span class="text-amber-400 font-bold">&#64;{{ $botUsername }}</span> boti nomidan yuboriladi
                    </p>
                </div>
                <span class="px-2.5 py-1 rounded-badge bg-sky-500/15 border border-sky-500/30 text-sky-400 text-[10px] font-mono font-bold shrink-0">
                    🤖 Bot nomidan
                </span>
            </div>

            <form action="{{ route('admin.telegram.broadcast') }}" method="POST" class="space-y-3.5">
                @csrf

                {{-- Yuborish turi --}}
                <div>
                    <label class="block text-xs font-mono text-mist mb-1.5">Kimga yuborilsin?</label>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                        <label class="flex items-center gap-2 p-2.5 rounded-btn border cursor-pointer transition-colors"
                               :class="targetType === 'all' ? 'bg-amber-500/15 border-amber-500/40 text-amber-300' : 'bg-ink-950 border-ink-border text-mist'">
                            <input type="radio" name="target_type" value="all" x-model="targetType" class="accent-amber-500">
                            <span class="text-xs font-mono font-bold">🌐 Barchaga ({{ $stats['active_users'] }})</span>
                        </label>

                        <label class="flex items-center gap-2 p-2.5 rounded-btn border cursor-pointer transition-colors"
                               :class="targetType === 'single' ? 'bg-amber-500/15 border-amber-500/40 text-amber-300' : 'bg-ink-950 border-ink-border text-mist'">
                            <input type="radio" name="target_type" value="single" x-model="targetType" class="accent-amber-500">
                            <span class="text-xs font-mono font-bold">👤 Bitta odamga</span>
                        </label>

                        <label class="flex items-center gap-2 p-2.5 rounded-btn border cursor-pointer transition-colors"
                               :class="targetType === 'role' ? 'bg-amber-500/15 border-amber-500/40 text-amber-300' : 'bg-ink-950 border-ink-border text-mist'">
                            <input type="radio" name="target_type" value="role" x-model="targetType" class="accent-amber-500">
                            <span class="text-xs font-mono font-bold">🎓 Rol bo'yicha</span>
                        </label>
                    </div>
                </div>

                {{-- Agar Bitta foydalanuvchi tanlangan bo'lsa --}}
                <div x-show="targetType === 'single'" x-cloak class="space-y-2 p-3 rounded-btn bg-ink-950/80 border border-ink-border">
                    <label class="block text-xs font-mono text-amber-400 font-bold">👤 Foydalanuvchini tanlang (Telegram ulanganlar):</label>
                    <select name="user_id" :required="targetType === 'single'"
                            class="w-full rounded-btn bg-ink-900 border border-ink-border px-3 py-2 text-xs text-paper font-mono focus:border-amber-400 outline-none">
                        <option value="">— Foydalanuvchini tanlang —</option>
                        @foreach($telegramUsers ?? [] as $tgUser)
                            <option value="{{ $tgUser->id }}" {{ (string) old('user_id') === (string) $tgUser->id ? 'selected' : '' }}>
                                {{ $tgUser->name }} (&#64;{{ $tgUser->username }}) {{ $tgUser->phone ? '• ' . $tgUser->phone : '' }} [ID: #{{ $tgUser->id }}]
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Agar Rol bo'yicha tanlangan bo'lsa --}}
                <div x-show="targetType === 'role'" x-cloak class="space-y-2 p-3 rounded-btn bg-ink-950/80 border border-ink-border">
                    <label class="block text-xs font-mono text-amber-400 font-bold">🎓 Qaysi roldagi foydalanuvchilarga yuborilsin?</label>
                    <select name="target_role" :required="targetType === 'role'"
                            class="w-full rounded-btn bg-ink-900 border border-ink-border px-3 py-2 text-xs text-paper font-mono focus:border-amber-400 outline-none">
                        <option value="student">O'quvchilar (Kitobxonlar)</option>
                        <option value="teacher">O'qituvchilar (Ustozlar)</option>
                        <option value="admin">Adminlar</option>
                    </select>
                </div>

                {{-- Sarlavha --}}
                <div>
                    <label class="block text-xs font-mono text-mist mb-1">E'lon / Xabar sarlavhasi (ixtiyoriy)</label>
                    <input type="text" name="title" value="{{ old('title') }}"
                           placeholder="Masalan: Muhim e'lon yoki Tabriklaymiz!"
                           class="w-full rounded-btn bg-ink-950 border border-ink-border px-3 py-2 text-xs text-paper focus:border-amber-400 outline-none">
                </div>

                {{-- Xabar matni --}}
                <div>
                    <label class="block text-xs font-mono text-mist mb-1">Xabar matni (HTML teglari qo'llab-quvvatlanadi: &lt;b&gt;, &lt;i&gt;, &lt;code&gt;)</label>
                    <textarea name="message" rows="4" required
                              placeholder="E'lon yoki xabar matnini kiriting..."
                              class="w-full rounded-btn bg-ink-950 border border-ink-border px-3 py-2 text-sm text-paper focus:border-amber-400 outline-none">{{ old('message') }}</textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-mono text-mist mb-1">Tugma yozuvi (ixtiyoriy)</label>
                        <input type="text" name="button_text" value="{{ old('button_text') }}"
                               placeholder="Masalan: 📖 Kitobni o'qish"
                               class="w-full rounded-btn bg-ink-950 border border-ink-border px-3 py-2 text-xs text-paper focus:border-amber-400 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-mono text-mist mb-1">Tugma havolasi (ixtiyoriy)</label>
                        <input type="url" name="button_url" value="{{ old('button_url') }}"
                               placeholder="https://..."
                               class="w-full rounded-btn bg-ink-950 border border-ink-border px-3 py-2 text-xs text-paper focus:border-amber-400 outline-none">
                    </div>
                </div>

                <button type="submit"
                        class="px-4 py-2.5 rounded-btn bg-amber-500 hover:bg-amber-400 text-ink-950 font-bold text-xs font-mono transition-colors inline-flex items-center gap-1.5">
                    <span>🚀 Bot orqali yuborish</span>
                </button>
            </form>
        </div>

        {{-- Telefon to'qnashuvlari va tasdiqlanmagan hisoblar --}}
        <div class="lg:col-span-6 p-5 rounded-panel bg-ink-900 border border-ink-border space-y-4">
            <div>
                <h2 class="text-base font-bold font-serif text-paper">📱 Telefon to'qnashuvlari & Tasdiqlanmagan hisoblar</h2>
                <p class="text-xs text-mist font-mono mt-0.5">
                    Boshqa hisobda band bo'lgan raqam urinishlari va telefonsiz Telegram akkauntlar
                </p>
            </div>

            @if(!empty($phoneCollisions))
                <div class="space-y-2">
                    <h3 class="text-xs font-mono font-bold text-amber-400">Oxirgi raqam to'qnashuvlari:</h3>
                    <div class="divide-y divide-ink-border border border-ink-border rounded-btn overflow-hidden">
                        @foreach(array_slice($phoneCollisions, 0, 5) as $col)
                            <div class="p-2.5 bg-ink-950 text-xs font-mono flex items-center justify-between gap-2">
                                <span>📞 {{ $col['phone'] ?? '' }} (User #{{ $col['user_id'] ?? '?' }} → Band: #{{ $col['existing_user_id'] ?? '?' }})</span>
                                <span class="text-mist text-[10px]">{{ $col['occurred_at'] ?? '' }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="space-y-2">
                <h3 class="text-xs font-mono font-bold text-mist">Telefoni tasdiqlanmagan Telegram foydalanuvchilar (50 🪙 bonus kutmoqda):</h3>
                @if($unverifiedUsers->isEmpty())
                    <p class="text-xs text-mist font-mono py-3">Barcha Telegram foydalanuvchilarning telefon raqamlari tasdiqlangan. ✅</p>
                @else
                    <div class="divide-y divide-ink-border border border-ink-border rounded-btn max-h-52 overflow-y-auto">
                        @foreach($unverifiedUsers as $u)
                            <div class="p-2.5 bg-ink-950 text-xs flex items-center justify-between gap-2">
                                <div>
                                    <span class="font-semibold text-paper">{{ $u->name }}</span>
                                    <span class="text-mist font-mono ml-1">(&#64;{{ $u->username }})</span>
                                </div>
                                <span class="text-[11px] font-mono text-amber-400">TG ID: {{ $u->telegram_id }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- 3. Telegram Stars To'lovlari Ro'yxati --}}
    <div class="p-5 rounded-panel bg-ink-900 border border-ink-border space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold font-serif text-paper">⭐️ Telegram Stars To'lovlari</h2>
                <p class="text-xs text-mist font-mono mt-0.5">Barcha yaratilgan, yakunlangan va qaytarilgan Stars tranzaksiyalari</p>
            </div>
        </div>

        <div class="admin-table-scroll border border-ink-border rounded-btn">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-ink-border bg-ink-950 font-mono text-[11px] text-mist uppercase">
                        <th class="py-2.5 px-3">ID</th>
                        <th class="py-2.5 px-3">Foydalanuvchi</th>
                        <th class="py-2.5 px-3">Paket</th>
                        <th class="py-2.5 px-3">Stars / Tanga</th>
                        <th class="py-2.5 px-3">Holat</th>
                        <th class="py-2.5 px-3">Charge ID</th>
                        <th class="py-2.5 px-3 text-right">Amal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink-border">
                    @forelse($payments as $payment)
                        <tr class="hover:bg-ink-950/60">
                            <td class="py-2.5 px-3 font-mono text-mist">#{{ $payment->id }}</td>
                            <td class="py-2.5 px-3">
                                @if($payment->user)
                                    <span class="font-semibold text-paper">{{ $payment->user->name }}</span>
                                    <span class="block text-[10px] font-mono text-mist">&#64;{{ $payment->user->username }}</span>
                                @else
                                    <span class="text-mist">User #{{ $payment->user_id }}</span>
                                @endif
                            </td>
                            <td class="py-2.5 px-3 text-paper">{{ $payment->title }}</td>
                            <td class="py-2.5 px-3 font-mono">
                                <span class="text-amber-400 font-bold">{{ $payment->stars_amount }} ⭐️</span>
                                <span class="text-mist">→ {{ $payment->coins_awarded }} 🪙</span>
                            </td>
                            <td class="py-2.5 px-3 font-mono">
                                @if($payment->status === 'completed')
                                    <span class="px-2 py-0.5 rounded-badge bg-emerald-500/15 text-emerald-400">To'langan</span>
                                @elseif($payment->status === 'refunded')
                                    <span class="px-2 py-0.5 rounded-badge bg-rose-500/15 text-rose-400">Qaytarilgan</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-badge bg-amber-500/15 text-amber-300">{{ $payment->status }}</span>
                                @endif
                            </td>
                            <td class="py-2.5 px-3 font-mono text-[11px] text-mist">
                                {{ $payment->telegram_payment_charge_id ?: '—' }}
                            </td>
                            <td class="py-2.5 px-3 text-right">
                                @if($payment->status === 'completed')
                                    <form action="{{ route('admin.telegram.refund', $payment) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit"
                                                onclick="return confirm('Haqiqatan ham ushbu Stars to\'lovini qaytarmoqchimisiz?')"
                                                class="px-2.5 py-1 rounded bg-rose-500/15 hover:bg-rose-500/25 border border-rose-500/30 text-rose-400 font-mono text-[11px]">
                                            ↩️ Qaytarish
                                        </button>
                                    </form>
                                @else
                                    <span class="text-mist font-mono">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-6 text-center text-mist font-mono">
                                Hozircha Telegram Stars to'lovlari mavjud emas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>
            {{ $payments->links() }}
        </div>
    </div>

</div>
@endsection
