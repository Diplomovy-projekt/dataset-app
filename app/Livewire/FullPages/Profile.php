<?php

namespace App\Livewire\FullPages;

use App\DatasetActions\DatasetActions;
use App\DatasetCrud\DatasetCrud;
use App\ImageService\ImageProcessor;
use App\ImageService\ImageRendering;
use App\Models\Dataset;
use App\Utils\QueryUtil;
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
