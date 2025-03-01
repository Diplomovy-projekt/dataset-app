<?php

namespace App\Livewire\FullPages;

use App\Configs\AppConfig;
use App\DatasetActions\DatasetActions;
use App\DatasetCrud\DatasetCrud;
use App\ExportService\ExportService;
use App\ImageService\ImageProcessor;
use App\ImageService\ImageRendering;
use App\Jobs\DeleteTempFile;
use App\Models\Dataset;
use App\Models\Image;
use App\Utils\QueryUtil;
use Livewire\Component;

class Profile extends Component
{
    use ImageRendering;
    public $datasets;
    public function render(ExportService $es)
    {
        $classIds = [10, 11, 12, 13, 14, 15, 16, 17, 18];
        $this->selectedDatasets = [4, 5, 6];

        $query = Image::whereIn('dataset_id', array_keys(array_filter($this->selectedDatasets)))
            ->whereNotIn('id', $this->selectedImages ?? [])
            // Only include images that has annotations with selected classes
            ->whereHas('annotations.class', function ($query) use ($classIds) {
                $query->whereIn('id', $classIds);
            })
            // Include only annotations with selected classes for the images
            ->with(['annotations' => function ($query) use ($classIds) {
                $query->whereIn('annotation_class_id', $classIds);
            }, 'annotations.class']);

        $bindings = $query->getBindings();
        $this->loadDatasets();
        return view('livewire.full-pages.profile');
    }

    private function loadDatasets()
    {
        $datasets = Dataset::where('user_id', auth()->id()) // Filter datasets by the authenticated user's ID
        ->with([
            'images' => function ($query) {
                $query->limit(1)->with(['annotations.class']);
            },
        ])
            ->with('classes')
            ->get();
        foreach($datasets as $key => $dataset){

            if($dataset->images->isEmpty()){
                $dataset->thumbnail = "placeholder-image.png";
                continue;
            }
            $dataset->thumbnail = "storage/datasets/{$dataset->unique_name}/thumbnails/{$dataset->images->first()->filename}";
            $processedImage = $this->prepareImagesForSvgRendering($dataset->images->first());
            //$datasets[$key]['images'] = $processedImage;
            $dataset->images = $processedImage;
            $dataset->stats = $dataset->getStats();
        }
        $this->datasets = $datasets->toArray();
    }
}
