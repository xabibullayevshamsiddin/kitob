<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\TopupRequest;
use App\Services\Shop\Exceptions\ShopException;
use App\Services\Topup\TopupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * Admin: karta orqali to'lov cheklarini ko'rish, tasdiqlash/rad etish va sozlamalar.
 */
class TopupController extends Controller
{
    public function __construct(
        protected TopupService $service
    ) {
    }

    public function index(Request $request): View
    {
        $this->service->expireStale();

        $tab = $request->get('status', TopupRequest::STATUS_PENDING_REVIEW);
        $tabs = [
            TopupRequest::STATUS_PENDING_REVIEW   => 'Tekshirilmoqda',
            TopupRequest::STATUS_AWAITING_PAYMENT => 'To\'lov kutilmoqda',
            TopupRequest::STATUS_APPROVED         => 'Tasdiqlangan',
            TopupRequest::STATUS_REJECTED         => 'Rad etilgan',
            'all'                                 => 'Barchasi',
        ];
        if (!array_key_exists($tab, $tabs)) {
            $tab = TopupRequest::STATUS_PENDING_REVIEW;
        }

        $query = TopupRequest::with('user')->latest('id');
        if ($tab !== 'all') {
            $query->where('status', $tab);
        }
        if ($search = trim((string) $request->get('q'))) {
            $digits = preg_replace('/\D+/', '', $search);
            $query->where(function ($q) use ($search, $digits) {
                $q->where('reference', 'like', '%' . strtoupper(ltrim($search, '#')) . '%')
                  ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%")->orWhere('username', 'like', "%{$search}%"));
                if ($digits !== '') {
                    $q->orWhere('expected_amount', (int) $digits);
                }
            });
        }

        $counts = TopupRequest::selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');
        $todayApproved = (int) TopupRequest::where('status', TopupRequest::STATUS_APPROVED)
            ->where('reviewed_at', '>=', now('Asia/Tashkent')->startOfDay()->utc())
            ->sum('expected_amount');

        return view('admin.topups.index', [
            'requests'      => $query->paginate(25)->withQueryString(),
            'tabs'          => $tabs,
            'tab'           => $tab,
            'counts'        => $counts,
            'todayApproved' => $todayApproved,
            'enabled'       => $this->service->isEnabled(),
        ]);
    }

    public function show(TopupRequest $topup): View
    {
        $topup->load(['user', 'reviewer']);

        $userHistory = TopupRequest::where('user_id', $topup->user_id)
            ->where('id', '!=', $topup->id)
            ->latest('id')
            ->take(10)
            ->get();

        return view('admin.topups.show', compact('topup', 'userHistory'));
    }

