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
            'username' => 'test_user_unique_1',
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
        User::factory()->create(['username' => 'taken_email_user', 'email' => 'taken@gmail.com']);
        $user = User::factory()->create(['username' => 'my_email_user', 'email' => 'my@gmail.com']);

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
            'username' => 'phone_user_1',
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
            'username'    => 'phone_user_2',
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
            'username'    => 'phone_user_3',
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

    public function test_users_can_have_identical_names_non_unique(): void
    {
        User::factory()->create(['name' => 'Shamsiddin', 'username' => 'user_one']);
        $user2 = User::factory()->create(['name' => 'Ali', 'username' => 'user_two']);

        // User2 ismini ham "Shamsiddin" ga o'zgartira olishi kerak (Ism unique bo'lmasligi kerak)
        Livewire::actingAs($user2)
            ->test(SettingsPage::class)
            ->set('name', 'Shamsiddin')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertEquals('Shamsiddin', $user2->fresh()->name);
    }

    public function test_users_cannot_have_duplicate_username(): void
    {
        User::factory()->create(['username' => 'kitobxon_botir']);
        $user2 = User::factory()->create(['username' => 'kitobxon_anvar']);

        // User2 boshqa foydalanuvchining usernamesini ololmasligi kerak
        Livewire::actingAs($user2)
            ->test(SettingsPage::class)
            ->set('username', 'kitobxon_botir')
            ->call('save')
            ->assertHasErrors(['username']);

        $this->assertEquals('kitobxon_anvar', $user2->fresh()->username);
    }

    public function test_username_uniqueness_is_case_insensitive(): void
    {
        User::factory()->create(['username' => 'sarvar_99']);
        $user2 = User::factory()->create(['username' => 'dilshod_01']);

        // Katta harflar bilan kiritilganda ham duplicate aniqlanishi shart
        Livewire::actingAs($user2)
            ->test(SettingsPage::class)
            ->set('username', 'SARVAR_99')
            ->call('save')
            ->assertHasErrors(['username']);

        $this->assertEquals('dilshod_01', $user2->fresh()->username);
    }

    public function test_registration_with_duplicate_username_fails_and_same_name_passes(): void
    {
        User::factory()->create([
            'name'     => 'Shamsiddin',
            'username' => 'shamsiddin_99',
            'phone'    => '+998901111111',
        ]);

        // 1. Bir xil username bilan ro'yxatdan o'tish rad etilishi kerak
        $response = $this->post('/register', [
            'name'                  => 'Shamsiddin Boshqa',
            'username'              => 'shamsiddin_99',
            'phone'                 => '+998902222222',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors(['username']);

        // 2. Bir xil ism bilan (lekin boshqa username bilan) ro'yxatdan o'tish muvaffaqiyatli bo'lishi kerak
        $response2 = $this->post('/register', [
            'name'                  => 'Shamsiddin', // aynan bir xil ism!
            'username'              => 'shamsiddin_yangi',
            'phone'                 => '+998903333333',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response2->assertSessionHasNoErrors();
    }
}
