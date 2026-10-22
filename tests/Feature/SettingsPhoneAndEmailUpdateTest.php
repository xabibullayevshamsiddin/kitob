<?php

namespace Tests\Feature;

use App\Http\Livewire\Settings\SettingsPage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class SettingsPhoneAndEmailUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_freely_update_email_in_settings(): void
    {
        $user = User::factory()->create([
            'email'    => 'old@gmail.com',
            'password' => Hash::make('password123'),
        ]);

        Livewire::actingAs($user)
            ->test(SettingsPage::class)
            ->set('email', 'new_email@gmail.com')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertEquals('new_email@gmail.com', $user->fresh()->email);
    }

    public function test_user_cannot_set_duplicate_email(): void
    {
        User::factory()->create(['email' => 'taken@gmail.com']);
        $user = User::factory()->create(['email' => 'my@gmail.com']);

        Livewire::actingAs($user)
            ->test(SettingsPage::class)
            ->set('email', 'taken@gmail.com')
            ->call('save')
            ->assertHasErrors(['email']);

        $this->assertEquals('my@gmail.com', $user->fresh()->email);
    }

    public function test_phone_change_requires_valid_current_password(): void
    {
        $user = User::factory()->create([
            'phone'    => '+998901112233',
            'password' => Hash::make('secret123'),
        ]);

        // Noto'g'ri parol bilan urinish
        Livewire::actingAs($user)
            ->test(SettingsPage::class)
            ->call('openPhoneModal')
            ->set('newPhone', '90 999 88 77')
            ->set('currentPassword', 'wrongpassword')
            ->call('requestPhoneChange')
            ->assertHasErrors(['currentPassword']);

        $this->assertEquals('+998901112233', $user->fresh()->phone);
    }

    public function test_phone_change_without_telegram_updates_phone_on_password_match(): void
    {
        $user = User::factory()->create([
            'phone'       => '+998901112233',
            'telegram_id' => null,
            'password'    => Hash::make('secret123'),
        ]);

        Livewire::actingAs($user)
            ->test(SettingsPage::class)
            ->call('openPhoneModal')
            ->set('newPhone', '90 999 88 77')
            ->set('currentPassword', 'secret123')
            ->call('requestPhoneChange')
            ->assertHasNoErrors()
            ->assertSet('showPhoneModal', false);

        $this->assertEquals('+998909998877', $user->fresh()->phone);
    }

    public function test_phone_change_with_telegram_requires_6_digit_code(): void
    {
        \Illuminate\Support\Facades\Http::fake([
            'https://api.telegram.org/*' => \Illuminate\Support\Facades\Http::response(['ok' => true, 'result' => []], 200),
        ]);

        $user = User::factory()->create([
            'phone'       => '+998901112233',
            'telegram_id' => 123456789,
            'password'    => Hash::make('secret123'),
        ]);

        // 1-qadam: requestPhoneChange chaqirilganda keshga kod tushadi
        $component = Livewire::actingAs($user)
            ->test(SettingsPage::class)
            ->call('openPhoneModal')
            ->set('newPhone', '90 999 88 77')
            ->set('currentPassword', 'secret123')
            ->call('requestPhoneChange')
            ->assertHasNoErrors()
            ->assertSet('codeSent', true);

        $cached = Cache::get("phone_change_" . $user->id);
        $this->assertNotNull($cached);
        $this->assertEquals('+998909998877', $cached['phone']);
        $this->assertNotEmpty($cached['code']);

        // Noto'g'ri kod bilan urinish
        $component->set('verificationCode', '000000')
            ->call('confirmPhoneChange')
            ->assertHasErrors(['verificationCode']);

        $this->assertEquals('+998901112233', $user->fresh()->phone);

        // To'g'ri kod bilan tasdiqlash
        $component->set('verificationCode', $cached['code'])
            ->call('confirmPhoneChange')
            ->assertHasNoErrors()
            ->assertSet('showPhoneModal', false);

        $this->assertEquals('+998909998877', $user->fresh()->phone);
    }
}
