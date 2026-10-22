<?php

namespace App\Http\Livewire\Settings;

use App\Http\Livewire\Concerns\WithToast;
use App\Models\User;
use App\Services\Telegram\TelegramClient;
use App\Support\PhoneNumber;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithFileUploads;

class SettingsPage extends Component
{
    use WithFileUploads;
    use WithToast;

    public string $name = '';
    public string $username = '';
    public string $email = '';
    public string $bio = '';
    public $avatar;

    // Telefon raqami va Telegram ma'lumotlari
    public ?string $phone = null;
    public bool $hasVerifiedPhone = false;
    public bool $isTelegramLinked = false;
    public ?string $telegramUsername = null;

    // Telefon raqamini o'zgartirish xavfsizlik oqimi
    public bool $showPhoneModal = false;
    public string $newPhone = '';
    public string $currentPassword = '';
    public string $verificationCode = '';
    public bool $codeSent = false;

    public function mount(): void
    {
        $user = Auth::user();

        $this->name     = $user->name;
        $this->username = $user->username;
        $this->email    = $user->email ?? '';
        $this->bio      = $user->bio ?? '';

        $this->phone            = $user->phone ? PhoneNumber::format($user->phone) : null;
        $this->hasVerifiedPhone = $user->hasVerifiedPhone();
        $this->isTelegramLinked = $user->isTelegramLinked();
        $this->telegramUsername = $user->telegram_username;
    }

    protected function rules(): array
    {
        $userId = Auth::id();

        return [
            'name'     => 'required|string|min:2|max:60',
            'username' => ['required', 'string', 'min:3', 'max:30', 'alpha_dash', Rule::unique('users', 'username')->ignore($userId)],
            'email'    => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'bio'      => 'nullable|string|max:300',
            'avatar'   => 'nullable|image|max:2048',
        ];
    }

    protected $validationAttributes = [
        'name'     => 'site.settings.attr_name',
        'username' => 'site.settings.username',
        'email'    => 'Elektron pochta (Gmail)',
        'bio'      => 'site.settings.bio',
    ];

    public function updatedUsername(): void
    {
        $clean = strtolower(trim((string) $this->username));
        $this->username = $clean;
        $this->resetErrorBag('username');

        if (strlen($clean) >= 3) {
            $alreadyExists = User::whereRaw('LOWER(username) = ?', [$clean])
                ->where('id', '!=', Auth::id())
                ->exists();

            if ($alreadyExists) {
                $this->addError('username', "Ushbu foydalanuvchi nomi (@{$clean}) allaqachon band. Iltimos, boshqa nom tanlang.");
            }
        }
    }

    public function save(): void
    {
        $this->username = strtolower(trim((string) $this->username));
        $this->name = trim((string) $this->name);

        // Username takrorlanmasligi (unique) qat'iy tekshiruvi (Katta-kichik harflaridan qat'i nazar)
        $alreadyExists = User::whereRaw('LOWER(username) = ?', [$this->username])
            ->where('id', '!=', Auth::id())
            ->exists();

        if ($alreadyExists) {
            $this->addError('username', "Ushbu foydalanuvchi nomi (@{$this->username}) allaqachon band. Iltimos, boshqa nom tanlang.");
            $this->toastError("Ushbu username band! Boshqa nom tanlang.");
            return;
        }

        try {
            $this->validate();
        } catch (ValidationException $e) {
            $this->toastError('Formada xatolik bor: ' . collect($e->validator->errors()->all())->first());
            throw $e;
        }

        $user = Auth::user();
        $emailVal = trim($this->email);
        $data = [
            'name'     => $this->name,
            'username' => $this->username,
            'email'    => !empty($emailVal) ? strtolower($emailVal) : null,
            'bio'      => $this->bio,
        ];

        if ($this->avatar) {
            try {
                $data['avatar'] = $this->avatar->store('avatars', 'public');
            } catch (\Throwable $e) {
                report($e);
                $this->toastError('Rasmni yuklashda xatolik yuz berdi. Qaytadan urinib ko\'ring.');
                return;
            }
        }

        $user->update($data);

        $hadAvatar = (bool) $this->avatar;
        $this->reset('avatar');

        $this->toastSuccess(
            $hadAvatar ? __('site.settings.saved_success') . ' Profil rasmi yangilandi.' : __('site.settings.saved_success')
        );
    }

    public function openPhoneModal(): void
    {
        $this->resetErrorBag();
        $this->reset(['newPhone', 'currentPassword', 'verificationCode', 'codeSent']);
        $this->showPhoneModal = true;
    }

    public function closePhoneModal(): void
    {
        $this->showPhoneModal = false;
        $this->resetErrorBag();
        $this->reset(['newPhone', 'currentPassword', 'verificationCode', 'codeSent']);
    }

