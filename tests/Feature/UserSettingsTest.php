<?php

namespace Tests\Feature;

use App\Livewire\Actions\Logout;
use App\Livewire\FullPages\AdminDashboard;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class UserSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpStorage(false);
    }

    public function test_update_password()
    {
        $user = User::factory()->create([
            'password' => Hash::make('current-password'),
        ]);
        Auth::login($user);

        // Test the component
        Livewire::test('profile.update-password-form')
            ->set('current_password', 'current-password')
            ->set('password', 'new-password')
            ->set('password_confirmation', 'new-password')
            ->call('updatePassword')
            ->assertDispatched('password-updated');

        // Verify the password was updated
        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
    }
    public function test_password_update_fails_with_incorrect_current_password(): void
    {
        // Create and login a user
        $user = User::factory()->create([
            'password' => Hash::make('correct-password'),
        ]);
        Auth::login($user);

        // Original password hash to compare later
        $originalPasswordHash = $user->password;

        // Test with incorrect current password
        Livewire::test('profile.update-password-form')
            ->set('current_password', 'wrong-password')
            ->set('password', 'new-password')
            ->set('password_confirmation', 'new-password')
            ->call('updatePassword')
            ->assertHasErrors(['current_password']);

        // Verify password was not changed in database
        $this->assertEquals($originalPasswordHash, $user->fresh()->password);
    }

    public function test_password_update_fails_when_confirmation_doesnt_match(): void
    {
        // Create and login a user
        $user = User::factory()->create([
            'password' => Hash::make('current-password'),
        ]);
        Auth::login($user);

        // Original password hash to compare later
        $originalPasswordHash = $user->password;

        // Test with mismatched password confirmation
        Livewire::test('profile.update-password-form')
            ->set('current_password', 'current-password')
            ->set('password', 'new-password')
            ->set('password_confirmation', 'different-password')
            ->call('updatePassword')
            ->assertHasErrors(['password']);

        // Verify password was not changed in database
        $this->assertEquals($originalPasswordHash, $user->fresh()->password);
    }

    public function test_user_can_be_deactivated(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
            'is_active' => true
        ]);
        Auth::login($user);

        $mockLogout = new class() extends Logout {
            public $wasCalled = false;

            public function __invoke(): void
            {
                $this->wasCalled = true;
                Auth::logout();
            }
        };

        $component = Livewire::test('profile.delete-user-form')
            ->set('password', 'password123')
            ->call('deactivateUser', $mockLogout);

        $this->assertTrue($mockLogout->wasCalled);

        $this->assertNull(Auth::user());

        $this->assertEquals(0, $user->fresh()->is_active);

        $component->assertRedirect();
    }
}
