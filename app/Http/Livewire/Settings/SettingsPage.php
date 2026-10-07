<?php

namespace App\Http\Livewire\Settings;

use App\Http\Livewire\Concerns\WithToast;
use Illuminate\Support\Facades\Auth;
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

    public string $bio = '';

    public $avatar;

    public function mount(): void
    {
        $user = Auth::user();

        $this->name     = $user->name;
        $this->username = $user->username;
        $this->bio      = $user->bio ?? '';
    }

    protected function rules(): array
    {
        $userId = Auth::id();

        return [
            'name'     => 'required|string|min:2|max:60',
            'username' => ['required', 'string', 'min:3', 'max:30', 'alpha_dash', Rule::unique('users', 'username')->ignore($userId)],
            'bio'      => 'nullable|string|max:300',
            'avatar'   => 'nullable|image|max:2048',
        ];
    }

    protected $validationAttributes = [
        'name'     => 'site.settings.attr_name',
        'username' => 'site.settings.username',
        'bio'      => 'site.settings.bio',
    ];

    public function save(): void
    {
        try {
            $this->validate();
        } catch (ValidationException $e) {
            $this->toastError('Formada xatolik bor: ' . collect($e->validator->errors()->all())->first());
            throw $e;
        }

        $user = Auth::user();
        $data = [
            'name'     => $this->name,
            'username' => $this->username,
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
            // Fayl allaqachon yo'q bo'lishi mumkin — DB tozalash baribir davom etadi.
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
