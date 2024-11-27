<?php

namespace App\Livewire\FullPages;

use App\Models\Dataset;
use Livewire\Component;

class Welcome extends Component
{
    public $datasets;
    public function render()
    {
        $this->loadDatasets();
        return view('livewire.full-pages.welcome');
    }

    public function loadDatasets()
    {
        $this->datasets = Dataset::all();
    }
}
