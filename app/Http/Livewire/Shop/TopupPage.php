<?php

namespace App\Http\Livewire\Shop;

use App\Http\Livewire\Concerns\WithToast;
use App\Models\TopupRequest;
use App\Services\Shop\Exceptions\ShopException;
use App\Services\Topup\TopupService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Karta orqali tanga/ball sotib olish sahifasi.
 * Ochiq so'rov ID'si public property'da saqlanmaydi — har safar serverda foydalanuvchi bo'yicha topiladi.
 */
class TopupPage extends Component
{
    use WithToast;
    use WithFileUploads;

    public $receipt = null;
    public string $payerCardLast4 = '';
    public string $note = '';

    protected function rules(): array
    {
        return [
            'receipt'        => 'required|image|mimes:jpg,jpeg,png,webp|max:' . (int) config('topup.receipt_max_kb', 6144),
            'payerCardLast4' => 'nullable|digits:4',
            'note'           => 'nullable|string|max:500',
        ];
    }

    protected $messages = [
        'receipt.required'      => 'Chek rasmini yuklang.',
        'receipt.image'         => 'Chek rasm (skrinshot) bo\'lishi kerak.',
        'receipt.mimes'         => 'Faqat JPG, PNG yoki WEBP.',
        'receipt.max'           => 'Rasm hajmi juda katta (maksimum 6 MB).',
        'payerCardLast4.digits' => 'Kartangizning oxirgi 4 raqamini kiriting.',
    ];

    public function choosePackage(string $packageKey, TopupService $service): void
    {
        try {
            $service->createRequest(Auth::user(), $packageKey);
            $this->toastSuccess("So'rov yaratildi. Endi ko'rsatilgan aniq summani kartaga o'tkazing.");
        } catch (ShopException $e) {
            $this->toastError($e->getMessage());
        }
    }

    public function cancelRequest(TopupService $service): void
    {
        $open = $service->openRequestFor(Auth::user());
        if (!$open) {
            return;
        }

        try {
            $service->cancel($open, Auth::user());
            $this->reset(['receipt', 'payerCardLast4', 'note']);
            $this->toastInfo("So'rov bekor qilindi.");
        } catch (ShopException $e) {
            $this->toastError($e->getMessage());
        }
    }

    public function submitReceipt(TopupService $service): void
    {
        $this->validate();

        $open = $service->openRequestFor(Auth::user());
        if (!$open || !$open->isAwaitingPayment()) {
            $this->toastError("Chek yuklash uchun ochiq so'rov topilmadi.");
            return;
        }

        try {
            $service->submitReceipt($open, Auth::user(), $this->receipt, $this->payerCardLast4 ?: null, $this->note ?: null);
            $this->reset(['receipt', 'payerCardLast4', 'note']);
            $this->toastSuccess('Chek qabul qilindi! Admin tekshirgach, hisobingizga tushadi.');
        } catch (ShopException $e) {
            $this->addError('receipt', $e->getMessage());
        }
    }

    public function render(TopupService $service): View
    {
        $user = Auth::user();

        return view('livewire.shop.topup-page', [
            'enabled'    => $service->isEnabled(),
            'packages'   => $service->activePackages(),
            'open'       => $service->openRequestFor($user),
            'card'       => $service->cardFormatted(),
            'cardHolder' => $service->cardHolder(),
            'cardBank'   => $service->cardBank(),
            'dailyLimit' => $service->dailyLimit(),
            'spentToday' => $service->spentToday($user),
            'history'    => TopupRequest::where('user_id', $user->id)->latest('id')->take(10)->get(),
        ])->layout('layouts.app', ['title' => "Tanga va ball sotib olish — Kitobxon"]);
    }
}
