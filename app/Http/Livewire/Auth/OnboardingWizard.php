<?php

namespace App\Http\Livewire\Auth;

use App\Models\CoinTransaction;
use App\Models\PointTransaction;
use App\Models\UserProfile;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Component;
use Livewire\WithFileUploads;

class OnboardingWizard extends Component
{
    use WithFileUploads;

    public int $currentStep = 1;
    public int $totalSteps = 5;
    public bool $isEditMode = false;

    // Step 1: Til va o'qish joyi
    public string $preferred_locale = 'uz';
    public array $reading_languages = ['uz'];
    public string $reading_place = '';

    // Step 2: O'qishdan maqsad
    public string $reading_goal = '';

    // Step 3: Sevimli janrlar va mutolaa formati
    public array $favorite_genres = [];
    public string $reading_format = 'all';

    // Step 4: Kunlik maqsad, mavzu va bildirishnomalar
    public int $daily_minutes_goal = 30;
    public string $reader_theme = 'dark';
    public bool $notify_streak = true;
    public bool $notify_daily_quote = true;
    public bool $notify_live = true;

    // Step 5: Profil va yakunlash
    public string $bio = '';
    public string $favorite_book = '';
    public string $avatar_preset = '';
    public $avatar = null;

    protected function getStepRules(int $step): array
    {
        return match ($step) {
            1 => [
                'preferred_locale' => 'required|in:uz,ru,en',
                'reading_place'    => 'required|in:home,office,university,library,travel,cafe,bedtime,other',
            ],
            2 => [
                'reading_goal' => 'required|in:knowledge,personal_dev,exam_prep,language,career,critical_thinking,spiritual,other',
            ],
            3 => [
                'favorite_genres' => 'nullable|array|max:5',
                'reading_format'  => 'required|in:3d_pdf,audio,video_quiz,all',
            ],
            4 => [
                'daily_minutes_goal' => 'required|in:15,30,60,90',
                'reader_theme'       => 'required|in:light,sepia,dark',
            ],
            5 => [
                'bio'           => 'nullable|string|max:300',
                'favorite_book' => 'nullable|string|max:120',
                'avatar'        => 'nullable|image|max:2048',
            ],
            default => [],
        };
    }

    public function mount()
    {
        $user = Auth::user();
        $this->isEditMode = request()->boolean('edit');

        if ($user && $user->hasCompletedOnboarding() && !$this->isEditMode) {
            return redirect()->route('home');
        }

        $currentLocale = Session::get('locale', App::getLocale() ?: 'uz');
        $this->preferred_locale = in_array($currentLocale, ['uz', 'ru', 'en'], true) ? $currentLocale : 'uz';
        App::setLocale($this->preferred_locale);

        if ($user) {
            $this->bio = (string) ($user->bio ?? '');

            $profile = $user->profile;
            if ($profile) {
                $this->reading_place = (string) ($profile->reading_place ?? '');
                $this->reading_goal  = (string) ($profile->reading_goal ?? '');

                $settings = is_array($profile->privacy_settings) ? $profile->privacy_settings : [];
                if (!empty($settings['preferred_locale']) && in_array($settings['preferred_locale'], ['uz', 'ru', 'en'], true)) {
                    $this->preferred_locale = $settings['preferred_locale'];
                    Session::put('locale', $this->preferred_locale);
                    App::setLocale($this->preferred_locale);
                }
                if (!empty($settings['reading_languages']) && is_array($settings['reading_languages'])) {
                    $this->reading_languages = $settings['reading_languages'];
                }
                if (!empty($settings['favorite_genres']) && is_array($settings['favorite_genres'])) {
                    $this->favorite_genres = $settings['favorite_genres'];
                }
                if (!empty($settings['reading_format'])) {
                    $this->reading_format = (string) $settings['reading_format'];
                }
                if (!empty($settings['daily_minutes_goal'])) {
                    $this->daily_minutes_goal = (int) $settings['daily_minutes_goal'];
                }
                if (!empty($settings['reader_theme'])) {
                    $this->reader_theme = (string) $settings['reader_theme'];
                }
                if (!empty($settings['favorite_book'])) {
                    $this->favorite_book = (string) $settings['favorite_book'];
                }
                if (!empty($settings['avatar_preset'])) {
                    $this->avatar_preset = (string) $settings['avatar_preset'];
                }
            }

            $prefs = is_array($user->notification_prefs) ? $user->notification_prefs : [];
            $this->notify_streak      = (bool) ($prefs['streak_reminder'] ?? true);
            $this->notify_daily_quote = (bool) ($prefs['daily_quote'] ?? true);
            $this->notify_live        = (bool) ($prefs['live_stream'] ?? true);
        }
    }

    public function hydrate()
    {
        if (in_array($this->preferred_locale, ['uz', 'ru', 'en'], true)) {
            App::setLocale($this->preferred_locale);
        }
    }

