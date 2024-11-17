<?php

namespace App\Livewire;

use Livewire\Component;

class HelloWorld extends Component
{
    public $count = 0;

    public function render()
    {
        return view('livewire.hello-world')
            ->layout('layouts.guest'); // Specify layout
    }


    public function increment()
    {
        $this->count++;
    }

    public function decrement()
    {
        $this->count--;
    }
}
