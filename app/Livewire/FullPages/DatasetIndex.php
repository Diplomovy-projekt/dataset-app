<?php

namespace App\Livewire\FullPages;

use App\Models\Dataset;
use Livewire\Component;

class DatasetIndex extends Component
{
    public $datasets;

    public function render()
    {
        $this->loadDatasets();
        return view('livewire.full-pages.dataset-index');
    }
    public function loadDatasets()
    {
        $this->datasets = Dataset::all();
    }
}
