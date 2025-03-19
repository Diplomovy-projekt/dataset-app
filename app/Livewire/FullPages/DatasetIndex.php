<?php

namespace App\Livewire\FullPages;

use App\ImageService\ImageRendering;
use App\ImageService\MyImageManager;
use App\Models\Dataset;
use Intervention\Image\ImageManager;
use Livewire\Component;
use Intervention\Image\Drivers\Vips\Driver as VipsDriver;

class DatasetIndex extends Component
{
    use ImageRendering;
    public $datasets;
    public $searchTerm;

    public function mount()
    {
        $ids = Dataset::approved()->pluck('id');
        $this->loadDatasets($ids);
    }

    public function render()
    {
        return view('livewire.full-pages.dataset-index');
    }

    public function loadDatasets($ids)
    {
        $datasets = Dataset::approved()->whereIn('id', $ids)->with([
            'images' => function ($query) {
                $query->limit(1)->with(['annotations.class']);
            },
        ])->with('classes')->get();
        foreach($datasets as $key => $dataset){

            if($dataset->images->isEmpty()){
                $dataset->thumbnail = "placeholder-image.png";
                continue;
            }
            $processedImage = $this->prepareImagesForSvgRendering($dataset->images->first());
            $dataset->images = $processedImage;
            $dataset->stats = $dataset->getStats();
        }
        $this->datasets = $datasets->toArray();
    }

    public function updatedSearchTerm()
    {
        $ids = Dataset::approved()->where('display_name', 'like', '%' . $this->searchTerm . '%')->pluck('id');
        $this->loadDatasets($ids);
    }
}
