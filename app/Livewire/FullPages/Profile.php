<?php

namespace App\Livewire\FullPages;

use App\ImageService\DatasetImageProcessor;
use App\ImageService\ImageRendering;
use App\Models\Dataset;
use Livewire\Component;

class Profile extends Component
{
    use ImageRendering;
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
