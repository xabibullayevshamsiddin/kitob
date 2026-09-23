<?php

namespace App\Http\Livewire\Settings;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class SettingsPage extends Component
{
    use WithFileUploads;

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
        'name'     => 'ism',
        'username' => 'username',
        'bio'      => 'bio',
    ];

    public function save(): void
    {
        $this->validate();

        $user = Auth::user();
        $data = [
            'name'     => $this->name,
            'username' => $this->username,
            'bio'      => $this->bio,
        ];

        if ($this->avatar) {
            $data['avatar'] = $this->avatar->store('avatars', 'public');
        }

        $user->update($data);

        $this->reset('avatar');

        session()->flash('success', 'Sozlamalar saqlandi! ✅');
    }

    public function render()
    {
        return view('livewire.settings.settings-page')
            ->layout('layouts.app', ['title' => 'Sozlamalar']);
    }
}
