<?php

namespace App\Livewire\FullPages;

use App\DatasetCrud\DatasetCrud;
use App\ImageService\ImageProcessor;
use App\ImageService\ImageRendering;
use App\Models\Dataset;
use App\Utils\QueryHelper;
use Livewire\Component;

class Profile extends Component
{
    use ImageRendering;
    public $datasets;
    public function render()
    {
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

    public function deleteDataset(DatasetCrud $datasetService, $id)
    {
        $result = $datasetService->deleteDataset($id);
        if($result->isSuccessful()){
            $this->loadDatasets();
        }

    }
}
