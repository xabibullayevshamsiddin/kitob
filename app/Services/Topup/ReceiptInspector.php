<?php

namespace App\Services\Topup;

use App\Models\TopupRequest;
use Carbon\Carbon;

/**
 * Chek rasmini avtomatik tekshirish. Har bir tekshiruv admin uchun bayroq (check) qaytaradi:
 *   ['code' => ..., 'level' => ok|info|warn|danger, 'message' => ...]
 * va umumiy xavf bali (risk_score, 0..100) hisoblanadi.
 *
 * Bu tekshiruvlar soxta chekni 100% aniqlay olmaydi — ular adminning e'tiborini
 * shubhali joylarga qaratadi. Asosiy himoya: unikal summa + admin bank ilovasida pulni ko'rishi.
 */
class ReceiptInspector
{
    /** Rasm tahrirlash dasturlari izlari (EXIF/XMP ichida) */
    protected const EDITOR_MARKERS = [
        'photoshop', 'gimp', 'picsart', 'snapseed', 'canva', 'pixlr', 'lightroom', 'fotor', 'polarr', 'meitu', 'paint.net', 'affinity',
    ];

    /** AI generator / C2PA izlari */
    protected const AI_MARKERS = [
        'trainedalgorithmicmedia', 'c2pa', 'midjourney', 'dall-e', 'dall·e', 'stable diffusion', 'stablediffusion',
        'adobe firefly', 'imagen', 'leonardo.ai', 'ideogram', 'openai',
    ];

    protected const WEIGHTS = ['danger' => 40, 'warn' => 15];

    public function sha256(string $absolutePath): string
    {
        return hash_file('sha256', $absolutePath);
    }

    /**
     * 64-bitlik difference hash (dHash): qayta siqilgan, kesilgan yoki biroz tahrirlangan
     * bir xil skrinshotlarni ham topish uchun.
     */
    public function dhash(string $absolutePath): ?string
    {
        if (!function_exists('imagecreatefromstring')) {
            return null;
        }

        $source = @imagecreatefromstring((string) file_get_contents($absolutePath));
        if (!$source) {
            return null;
        }

        $small = imagecreatetruecolor(9, 8);
        imagecopyresampled($small, $source, 0, 0, 0, 0, 9, 8, imagesx($source), imagesy($source));
        imagedestroy($source);

        $bits = '';
        for ($y = 0; $y < 8; $y++) {
            for ($x = 0; $x < 8; $x++) {
                $bits .= $this->luma($small, $x, $y) > $this->luma($small, $x + 1, $y) ? '1' : '0';
            }
        }
        imagedestroy($small);

        $hex = '';
        foreach (str_split($bits, 4) as $nibble) {
            $hex .= dechex(bindec($nibble));
        }

        return $hex;
    }

    public function hammingDistance(string $a, string $b): int
    {
        $distance = 0;
        for ($i = 0, $len = min(strlen($a), strlen($b)); $i < $len; $i++) {
            $distance += substr_count(decbin(hexdec($a[$i]) ^ hexdec($b[$i])), '1');
        }

        return $distance;
    }

    /**
     * Barcha tekshiruvlarni bajaradi.
     *
     * @return array{checks: array, risk_score: int}
     */
    public function inspect(TopupRequest $topup, string $absolutePath, ?array $ai): array
    {
        $checks = array_merge(
            $this->duplicateChecks($topup),
            $this->fileChecks($absolutePath),
            $this->behaviourChecks($topup),
            $this->aiChecks($topup, $ai),
        );

        $risk = 0;
        foreach ($checks as $check) {
            $risk += self::WEIGHTS[$check['level']] ?? 0;
        }

        return ['checks' => $checks, 'risk_score' => min(100, $risk)];
    }

