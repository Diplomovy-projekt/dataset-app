<?php

namespace App\Livewire\Forms;

use App\Configs\AppConfig;
use App\Models\Invitation;
use App\Models\User;
use App\Utils\Util;
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
    public $agreed = false;

    public function mount($token)
    {
        $this->canRender();
    }
    #[Layout('layouts.clean')]
    public function render()
    {
        return view('livewire.forms.register');
    }

    private function canRender()
    {
        $invitation = Invitation::notExpired()->where('token', $this->token)->first();
        if(!$invitation || $invitation->used){
            abort(404);
        }
    }

    public function register()
    {
        $this->validate([
            'name' => 'required',
            'password' => 'required|confirmed',
            'agreed' => 'accepted',
        ]);

        try {
            $invitation = Invitation::where('token', $this->token)
                ->notUsed()
                ->notExpired()
                ->firstOrFail();

            DB::beginTransaction();

            $user = User::create([
                'name' => $this->name,
                'email' => $invitation->email,
                'password' => Hash::make($this->password),
                'role' => $invitation->role,
                'is_active' => true,
            ]);

            $invitation->used = true;
            $invitation->save();

            auth()->login($user);

            DB::commit();

            $this->dispatch('flash-msg', type: 'success', message: 'Registered successfully');
            return redirect()->route('profile');
        } catch (\Exception $e) {
            Util::logException($e, 'register in Register form');
            DB::rollBack();
            $this->dispatch('flash-msg', type: 'error', message: 'Failed to register');
        }
    }

}
