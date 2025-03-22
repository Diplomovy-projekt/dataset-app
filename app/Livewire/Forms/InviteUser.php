<?php

namespace App\Livewire\Forms;

use App\Mail\UserInvitationMail;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Livewire\Component;

class InviteUser extends Component
{
    public string $email;
    public string $role = 'user';
    public string $emailAlreadySentMsg = '';

    public function render()
    {
        return view('livewire.forms.invite-user');
    }

    public function sendInvitation()
    {
        $this->validate([
            'email' => 'required|email',
            'role' => 'required|in:admin,user',
        ]);

        if ($this->checkIfUserExists()) {
            return;
        }

        $existingInvitation = Invitation::where('email', $this->email)->first();

        if ($existingInvitation) {
            if ($existingInvitation->used) {
                $this->dispatch('flash-msg', type: 'error', message: 'This invitation has already been used.');
                return;
            }

            $this->emailAlreadySentMsg = "An invitation email was already sent. Do you want to resend it?";
            return;
        }

        $this->sendNewInvitation();
    }

    public function resendInvitation()
    {
        $existingInvitation = Invitation::notUsed()->where('email', $this->email)->first();

        if ($existingInvitation) {
            $this->sendNewInvitation($existingInvitation);
        }
    }

    private function sendNewInvitation($existingInvitation = null)
    {
        try {
            $token = Str::random(64);

            if ($existingInvitation) {
                $existingInvitation->update(['token' => $token]);
            } else {
                $existingInvitation = Invitation::create([
                    'email' => $this->email,
                    'role' => $this->role,
                    'token' => $token,
                ]);
            }

            Mail::to($this->email)->send(new UserInvitationMail($existingInvitation));

            $this->dispatch('flash-msg', type: 'success', message: 'Invitation email sent successfully.');
            $this->reset();
        } catch (\Exception $e) {
            $this->dispatch('flash-msg', type: 'error', message: 'Failed to send invitation email. ' . $e->getMessage());
        }
    }

    private function checkIfUserExists()
    {
        if (User::where('email', $this->email)->exists()) {
            $this->dispatch('flash-msg', type: 'error', message: 'User already exists.');
            $this->reset();
            return true;
        }
        return false;
    }
}