    public function selectLocale(string $locale): void
    {
        if (!in_array($locale, ['uz', 'ru', 'en'], true)) {
            return;
        }

        $this->preferred_locale = $locale;
        Session::put('locale', $locale);
        App::setLocale($locale);

        if (!in_array($locale, $this->reading_languages, true)) {
            $this->reading_languages[] = $locale;
        }
    }

    public function toggleReadingLanguage(string $lang): void
    {
        if (!in_array($lang, ['uz', 'ru', 'en', 'world'], true)) {
            return;
        }

        if (in_array($lang, $this->reading_languages, true)) {
            if (count($this->reading_languages) > 1) {
                $this->reading_languages = array_values(array_diff($this->reading_languages, [$lang]));
            }
        } else {
            $this->reading_languages[] = $lang;
        }
    }

    public function selectPlace(string $place): void
    {
        $this->reading_place = $place;
        $this->validate($this->getStepRules(1));
        $this->nextStep();
    }

    public function selectGoal(string $goal): void
    {
        $this->reading_goal = $goal;
        $this->validate($this->getStepRules(2));
        $this->nextStep();
    }

    public function toggleGenre(string $genre): void
    {
        $allowed = ['fiction', 'self_help', 'business', 'psychology', 'history', 'science', 'detective', 'classic'];
        if (!in_array($genre, $allowed, true)) {
            return;
        }

        if (in_array($genre, $this->favorite_genres, true)) {
            $this->favorite_genres = array_values(array_diff($this->favorite_genres, [$genre]));
        } elseif (count($this->favorite_genres) < 5) {
            $this->favorite_genres[] = $genre;
        }
    }

    public function selectFormat(string $format): void
    {
        if (in_array($format, ['3d_pdf', 'audio', 'video_quiz', 'all'], true)) {
            $this->reading_format = $format;
        }
    }

    public function selectDailyGoal(int $minutes): void
    {
        if (in_array($minutes, [15, 30, 60, 90], true)) {
            $this->daily_minutes_goal = $minutes;
        }
    }

    public function selectReaderTheme(string $theme): void
    {
        if (in_array($theme, ['light', 'sepia', 'dark'], true)) {
            $this->reader_theme = $theme;
            $this->dispatchBrowserEvent('onboarding-theme-selected', ['theme' => $theme]);
        }
    }

    public function selectTheme(string $theme): void
    {
        $this->selectReaderTheme($theme);
    }

    public function selectAvatarPreset(string $preset): void
    {
        $this->avatar_preset = ($this->avatar_preset === $preset) ? '' : $preset;
    }

    public function selectPresetAvatar(string $preset): void
    {
        $this->selectAvatarPreset($preset);
    }

    public function goToStep(int $step): void
    {
        if ($step < 1 || $step > $this->totalSteps) {
            return;
        }

        if ($step < $this->currentStep) {
            $this->resetErrorBag();
            $this->currentStep = $step;
            return;
        }

        // Oldinga o'tishda joriy qadamni tekshiramiz
        if ($this->currentStep === 1 && empty($this->reading_place)) {
            $this->reading_place = 'home';
        }
        if ($this->currentStep === 2 && empty($this->reading_goal)) {
            $this->reading_goal = 'knowledge';
        }

        $this->resetErrorBag();
        $this->currentStep = $step;
    }

    public function nextStep(): void
    {
        $rules = $this->getStepRules($this->currentStep);
        if (!empty($rules)) {
            $this->validate($rules);
        }

        $this->resetErrorBag();
        if ($this->currentStep < $this->totalSteps) {
            $this->currentStep++;
        }
    }

