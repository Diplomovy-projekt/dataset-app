<?php

namespace App\Livewire\FullPages;

use App\ImageProcessing\DatasetImageProcessor;
use App\Models\Dataset;
use Livewire\Component;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class Profile extends Component
{
    public $datasets;
    public function render()
    {

        $imgProcessor = new DatasetImageProcessor();
        $imgProcesses = $imgProcessor->processImages('0193e0701494-0125-7f45-92e2-8dca8d79a659619dfad8');

        $this->loadDatasets();
        return view('livewire.full-pages.profile');
    }

    public function loadDatasets()
    {
        $this->datasets = Dataset::all();
    }
}
