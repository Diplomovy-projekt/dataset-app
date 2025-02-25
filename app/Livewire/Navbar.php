<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Session;
use Livewire\Component;

class Navbar extends Component
{
    public function render()
    {
        return view('livewire.navbar');
    }

    public function logout()
    {
        auth()->logout();
        Session::invalidate();
        Session::regenerateToken();

        return redirect()->route('login');
    }

}