    /**
     * Telefon raqamini o'zgartirish so'rovi (1-bosqich: Parol va yangi raqamni tekshirish, kod yuborish).
     */
    public function requestPhoneChange(): void
    {
        $this->resetErrorBag();
        $user = Auth::user();

        // 1. Shart: Joriy parolni tekshirish (Xavfsizlik)
        if (empty($this->currentPassword)) {
            $this->addError('currentPassword', "Hisobingiz xavfsizligi uchun joriy parolingizni kiriting.");
            return;
        }

        if (!Hash::check($this->currentPassword, $user->password)) {
            $this->addError('currentPassword', "Amaldagi parolingiz noto'g'ri kiritildi.");
            return;
        }

        // 2. Shart: Yangi telefon raqami formati va mavjudligini tekshirish
        $cleanPhone = trim($this->newPhone);
        if (empty($cleanPhone)) {
            $this->addError('newPhone', "Yangi telefon raqamingizni kiriting.");
            return;
        }

        $normalized = PhoneNumber::normalize($cleanPhone);
        if (!$normalized || !PhoneNumber::isUzbekistan($normalized)) {
            $this->addError('newPhone', "Iltimos, to'liq va to'g'ri O'zbekiston raqamini kiriting (masalan: 90 123 45 67).");
            return;
        }

        if ($normalized === $user->phone) {
            $this->addError('newPhone', "Kiritilgan raqam sizning amaldagi raqamingiz bilan bir xil.");
            return;
        }

        $alreadyExists = User::where('phone', $normalized)->where('id', '!=', $user->id)->exists();
        if ($alreadyExists) {
            $this->addError('newPhone', "Bu telefon raqami allaqachon boshqa foydalanuvchi hisobiga biriktirilgan.");
            return;
        }

        // 3. Shart: Agar Telegram ulangan bo'lsa -> Bot orqali 6 xonali tasdiqlash kodi
        if ($user->telegram_id) {
            $code = (string) random_int(100000, 999999);
            Cache::put("phone_change_" . $user->id, [
                'phone' => $normalized,
                'code'  => $code,
            ], now()->addMinutes(5));

            try {
                $client = new TelegramClient();
                $formattedNew = PhoneNumber::format($normalized);
                $message = "🔐 <b>Kitobxon: Telefon raqamini o'zgartirish tasdig'i</b>\n\n"
                         . "Hisobingizdagi telefon raqamini <b>{$formattedNew}</b> ga o'zgartirish so'ralmoqda.\n\n"
                         . "Sizning bir martalik tasdiqlash kodingiz:\n"
                         . "👉 <code>{$code}</code> 👈\n\n"
                         . "⚠️ Agar buni siz so'ramagan bo'lsangiz, kodingizni hech kimga bermang va parolingizni zudlik bilan yangilang!\n"
                         . "Kod amal qilish muddati: 5 daqiqa.";

                $client->sendMessage($user->telegram_id, $message);

                $this->codeSent = true;
                $this->toastSuccess("Telegram botingizga 6 xonali tasdiqlash kodi yuborildi!");
            } catch (\Throwable $e) {
                report($e);
                $this->addError('newPhone', "Telegram botga kod yuborishda xatolik yuz berdi. Bot bilan aloqani tekshiring.");
            }
        } else {
            // Agar Telegram ulanmagan bo'lsa: Joriy parol tekshirilgan holda raqam yangilanadi
            $user->update([
                'phone'             => $normalized,
                'phone_verified_at' => now(),
            ]);

            $this->phone = PhoneNumber::format($normalized);
            $this->hasVerifiedPhone = true;
            $this->closePhoneModal();
            $this->toastSuccess("Telefon raqamingiz muvaffaqiyatli yangilandi! Xavfsiz kirish uchun Telegram botimizni ulab qo'yishni tavsiya qilamiz.");
        }
    }

    /**
     * Telefon raqamini o'zgartirishni yakunlash (2-bosqich: Telegram kodi orqali tasdiqlash).
     */
    public function confirmPhoneChange(): void
    {
        $this->resetErrorBag();
        $user = Auth::user();

        if (empty(trim($this->verificationCode))) {
            $this->addError('verificationCode', "Telegram botingizga borgan 6 xonali tasdiqlash kodini kiriting.");
            return;
        }

        $cached = Cache::get("phone_change_" . $user->id);
        if (!$cached) {
            $this->addError('verificationCode', "Tasdiqlash kodi muddati tugagan. Iltimos, qaytadan kod oling.");
            return;
        }

        if (trim($this->verificationCode) !== (string) $cached['code']) {
            $this->addError('verificationCode', "Kiritilgan tasdiqlash kodi noto'g'ri.");
            return;
        }

        $newPhone = $cached['phone'];
        $user->update([
            'phone'             => $newPhone,
            'phone_verified_at' => now(),
        ]);

        Cache::forget("phone_change_" . $user->id);

        if ($user->telegram_id) {
            try {
                $client = new TelegramClient();
                $formatted = PhoneNumber::format($newPhone);
                $client->sendMessage($user->telegram_id, "✅ <b>Telefon raqamingiz muvaffaqiyatli yangilandi:</b>\n\n{$formatted}\n\nEndi ushbu yangi raqam orqali tizimga kirishingiz mumkin.");
            } catch (\Throwable $e) {}
        }

        $this->phone = PhoneNumber::format($newPhone);
        $this->hasVerifiedPhone = true;
        $this->closePhoneModal();
        $this->toastSuccess("Telefon raqamingiz muvaffaqiyatli o'zgartirildi!");
    }

    /**
     * Foydalanuvchi o'z profil rasmini o'chiradi (yuklangan fayl ham o'chadi).
     */
    public function removeAvatar(): void
    {
        $user = Auth::user();

        if (! $user->avatar) {
            return;
        }

        try {
            Storage::disk('public')->delete($user->avatar);
        } catch (\Throwable $e) {
            report($e);
        }

        $user->update(['avatar' => null]);
        $this->reset('avatar');

        $this->toastSuccess(__('site.settings.avatar_removed'));
    }

    public function render()
    {
        return view('livewire.settings.settings-page')
            ->layout('layouts.app', ['title' => __('site.settings.title')]);
    }
}