    protected function duplicateChecks(TopupRequest $topup): array
    {
        $checks = [];
        $others = TopupRequest::where('id', '!=', $topup->id)->whereNotNull('receipt_path');

        if ($topup->receipt_sha256) {
            $same = (clone $others)->where('receipt_sha256', $topup->receipt_sha256)->first();
            if ($same) {
                $checks[] = $this->check('duplicate_file', 'danger', "Aynan shu fayl avval #{$same->reference} so'rovida yuborilgan.");
            }
        }

        if ($topup->receipt_dhash) {
            $maxDistance = (int) config('topup.similar_hash_distance', 6);
            $similar = (clone $others)
                ->whereNotNull('receipt_dhash')
                ->where('created_at', '>=', now()->subDays(180))
                ->latest('id')
                ->limit(3000)
                ->get(['id', 'reference', 'user_id', 'receipt_dhash'])
                ->filter(fn ($row) => $this->hammingDistance($topup->receipt_dhash, $row->receipt_dhash) <= $maxDistance);

            if ($similar->isNotEmpty()) {
                $refs = $similar->take(5)->map(fn ($row) => '#' . $row->reference)->implode(', ');
                // Bir ilovaning turli cheklari ham shablon tufayli o'xshaydi — shuning uchun faqat ogohlantirish.
                // Aniq takrorlarni sha256 va tranzaksiya raqami tekshiruvlari ushlaydi.
                $checks[] = $this->check('similar_image', 'warn', "Rasm avvalgi chek(lar)ga juda o'xshaydi ({$refs}). Bir xil ilova shabloni bo'lishi ham mumkin — summa va vaqtni solishtiring.");
            }
        }

        return $checks;
    }

    protected function fileChecks(string $absolutePath): array
    {
        $checks = [];
        $haystack = strtolower((string) file_get_contents($absolutePath));

        foreach (self::AI_MARKERS as $marker) {
            if (str_contains($haystack, $marker)) {
                $checks[] = $this->check('ai_marker', 'danger', "Fayl ichida AI generator izi topildi (\"{$marker}\").");
                break;
            }
        }

        foreach (self::EDITOR_MARKERS as $marker) {
            if (str_contains($haystack, $marker)) {
                $checks[] = $this->check('editor_marker', 'warn', "Rasm tahrirlash dasturida saqlangan (\"{$marker}\").");
                break;
            }
        }

        $size = @getimagesize($absolutePath);
        if ($size && (min($size[0], $size[1]) < 300)) {
            $checks[] = $this->check('low_resolution', 'warn', "Rasm juda kichik ({$size[0]}×{$size[1]}) — tafsilotlarni tekshirish qiyin.");
        }

        return $checks;
    }

    protected function behaviourChecks(TopupRequest $topup): array
    {
        $checks = [];

        if ($topup->submitted_at && $topup->created_at && $topup->created_at->diffInSeconds($topup->submitted_at) < 20) {
            $checks[] = $this->check('too_fast', 'warn', "Chek so'rov yaratilganidan 20 soniya ichida yuklangan — bu vaqtda to'lov qilish qiyin.");
        }

        $recentRejected = TopupRequest::where('user_id', $topup->user_id)
            ->where('status', TopupRequest::STATUS_REJECTED)
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        if ($recentRejected >= 2) {
            $checks[] = $this->check('user_history', 'warn', "Foydalanuvchining so'nggi 30 kunda {$recentRejected} ta so'rovi rad etilgan.");
        }

        return $checks;
    }