    /** Chek rasmi faqat admin uchun, yopiq diskdan oqim sifatida beriladi */
    public function receipt(TopupRequest $topup): Response
    {
        $disk = Storage::disk(TopupService::DISK);
        abort_unless($topup->receipt_path && $disk->exists($topup->receipt_path), 404);

        return response()->file($disk->path($topup->receipt_path), [
            'Content-Type'           => $topup->receipt_mime ?: 'image/jpeg',
            'Cache-Control'          => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function approve(Request $request, TopupRequest $topup): RedirectResponse
    {
        $request->validate([
            'money_confirmed' => 'accepted',
            'admin_note'      => 'nullable|string|max:2000',
        ], [
            'money_confirmed.accepted' => 'Avval pul kartangizga tushganini bank ilovasida tekshiring va belgilang.',
        ]);

        try {
            $this->service->approve($topup, $request->user(), $request->input('admin_note'));
        } catch (ShopException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.topups.index')
            ->with('success', "#{$topup->reference} tasdiqlandi: {$topup->reward_label} foydalanuvchi hisobiga qo'shildi.");
    }

    public function reject(Request $request, TopupRequest $topup): RedirectResponse
    {
        $validated = $request->validate([
            'reject_reason' => 'required|string|max:500',
            'admin_note'    => 'nullable|string|max:2000',
        ], [
            'reject_reason.required' => 'Rad etish sababini yozing (foydalanuvchiga ko\'rsatiladi).',
        ]);

        try {
            $this->service->reject($topup, $request->user(), $validated['reject_reason'], $validated['admin_note'] ?? null);
        } catch (ShopException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.topups.index')->with('success', "#{$topup->reference} rad etildi.");
    }

    public function reanalyze(TopupRequest $topup): RedirectResponse
    {
        abort_unless($topup->isPendingReview(), 422);
        $this->service->analyze($topup);

        return back()->with('success', 'Chek qayta tekshirildi.');
    }

    public function settings(\App\Services\Shop\ShopService $shop): View
    {
        $packages = $this->service->allPackages();

        return view('admin.topups.settings', [
            'enabled'    => (bool) setting('topup_enabled', false),
            'cardNumber' => $this->service->cardFormatted(),
            'cardHolder' => $this->service->cardHolder(),
            'cardBank'   => $this->service->cardBank(),
            'dailyLimit' => $this->service->dailyLimit(),
            'packages'   => $packages,
            'warnings'   => $this->arbitrageWarnings($packages, $shop->getExchangeRate()),
        ]);
    }

    /**
     * Ball paketini do'konda tangaga almashtirish to'g'ridan-to'g'ri tanga sotib olishdan
     * arzonroq bo'lib qolsa (narx teshigi), admin'ni ogohlantiradi.
     */
    protected function arbitrageWarnings(array $packages, int $pointsPerCoin): array
    {
        $coinPackages = array_filter($packages, fn ($p) => $p['active'] && $p['coins'] > 0 && $p['points'] === 0);
        if ($coinPackages === []) {
            return [];
        }

        $bestCoinsPerSum = max(array_map(fn ($p) => $p['coins'] / $p['price'], $coinPackages));

        $warnings = [];
        foreach ($packages as $p) {
            if (!$p['active'] || $p['points'] === 0) {
                continue;
            }
            $viaExchange = ($p['coins'] + $p['points'] / $pointsPerCoin) / $p['price'];
            if ($viaExchange > $bestCoinsPerSum) {
                $warnings[] = "«{$p['title']}»: ballarni almashtirib ({$pointsPerCoin} ball = 1 tanga) tanga paketidan arzonroq tanga olish mumkin. Ball narxini oshiring yoki almashtirish kursini o'zgartiring.";
            }
        }

        return $warnings;
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'card_number'         => ['nullable', 'string', 'max:30', function ($attr, $value, $fail) {
                $digits = preg_replace('/\D+/', '', (string) $value);
                if ($digits !== '' && (strlen($digits) < 16 || strlen($digits) > 19)) {
                    $fail('Karta raqami 16 xonali bo\'lishi kerak.');
                }
            }],
            'card_holder'         => 'nullable|string|max:100',
            'card_bank'           => 'nullable|string|max:60',
            'daily_limit'         => 'required|integer|min:1000|max:100000000',
            'packages'            => 'array|max:20',
            'packages.*.key'      => 'nullable|string|max:40',
            'packages.*.title'    => 'nullable|string|max:120',
            'packages.*.price'    => 'nullable|integer|min:0|max:100000000',
            'packages.*.coins'    => 'nullable|integer|min:0|max:10000000',
            'packages.*.points'   => 'nullable|integer|min:0|max:10000000',
        ]);

        $enabled = $request->boolean('enabled');
        $digits = preg_replace('/\D+/', '', (string) ($validated['card_number'] ?? ''));
        if ($enabled && $digits === '') {
            return back()->withInput()->with('error', 'To\'lovni yoqish uchun karta raqamini kiriting.');
        }

        Setting::set('topup_enabled', $enabled, 'topup', 'boolean');
        Setting::set('topup_card_number', $digits, 'topup', 'string');
        Setting::set('topup_card_holder', trim((string) ($validated['card_holder'] ?? '')), 'topup', 'string');
        Setting::set('topup_card_bank', trim((string) ($validated['card_bank'] ?? '')), 'topup', 'string');
        Setting::set('topup_daily_limit', (int) $validated['daily_limit'], 'topup', 'integer');

        $packages = collect($request->input('packages', []))
            ->map(fn ($p) => array_merge((array) $p, ['active' => !empty($p['active'])]))
            ->all();
        $this->service->savePackages($packages);

        return redirect()->route('admin.topups.settings')->with('success', 'To\'lov sozlamalari saqlandi.');
    }
}
