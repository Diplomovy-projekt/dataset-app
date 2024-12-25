<?php

namespace App\Livewire\FullPages;

use App\ImageService\ImageRendering;
use App\Models\Dataset;
use Livewire\Component;

class DatasetIndex extends Component
{
    use ImageRendering;
    public $datasets;

    public function render()
    {
        $this->loadDatasets();
        return view('livewire.full-pages.dataset-index');
    }
    public function loadDatasets()
    {
        $datasets = Dataset::with(['images' => function ($query) {
            $query->limit(1);
        }])->with('classes')->get();
        foreach($datasets as $key => $dataset){
            $classes = $this->addColorsAndStateToClasses($dataset->classes);
            $processedImage = $this->prepareImagesForSvgRendering($dataset->images->first(), $classes);
            $datasets[$key]['images'] = $processedImage;
        }
        $this->datasets = $datasets->toArray();
    }
}