    protected function aiChecks(TopupRequest $topup, ?array $ai): array
    {
        if ($ai === null) {
            return [$this->check('ai_unavailable', 'info', "AI tahlili bajarilmadi — chekni qo'lda diqqat bilan tekshiring.")];
        }

        $checks = [];
        $expected = (int) $topup->expected_amount;

        if (!$ai['is_payment_receipt']) {
            $checks[] = $this->check('not_receipt', 'danger', "AI: rasm to'lov chekiga o'xshamaydi.");
        }

        if ($ai['transfer_successful'] === false) {
            $checks[] = $this->check('not_successful', 'danger', "AI: chekda o'tkazma muvaffaqiyatsiz yoki kutilmoqda deb ko'rsatilgan.");
        }

        if ($ai['amount'] === null) {
            $checks[] = $this->check('amount_unreadable', 'warn', "AI: chekdagi summani o'qib bo'lmadi.");
        } elseif ($ai['amount'] !== $expected) {
            $checks[] = $ai['amount'] === (int) $topup->base_amount
                ? $this->check('amount_without_offset', 'warn', "Chekda " . $this->money($ai['amount']) . " — unikal qo'shimchasiz o'tkazilgan (kutilgan " . $this->money($expected) . "). Bank ilovasida alohida tekshiring.")
                : $this->check('amount_mismatch', 'danger', "Chekdagi summa " . $this->money($ai['amount']) . ", kutilgan " . $this->money($expected) . ".");
        }

        if ($topup->card_last4) {
            if ($ai['recipient_card_last4'] === null) {
                $checks[] = $this->check('card_unreadable', 'warn', "AI: qabul qiluvchi karta raqamini o'qib bo'lmadi.");
            } elseif ($ai['recipient_card_last4'] !== $topup->card_last4) {
                $checks[] = $this->check('card_mismatch', 'danger', "Chekdagi qabul qiluvchi karta •••• {$ai['recipient_card_last4']}, sizniki •••• {$topup->card_last4}.");
            }
        }

        if ($ai['datetime']) {
            try {
                $paidAt = Carbon::parse($ai['datetime'], 'Asia/Tashkent');
                if ($paidAt->lt($topup->created_at->copy()->subMinutes(10))) {
                    $checks[] = $this->check('receipt_too_old', 'danger', "Chek sanasi ({$paidAt->format('d.m.Y H:i')}) so'rov yaratilishidan oldin — eski chek bo'lishi mumkin.");
                } elseif ($paidAt->gt(now()->addMinutes(10))) {
                    $checks[] = $this->check('receipt_in_future', 'warn', "Chek sanasi ({$paidAt->format('d.m.Y H:i')}) kelajakda ko'rsatilgan.");
                }
            } catch (\Throwable) {
                $checks[] = $this->check('date_unreadable', 'info', "AI: chek sanasini aniqlab bo'lmadi.");
            }
        }

        if ($ai['transaction_id']) {
            $dup = TopupRequest::where('id', '!=', $topup->id)
                ->where('extracted_transaction_id', $ai['transaction_id'])
                ->first();
            if ($dup) {
                $checks[] = $this->check('duplicate_transaction', 'danger', "Tranzaksiya raqami {$ai['transaction_id']} avval #{$dup->reference} so'rovida ishlatilgan.");
            }
        }

        if ($topup->payer_card_last4 && $ai['sender_card_last4'] && $ai['sender_card_last4'] !== $topup->payer_card_last4) {
            $checks[] = $this->check('payer_card_mismatch', 'warn', "Foydalanuvchi yozgan karta •••• {$topup->payer_card_last4}, chekda •••• {$ai['sender_card_last4']}.");
        }

        $reasons = $ai['suspicion_reasons'] ? ' (' . implode('; ', $ai['suspicion_reasons']) . ')' : '';
        if ($ai['ai_generated_suspected']) {
            $checks[] = $this->check('ai_generated', 'danger', 'AI: rasm sun\'iy yaratilganga o\'xshaydi' . $reasons . '.');
        } elseif ($ai['edit_suspected']) {
            $checks[] = $this->check('edit_suspected', 'warn', 'AI: tahrirlash belgilari bor' . $reasons . '.');
        }

        if (empty(array_filter($checks, fn ($c) => in_array($c['level'], ['warn', 'danger'], true)))) {
            $checks[] = $this->check('ai_match', 'ok', "AI: summa, karta va sana kutilganga mos.");
        }

        return $checks;
    }

    protected function check(string $code, string $level, string $message): array
    {
        return ['code' => $code, 'level' => $level, 'message' => $message];
    }

    protected function money(int $amount): string
    {
        return number_format($amount, 0, '.', ' ') . " so'm";
    }

    protected function luma(\GdImage $image, int $x, int $y): float
    {
        $rgb = imagecolorat($image, $x, $y);

        return 0.299 * (($rgb >> 16) & 0xFF) + 0.587 * (($rgb >> 8) & 0xFF) + 0.114 * ($rgb & 0xFF);
    }
}
