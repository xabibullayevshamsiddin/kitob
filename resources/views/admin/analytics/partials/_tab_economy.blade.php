@php
    $shop = $data['shop_metrics'] ?? [];
    $pointSources = $data['point_sources'] ?? [];
    $dailyIssuance = $data['daily_issuance'] ?? [];
    $streakDist = $data['streak_distribution'] ?? [];
    $abuseSignals = $data['abuse_signals'] ?? [];

    $uzNum = function ($val, int $decimals = 0): string {
        return number_format((float) $val, $decimals, '.', ' ');
    };
@endphp

<div class="space-y-6">

    {{-- Shop & Tanga Iqtisodiyoti Ko'rsatkichlari --}}
    <div>
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-sm font-bold font-serif text-paper">Do‘kon va Tanga Iqtisodiyoti (Game Economy)</h2>
            <a href="{{ route('admin.shop.index', ['tab' => 'economy']) }}" class="text-xs font-mono text-amber-400 hover:underline">
                Do‘kon boshqaruviga o‘tish &rarr;
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5 font-mono">
            <div class="bg-ink-900 border border-ink-border rounded-panel p-4">
                <p class="text-[10px] uppercase text-mist tracking-wider">Aylanmadagi Tangalar</p>
                <p class="text-xl font-bold text-amber-400 mt-1">{{ $uzNum($shop['circulation'] ?? 0) }}</p>
                <p class="text-[10px] text-mist/70 mt-0.5">Foydalanuvchilar balansida</p>
            </div>

            <div class="bg-ink-900 border border-ink-border rounded-panel p-4">
                <p class="text-[10px] uppercase text-mist tracking-wider">Jami Chiqarilgan</p>
                <p class="text-xl font-bold text-emerald-400 mt-1">{{ $uzNum($shop['issued_total'] ?? 0) }}</p>
                <p class="text-[10px] text-mist/70 mt-0.5">Faollik va bonuslar evaziga</p>
            </div>

            <div class="bg-ink-900 border border-ink-border rounded-panel p-4">
                <p class="text-[10px] uppercase text-mist tracking-wider">Do‘konda Sarflangan</p>
                <p class="text-xl font-bold text-rose-400 mt-1">{{ $uzNum($shop['spent_total'] ?? 0) }}</p>
                <p class="text-[10px] text-mist/70 mt-0.5">{{ $uzNum($shop['purchases_count'] ?? 0) }} ta xarid</p>
            </div>

            <div class="bg-ink-900 border border-ink-border rounded-panel p-4">
                <p class="text-[10px] uppercase text-mist tracking-wider">Ball → Tanga Hajmi</p>
                <p class="text-xl font-bold text-cyan-400 mt-1">{{ $uzNum($shop['exchange_coins'] ?? 0) }}</p>
                <p class="text-[10px] text-mist/70 mt-0.5">Almashtirilgan tangalar</p>
            </div>
        </div>
    </div>

    {{-- Eng ko'p sotilgan buyumlar --}}
    @if(!empty($shop['top_items']))
        <div class="bg-ink-900 border border-ink-border rounded-panel p-4">
            <h3 class="text-sm font-bold font-serif text-paper mb-3">Eng Ko‘p Xarid Qilingan Buyumlar</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs font-mono">
                    <thead class="text-[10px] text-mist uppercase border-b border-ink-border">
                        <tr>
                            <th class="py-2">#</th>
                            <th class="py-2">Buyum</th>
                            <th class="py-2">Kategoriya</th>
                            <th class="py-2 text-right">Narx</th>
                            <th class="py-2 text-right">Sotildi</th>
                            <th class="py-2 text-right">Jami Daromad</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-border/60">
                        @foreach($shop['top_items'] as $idx => $tItem)
                            <tr class="hover:bg-ink-950/30">
                                <td class="py-2 text-mist/70">{{ $idx + 1 }}</td>
                                <td class="py-2 font-medium text-paper">{{ $tItem['name'] }}</td>
                                <td class="py-2 text-mist">{{ ucfirst($tItem['category']) }}</td>
                                <td class="py-2 text-right text-amber-400">{{ $uzNum($tItem['price_coins']) }} 🪙</td>
                                <td class="py-2 text-right font-bold text-paper">{{ $uzNum($tItem['units_sold']) }} ta</td>
                                <td class="py-2 text-right text-amber-400 font-bold">{{ $uzNum($tItem['total_revenue']) }} 🪙</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Ball manbalari va Streak taqsimoti --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        {{-- Ball manbalari --}}
        <div class="bg-ink-900 border border-ink-border rounded-panel p-4">
            <h3 class="text-sm font-bold font-serif text-paper mb-1">Ball Manbalari Taqsimoti</h3>
            <p class="text-xs font-mono text-mist mb-3">Tanlangan davrda berilgan ballar</p>

            <div class="space-y-3 font-mono text-xs">
                @forelse($pointSources['sources'] ?? [] as $src)
                    <div>
                        <div class="flex justify-between text-[11px] mb-1">
                            <span class="text-paper">{{ $src['label'] }}</span>
                            <span class="text-amber-400 font-bold">{{ $uzNum($src['points']) }} ball ({{ $src['percent'] }}%)</span>
                        </div>
                        <div class="w-full bg-ink-950 rounded-full h-1.5 overflow-hidden">
                            <div class="bg-amber-400 h-1.5 rounded-full" style="width: {{ min(100, $src['percent']) }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-mist text-center py-4">Ma'lumotlar yo'q.</p>
                @endforelse
            </div>
        </div>

        {{-- Streak taqsimoti --}}
        <div class="bg-ink-900 border border-ink-border rounded-panel p-4">
            <h3 class="text-sm font-bold font-serif text-paper mb-1">Streak Taqsimoti</h3>
            <p class="text-xs font-mono text-mist mb-3">Faol kitobxonlarning uzluksiz kunlari</p>

            <div class="grid grid-cols-2 gap-3 font-mono text-xs">
                @foreach($streakDist['buckets'] ?? [] as $bucket)
                    <div class="p-3 bg-ink-950 border border-ink-border rounded-btn">
                        <p class="text-[10px] text-mist uppercase">{{ $bucket['label'] }}</p>
                        <p class="text-lg font-bold text-paper mt-0.5">{{ $uzNum($bucket['users_count'] ?? 0) }}</p>
                        <p class="text-[10px] text-amber-400">{{ $bucket['percent'] ?? 0 }}% kitobxonlar</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

</div>