    public function prevStep(): void
    {
        $this->resetErrorBag();
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    public function skipOnboarding()
    {
        if (empty($this->reading_place)) {
            $this->reading_place = 'home';
        }
        if (empty($this->reading_goal)) {
            $this->reading_goal = 'knowledge';
        }

        return $this->finish();
    }

    public function finish()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        if (empty($this->reading_place)) {
            $this->reading_place = 'home';
        }
        if (empty($this->reading_goal)) {
            $this->reading_goal = 'knowledge';
        }

        $this->validate($this->getStepRules(5));

        if ($this->avatar) {
            $path = $this->avatar->store('avatars', 'public');
            $user->avatar = $path;
        }

        if ($this->bio !== '') {
            $user->bio = trim($this->bio);
        }

        // Bildirishnoma sozlamalarini yangilash
        $existingNotifs = is_array($user->notification_prefs) ? $user->notification_prefs : [];
        $user->notification_prefs = array_merge($existingNotifs, [
            'enabled'          => true,
            'telegram_enabled' => true,
            'streak_reminder'  => $this->notify_streak,
            'daily_quote'      => $this->notify_daily_quote,
            'live_stream'      => $this->notify_live,
            'ban_status'       => $existingNotifs['ban_status'] ?? true,
            'new_follower'     => $existingNotifs['new_follower'] ?? true,
        ]);

        // Birinchi marta onboarding yakunlanayotgan bo'lsa — +50 ball va +25 tanga xush kelibsiz bonusi!
        $alreadyCompletedBefore = $user->hasCompletedOnboarding()
            || PointTransaction::where('user_id', $user->id)->where('description', 'like', '%Onboarding%')->exists();

        $bonusPoints = 0;
        $bonusCoins = 0;

        if (!$alreadyCompletedBefore) {
            $bonusPoints = 50;
            $bonusCoins = 25;
            $user->total_points = (int) $user->total_points + $bonusPoints;
            $user->coin_balance = (int) $user->coin_balance + $bonusCoins;
        }

        $user->save();

        if ($bonusPoints > 0) {
            PointTransaction::create([
                'user_id'     => $user->id,
                'points'      => $bonusPoints,
                'source'      => 'bonus',
                'description' => 'Onboardingni to\'liq yakunlash uchun xush kelibsiz bonusi! 🎉',
            ]);
        }

        if ($bonusCoins > 0) {
            try {
                CoinTransaction::create([
                    'user_id'     => $user->id,
                    'coins'       => $bonusCoins,
                    'source'      => 'bonus',
                    'description' => 'Onboarding shaxsiy mutolaa rejasini tuzish bonusi 🪙',
                ]);
            } catch (\Throwable $e) {}
        }

        $existingPrivacy = is_array($user->profile?->privacy_settings) ? $user->profile->privacy_settings : [];

        UserProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'reading_place'    => $this->reading_place,
                'reading_goal'     => $this->reading_goal,
                'privacy_settings' => array_merge($existingPrivacy, [
                    'show_stats'              => $existingPrivacy['show_stats'] ?? true,
                    'show_activity'           => $existingPrivacy['show_activity'] ?? true,
                    'preferred_locale'        => $this->preferred_locale,
                    'reading_languages'       => $this->reading_languages,
                    'favorite_genres'         => $this->favorite_genres,
                    'reading_format'          => $this->reading_format,
                    'daily_minutes_goal'      => $this->daily_minutes_goal,
                    'daily_reading_minutes'   => $this->daily_minutes_goal,
                    'reader_theme'            => $this->reader_theme,
                    'favorite_book'           => trim($this->favorite_book),
                    'avatar_preset'           => $this->avatar_preset,
                    'avatar_emblem'           => $this->avatar_preset,
                    'onboarding_completed_at' => now()->toIso8601String(),
                ]),
            ]
        );

        Session::put('locale', $this->preferred_locale);
        App::setLocale($this->preferred_locale);

        $flashMessages = [
            'uz' => $bonusPoints > 0
                ? "🎉 Xush kelibsiz! Shaxsiy mutolaa rejangiz saqlandi. +{$bonusPoints} ball va +{$bonusCoins} tanga hisobingizga qo'shildi!"
                : "✅ Shaxsiy mutolaa rejangiz va sozlamalaringiz muvaffaqiyatli yangilandi!",
            'ru' => $bonusPoints > 0
                ? "🎉 Добро пожаловать! Ваш план чтения сохранен. Начислено +{$bonusPoints} баллов и +{$bonusCoins} монет!"
                : "✅ Ваш персональный план чтения и настройки успешно обновлены!",
            'en' => $bonusPoints > 0
                ? "🎉 Welcome aboard! Your reading plan is saved. +{$bonusPoints} points and +{$bonusCoins} coins added to your balance!"
                : "✅ Your personal reading plan and preferences have been updated!",
        ];

        session()->flash('success', $flashMessages[$this->preferred_locale] ?? $flashMessages['uz']);
        return redirect()->route('home');
    }

    public function render()
    {
        App::setLocale($this->preferred_locale);

        $titles = [
            'uz' => 'Shaxsiy mutolaa rejasini sozlash',
            'ru' => 'Настройка личного плана чтения',
            'en' => 'Personal Reading Setup',
        ];

        return view('livewire.auth.onboarding-wizard', [
            'preferred_locale'   => $this->preferred_locale,
            'isEditMode'         => $this->isEditMode,
            'currentStep'        => $this->currentStep,
            'totalSteps'         => $this->totalSteps,
            'step'               => $this->currentStep,
            'reading_languages'  => $this->reading_languages,
            'reading_place'      => $this->reading_place,
            'reading_goal'       => $this->reading_goal,
            'favorite_genres'    => $this->favorite_genres,
            'reading_format'     => $this->reading_format,
            'daily_minutes_goal' => $this->daily_minutes_goal,
            'reader_theme'       => $this->reader_theme,
            'avatar_preset'      => $this->avatar_preset,
            'favorite_book'      => $this->favorite_book,
            'bio'                => $this->bio,
            'notify_streak'      => $this->notify_streak,
            'notify_daily_quote' => $this->notify_daily_quote,
            'notify_live'        => $this->notify_live,
        ])->layout('layouts.auth', ['title' => $titles[$this->preferred_locale] ?? $titles['uz']]);
    }
}
