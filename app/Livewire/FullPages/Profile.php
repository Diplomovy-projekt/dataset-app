<?php

namespace App\Livewire\FullPages;

use App\ImageService\DatasetImageProcessor;
use App\Models\Dataset;
use Livewire\Component;

class Profile extends Component
{
    public $datasets;
    public function render()
    {

        //$imgProcessor = new DatasetImageProcessor();
        //$imgProcesses = $imgProcessor->createClassCropsForNewDataset('0193e9e1f169-0e05-76a2-aca1-8bacbc0dea97f26dac78');

        $this->loadDatasets();
        return view('livewire.full-pages.profile');
    }

    public function loadDatasets()
    {
        $this->datasets = Dataset::all();
    }
}
