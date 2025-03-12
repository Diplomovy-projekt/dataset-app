<?php

namespace App\Livewire\Forms;

use App\Configs\AppConfig;
use App\Mail\UserInvitationMail;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Livewire\Component;

class InviteUser extends Component
{

    public string $email;
    public string $role = 'user';

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

        $this->deleteExpiredInvite();

        try {
            $token = Str::random(64);
            $invitation = Invitation::create([
                'email' => $this->email,
                'role' => $this->role,
                'token' => $token,
            ]);
            Mail::to($this->email)->send(new UserInvitationMail($invitation));
            $this->dispatch('flash-msg', type: 'success', message: 'Invitation email sent successfully.');
            $this->reset();
        } catch (\Exception $e) {
            $this->dispatch('flash-msg', [
                'type' => 'error',
                'message' => 'Failed to send invitation email.' . $e->getMessage()
            ]);
        }

    }

    private function deleteExpiredInvite()
    {
        $invitation = Invitation::where('email', $this->email)->expired()->first();
        if($invitation){
            $invitation->delete();
        }
    }

    private function checkIfUserExists()
    {
        if (User::where('email', $this->email)->exists()) {
            $this->dispatch('flash-msg', type: 'error', message: 'User already exists.');
            $this->reset();
            return true; // Indicate that user exists
        }
        return false;
    }
}
