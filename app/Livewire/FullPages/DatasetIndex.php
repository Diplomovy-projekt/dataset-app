<?php

namespace App\Livewire\FullPages;

use App\ImageService\ImageRendering;
use App\Models\Dataset;
use Livewire\Component;

class DatasetIndex extends Component
{
    use ImageRendering;
    public $datasets;
    public $searchTerm;

    public function render()
    {
        $this->loadDatasets();
        return view('livewire.full-pages.dataset-index');
    }

    public function loadDatasets()
    {
        $datasets = Dataset::with([
            'images' => function ($query) {
                $query->limit(1)->with(['annotations.class']);
            },
        ])->with('classes')->get();
        foreach($datasets as $key => $dataset){

            if($dataset->images->isEmpty()){
                $dataset->thumbnail = "placeholder-image.png";
                continue;
            }
            $dataset->thumbnail = "storage/datasets/{$dataset->unique_name}/thumbnails/{$dataset->images->first()->filename}";
            $processedImage = $this->prepareImagesForSvgRendering($dataset->images->first());
            $dataset->images = $processedImage;
            $dataset->stats = $dataset->getStats();
        }
        $this->datasets = $datasets->toArray();
    }
}
