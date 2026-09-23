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

    public int $currentStep = 1;
    public int $totalSteps = 3;

    public string $reading_place = '';
    public string $reading_goal = '';
    public string $bio = '';
    public $avatar = null;

    protected $rules = [
        1 => ['reading_place' => 'required|in:home,office,university,library,travel,other'],
        2 => ['reading_goal' => 'required|in:knowledge,personal_dev,exam_prep,language,career,spiritual,other'],
        3 => ['bio' => 'nullable|string|max:300', 'avatar' => 'nullable|image|max:2048'],
    ];

    public function mount()
    {
        $user = Auth::user();
        if ($user && $user->profile && $user->profile->reading_place) {
            return redirect()->route('dashboard');
        }

        if ($user && $user->bio) {
            $this->bio = $user->bio;
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

        if ($this->avatar) {
            $path = $this->avatar->store('avatars', 'public');
            $user->avatar = $path;
        }

        if (!empty($this->bio)) {
            $user->bio = $this->bio;
        }

        $bonusPoints = 50;
        $user->total_points += $bonusPoints;
        $user->save();

        PointTransaction::create([
            'user_id' => $user->id,
            'points' => $bonusPoints,
            'source' => 'bonus',
            'description' => 'Onboardingni to\'liq yakunlash uchun xush kelibsiz bonusi! 🎉',
        ]);

        UserProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'reading_place' => $this->reading_place,
                'reading_goal' => $this->reading_goal,
                'privacy_settings' => ['show_stats' => true, 'show_activity' => true],
            ]
        );

        session()->flash('success', 'Xush kelibsiz! Onboarding muvaffaqiyatli yakunlandi va 50 ball hisobingizga qo\'shildi! 🎉');
        return redirect()->route('dashboard');
    }

    public function render()
    {
        return view('livewire.auth.onboarding-wizard')
            ->layout('layouts.auth', ['title' => 'Tanishuv va sozlash']);
    }
}
