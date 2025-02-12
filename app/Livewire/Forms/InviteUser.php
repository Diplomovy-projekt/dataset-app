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
    public array $authRoles = AppConfig::AUTH_ROLES;

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

        $this->checkExpiredInvite();
        try {
            $token = Str::random(64);
            $invitation = Invitation::create([
                'email' => $this->email,
                'role' => $this->role,
                'token' => $token,
            ]);
            Mail::to($this->email)->send(new UserInvitationMail($invitation));
            session()->flash('success', 'Invitation sent successfully!');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to send invitation. Please try again.');
        }
        $this->reset();

    }

    private function checkExpiredInvite()
    {
        $invitation = Invitation::where('email', $this->email)->first();
        if ($invitation && $invitation->created_at->addHours(AppConfig::URL_EXPIRATION)->isPast()) {
            $invitation->delete();
        }
    }
}
