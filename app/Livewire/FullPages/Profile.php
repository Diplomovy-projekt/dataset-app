<?php

namespace App\Livewire\FullPages;

use App\Models\Dataset;
use Livewire\Component;

class Profile extends Component
{
    public $datasets;
    public function render()
    {
        $this->loadDatasets();
        return view('livewire.full-pages.profile');
    }

    public function loadDatasets()
    {
        $this->datasets = Dataset::all();
    }
}
