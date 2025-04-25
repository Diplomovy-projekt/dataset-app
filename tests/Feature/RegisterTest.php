<?php

namespace Tests\Feature;

use App\Models\Invitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_component_renders()
    {
        $invitation = Invitation::factory()->create([
            'token' => 'valid-token',
            'email' => 'test@example.com',
        ]);

        Livewire::test('forms.register', ['token' => 'valid-token'])
            ->assertSee('Complete Registration')
            ->assertSet('token', 'valid-token');
    }

    public function test_successful_registration()
    {
        // Create the invitation
        $invitation = Invitation::factory()->create([
            'token' => 'valid-token',
            'email' => 'test@example.com',
        ]);

        // Test the registration flow via Livewire
        Livewire::test('forms.register', ['token' => 'valid-token'])
            ->set('name', 'Test User')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->set('agreed', true)
            ->call('register')
            ->assertRedirect(route('profile'));

        // Check if the user was created in the database
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com', // Ensure the user is registered with the correct email
            'name' => 'Test User',
            'is_active' => true,
        ]);

        // Check that the password is hashed
        $user = \App\Models\User::where('email', 'test@example.com')->first();
        $this->assertTrue(Hash::check('password123', $user->password));
    }


    public function test_invalid_registration_due_to_token()
    {
        Livewire::test('forms.register', ['token' => 'valid-token'])
            ->assertStatus(404);
    }

    public function test_registration_with_invalid_input()
    {
        $invitation = Invitation::factory()->create([
            'token' => 'valid-token',
            'email' => 'test@example.com',
        ]);

        Livewire::test('forms.register', ['token' => 'valid-token'])
            ->set('name', '')
            ->set('password', 'password123')
            ->set('password_confirmation', 'wrong-password')
            ->set('agreed', false)
            ->call('register')
            ->assertHasErrors(['name', 'password', 'agreed']);
    }

}
