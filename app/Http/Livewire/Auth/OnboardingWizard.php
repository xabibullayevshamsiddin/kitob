<?php

namespace App\Http\Livewire\Auth;

use App\Models\UserProfile;
use App\Models\PointTransaction;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class OnboardingWizard extends Component
{
    use WithFileUploads;

    public bool $isEdit = false;
    public int $currentStep = 1;
    public int $totalSteps = 4;

    public string $reading_place = '';
    public string $reading_goal = '';
    public int $daily_reading_minutes = 30;
    public string $preferred_locale = 'uz';
    public array $favorite_genres = [];

    public string $bio = '';
    public $avatar = null;

    protected $rules = [
        1 => ['reading_place' => 'required|in:home,office,university,library,travel,other'],
        2 => ['reading_goal' => 'required|in:knowledge,personal_dev,exam_prep,language,career,spiritual,other'],
        3 => [
            'daily_reading_minutes' => 'required|integer|min:5|max:240',
            'preferred_locale' => 'required|in:uz,ru,en',
            'favorite_genres' => 'nullable|array',
        ],
        4 => ['bio' => 'nullable|string|max:300', 'avatar' => 'nullable|image|max:2048'],
    ];

    public function mount()
    {
        $this->isEdit = request()->boolean('edit') || request()->has('edit');
        $user = Auth::user();

        // Agar tahrirlash rejimida bo'lmasa va foydalanuvchi allaqachon onboardingdan o'tgan bo'lsa -> home
        if (!$this->isEdit && $user && $user->profile && $user->profile->reading_place && $user->hasCompletedOnboarding()) {
            return redirect()->route('home');
        }

        if ($user) {
            $this->bio = $user->bio ?? '';
            if ($user->profile) {
                $this->reading_place = $user->profile->reading_place ?? '';
                $this->reading_goal = $user->profile->reading_goal ?? '';
                $priv = is_array($user->profile->privacy_settings) ? $user->profile->privacy_settings : [];
                $this->daily_reading_minutes = (int) ($priv['daily_reading_minutes'] ?? 30);
                $this->preferred_locale = $priv['preferred_locale'] ?? app()->getLocale();
                $this->favorite_genres = (array) ($priv['favorite_genres'] ?? []);
            }
        }
    }

    public function selectPlace(string $place)
    {
        $this->reading_place = $place;
        $this->validate($this->rules[1]);
        $this->nextStep();
    }

    public function selectGoal(string $goal)
    {
        $this->reading_goal = $goal;
        $this->validate($this->rules[2]);
        $this->nextStep();
    }

    public function setMinutes(int $minutes)
    {
        $this->daily_reading_minutes = $minutes;
    }

    public function setLocale(string $locale)
    {
        if (in_array($locale, ['uz', 'ru', 'en'], true)) {
            $this->preferred_locale = $locale;
            session()->put('locale', $locale);
            app()->setLocale($locale);
            \Illuminate\Support\Facades\Cookie::queue(
                \Illuminate\Support\Facades\Cookie::make('locale', $locale, 60 * 24 * 365)
            );
        }
    }

    public function toggleGenre(string $genre)
    {
        if (in_array($genre, $this->favorite_genres, true)) {
            $this->favorite_genres = array_values(array_diff($this->favorite_genres, [$genre]));
        } else {
            if (count($this->favorite_genres) < 5) {
                $this->favorite_genres[] = $genre;
            }
        }
    }

    public function saveStepThree()
    {
        $this->validate($this->rules[3]);
        $this->nextStep();
    }

    public function nextStep()
    {
        $this->resetErrorBag();
        if ($this->currentStep < $this->totalSteps) {
            $this->currentStep++;
        }
    }

    public function prevStep()
    {
        $this->resetErrorBag();
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    public function finish()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        if ($this->avatar) {
            $path = $this->avatar->store('avatars', 'public');
            $user->avatar = $path;
        }

        if ($this->bio !== null) {
            $user->bio = $this->bio;
        }

        $wasCompleted = $user->profile && !empty($user->profile->reading_place);

        // Faqat yangi onboarding bo'lsa 50 ball beriladi (tahrirlashda takroran berilmaydi)
        if (!$wasCompleted) {
            $bonusPoints = 50;
            $user->total_points += $bonusPoints;

            try {
                PointTransaction::create([
                    'user_id' => $user->id,
                    'points' => $bonusPoints,
                    'source' => 'bonus',
                    'description' => 'Onboardingni to\'liq yakunlash uchun xush kelibsiz bonusi! 🎉',
                ]);
            } catch (\Throwable $e) {}
        }

        $user->save();

        $currentPriv = is_array($user->profile?->privacy_settings) ? $user->profile->privacy_settings : ['show_stats' => true, 'show_activity' => true];
        $currentPriv['daily_reading_minutes'] = $this->daily_reading_minutes;
        $currentPriv['preferred_locale'] = $this->preferred_locale;
        $currentPriv['favorite_genres'] = $this->favorite_genres;

        UserProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'reading_place' => $this->reading_place ?: 'home',
                'reading_goal' => $this->reading_goal ?: 'knowledge',
                'privacy_settings' => $currentPriv,
            ]
        );

        // Sayt asosiy tilini darhol tanlangan tilga o'zgartirish va sessiya/cookieda mustahkamlash
        session()->put('locale', $this->preferred_locale);
        app()->setLocale($this->preferred_locale);
        \Illuminate\Support\Facades\Cookie::queue(
            \Illuminate\Support\Facades\Cookie::make('locale', $this->preferred_locale, 60 * 24 * 365)
        );

        if ($this->isEdit) {
            $msg = match($this->preferred_locale) {
                'ru' => 'Персональный план чтения и язык интерфейса успешно сохранены!',
                'en' => 'Personal reading plan and interface language saved successfully!',
                default => 'Shaxsiy mutolaa rejangiz va sayt tili muvaffaqiyatli saqlandi!',
            };
            session()->flash('success', $msg);
            return redirect()->route('settings');
        }

        $msg = match($this->preferred_locale) {
            'ru' => 'Добро пожаловать! Онбординг успешно пройден, вам начислено 50 баллов! 🎉',
            'en' => 'Welcome! Onboarding completed successfully and 50 points added to your account! 🎉',
            default => 'Xush kelibsiz! Onboarding muvaffaqiyatli yakunlandi va 50 ball hisobingizga qo\'shildi! 🎉',
        };
        session()->flash('success', $msg);
        return redirect()->route('home');
    }

    public function render()
    {
        return view('livewire.auth.onboarding-wizard')
            ->layout('layouts.auth', ['title' => $this->isEdit ? 'Mutolaa rejasini sozlash' : 'Tanishuv va sozlash']);
    }
}
