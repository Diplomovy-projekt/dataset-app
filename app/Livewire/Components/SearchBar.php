<?php

namespace App\Livewire\Components;

use Livewire\Component;

class SearchBar extends Component
{
    public $searchTerm;
    public function render()
    {
        return view('livewire.components.search-bar');
    }
}
