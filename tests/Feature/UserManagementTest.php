<?php

namespace Tests\Feature;

use App\Livewire\Forms\InviteUser;
use App\Livewire\FullPages\AdminUsers;
use App\Mail\UserInvitationMail;
use App\Models\Dataset;
use App\Models\Invitation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;
    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpStorage(false);
    }
    /**
     * A basic feature test example.
     */
    public function test_invite_user()
    {
        Mail::fake();
        $admin = \App\Models\User::where('role', 'admin')->first();
        $this->actingAs($admin);
        // Example invitation data
        $email = 'testuser@example.com'; // Replace with the email you're testing
        $role = 'user'; // Replace with the role you want to test


        Livewire::test(InviteUser::class)
            ->set('email', $email)
            ->set('role', $role)
            ->call('sendInvitation')
            ->assertDispatched('flash-msg',
                type: 'success',
                message: 'Invitation email sent successfully.');


        // Assert that the invitation is in the database
        $this->assertDatabaseHas('invitations', [
            'email' => $email,
            'role' => $role,
        ]);

        // Assert that an email was sent
        Mail::assertSent(UserInvitationMail::class, function ($mail) use ($email) {
            return $mail->hasTo($email); // Assert that the mail was sent to the expected email address
        });
    }

    public function test_delete_user()
    {
        $this->importDataset('public');
        $admin = \App\Models\User::where('role', 'admin')->first();
        $admin2 = \App\Models\User::where('role', 'admin')->where('id', '!=', $admin->id)->first();
        $dataset = Dataset::first();

        $this->assertEquals($dataset->user_id, $admin->id);
        $this->actingAs($admin2);
        Livewire::test(AdminUsers::class)
            ->call('deleteUser', $admin->id, $admin2->id)
            ->assertDispatched('flash-msg',
                type: 'success',
                message: 'User deleted successfully!');

        $dataset = Dataset::first();
        $this->assertEquals($dataset->user_id, $admin2->id);
        $this->assertDatabaseMissing('users', [
            'id' => $admin->id,
        ]);
    }

    public function test_update_role()
    {
        $admin = \App\Models\User::where('role', 'admin')->first();
        $admin2 = \App\Models\User::where('role', 'admin')->where('id', '!=', $admin->id)->first();

        $this->actingAs($admin);
        Livewire::test(AdminUsers::class)
            ->call('updateRole', $admin2->id, 'user')
            ->assertDispatched('flash-msg',
                type: 'success',
                message: 'Role updated successfully!');

        $user = \App\Models\User::find($admin2->id);
        $this->assertEquals('user', $user->role);
    }
    public function test_deactivate_user()
    {
        $admin = \App\Models\User::where('role', 'admin')->first();
        $admin2 = \App\Models\User::where('role', 'admin')->where('id', '!=', $admin->id)->first();

        $this->actingAs($admin);
        Livewire::test(AdminUsers::class)
            ->call('toggleActiveUser', $admin2->id)
            ->assertDispatched('flash-msg',
                type: 'success',
                message: 'Action completed successfully!');

        $user = \App\Models\User::find($admin2->id);
        $this->assertEquals($admin2->is_active, !$user->is_active);
    }
    public function test_cancel_invitation()
    {
        $admin = \App\Models\User::where('role', 'admin')->first();
        $this->actingAs($admin);
        $invitation = Invitation::create([
            'email' => "text@example.com",
            'role' => 'user',
            'token' => "token1",
            'invited_by' => $admin->id,
        ]);
        Livewire::test(AdminUsers::class)
            ->call('cancelInvitation', $invitation->id)
            ->assertDispatched('flash-msg',
                type: 'success',
                message: 'Invitation cancelled successfully!');

        $this->assertDatabaseMissing('invitations', [
            'id' => $invitation->id,
        ]);
    }

    public function test_resend_invitation()
    {
        $admin = \App\Models\User::where('role', 'admin')->first();
        $this->actingAs($admin);
        $invitation = Invitation::create([
            'email' => "text@example.com",
            'role' => 'user',
            'token' => "token1",
            'invited_by' => $admin->id,
        ]);
        Livewire::test(AdminUsers::class)
            ->call('resendInvitation', $invitation->id)
            ->assertDispatched('flash-msg',
                type: 'success',
                message: 'Invitation resent successfully!');

        $updatedInvitation = Invitation::find($invitation->id);
        $this->assertNotEquals($invitation->token, $updatedInvitation->token);
    }
}
