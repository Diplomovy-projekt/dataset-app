<?php

namespace App\Livewire\Forms;

use App\Configs\AppConfig;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Register extends Component
{
    public string $token = '';
    public string $name = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function mount($token)
    {
        $this->canRender();
    }
    #[Layout('layouts.clean')]
    public function render()
    {
        return view('livewire.forms.register');
    }

    public function canRender()
    {
        $invitation = Invitation::where('token', $this->token)->first();
        if(!$invitation || $invitation->used){
            abort(404);
        }
    }

    public function register()
    {
        $this->validate([
            'name' => 'required',
            'password' => 'required|confirmed',
        ]);

        try {
            $invitation = Invitation::where('token', $this->token)->firstOrFail();
            if($invitation->created_at->addHours(AppConfig::URL_EXPIRATION)->isPast()){
                $invitation->delete();
                session()->flash('error', 'Invitation link has expired.');
                return redirect()->route('login');
            }

            DB::beginTransaction();
            $user = User::create([
                'name' => $this->name,
                'email' => $invitation->email,
                'password' => Hash::make($this->password),
                'role' => $invitation->role,
            ]);
            $invitation->used = true;
            $invitation->save();
            auth()->login($user);
            DB::commit();

            return redirect()->route('profile');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Failed to register. Please try again.');
        }
    }

}
