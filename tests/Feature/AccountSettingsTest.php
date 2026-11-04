<?php

namespace Tests\Feature;

use App\Livewire\AccountSettings;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class AccountSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_account_settings(): void
    {
        $response = $this->get('/account');
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_account_settings(): void
    {
        $user = User::factory()->create([
            'name' => 'Robert Hofer',
            'email' => 'robert@test.ch',
            'role' => 'admin',
        ]);

        $response = $this->actingAs($user)->get('/account');
        $response->assertStatus(200);
        $response->assertSee('Mein Konto', false);
        $response->assertSee('Dein Konto', false);
        $response->assertSee('Account-Informationen', false);
        $response->assertSee('Robert Hofer', false);
    }

    public function test_user_can_update_personal_information(): void
    {
        $user = User::factory()->create([
            'name' => 'Robert Hofer',
            'email' => 'robert@test.ch',
        ]);

        Livewire::actingAs($user)
            ->test(AccountSettings::class)
            ->set('tab', 'personal')
            ->set('name', 'Robert Hofer (Zürich)')
            ->set('email', 'robert.neu@test.ch')
            ->call('updateProfile')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Robert Hofer (Zürich)',
            'email' => 'robert.neu@test.ch',
        ]);
    }

    public function test_user_can_update_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('old-secret-123'),
        ]);

        Livewire::actingAs($user)
            ->test(AccountSettings::class)
            ->set('tab', 'password')
            ->set('current_password', 'old-secret-123')
            ->set('new_password', 'new-super-secret-456')
            ->set('new_password_confirmation', 'new-super-secret-456')
            ->call('updatePassword')
            ->assertHasNoErrors();

        $user->refresh();
        $this->assertTrue(Hash::check('new-super-secret-456', $user->password));
    }

    public function test_password_update_fails_with_wrong_current_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('old-secret-123'),
        ]);

        Livewire::actingAs($user)
            ->test(AccountSettings::class)
            ->set('tab', 'password')
            ->set('current_password', 'wrong-password')
            ->set('new_password', 'new-super-secret-456')
            ->set('new_password_confirmation', 'new-super-secret-456')
            ->call('updatePassword')
            ->assertHasErrors(['current_password']);
    }
}
