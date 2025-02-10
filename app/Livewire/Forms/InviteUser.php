<?php

namespace App\Livewire\Forms;

use App\Configs\AppConfig;
use App\Mail\UserInvitationMail;
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

        $password = Str::random(12);

        try {
            /*$user = User::create([
                'name' => $this->email,
                'email' => $this->email,
                'password' => Hash::make($password),
                'role' => $this->role,
            ]);*/
            Mail::to($this->email)->send(new UserInvitationMail($this->email, $password, $this->role));
            session()->flash('success', 'Invitation sent successfully!');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to send invitation. Please try again.');
        }
        $this->reset();

    }
}
